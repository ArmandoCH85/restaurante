# Design Document - Delivery UI Enhancement

## Overview

Este documento detalla el diseño para mejorar significativamente la experiencia de usuario de la página de gestión de delivery orders utilizando exclusivamente componentes nativos de Filament 3. El diseño se enfoca en crear una interfaz moderna, intuitiva y visualmente atractiva que aproveche al máximo las capacidades del framework.

## Architecture

### Component Structure
```
ManageDeliveryOrders (Page)
├── HeaderSection (Stats Cards)
├── FiltersSection (Enhanced Filters)
├── TableSection (Modern Table)
└── SidebarForm (Enhanced Form)
```

### Design System
- **Colores**: Usar la paleta de colores nativa de Filament 3 (primary, success, warning, danger, gray)
- **Tipografía**: Sistema tipográfico de Filament con jerarquía clara
- **Espaciado**: Grid system y spacing tokens de Filament
- **Componentes**: Solo componentes nativos de Filament 3

## Components and Interfaces

### 1. Enhanced Table Component

#### Table Configuration
```php
// Usar Table Builder con configuraciones avanzadas
->columns([
    // Badge column para estados con colores dinámicos
    Tables\Columns\BadgeColumn::make('status')
        ->colors([
            'secondary' => 'pending',
            'primary' => 'assigned', 
            'warning' => 'in_transit',
            'success' => 'delivered',
            'danger' => 'cancelled',
        ])
        ->icons([
            'heroicon-o-clock' => 'pending',
            'heroicon-o-user' => 'assigned',
            'heroicon-o-truck' => 'in_transit', 
            'heroicon-o-check-circle' => 'delivered',
            'heroicon-o-x-circle' => 'cancelled',
        ]),
    
    // Text columns con mejor formato
    Tables\Columns\TextColumn::make('order_id')
        ->badge()
        ->color('primary'),
        
    // Layout column para información compleja
    Tables\Columns\Layout\Stack::make([
        Tables\Columns\TextColumn::make('order.customer.name')
            ->weight(FontWeight::Bold),
        Tables\Columns\TextColumn::make('order.customer.phone')
            ->color('gray')
            ->size(TextColumnSize::Small),
    ]),
])
```

#### Enhanced Actions
```php
->actions([
    // Action groups para mejor organización
    Tables\Actions\ActionGroup::make([
        Tables\Actions\Action::make('assign')
            ->icon('heroicon-o-user-plus')
            ->color('success')
            ->form([...])
            ->modalHeading('Asignar Repartidor')
            ->modalDescription('Selecciona un repartidor para este pedido'),
            
        Tables\Actions\Action::make('transit')
            ->icon('heroicon-o-truck')
            ->color('warning')
            ->requiresConfirmation(),
            
        Tables\Actions\Action::make('deliver')
            ->icon('heroicon-o-check-circle') 
            ->color('success')
            ->requiresConfirmation(),
    ])
    ->label('Acciones')
    ->icon('heroicon-m-ellipsis-vertical')
    ->size(ActionSize::Small)
    ->button(),
])
```

### 2. Enhanced Form Component

#### Section-based Form Layout
```php
Forms\Components\Wizard::make([
    Forms\Components\Wizard\Step::make('Cliente')
        ->icon('heroicon-o-user')
        ->schema([
            Forms\Components\Section::make('Búsqueda de Cliente')
                ->description('Busca un cliente existente o crea uno nuevo')
                ->icon('heroicon-o-magnifying-glass')
                ->schema([
                    Forms\Components\Select::make('existing_customer')
                        ->searchable()
                        ->getSearchResultsUsing(...)
                        ->suffixIcon('heroicon-o-magnifying-glass'),
                ]),
        ]),
        
    Forms\Components\Wizard\Step::make('Entrega')
        ->icon('heroicon-o-truck')
        ->schema([
            Forms\Components\Section::make('Detalles de Entrega')
                ->schema([
                    Forms\Components\ToggleButtons::make('delivery_type')
                        ->options([
                            'domicilio' => 'A Domicilio',
                            'recoger' => 'Por Recoger'
                        ])
                        ->icons([
                            'domicilio' => 'heroicon-o-home',
                            'recoger' => 'heroicon-o-building-storefront'
                        ])
                        ->inline(),
                ]),
        ]),
])
```

#### Enhanced Field Components
```php
// Rich Text Input con validación visual
Forms\Components\TextInput::make('customer_name')
    ->label('Nombre del Cliente')
    ->prefixIcon('heroicon-o-user')
    ->placeholder('Ingrese el nombre completo')
    ->helperText('Este campo es obligatorio')
    ->live(onBlur: true)
    ->afterStateUpdated(fn ($state, $set) => 
        $set('customer_slug', Str::slug($state))
    ),

// Select con búsqueda avanzada
Forms\Components\Select::make('delivery_person_id')
    ->label('Repartidor Asignado')
    ->relationship('deliveryPerson', 'full_name')
    ->searchable()
    ->preload()
    ->createOptionForm([...])
    ->createOptionModalHeading('Crear Nuevo Repartidor')
    ->suffixIcon('heroicon-o-user-plus'),
```

### 3. Stats Dashboard Component

#### Info Cards Layout
```php
protected function getHeaderWidgets(): array
{
    return [
        DeliveryStatsWidget::class,
    ];
}

// Widget Implementation
class DeliveryStatsWidget extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Pedidos Pendientes', $this->getPendingCount())
                ->description('Esperando asignación')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning')
                ->chart([7, 2, 10, 3, 15, 4, 17]),
                
            Card::make('En Tránsito', $this->getInTransitCount())
                ->description('Siendo entregados')
                ->descriptionIcon('heroicon-o-truck')
                ->color('primary'),
                
            Card::make('Entregados Hoy', $this->getDeliveredTodayCount())
                ->description('Completados exitosamente')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }
}
```

