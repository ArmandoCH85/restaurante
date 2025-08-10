# Copilot Project Instructions — Restaurante (Laravel 12 + Filament 3)

Idioma y tono
- Responder en español. No inventar; si algo no está claro, pedir la mínima aclaración necesaria.
- Priorizar componentes y patrones de Filament 3 y Livewire ya usados en el proyecto.

Arquitectura y módulos clave
- Stack: Laravel 12 (PHP 8.2), Filament 3, Livewire, Tailwind (Vite), MySQL/PostgreSQL.
- Dominios principales:
  - POS: app/Livewire/Pos/PointOfSale.php y vistas en resources/views/pos.
  - Mapa de Mesas: app/Livewire/TableMap/* (vistas bajo resources/views/livewire/table-map/*).
  - Delivery: app/Livewire/Delivery/* con estados y tracking.
  - Facturación SUNAT: app/Services/SunatService.php usando greenter/lite.
  - Caja/Reportes: Filament Resources/Pages/Widgets bajo app/Filament/**.
- Autorización: Spatie Permission (middlewares alias en bootstrap/app.php) y Filament Shield.

Flujos de desarrollo (local)
- Servir app, cola y Vite juntos: composer run dev
- Migraciones/seeders: php artisan migrate; php artisan db:seed
- Tests (Pest): php artisan test (usa tests/Feature y tests/Unit)
- Assets: npm run dev para desarrollo; npm run build para producción
- Cache/optimizaciones: composer update dispara @php artisan filament:upgrade (post-autoload-dump)

Convenciones del proyecto
- Filament 3:
  - Resources en app/Filament/Resources/** con Pages/RelationManagers; usar Tables/Forms builders.
  - Pages personalizadas en app/Filament/Pages/**; Widgets en app/Filament/Widgets/**.
  - Overrides de vistas en resources/views/vendor/filament/** y vendor/filamentloginscreen/** (mantener compatibilidad en upgrades).
- Livewire:
  - Componentes en app/Livewire/** con vistas Blade en resources/views/livewire/**.
  - Preferir eventos Livewire/Filament Actions en lugar de JS inline pesado.
- Vistas Blade: layouts en resources/views/layouts/{app,pos,tableview}. Centralizar scripts con @push('scripts') y Vite.
- JS: código propio preferentemente en resources/js y servido con Vite; public/js contiene build/terceros (pos-*.js, filament/*).
- Permisos/rutas: proteger rutas con middleware de alias definidos en bootstrap/app.php (role, permission, role_or_permission, pos.access, tables.access, delivery.access, tables.maintenance.access).

Integraciones y puntos críticos
- SUNAT (greenter/lite): variables .env requeridas (SUNAT_RUC, USUARIO_SOL, CLAVE, CERTIFICADO_PATH/PASSWORD, SUNAT_PRODUCTION). Ver docs/SUNAT_CONFIGURATION.md.
- PDFs: barryvdh/laravel-dompdf; plantillas en resources/views/pdf/** y print/**.
- Excel/Reportes: maatwebsite/excel y phpoffice/phpspreadsheet.
- Eventos/Listeners: app/Events/** y app/Listeners/** para recalcular totales, notificaciones de delivery y caja.

Patrones concretos que debes seguir
- Modelos principales en app/Models/** (Order, Invoice, Payment, Table, DeliveryOrder, CashRegister, Product, etc.). Usa relaciones Eloquent y casts ya existentes (Enums como Enums/InvoiceStatusEnum.php cuando aplique).
- En Filament Resources, usa getEloquentQuery() para scopes globales; respeta Policies/Shield.
- En vistas de impresión (pdf/print), mantener estilos mínimos y consistentes por las impresoras térmicas (ver docs/THERMAL_PRINTING_*.md).
- No añadas assets compilados nuevos en public/js; en su lugar agrega a resources/js y referencia con @vite.

Ejemplos del codebase
- Middleware de permisos (bootstrap/app.php): alias 'role', 'permission', 'role_or_permission', 'pos.access', 'tables.access', 'delivery.access', 'tables.maintenance.access'. Úsalos en rutas para proteger accesos.
- POS unifica flujos de pago: resources/views/pos/{invoice-form,unified-payment-form,payment-history}.blade.php; favorece Livewire/Filament Actions para modales.

### Ejemplos rápidos (snippets)

- Rutas protegidas (routes/web.php):
```php
use App\Http\Controllers\PosController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'pos.access', 'role_or_permission:cashier|pos.access'])->group(function () {
  Route::get('/pos', [PosController::class, 'index']);
});
```

- Filament Resource con scope global:
```php
use Illuminate\Database\Eloquent\Builder;

public static function getEloquentQuery(): Builder
{
  return parent::getEloquentQuery()->where('status', '!=', 'voided');
}
```

Depuración y troubleshooting
- Logs: storage/logs/laravel.log y logs específicos de delivery.
- Colas: composer run dev levanta queue:listen; funcionalidades de notificación/estado dependen de cola activa.
- Tras actualizar dependencias, limpiar/cargar caches de Filament si hay problemas (artisan filament:optimize/optimize-clear).

- Usa Sequential Thinking, Code Retrieval y Context7 siempre; no inventes, ni alucines, ni supongas nada.
- Si no sabes algo, me lo preguntas.
- Piensa siempre en arquitectura, estabilidad y mantenibilidad del código.
- Usa patrones de diseño y buenas prácticas en el desarrollo.
- Evita consultas N+1 en Eloquent.
- Sé KISS y YAGNI.


