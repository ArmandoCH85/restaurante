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
        'edit_address_enabled' => false,
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
                Forms\Components\TextInput::make('phone')
                    ->label('Teléfono o Nombre del Cliente')
                    ->placeholder('Ingrese teléfono (987654321) o nombre (Juan Pérez)')
                    ->prefixIcon('heroicon-o-magnifying-glass')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (empty($state) || strlen($state) < 3) {
                            // Limpiar campos si no hay búsqueda válida
                            $set('customer_id', null);
                            $set('customer_name', '');
                            $set('address', '');
                            $set('reference', '');

                            // También limpiar el array simple
                            $this->simple['customer_id'] = null;
                            $this->simple['customer_name'] = '';
                            $this->simple['address'] = '';
                            $this->simple['reference'] = '';
                            return;
                        }

                        // Buscar cliente por teléfono o nombre
                        $customer = Customer::where('phone', 'LIKE', "%{$state}%")
                            ->orWhere('name', 'LIKE', "%{$state}%")
                            ->first();

                        if ($customer) {
                            // Cliente encontrado - prellenar campos
                            $set('customer_id', $customer->id);
                            $set('customer_name', $customer->name ?? '');
                            $set('phone', $customer->phone ?? ''); // Actualizar con teléfono completo
                            $set('address', $customer->address ?? '');
                            $set('reference', $customer->address_references ?? '');

                            // Sincronizar con el array simple
                            $this->simple['customer_id'] = $customer->id;
                            $this->simple['customer_name'] = $customer->name ?? '';
                            $this->simple['phone'] = $customer->phone ?? '';
                            $this->simple['address'] = $customer->address ?? '';
                            $this->simple['reference'] = $customer->address_references ?? '';

                            \Filament\Notifications\Notification::make()
                                ->title('✅ Cliente encontrado')
                                ->body("Datos de {$customer->name} cargados automáticamente")
                                ->success()
                                ->send();
                        } else {
                            // Cliente no encontrado - limpiar campos para completar manualmente
                            $set('customer_id', null);
                            $set('customer_name', '');
                            $set('address', '');
                            $set('reference', '');

                            // También limpiar el array simple
                            $this->simple['customer_id'] = null;
                            $this->simple['customer_name'] = '';
                            $this->simple['address'] = '';
                            $this->simple['reference'] = '';

                            \Filament\Notifications\Notification::make()
                                ->title('📝 Cliente no encontrado')
                                ->body('Complete los datos para crear un nuevo cliente')
                                ->info()
                                ->send();
                        }
                    }),

                Forms\Components\Hidden::make('customer_id'),

                Forms\Components\TextInput::make('customer_name')
                    ->label('Nombre del cliente')
                    ->required()
                    ->placeholder('Ej. Juan Perez')
                    ->prefixIcon('heroicon-o-user')
                    ->disabled(fn(Forms\Get $get): bool => !empty($get('customer_id')))
                    ->helperText(
                        fn(Forms\Get $get): string =>
                        !empty($get('customer_id'))
                        ? '✅ Cliente encontrado en base de datos'
                        : '📝 Ingrese el nombre del nuevo cliente'
                    ),
                Forms\Components\Section::make('Dirección de entrega')
                    ->schema([
                        Forms\Components\Grid::make(1)
                            ->schema([
                                Forms\Components\Textarea::make('address')
                                    ->label('')
                                    ->required()
                                    ->rows(3)
                                    ->placeholder('Calle, número, referencias breves')
                                    ->disabled(fn(Forms\Get $get): bool => !empty($get('customer_id')) && !$get('edit_address_enabled'))
                                    ->helperText(
                                        fn(Forms\Get $get): string =>
                                        !empty($get('customer_id'))
                                        ? '📍 Dirección del cliente registrado' . ($get('edit_address_enabled') ? ' (editando...)' : '')
                                        : '📝 Ingrese la dirección de entrega'
                                    )
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->headerActions([
                        Forms\Components\Actions\Action::make('toggle_edit_address')
                            ->label(fn(Forms\Get $get): string => $get('edit_address_enabled') ? 'Guardar' : 'Editar')
                            ->icon(fn(Forms\Get $get): string => $get('edit_address_enabled') ? 'heroicon-m-check' : 'heroicon-m-pencil')
                            ->color(fn(Forms\Get $get): string => $get('edit_address_enabled') ? 'success' : 'primary')
                            ->visible(fn(Forms\Get $get): bool => !empty($get('customer_id')))
                            ->action(function (Forms\Set $set, Forms\Get $get) {
                                $isEditing = $get('edit_address_enabled');

                                if ($isEditing) {
                                    // Guardar cambios
                                    $customerId = $get('customer_id');
                                    $newAddress = $get('address');

                                    if ($customerId && $newAddress) {
                                        $customer = Customer::find($customerId);
                                        if ($customer) {
                                            $customer->update(['address' => $newAddress]);

                                            Notification::make()
                                                ->title('✅ Dirección actualizada')
                                                ->body('La dirección del cliente ha sido guardada correctamente')
                                                ->success()
                                                ->send();
                                        }
                                    }

                                    // Deshabilitar edición
                                    $set('edit_address_enabled', false);
                                } else {
                                    // Habilitar edición
                                    $set('edit_address_enabled', true);
                                }
                            })
                    ])
                    ->columnSpanFull(),
                Forms\Components\Hidden::make('edit_address_enabled')
                    ->default(false),
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

        // Debug para verificar qué datos estamos recibiendo
        \Log::info('🚿 DEBUG: Datos del formulario', ['data' => $data]);

        // Asegurar que los datos básicos estén presentes
        if (empty($data['customer_name'])) {
            $data['customer_name'] = $this->simple['customer_name'] ?? '';
        }
        if (empty($data['phone'])) {
            $data['phone'] = $this->simple['phone'] ?? '';
        }
        if (empty($data['address'])) {
            $data['address'] = $this->simple['address'] ?? '';
        }

        // Validación mínima
        $this->validate([
            'simple.phone' => 'required|string|min:6',
            'simple.customer_name' => 'required|string|min:3',
            'simple.address' => 'required|string|min:5',
            'simple.recipient_name' => 'nullable|string|min:3',
            'simple.recipient_phone' => 'nullable|string|min:6',
            'simple.recipient_address' => 'nullable|string|min:5',
        ]);

        try {
            DB::transaction(function () use ($data) {
                // Verificar que tenemos los datos mínimos necesarios
                if (empty($data['customer_name']) || empty($data['phone']) || empty($data['address'])) {
                    throw new \Exception('Faltan datos obligatorios del cliente (nombre, teléfono o dirección)');
                }

                // Buscar cliente existente o crear uno nuevo
                if (!empty($data['customer_id'])) {
                    // Cliente ya identificado por la búsqueda automática
                    $customer = Customer::find($data['customer_id']);
                    // Actualizar datos si fueron modificados
                    if ($customer) {
                        $customer->update([
                            'name' => $data['customer_name'] ?: $customer->name,
                            'phone' => $data['phone'] ?: $customer->phone,
                            'address' => $data['address'] ?: $customer->address,
                            'address_references' => $data['reference'] ?? $customer->address_references,
                        ]);
                    }
                } else {
                    // Buscar por teléfono por si acaso no fue detectado
                    $customer = Customer::where('phone', $data['phone'])->first();

                    if (!$customer) {
                        // Crear nuevo cliente
                        $customer = Customer::create([
                            'name' => $data['customer_name'],
                            'phone' => $data['phone'],
                            'address' => $data['address'],
                            'address_references' => $data['reference'] ?? '',
                        ]);
                    } else {
                        // Cliente existe, actualizar datos
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
                session([
                    'delivery_data' => [
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
                    ]
                ]);

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


}

