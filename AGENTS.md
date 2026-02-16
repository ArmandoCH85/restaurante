# AGENTS.md - Restaurante Project Guide

This document provides essential information for agentic coding agents working in this repository.

## Project Overview

A restaurant management system built with **Laravel 12** (PHP 8.2), **Filament 3**, **Livewire**, and **Tailwind CSS**. Features include POS, table management, delivery tracking, and SUNAT electronic invoicing (Peru).

## Build/Lint/Test Commands

### Development Server
```bash
composer run dev           # Serve app + queue + Vite concurrently
php artisan serve          # Start Laravel dev server only
php artisan queue:listen   # Start queue worker
npm run dev                # Start Vite for frontend assets
```

### Build for Production
```bash
npm run build              # Build frontend assets with Vite
composer update            # Updates dependencies (triggers filament:upgrade)
```

### Database
```bash
php artisan migrate        # Run migrations
php artisan db:seed        # Run seeders
php artisan migrate:fresh --seed  # Reset and seed database
```

### Code Style & Linting
```bash
./vendor/bin/pint          # Run Laravel Pint (PHP code style fixer)
./vendor/bin/pint --test   # Check code style without fixing
```

### Testing (Pest)
```bash
php artisan test                        # Run all tests
php artisan test --filter=TestName     # Run specific test class
php artisan test path/to/Test.php      # Run single test file
./vendor/bin/pest                       # Run Pest directly
./vendor/bin/pest --filter="test_name" # Run test by name pattern
```

### Cache & Optimization
```bash
php artisan optimize:clear  # Clear all caches
php artisan filament:optimize-clear  # Clear Filament caches
php artisan config:clear    # Clear config cache
php artisan route:clear     # Clear route cache
```

## Code Style Guidelines

### PHP Formatting
- Use Laravel Pint for automatic code formatting
- Follow PSR-12 coding standards
- Use strict typing where applicable: `declare(strict_types=1);`
- Use typed properties and return types

### Imports
```php
use App\Models\Order;                           // Models first
use App\Services\SunatService;                  // Services second
use App\Events\PaymentRegistered;               // Events third
use Illuminate\Support\Facades\Log;             // Illuminate facades
use Illuminate\Database\Eloquent\Relations\BelongsTo;  // Relations
use Filament\Tables;                            // Filament last
```

### Naming Conventions
- **Models**: PascalCase singular (`Order`, `Invoice`, `CashRegister`)
- **Controllers**: PascalCase + Controller (`PosController`)
- **Services**: PascalCase + Service (`SunatService`)
- **Traits**: PascalCase (`CalculatesIgv`)
- **Enums**: PascalCase singular (`InvoiceStatusEnum`)
- **Database columns**: snake_case (`order_datetime`, `cash_register_id`)
- **Methods**: camelCase (`getTotalPaid()`, `recalculateTotals()`)
- **Constants**: UPPER_SNAKE_CASE (`STATUS_OPEN`, `METHOD_CASH`)

### Models
```php
class Order extends Model
{
    use CalculatesIgv;  // Traits at the top
    
    protected $with = ['customer', 'table'];  // Eager loading
    
    const STATUS_OPEN = 'open';  // Constants
    
    protected $fillable = [...];  // Mass assignment
    
    protected $casts = [         // Type casting
        'order_datetime' => 'datetime',
        'total' => 'decimal:2',
        'billed' => 'boolean',
    ];
    
    public function customer(): BelongsTo  // Relationships with return type
    {
        return $this->belongsTo(Customer::class);
    }
}
```

### Filament Resources
```php
class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Reportes';
    
    public static function table(Table $table): Table
    {
        return $table->columns([...])->filters([...])->actions([...]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customer']);
    }
}
```

### Enums
```php
enum InvoiceStatusEnum: string
{
    case PAID = 'paid';
    case PENDING = 'pending';
    
    public function getLabel(): string
    {
        return match ($this) {
            self::PAID => 'Pagado',
            self::PENDING => 'Pendiente',
        };
    }
}
```

### Error Handling
```php
// Use try-catch with logging in services
try {
    // operation
} catch (\Exception $e) {
    Log::error('Operation failed', [
        'context' => $context,
        'error' => $e->getMessage(),
    ]);
    throw $e;  // Re-throw or handle gracefully
}

// Use transactions for database operations
return DB::transaction(function () use ($data) {
    // database operations
});
```

### Logging
```php
Log::info('Operation successful', ['id' => $id]);
Log::warning('Unexpected state', ['context' => $data]);
Log::error('Critical failure', ['error' => $e->getMessage()]);
```

## Architecture

### Key Directories
- `app/Models/` - Eloquent models with relationships and business logic
- `app/Services/` - Business logic services (`SunatService`, `RucLookupService`)
- `app/Filament/Resources/` - Filament admin resources
- `app/Filament/Widgets/` - Dashboard widgets
- `app/Livewire/` - Livewire components
- `app/Events/` - Event classes
- `app/Listeners/` - Event listeners
- `app/Enums/` - PHP Enums
- `app/Traits/` - Reusable traits (`CalculatesIgv`)
- `app/Http/Middleware/` - Custom middleware

### Middleware Aliases (bootstrap/app.php)
- `role` - Spatie role middleware
- `permission` - Spatie permission middleware
- `pos.access` - POS access control
- `tables.access` - Table management access
- `delivery.access` - Delivery module access

### Route Protection
```php
Route::middleware(['auth', 'pos.access', 'role_or_permission:cashier|pos.access'])
    ->group(function () {
        Route::get('/pos', [PosController::class, 'index']);
    });
```

## Key Domain Concepts

### IGV (Peruvian Tax)
- All prices in the system **include IGV** (18% by default)
- Use `CalculatesIgv` trait for tax calculations
- IGV rate configurable via `AppSetting::getSetting('FacturacionElectronica', 'igv_percent')`

### SUNAT Electronic Invoicing
- Uses `greenter/lite` library
- Configuration in `app/Services/SunatService.php`
- Environment settings: `AppSetting::getSetting('FacturacionElectronica', 'environment')`

## Important Rules

1. **Never commit secrets** - Use `.env` for sensitive configuration
2. **Avoid N+1 queries** - Use eager loading (`$with` property, `with()`, `load()`)
3. **Use Filament patterns** - Tables, Forms, Actions builders
4. **Prefer Livewire events** over inline JavaScript
5. **Use Vite** for frontend assets, not `public/js/`
6. **Follow KISS/YAGNI principles**
7. **Run Pint** after making PHP changes
8. **Test critical paths** with Pest

## Existing Rules (from .github/copilot-instructions.md)

- Respond in Spanish for user-facing messages
- Use Filament 3 and Livewire patterns already in the project
- Protect routes with middleware aliases defined in `bootstrap/app.php`
- Use `getEloquentQuery()` for global scopes in Filament Resources
- Maintain thermal printer compatibility in PDF/print templates
- Do not add compiled assets to `public/js/`; use `resources/js/` with Vite

## File Locations

- Views: `resources/views/`
- Layouts: `resources/views/layouts/{app,pos,tableview}.blade.php`
- PDF templates: `resources/views/pdf/` and `resources/views/print/`
- JavaScript: `resources/js/`
- Configuration: `config/` (especially `config/filament.php`, `config/greenter.php`)
- Migrations: `database/migrations/`
- Seeders: `database/seeders/`
- Factories: `database/factories/`
