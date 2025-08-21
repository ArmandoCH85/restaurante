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
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
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