### 4. Enhanced Filters Component

#### Filter Layout with Sections
```php
->filters([
    Tables\Filters\SelectFilter::make('status')
        ->label('Estado del Pedido')
        ->options([
            'pending' => 'Pendiente',
            'assigned' => 'Asignado',
            'in_transit' => 'En Tránsito',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ])
        ->indicator('Estado')
        ->multiple(),
        
    Tables\Filters\Filter::make('date_range')
        ->form([
            Forms\Components\DatePicker::make('from')
                ->label('Desde')
                ->native(false),
            Forms\Components\DatePicker::make('until')
                ->label('Hasta')
                ->native(false),
        ])
        ->query(function (Builder $query, array $data): Builder {
            return $query
                ->when($data['from'], fn ($query, $date) => 
                    $query->whereDate('created_at', '>=', $date))
                ->when($data['until'], fn ($query, $date) => 
                    $query->whereDate('created_at', '<=', $date));
        })
        ->indicateUsing(function (array $data): array {
            $indicators = [];
            if ($data['from'] ?? null) {
                $indicators[] = Indicator::make('Desde ' . Carbon::parse($data['from'])->toFormattedDateString())
                    ->removeField('from');
            }
            if ($data['until'] ?? null) {
                $indicators[] = Indicator::make('Hasta ' . Carbon::parse($data['until'])->toFormattedDateString())
                    ->removeField('until');
            }
            return $indicators;
        }),
], layout: FiltersLayout::AboveContent)
->filtersFormColumns(3)
```

## Data Models

### Enhanced Model Relationships
```php
// DeliveryOrder Model enhancements
class DeliveryOrder extends Model
{
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'estimated_delivery_time' => 'datetime',
        'actual_delivery_time' => 'datetime',
    ];
    
    // Accessor for status badge
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'assigned' => 'Asignado',
            'in_transit' => 'En Tránsito',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        };
    }
    
    // Accessor for status color
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'secondary',
            'assigned' => 'primary',
            'in_transit' => 'warning',
            'delivered' => 'success',
            'cancelled' => 'danger',
        };
    }
}
```

## Error Handling

### Form Validation with Visual Feedback
```php
Forms\Components\TextInput::make('phone')
    ->label('Teléfono')
    ->tel()
    ->required()
    ->rules(['regex:/^[0-9]{9}$/'])
    ->validationMessages([
        'required' => 'El teléfono es obligatorio.',
        'regex' => 'El teléfono debe tener 9 dígitos.',
    ])
    ->live(onBlur: true)
    ->afterStateUpdated(function ($state, $set, $get) {
        if (strlen($state) === 9) {
            // Buscar cliente por teléfono
            $customer = Customer::where('phone', $state)->first();
            if ($customer) {
                Notification::make()
                    ->title('Cliente encontrado')
                    ->body("Se encontró: {$customer->name}")
                    ->success()
                    ->send();
            }
        }
    }),
```

### Action Error Handling
```php
Tables\Actions\Action::make('assign_delivery')
    ->action(function (DeliveryOrder $record, array $data) {
        try {
            DB::transaction(function () use ($record, $data) {
                $record->assignDeliveryPerson($data['delivery_person_id']);
            });
            
            Notification::make()
                ->title('Repartidor asignado exitosamente')
                ->body("El pedido #{$record->order_id} ha sido asignado")
                ->success()
                ->send();
                
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al asignar repartidor')
                ->body('Por favor, intente nuevamente')
                ->danger()
                ->send();
        }
    })
```

## Testing Strategy

### Component Testing
1. **Form Components**: Validar que todos los campos usen componentes nativos de Filament
2. **Table Components**: Verificar que badges, acciones y filtros funcionen correctamente
3. **Responsive Design**: Probar en diferentes tamaños de pantalla
4. **Accessibility**: Validar que todos los componentes sean accesibles

### User Experience Testing
1. **Navigation Flow**: Probar el flujo completo de creación de pedidos
2. **Visual Feedback**: Verificar que todas las acciones den feedback visual
3. **Error States**: Probar manejo de errores y validaciones
4. **Performance**: Medir tiempos de carga y respuesta

### Integration Testing
1. **Database Operations**: Verificar que todas las operaciones CRUD funcionen
2. **Real-time Updates**: Probar actualizaciones en tiempo real
3. **Notification System**: Verificar que las notificaciones se muestren correctamente
4. **Export/Import**: Probar funcionalidades de exportación

## Implementation Notes

### Filament 3 Best Practices
1. **Component Consistency**: Usar solo componentes nativos de Filament 3
2. **Theme Integration**: Aprovechar el sistema de temas de Filament
3. **Performance**: Usar lazy loading y paginación eficiente
4. **Accessibility**: Seguir las guías de accesibilidad de Filament

### Code Organization
1. **Resource Classes**: Mantener lógica de negocio en Resources
2. **Widget Classes**: Crear widgets reutilizables para estadísticas
3. **Custom Components**: Solo cuando sea absolutamente necesario
4. **Styling**: Usar clases de Tailwind incluidas en Filament

### Migration Strategy
1. **Incremental Updates**: Implementar cambios de forma gradual
2. **Backward Compatibility**: Mantener funcionalidad existente
3. **User Training**: Documentar cambios para usuarios finales
4. **Rollback Plan**: Tener plan de reversión si es necesario