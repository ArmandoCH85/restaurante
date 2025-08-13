<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\DeliveryOrder;
use App\Models\CashRegister;
use Filament\Resources\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ManageDeliveryOrders extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static string $resource = DeliveryOrderResource::class;

    protected static string $view = 'filament.resources.delivery-order-resource.pages.manage-delivery-orders';

    protected static ?string $title = 'Gestión de Delivery';

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public function mount(): void
    {
        $step = request()->query('step');
        if ($step === 'cliente') {
            // Redirigir a la vista simple ya creada
            $this->redirect(DeliveryOrderResource::getUrl('simple'), navigate: true);
            return;
        }
    }

    public function getMaxContentWidth(): ?string
    {
        return 'full'; // Margen izquierdo reducido, contenido más pegado al sidebar
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Widget deshabilitado debido a problemas de compatibilidad con Livewire
            // La funcionalidad principal de delivery funciona sin el widget
        ];
    }

    // Propiedades del formulario - INICIALIZADAS CORRECTAMENTE
    public ?array $newDeliveryData = [
        'existing_customer' => null,
        'phone' => '',
        'customer_name' => '',
        'document_type' => 'dni',
        'document_number' => '',
        'address' => '',
        'reference' => '',
        'delivery_type' => 'domicilio',
        'delivery_person_id' => null,
        'customer_found_flag' => false,
        'selected_customer_id' => null,
        'customer_status_message' => '📝 No se ha seleccionado cliente - Complete los datos para crear nuevo cliente',
    ];

    // Estado de búsqueda de cliente
    public $customerFound = false;
    public $searchQuery = '';
    public $selectedCustomer = null;

    protected $listeners = ['refresh-form' => '$refresh'];



    public function updatedNewDeliveryDataExisting_customer($value)
    {
        \Log::info('updatedNewDeliveryDataExisting_customer called with value: ' . $value);

        if ($value) {
            $customer = Customer::find($value);
            if ($customer) {
                $this->newDeliveryData['phone'] = $customer->phone ?? '';
                $this->newDeliveryData['customer_name'] = $customer->name;
                $this->newDeliveryData['document_type'] = $customer->document_type ?? 'dni';
                $this->newDeliveryData['document_number'] = $customer->document_number ?? '';
                $this->newDeliveryData['address'] = $customer->address ?? '';
                $this->newDeliveryData['customer_found_flag'] = true;
                $this->newDeliveryData['selected_customer_id'] = $customer->id;
                $this->newDeliveryData['customer_status_message'] = "✅ Cliente encontrado: {$customer->name} - Datos cargados automáticamente";
            }
        } else {
            $this->newDeliveryData['phone'] = '';
            $this->newDeliveryData['customer_name'] = '';
            $this->newDeliveryData['document_type'] = 'dni';
            $this->newDeliveryData['document_number'] = '';
            $this->newDeliveryData['address'] = '';
            $this->newDeliveryData['customer_found_flag'] = false;
            $this->newDeliveryData['selected_customer_id'] = null;
            $this->newDeliveryData['customer_status_message'] = '📝 No se ha seleccionado cliente - Complete los datos para crear nuevo cliente';
        }
    }


    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('export_all')
                ->label('Exportar Todo')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->requiresConfirmation()
                ->modalHeading('Exportar todos los pedidos')
                ->modalDescription('Se exportarán todos los pedidos de delivery según los filtros aplicados.')
                ->modalSubmitActionLabel('Exportar')
                ->form([
                    \Filament\Forms\Components\Select::make('format')
                        ->label('Formato de exportación')
                        ->options([
                            'xlsx' => 'Excel (.xlsx)',
                            'csv' => 'CSV (.csv)',
                            'pdf' => 'PDF (.pdf)',
                        ])
                        ->default('xlsx')
                        ->required(),
                    \Filament\Forms\Components\Checkbox::make('include_details')
                        ->label('Incluir detalles de productos')
                        ->default(true),
                    \Filament\Forms\Components\DatePicker::make('date_from')
                        ->label('Fecha desde')
                        ->native(false),
                    \Filament\Forms\Components\DatePicker::make('date_to')
                        ->label('Fecha hasta')
                        ->native(false),
                ])
                ->action(function (array $data) {
                    $format = $data['format'] ?? 'xlsx';

                    \Filament\Notifications\Notification::make()
                        ->title('📄 Exportación iniciada')
                        ->body("Generando archivo en formato {$format}...")
                        ->info()
                        ->send();

                    // Simular proceso de exportación
                    \Filament\Notifications\Notification::make()
                        ->title('✅ Exportación completada')
                        ->body('El archivo está listo para descargar')
                        ->success()
                        ->duration(5000)
                        ->actions([
                            \Filament\Notifications\Actions\Action::make('download')
                                ->label('Descargar archivo')
                                ->button()
                                ->color('success'),
                        ])
                        ->send();
                }),
        ];
    }

    protected function getForms(): array
    {
        return [
            'newDeliveryForm',
        ];
    }

    public function table(Table $table): Table
    {
        return DeliveryOrderResource::table($table)
            ->query(DeliveryOrderResource::getEloquentQuery());
    }

    public function newDeliveryForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('Cliente')
                        ->icon('heroicon-o-user')
                        ->description('Buscar o crear cliente')
                        ->schema([
                            Forms\Components\Section::make('Búsqueda de Cliente')
                                ->description('Busca un cliente existente o los datos se usarán para crear uno nuevo')
                                ->icon('heroicon-o-magnifying-glass')
                                ->collapsible()
                                ->schema([
                                    Forms\Components\Select::make('existing_customer')
                                        ->label('Cliente Existente')
                                        ->placeholder('Buscar por nombre o teléfono...')
                                        ->searchable()
                                        ->suffixIcon('heroicon-o-magnifying-glass')
                                        ->getSearchResultsUsing(function (string $search): array {
                                            return Customer::where('name', 'like', "%{$search}%")
                                                ->orWhere('phone', 'like', "%{$search}%")
                                                ->limit(10)
                                                ->get()
                                                ->mapWithKeys(function ($customer) {
                                                    return [$customer->id => "{$customer->name} - {$customer->phone}"];
                                                })
                                                ->toArray();
                                        })
                                        ->getOptionLabelUsing(function ($value): ?string {
                                            $customer = Customer::find($value);
                                            return $customer ? "{$customer->name} - {$customer->phone}" : '';
                                        })
                                        ->live()
                                        ->afterStateUpdated(function (callable $set, $state) {
                                            if ($state) {
                                                $customer = Customer::find($state);
                                                if ($customer) {
                                                    $set('phone', $customer->phone ?? '');
                                                    $set('customer_name', $customer->name);
                                                    $set('document_type', $customer->document_type ?? 'dni');
                                                    $set('document_number', $customer->document_number ?? '');
                                                    $set('address', $customer->address ?? '');
                                                    $set('customer_found_flag', true);
                                                    $set('selected_customer_id', $customer->id);
                                                    $set('customer_status_message', "✅ Cliente encontrado: {$customer->name}");

                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Cliente encontrado')
                                                        ->body("Datos de {$customer->name} cargados automáticamente")
                                                        ->success()
                                                        ->send();
                                                }
                                            } else {
                                                $set('phone', '');
                                                $set('customer_name', '');
                                                $set('document_type', 'dni');
                                                $set('document_number', '');
                                                $set('address', '');
                                                $set('customer_found_flag', false);
                                                $set('selected_customer_id', null);
                                                $set('customer_status_message', '📝 Complete los datos para crear nuevo cliente');
                                            }
                                        }),

                                    // CAMPOS OCULTOS PARA ESTADO
                                    Forms\Components\Hidden::make('customer_found_flag')
                                        ->default(false)
                                        ->dehydrated(),

                                    Forms\Components\Hidden::make('selected_customer_id')
                                        ->dehydrated(),

                                    // INDICADOR DE ESTADO
                                    Forms\Components\Placeholder::make('customer_status_message')
                                        ->label('')
                                        ->content(fn (Forms\Get $get): string =>
                                            $get('customer_status_message') ?? '📝 Complete los datos para crear nuevo cliente'
                                        ),
                                ]),

                            Forms\Components\Section::make('Datos del Cliente')
                                ->description('Información de contacto del cliente')
                                ->icon('heroicon-o-identification')
                                ->schema([
                                    Forms\Components\TextInput::make('phone')
                                        ->label('Teléfono')
                                        ->tel()
                                        ->required()
                                        ->placeholder('Ingrese el teléfono')
                                        ->prefixIcon('heroicon-o-phone')
                                        ->disabled(fn (Forms\Get $get): bool => (bool) $get('customer_found_flag'))
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(function ($state, callable $set) {
                                            if (strlen($state) === 9) {
                                                $customer = Customer::where('phone', $state)->first();
                                                if ($customer) {
                                                    \Filament\Notifications\Notification::make()
                                                        ->title('Cliente encontrado por teléfono')
                                                        ->body("Se encontró: {$customer->name}")
                                                        ->info()
                                                        ->send();
                                                }
                                            }
                                        }),

                                    Forms\Components\TextInput::make('customer_name')
                                        ->label('Nombre Completo')
                                        ->required()
                                        ->placeholder('Ingrese el nombre completo del cliente')
                                        ->prefixIcon('heroicon-o-user')
                                        ->disabled(fn (Forms\Get $get): bool => (bool) $get('customer_found_flag')),

                                    Forms\Components\Grid::make(2)
                                        ->schema([
                                            Forms\Components\Select::make('document_type')
                                                ->label('Tipo de Documento')
                                                ->options([
                                                    'dni' => 'DNI',
                                                    'ruc' => 'RUC',
                                                ])
                                                ->default('dni')
                                                ->native(false)
                                                ->prefixIcon('heroicon-o-identification'),

                                            Forms\Components\TextInput::make('document_number')
                                                ->label('Número de Documento')
                                                ->placeholder('Opcional')
                                                ->prefixIcon('heroicon-o-hashtag'),
                                        ]),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Entrega')
                        ->icon('heroicon-o-truck')
                        ->description('Detalles de la entrega')
                        ->schema([
                            Forms\Components\Section::make('Tipo de Servicio')
                                ->description('Selecciona el tipo de entrega')
                                ->icon('heroicon-o-cog-6-tooth')
                                ->schema([
                                    Forms\Components\ToggleButtons::make('delivery_type')
                                        ->label('Tipo de Entrega')
                                        ->options([
                                            'domicilio' => 'A Domicilio',
                                            'recoger' => 'Por Recoger'
                                        ])
                                        ->icons([
                                            'domicilio' => 'heroicon-o-home',
                                            'recoger' => 'heroicon-o-building-storefront'
                                        ])
                                        ->colors([
                                            'domicilio' => 'primary',
                                            'recoger' => 'warning'
                                        ])
                                        ->inline()
                                        ->default('domicilio')
                                        ->required(),
                                ]),

                            Forms\Components\Section::make('Dirección de Entrega')
                                ->description('Información detallada de la dirección')
                                ->icon('heroicon-o-map-pin')
                                ->schema([
                                    Forms\Components\Textarea::make('address')
                                        ->label('Dirección Completa')
                                        ->required()
                                        ->rows(3)
                                        ->placeholder('Ingrese la dirección completa de entrega')
                                        ->helperText('Incluya referencias como número de casa, piso, departamento, etc.')
                                        ->live(),

                                    Forms\Components\Textarea::make('reference')
                                        ->label('Referencias Adicionales')
                                        ->rows(2)
                                        ->placeholder('Color de casa, puntos de referencia, instrucciones especiales...')
                                        ->helperText('Información adicional que ayude al repartidor a encontrar la dirección'),
                                ]),
                        ]),

                    Forms\Components\Wizard\Step::make('Repartidor')
                        ->icon('heroicon-o-user-circle')
                        ->description('Asignar repartidor')
                        ->schema([
                            Forms\Components\Section::make('Asignación de Repartidor')
                                ->description('Selecciona el repartidor que realizará la entrega')
                                ->icon('heroicon-o-users')
                                ->schema([
                                    Forms\Components\Select::make('delivery_person_id')
                                        ->label('Repartidor Disponible')
                                        ->options(function () {
                                            return Employee::where('position', 'Delivery')
                                                ->get()
                                                ->mapWithKeys(function ($employee) {
                                                    $status = '🟢 Disponible'; // Aquí podrías agregar lógica para verificar disponibilidad
                                                    return [$employee->id => "{$employee->first_name} {$employee->last_name} - {$status}"];
                                                })
                                                ->toArray();
                                        })
                                        ->searchable()
                                        ->preload()
                                        ->placeholder('Seleccionar repartidor...')
                                        ->suffixIcon('heroicon-o-user-plus')
                                        ->helperText('Puedes dejar vacío para asignar más tarde')
                                        ->createOptionForm([
                                            Forms\Components\TextInput::make('first_name')
                                                ->label('Nombre')
                                                ->required()
                                                ->prefixIcon('heroicon-o-user'),
                                            Forms\Components\TextInput::make('last_name')
                                                ->label('Apellido')
                                                ->required()
                                                ->prefixIcon('heroicon-o-user'),
                                            Forms\Components\TextInput::make('document_number')
                                                ->label('Número de Documento')
                                                ->required()
                                                ->prefixIcon('heroicon-o-identification'),
                                            Forms\Components\TextInput::make('phone')
                                                ->label('Teléfono')
                                                ->tel()
                                                ->prefixIcon('heroicon-o-phone'),
                                            Forms\Components\Hidden::make('position')
                                                ->default('Delivery'),
                                        ])
                                        ->createOptionModalHeading('Crear Nuevo Repartidor'),
                                ]),
                        ]),
                ])
                ->skippable()
                ->persistStepInQueryString()
                ->columnSpanFull(),
            ])
            ->statePath('newDeliveryData');
    }

    public function createDeliveryOrder(): void
    {
        try {
            // Validar el formulario
            $this->getForm('newDeliveryForm')->validate();

            // Obtener los datos validados
            $data = $this->getForm('newDeliveryForm')->getState();

            DB::transaction(function () use ($data) {
                // Determinar si se seleccionó un cliente existente o crear uno nuevo
                if (isset($data['customer_found_flag']) && $data['customer_found_flag'] && isset($data['selected_customer_id'])) {
                    // Cliente existente seleccionado - usar ese cliente
                    $customer = Customer::find($data['selected_customer_id']);

                    // Actualizar solo la dirección (puede haber cambiado para este delivery específico)
                    if ($customer && $customer->address !== $data['address']) {
                        $customer->update([
                            'address' => $data['address'],
                            'document_type' => $data['document_type'] ?? $customer->document_type,
                            'document_number' => $data['document_number'] ?? $customer->document_number,
                        ]);
                    }
                } else {
                    // Crear nuevo cliente
                    $customer = Customer::create([
                        'name' => $data['customer_name'],
                        'phone' => $data['phone'],
                        'document_type' => $data['document_type'] ?? null,
                        'document_number' => $data['document_number'] ?? null,
                        'address' => $data['address'],
                    ]);
                }

                // Buscar el employee_id basado en el user_id actual
                $currentEmployee = Employee::where('user_id', Auth::id())->first();
                $employeeId = $currentEmployee ? $currentEmployee->id : null;

                // Crear una orden temporal para el delivery
                $order = Order::create([
                    'customer_id' => $customer->id,
                    'employee_id' => $employeeId,
                    'order_datetime' => now(),
                    'table_id' => null, // No hay mesa para delivery
                    'total' => 0, // Se actualizará en el POS
                    'status' => Order::STATUS_OPEN,
                    'service_type' => 'delivery',
                    'billed' => false,
                ]);

                // Crear el registro de DeliveryOrder
                $deliveryOrder = DeliveryOrder::create([
                    'order_id' => $order->id,
                    'delivery_address' => $data['address'],
                    'delivery_references' => $data['reference'] ?? '',
                    'delivery_person_id' => $data['delivery_person_id'] ?? null,
                    'status' => DeliveryOrder::STATUS_PENDING,
                ]);

                // Preparar datos para el POS
                $deliveryData = [
                    'order_id' => $order->id,
                    'delivery_order_id' => $deliveryOrder->id,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'delivery_type' => $data['delivery_type'],
                    'delivery_address' => $data['address'],
                    'delivery_references' => $data['reference'] ?? '',
                    'delivery_person_id' => $data['delivery_person_id'] ?? null,
                    'service_type' => 'delivery',
                ];

                // Guardar en sesión para el POS
                session(['delivery_data' => $deliveryData]);

                // Notificación de éxito
                Notification::make()
                    ->title('✅ Pedido de Delivery Creado')
                    ->body("Pedido #{$order->id} creado para {$customer->name} - Redirigiendo al POS...")
                    ->success()
                    ->send();

                // Redirigir al POS con los datos del delivery
                $this->redirect('/admin/pos-interface?from=delivery&order_id=' . $order->id);
            });

        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Error')
                ->body('Error al procesar los datos: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function resetForm(): void
    {
        $this->getForm('newDeliveryForm')->fill([
            'existing_customer' => null,
            'customer_found_flag' => false,
            'selected_customer_id' => null,
            'customer_status_message' => '📝 No se ha seleccionado cliente - Complete los datos para crear nuevo cliente',
            'delivery_type' => 'domicilio',
            'document_type' => 'dni',
            'phone' => '',
            'customer_name' => '',
            'document_number' => '',
            'address' => '',
            'reference' => '',
            'delivery_person_id' => null,
        ]);
        $this->newDeliveryData = [
            'existing_customer' => null,
            'phone' => '',
            'customer_name' => '',
            'document_type' => 'dni',
            'document_number' => '',
            'address' => '',
            'reference' => '',
            'delivery_type' => 'domicilio',
            'delivery_person_id' => null,
            'customer_found_flag' => false,
            'selected_customer_id' => null,
            'customer_status_message' => '📝 No se ha seleccionado cliente - Complete los datos para crear nuevo cliente',
        ];
        $this->customerFound = false;
        $this->searchQuery = '';
        $this->selectedCustomer = null;
    }
}
