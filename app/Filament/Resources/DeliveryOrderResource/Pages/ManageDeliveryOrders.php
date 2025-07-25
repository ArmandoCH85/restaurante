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
        return [];
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
                // 0. BÚSQUEDA DE CLIENTE EXISTENTE
                Forms\Components\Select::make('existing_customer')
                    ->label('🔍 Buscar Cliente Existente')
                    ->placeholder('Buscar por nombre o teléfono...')
                    ->searchable()
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
                        \Log::info('afterStateUpdated called with state: ' . $state);
                        
                        if ($state) {
                            $customer = Customer::find($state);
                            if ($customer) {
                                \Log::info('Setting form fields for customer: ' . $customer->name);
                                
                                $set('phone', $customer->phone ?? '');
                                $set('customer_name', $customer->name);
                                $set('document_type', $customer->document_type ?? 'dni');
                                $set('document_number', $customer->document_number ?? '');
                                $set('address', $customer->address ?? '');
                                $set('customer_found_flag', true);
                                $set('selected_customer_id', $customer->id);
                                $set('customer_status_message', "✅ Cliente encontrado: {$customer->name} - Datos cargados automáticamente");
                            }
                        } else {
                            $set('phone', '');
                            $set('customer_name', '');
                            $set('document_type', 'dni');
                            $set('document_number', '');
                            $set('address', '');
                            $set('customer_found_flag', false);
                            $set('selected_customer_id', null);
                            $set('customer_status_message', '📝 No se ha seleccionado cliente - Complete los datos para crear nuevo cliente');
                        }
                    })
                    ->columnSpanFull(),

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
                        $get('customer_status_message') ?? '📝 No se ha seleccionado cliente - Complete los datos para crear nuevo cliente'
                    )
                    ->columnSpanFull(),
                
                // 1. TIPO DE ENTREGA
                Forms\Components\Radio::make('delivery_type')
                    ->label('Tipo de entrega')
                    ->options([
                        'domicilio' => 'A DOMICILIO',
                        'recoger' => 'POR RECOGER',
                    ])
                    ->default('domicilio')
                    ->required()
                    ->inline()
                    ->columnSpanFull(),
                
                // 2. TELÉFONO (OBLIGATORIO)
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->required()
                    ->placeholder('Ingrese el teléfono')
                    ->disabled(fn (Forms\Get $get): bool => (bool) $get('customer_found_flag'))
                    ->reactive()
                    ->columnSpanFull(),
                
                // 3. NOMBRE CLIENTE (OBLIGATORIO)
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nombre cliente')
                    ->required()
                    ->placeholder('Ingrese el nombre completo del cliente')
                    ->disabled(fn (Forms\Get $get): bool => (bool) $get('customer_found_flag'))
                    ->reactive()
                    ->columnSpanFull(),
                
                // 4. TIPO DOCUMENTO / NÚMERO (SIEMPRE NO OBLIGATORIO)
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->label('Tipo Documento')
                            ->options([
                                'dni' => 'DNI',
                                'ruc' => 'RUC',
                            ])
                            ->default('dni')
                            ->native(false)
                            ->reactive()
                            ->columnSpan(1),
                            
                        Forms\Components\TextInput::make('document_number')
                            ->label('Número')
                            ->placeholder('Número de documento (opcional)')
                            ->reactive()
                            ->columnSpan(1),
                    ]),
                
                // 5. DIRECCIÓN (OBLIGATORIO)
                Forms\Components\Textarea::make('address')
                    ->label('Dirección')
                    ->required()
                    ->rows(3)
                    ->placeholder('Ingrese la dirección completa de entrega')
                    ->live()
                    ->columnSpanFull(),
                
                // 6. REFERENCIA (NO OBLIGATORIO)
                Forms\Components\Textarea::make('reference')
                    ->label('Referencia')
                    ->rows(2)
                    ->placeholder('Referencias adicionales (opcional)')
                    ->columnSpanFull(),
                
                // 7. REPARTIDOR (LIST BOX)
                Forms\Components\Select::make('delivery_person_id')
                    ->label('Repartidor')
                    ->options(function () {
                        return Employee::where('position', 'Delivery')
                            ->get()
                            ->mapWithKeys(function ($employee) {
                                return [$employee->id => $employee->first_name . ' ' . $employee->last_name . ' - ' . ($employee->phone ?? 'Sin teléfono')];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->placeholder('Seleccionar repartidor de la base de datos')
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

                // Preparar datos para el POS
                $deliveryData = [
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'delivery_type' => $data['delivery_type'],
                    'delivery_address' => $data['address'],
                    'delivery_references' => $data['reference'] ?? '',
                    'delivery_person_id' => $data['delivery_person_id'] ?? null,
                    'service_type' => 'delivery', // IMPORTANTE para reportes
                ];

                // Guardar en sesión para el POS
                session(['delivery_data' => $deliveryData]);

                // Notificación de éxito
                Notification::make()
                    ->title('✅ Datos de Delivery Guardados')
                    ->body("Cliente: {$customer->name} - Redirigiendo al POS...")
                    ->success()
                    ->send();

                // Redirigir al POS con los datos del delivery
                $this->redirect('/admin/pos-interface?from=delivery');
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