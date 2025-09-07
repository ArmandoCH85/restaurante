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
                    ->createOptionForm([
                        Forms\Components\Select::make('document_type')
                            ->label('Tipo Documento')
                            ->options([
                                'DNI' => 'DNI',
                                'RUC' => 'RUC'
                            ])
                            ->default('DNI')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Limpiar número cuando cambia el tipo
                                $set('document_number', '');
                                $set('name', '');
                                $set('phone', '');
                                $set('address', '');
                            }),
                        Forms\Components\TextInput::make('document_number')
                            ->label('Número de Documento')
                            ->required()
                            ->maxLength(11)
                            ->live(debounce: 500)
                            ->rules([
                                'regex:/^[0-9]{8,11}$/'
                            ])
                            ->helperText(fn (callable $get) =>
                                $get('document_type') === 'DNI'
                                    ? 'Ingrese 8 dígitos para DNI'
                                    : 'Ingrese 11 dígitos para RUC'
                            ),
                            Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('lookup_document')
                                ->label('🔍 Buscar en RENIEC/SUNAT')
                                ->icon('heroicon-o-magnifying-glass')
                                ->color('primary')
                                ->action(function (callable $set, callable $get) {
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

                                            // Debug: Ver qué datos llegan
                                            \Illuminate\Support\Facades\Log::info('RUC Lookup Result', [
                                                'ruc' => $documentNumber,
                                                'companyData' => $companyData,
                                                'has_direccion' => isset($companyData['direccion']) ? !empty($companyData['direccion']) : false,
                                                'direccion_value' => $companyData['direccion'] ?? 'NO DIRECCION',
                                                'distrito' => $companyData['distrito'] ?? 'NO DISTRITO',
                                                'provincia' => $companyData['provincia'] ?? 'NO PROVINCIA'
                                            ]);

                                            if ($companyData) {
                                                $set('name', $companyData['razon_social']);

                                                // Construir dirección completa con mejor formato
                                                $addressParts = [];

                                                if (!empty($companyData['direccion'])) {
                                                    $addressParts[] = trim($companyData['direccion']);
                                                }

                                                if (!empty($companyData['distrito'])) {
                                                    $addressParts[] = trim($companyData['distrito']);
                                                }

                                                if (!empty($companyData['provincia'])) {
                                                    $addressParts[] = trim($companyData['provincia']);
                                                }

                                                $fullAddress = implode(', ', array_filter($addressParts));

                                                // Debug: Ver dirección final para RUC
                                                \Illuminate\Support\Facades\Log::info('RUC Final Address Construction', [
                                                    'ruc' => $documentNumber,
                                                    'addressParts' => $addressParts,
                                                    'fullAddress' => $fullAddress,
                                                    'will_assign' => !empty($fullAddress)
                                                ]);

                                                // Solo asignar si hay dirección
                                                if (!empty($fullAddress)) {
                                                    $set('address', $fullAddress);
                                                }

                                                // Teléfono si existe
                                                if (!empty($companyData['telefono'])) {
                                                    $set('phone', $companyData['telefono']);
                                                }

                                                // Agregar información adicional a referencias
                                                $referenceParts = [];
                                                if (!empty($companyData['departamento'])) {
                                                    $referenceParts[] = 'Departamento: ' . $companyData['departamento'];
                                                }
                                                if (!empty($companyData['ubigeo'])) {
                                                    $referenceParts[] = 'Ubigeo: ' . $companyData['ubigeo'];
                                                }
                                                if (!empty($companyData['estado'])) {
                                                    $referenceParts[] = 'Estado: ' . $companyData['estado'];
                                                }

                                                if (!empty($referenceParts)) {
                                                    $set('reference', implode(' | ', $referenceParts));
                                                }

                                                $notificationBody = 'Datos de ' . $companyData['razon_social'] . ' cargados correctamente.';
                                                if (!empty($fullAddress)) {
                                                    $notificationBody .= ' Dirección incluida.';
                                                }

                                                \Filament\Notifications\Notification::make()
                                                    ->title('✅ Empresa encontrada')
                                                    ->body($notificationBody)
                                                    ->success()
                                                    ->duration(4000)
                                                    ->send();
                                            } else {
                                                \Filament\Notifications\Notification::make()
                                                    ->title('❌ RUC no encontrado')
                                                    ->body('No se encontró información para este RUC.')
                                                    ->warning()
                                                    ->send();
                                            }
                                        } elseif ($documentType === 'DNI') {
                                            $personData = $rucLookupService->lookupDni($documentNumber);

                                            // Debug: Ver qué datos llegan
                                            \Illuminate\Support\Facades\Log::info('DNI Lookup Result', [
                                                'dni' => $documentNumber,
                                                'personData' => $personData,
                                                'has_direccion' => isset($personData['direccion']) ? !empty($personData['direccion']) : false,
                                                'direccion_value' => $personData['direccion'] ?? 'NO DIRECCION',
                                                'distrito' => $personData['distrito'] ?? 'NO DISTRITO',
                                                'provincia' => $personData['provincia'] ?? 'NO PROVINCIA'
                                            ]);

                                            if ($personData) {
                                                $set('name', $personData['nombre_completo']);

                                                // Construir dirección completa con mejor formato
                                                $addressParts = [];

                                                if (!empty($personData['direccion'])) {
                                                    $addressParts[] = trim($personData['direccion']);
                                                }

                                                if (!empty($personData['distrito'])) {
                                                    $addressParts[] = trim($personData['distrito']);
                                                }

                                                if (!empty($personData['provincia'])) {
                                                    $addressParts[] = trim($personData['provincia']);
                                                }

                                                $fullAddress = implode(', ', array_filter($addressParts));

                                                // Debug: Ver dirección final para DNI
                                                \Illuminate\Support\Facades\Log::info('DNI Final Address Construction', [
                                                    'dni' => $documentNumber,
                                                    'addressParts' => $addressParts,
                                                    'fullAddress' => $fullAddress,
                                                    'will_assign' => !empty($fullAddress)
                                                ]);

                                                // Solo asignar si hay dirección
                                                if (!empty($fullAddress)) {
                                                    $set('address', $fullAddress);
                                                }

                                                // Agregar información adicional a referencias si hay datos extra
                                                $referenceParts = [];
                                                if (!empty($personData['departamento'])) {
                                                    $referenceParts[] = 'Departamento: ' . $personData['departamento'];
                                                }
                                                if (!empty($personData['ubigeo'])) {
                                                    $referenceParts[] = 'Ubigeo: ' . $personData['ubigeo'];
                                                }

                                                if (!empty($referenceParts)) {
                                                    $set('reference', implode(' | ', $referenceParts));
                                                }

                                                $notificationBody = 'Datos de ' . $personData['nombre_completo'] . ' cargados correctamente.';
                                                if (!empty($fullAddress)) {
                                                    $notificationBody .= ' Dirección incluida.';
                                                }

                                                \Filament\Notifications\Notification::make()
                                                    ->title('✅ Persona encontrada')
                                                    ->body($notificationBody)
                                                    ->success()
                                                    ->duration(4000)
                                                    ->send();
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
                                ->icon('heroicon-o-magnifying-glass')
                                ->color('primary')
                                ->visible(fn (callable $get) => !empty($get('document_number')) && strlen($get('document_number')) >= 8)
                        ]),
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
                            ->rows(2),
                    ])
                    ->createOptionUsing(function (array $data) {
                        $customer = Customer::create([
                            'name' => $data['name'],
                            'phone' => $data['phone'],
                            'address' => $data['address'] ?? null,
                            'address_references' => $data['address_references'] ?? null,
                            'document_type' => $data['document_type'] ?? null,
                            'document_number' => $data['document_number'] ?? null,
                        ]);
                        return $customer->id;
                    })
                    ->createOptionAction(function ($action) {
                        return $action
                            ->modalHeading('Nuevo Cliente')
                            ->modalWidth('lg')
                            ->modalSubmitActionLabel('Guardar cliente')
                            ->label('Crear nuevo');
                    })
                    ->reactive()
            ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) {
                            return;
                        }
                        $customer = Customer::find($state);
                        if ($customer) {
                // statePath('simple'): usar claves relativas
                $set('customer_name', $customer->name ?? '');
                $set('phone', $customer->phone ?? '');
                $set('address', $customer->address ?? '');
                $set('reference', $customer->address_references ?? '');
                        }
                    })
                    ->helperText('Busca por nombre o teléfono, o crea un cliente nuevo')
                    ->columnSpanFull(),

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
