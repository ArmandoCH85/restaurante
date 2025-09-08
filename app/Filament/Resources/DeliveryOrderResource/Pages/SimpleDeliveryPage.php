<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\Order;
use App\Models\DeliveryOrder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SimpleDeliveryPage extends Page
{
    protected static string $resource = DeliveryOrderResource::class;

    protected static ?string $title = ' '; // ocultar título visible

    protected static string $view = 'filament.resources.delivery-order-resource.pages.simple-delivery-page';

    public ?array $simple = [
        'customer_id' => null,
        'customer_name' => '',
        'phone' => '',
        'address' => '',
        'reference' => '',
        'recipient_name' => '',
        'recipient_phone' => '',
        'recipient_address' => '',
    ];

    public bool $showNewCustomerFields = false;

    // Propiedades para el formulario de creación de cliente
    public ?string $document_type = 'DNI';
    public ?string $document_number = '';
    public ?string $name = '';
    public ?string $phone = '';
    public ?string $address = '';
    public ?string $address_references = '';

    protected function getForms(): array
    {
        return [
            'simpleForm',
        ];
    }

    public function simpleForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('Buscar cliente')
                    ->placeholder('Escribe nombre o teléfono')
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->options(function () {
                        return Customer::query()
                            ->orderByDesc('updated_at')
                            ->orderBy('name')
                            ->limit(5)
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => trim(($c->name ?? 'Sin nombre') . ' - ' . ($c->phone ?? '')),
                            ])
                            ->toArray();
                    })
                    ->preload()
                    ->getSearchResultsUsing(function (string $search) {
                        return Customer::query()
                            ->when($search, function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%")
                                  ->orWhere('phone', 'like', "%{$search}%");
                            })
                            ->orderBy('name')
                            ->limit(50)
                            ->get()
                            ->mapWithKeys(fn ($c) => [
                                $c->id => trim(($c->name ?? 'Sin nombre') . ' - ' . ($c->phone ?? '')),
                            ])
                            ->toArray();
                    })
                    ->getOptionLabelUsing(fn ($value) => optional(Customer::find($value), fn ($c) => trim(($c->name ?? '') . ' - ' . ($c->phone ?? ''))) ?? '')
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            // Si no hay cliente seleccionado, mostrar campos de creación
                            $this->showNewCustomerFields = false;
                            return;
                        }
                        $customer = Customer::find($state);
                        if ($customer) {
                            // Cliente existente encontrado - ocultar campos de creación
                            $this->showNewCustomerFields = false;
                            // statePath('simple'): usar claves relativas
                            $set('customer_name', $customer->name ?? '');
                            $set('phone', $customer->phone ?? '');
                            $set('address', $customer->address ?? '');
                            $set('reference', $customer->address_references ?? '');
                        }
                    }),


                // BOTÓN PARA MOSTRAR CAMPOS DE CREACIÓN DE CLIENTE
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('showNewCustomerFields')
                        ->label('Crear Nuevo Cliente')
                        ->icon('heroicon-o-user-plus')
                        ->color('gray')
                        ->visible(fn () => !$this->showNewCustomerFields)
                        ->action(function () {
                            $this->showNewCustomerFields = true;
                        }),
                        Forms\Components\Actions\Action::make('hideNewCustomerFields')
                        ->label('Cancelar Creación')
                        ->icon('heroicon-o-x-mark')
                        ->color('gray')
                        ->visible(fn () => $this->showNewCustomerFields)
                        ->action(function () {
                            $this->showNewCustomerFields = false;
                        }),
                    Forms\Components\Actions\Action::make('createNewCustomer')
                        ->label('Guardar Cliente')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->visible(fn () => $this->showNewCustomerFields)
                        ->action(function () {
                            $this->createNewCustomer();
                        }),
                ])
                ->visible(fn () => !$this->simple['customer_id']) // Solo mostrar si no hay cliente seleccionado
                ->columnSpanFull(),

                // SECCIÓN CONDICIONAL PARA CREACIÓN DE CLIENTE NUEVO
                Forms\Components\Section::make('Nuevo Cliente')
                    ->description('Complete los datos para crear un cliente nuevo')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn () => $this->showNewCustomerFields)
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('document_type')
                            ->label('Tipo Documento')
                            ->options([
                                'DNI' => 'DNI',
                                'RUC' => 'RUC'
                            ])
                            ->default('DNI')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Limpiar campos cuando cambia el tipo (usar estado del formulario)
                                $set('document_number', '');
                                $set('name', '');
                                $set('phone', '');
                                $set('address', '');
                                $set('address_references', '');
                            })
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('document_number')
                            ->label('Número de Documento')
                            ->required()
                            ->maxLength(11)
                            ->live(debounce: 500)
                            ->rules([
                                'regex:/^[0-9]{8,11}$/'
                            ])
                            ->helperText(fn (Forms\Get $get) =>
                                $get('document_type') === 'DNI'
                                    ? 'Ingrese 8 dígitos para DNI'
                                    : 'Ingrese 11 dígitos para RUC'
                            ),
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('lookup_document')
                                ->label('🔍 Buscar en RENIEC/SUNAT')
                                ->icon('heroicon-o-magnifying-glass')
                                ->color('primary')
                                ->action(function (Forms\Get $get, Forms\Set $set) {
                                    // Leer estado actual del formulario (statePath 'simple')
                                    $documentType = $get('document_type') ?? 'DNI';
                                    $documentNumber = $get('document_number') ?? '';

                                    if (empty($documentNumber)) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('⚠️ Campo requerido')
                                            ->body('Ingrese un número de documento para buscar.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    // Validar formato según tipo de documento
                                    if ($documentType === 'DNI' && !preg_match('/^[0-9]{8}$/', $documentNumber)) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('⚠️ Formato inválido')
                                            ->body('El DNI debe tener exactamente 8 dígitos.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    if ($documentType === 'RUC' && !preg_match('/^[0-9]{11}$/', $documentNumber)) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('⚠️ Formato inválido')
                                            ->body('El RUC debe tener exactamente 11 dígitos.')
                                            ->warning()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        $rucLookupService = app(\App\Services\RucLookupService::class);

                                        if ($documentType === 'RUC') {
                                            $companyData = $rucLookupService->lookupRuc($documentNumber);
                                            if ($companyData) {
                                                // Si Factiliza no trae teléfono, intentar obtenerlo de un cliente existente con ese RUC
                                                $rucPhone = $companyData['telefono'] ?? '';
                                                if (empty($rucPhone)) {
                                                    $existingByRuc = \App\Models\Customer::where('document_type', 'RUC')
                                                        ->where('document_number', $documentNumber)
                                                        ->first();
                                                    if ($existingByRuc && !empty($existingByRuc->phone)) {
                                                        $rucPhone = $existingByRuc->phone;
                                                    }
                                                }

                                                // Usar $set para actualizar campos principales del formulario de delivery
                                                // Estos son los campos que se ven en el formulario principal
                                                $set('customer_name', $companyData['razon_social'] ?? '');
                                                $set('phone', $rucPhone);
                                                $set('address', $companyData['direccion'] ?? ''); // Campo principal de dirección
                                                $set('reference', 'Estado: ' . ($companyData['estado'] ?? ''));

                                                // También actualizar campos de creación de cliente (por si están visibles)
                                                $set('name', $companyData['razon_social'] ?? '');
                                                $set('address_references', 'Estado: ' . ($companyData['estado'] ?? ''));

                                                // Actualizar el array simple para persistencia
                                                $this->simple['customer_name'] = $companyData['razon_social'] ?? '';
                                                $this->simple['address'] = $companyData['direccion'] ?? '';
                                                $this->simple['phone'] = $rucPhone;
                                                $this->simple['reference'] = 'Estado: ' . ($companyData['estado'] ?? '');
                                            } else {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('❌ RUC no encontrado')
                                                    ->body('No se encontró información para este RUC.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        } else { // DNI
                                            $personData = $rucLookupService->lookupDni($documentNumber);
                                            if ($personData) {
                                                // Intentar obtener teléfono: primero de Factiliza, si no, de un cliente existente con ese DNI
                                                $dniPhone = $personData['telefono'] ?? '';
                                                if (empty($dniPhone)) {
                                                    $existingByDni = \App\Models\Customer::where('document_type', 'DNI')
                                                        ->where('document_number', $documentNumber)
                                                        ->first();
                                                    if ($existingByDni && !empty($existingByDni->phone)) {
                                                        $dniPhone = $existingByDni->phone;
                                                    }
                                                }

                                                // Usar $set para actualizar campos principales del formulario de delivery
                                                // Estos son los campos que se ven en el formulario principal
                                                $set('customer_name', $personData['nombre_completo'] ?? '');
                                                $set('phone', $dniPhone);
                                                $set('address', $personData['direccion'] ?? ''); // Campo principal de dirección

                                                // También actualizar campos de creación de cliente (por si están visibles)
                                                $set('name', $personData['nombre_completo'] ?? '');

                                                // Actualizar el array simple para persistencia
                                                $this->simple['customer_name'] = $personData['nombre_completo'] ?? '';
                                                $this->simple['address'] = $personData['direccion'] ?? '';
                                                $this->simple['phone'] = $dniPhone;
                                            } else {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('❌ DNI no encontrado')
                                                    ->body('No se encontró información para este DNI.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        }
                                    } catch (\Exception $e) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('⚠️ Error de búsqueda')
                                            ->body($e->getMessage())
                                            ->danger()
                                            ->send();
                                    }
                                })
                                ->visible(fn (Forms\Get $get) => (
                                    $get('document_type') === 'DNI' && strlen((string) $get('document_number')) >= 8
                                ) || (
                                    $get('document_type') === 'RUC' && strlen((string) $get('document_number')) >= 11
                                ))
                        ])
                        ->columnSpanFull(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre Completo')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->tel()
                            ->required()
                            ->maxLength(30),
                        Forms\Components\Textarea::make('address')
                            ->label('Dirección')
                            ->rows(2),
                        Forms\Components\Textarea::make('address_references')
                            ->label('Referencias')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                // (Botón de copiar datos removido a solicitud del usuario)
                Forms\Components\TextInput::make('customer_name')
                    ->label('Nombre del cliente')
                    ->required()
                    ->placeholder('Ej. Juan Perez')
                    ->prefixIcon('heroicon-o-user'),
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono')
                    ->tel()
                    ->required()
                    ->placeholder('9XXXXXXXX')
                    ->prefixIcon('heroicon-o-phone'),
                Forms\Components\Textarea::make('address')
                    ->label('Dirección de entrega')
                    ->required()
                    ->rows(3)
                    ->placeholder('Calle, número, referencias breves')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('reference')
                    ->label('Referencias (opcional)')
                    ->rows(2)
                    ->placeholder('Color de casa, piso, indicaciones...')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Persona que recibe el delivery (Opcional)')
                    ->description('Información de contacto de quien recibirá el pedido')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('Nombre completo')
                            ->placeholder('Ej. María Gonzales')
                            ->prefixIcon('heroicon-o-user')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('recipient_phone')
                            ->label('Teléfono de contacto')
                            ->tel()
                            ->placeholder('9XXXXXXXX')
                            ->prefixIcon('heroicon-o-phone'),

                        Forms\Components\Textarea::make('recipient_address')
                            ->label('Dirección exacta de entrega')
                            ->rows(3)
                            ->placeholder('Calle, número, departamento, referencias específicas')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('simple')
            ->columns(1);
    }

    public function createNewCustomer(): void
    {
        // Validar campos del nuevo cliente
        $this->validate([
            'document_type' => 'required|in:DNI,RUC',
            'document_number' => 'required|string|max:11',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'address' => 'nullable|string',
            'address_references' => 'nullable|string',
        ]);

        try {
            // Crear el nuevo cliente
            $customer = Customer::create([
                'name' => $this->name,
                'phone' => $this->phone,
                'address' => $this->address ?? null,
                'address_references' => $this->address_references ?? null,
                'document_type' => $this->document_type,
                'document_number' => $this->document_number,
            ]);

            // Actualizar el formulario principal con los datos del nuevo cliente
            $this->simpleForm()->fill([
                'customer_id' => $customer->id,
                'customer_name' => $customer->name,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'reference' => $customer->address_references,
            ]);

            // Ocultar los campos de creación
            $this->showNewCustomerFields = false;

            // Limpiar los campos del formulario de creación
            $this->reset(['document_type', 'document_number', 'name', 'phone', 'address', 'address_references']);

            Notification::make()
                ->title('✅ Cliente creado exitosamente')
                ->body('Los datos del cliente han sido guardados.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Error al crear cliente')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function createSimpleDelivery(): void
    {
        $data = $this->getForm('simpleForm')->getState();

        // Validación mínima (permite cliente existente o datos nuevos)
        $this->validate([
            'simple.customer_id' => 'nullable|exists:customers,id',
            'simple.customer_name' => 'required_without:simple.customer_id|string|min:3',
            'simple.phone' => 'required_without:simple.customer_id|string|min:6',
            'simple.address' => 'required|string|min:5',
            'simple.recipient_name' => 'nullable|string|min:3',
            'simple.recipient_phone' => 'nullable|string|min:6',
            'simple.recipient_address' => 'nullable|string|min:5',
        ]);

        try {
            DB::transaction(function () use ($data) {
                // Cliente existente o creación/actualización por teléfono
                if (!empty($data['customer_id'])) {
                    $customer = Customer::find($data['customer_id']);
                    // Sincroniza datos básicos con lo ingresado si se modificaron
                    if ($customer) {
                        $customer->update([
                            'name' => $data['customer_name'] ?: ($customer->name ?? ''),
                            'phone' => $data['phone'] ?: ($customer->phone ?? ''),
                            'address' => $data['address'] ?: ($customer->address ?? ''),
                            'address_references' => $data['reference'] ?? ($customer->address_references ?? ''),
                        ]);
                    }
                } else {
                    $customer = Customer::where('phone', $data['phone'])->first();
                    if (!$customer) {
                        $customer = Customer::create([
                            'name' => $data['customer_name'],
                            'phone' => $data['phone'],
                            'address' => $data['address'],
                            'address_references' => $data['reference'] ?? '',
                        ]);
                    } else {
                        // Actualiza si cambió
                        $customer->update([
                            'name' => $data['customer_name'],
                            'address' => $data['address'],
                            'address_references' => $data['reference'] ?? '',
                        ]);
                    }
                }

                $currentEmployee = Employee::where('user_id', Auth::id())->first();

                $order = Order::create([
                    'customer_id' => $customer->id,
                    'employee_id' => $currentEmployee?->id,
                    'order_datetime' => now(),
                    'table_id' => null,
                    'total' => 0,
                    'status' => Order::STATUS_OPEN,
                    'service_type' => 'delivery',
                    'billed' => false,
                ]);

                $deliveryOrder = DeliveryOrder::create([
                    'order_id' => $order->id,
                    'delivery_address' => $data['address'],
                    'delivery_references' => $data['reference'] ?? '',
                    'recipient_name' => $data['recipient_name'] ?? '',
                    'recipient_phone' => $data['recipient_phone'] ?? '',
                    'recipient_address' => $data['recipient_address'] ?? '',
                    'status' => DeliveryOrder::STATUS_PENDING,
                ]);

                // Datos a sesión para POS (igual que el flujo actual)
                session(['delivery_data' => [
                    'order_id' => $order->id,
                    'delivery_order_id' => $deliveryOrder->id,
                    'customer_id' => $customer->id,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'delivery_type' => 'domicilio',
                    'delivery_address' => $data['address'],
                    'delivery_references' => $data['reference'] ?? '',
                    'recipient_name' => $data['recipient_name'] ?? '',
                    'recipient_phone' => $data['recipient_phone'] ?? '',
                    'recipient_address' => $data['recipient_address'] ?? '',
                    'service_type' => 'delivery',
                ]]);

                Notification::make()
                    ->title('Pedido de Delivery Creado')
                    ->body('Redirigiendo al POS para cargar productos...')
                    ->success()
                    ->send();

                $this->redirect('/admin/pos-interface?from=delivery&order_id=' . $order->id);
            });
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al crear')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    // KISS: Hook de Livewire para cuando cambia el select de cliente
    public function updatedSimpleCustomerId($value): void
    {
        if (!$value) {
            return;
        }
        $customer = Customer::find($value);
        if (!$customer) {
            return;
        }
        // set relative keys (statePath simple)
    $this->getForm('simpleForm')->fill([
            'customer_name' => $customer->name ?? '',
            'phone' => $customer->phone ?? '',
            'address' => $customer->address ?? '',
            'reference' => $customer->address_references ?? '',
            'recipient_name' => '',
            'recipient_phone' => '',
            'recipient_address' => '',
        ]);
    }

    // copySelectedCustomer eliminado (ya no es necesario)

}

