<?php

namespace App\Filament\Resources\DeliveryOrderResource\Pages;

use App\Filament\Resources\DeliveryOrderResource;
use App\Models\Customer;
use App\Models\DeliveryOrder;
use App\Models\Employee;
use App\Models\Order;
use App\Services\DeliveryGeocodingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SimpleDeliveryPage extends Page
{
    protected static string $resource = DeliveryOrderResource::class;

    protected static ?string $title = ' ';

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
        'delivery_latitude' => null,
        'delivery_longitude' => null,
        'edit_address_enabled' => false,
    ];

    protected function getForms(): array
    {
        return ['simpleForm'];
    }

    public function simpleForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('phone')
                    ->label('Telefono o Nombre del Cliente')
                    ->placeholder('Ingrese telefono o nombre')
                    ->prefixIcon('heroicon-o-magnifying-glass')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set): void {
                        if (empty($state) || strlen($state) < 3) {
                            $set('customer_id', null);
                            $set('customer_name', '');
                            $set('address', '');
                            $set('reference', '');
                            $this->simple['customer_id'] = null;
                            $this->simple['customer_name'] = '';
                            $this->simple['address'] = '';
                            $this->simple['reference'] = '';

                            return;
                        }

                        $customer = Customer::query()
                            ->where('phone', 'LIKE', "%{$state}%")
                            ->orWhere('name', 'LIKE', "%{$state}%")
                            ->first();

                        if (! $customer) {
                            $set('customer_id', null);
                            $set('customer_name', '');
                            $set('address', '');
                            $set('reference', '');
                            $this->simple['customer_id'] = null;
                            $this->simple['customer_name'] = '';
                            $this->simple['address'] = '';
                            $this->simple['reference'] = '';

                            Notification::make()
                                ->title('Cliente no encontrado')
                                ->body('Completa los datos para crear un nuevo cliente.')
                                ->info()
                                ->send();

                            return;
                        }

                        $set('customer_id', $customer->id);
                        $set('customer_name', $customer->name ?? '');
                        $set('phone', $customer->phone ?? '');
                        $set('address', $customer->address ?? '');
                        $set('reference', $customer->address_references ?? '');

                        $this->simple['customer_id'] = $customer->id;
                        $this->simple['customer_name'] = $customer->name ?? '';
                        $this->simple['phone'] = $customer->phone ?? '';
                        $this->simple['address'] = $customer->address ?? '';
                        $this->simple['reference'] = $customer->address_references ?? '';

                        Notification::make()
                            ->title('Cliente encontrado')
                            ->body("Datos de {$customer->name} cargados.")
                            ->success()
                            ->send();
                    }),

                Forms\Components\Hidden::make('customer_id'),

                Forms\Components\TextInput::make('customer_name')
                    ->label('Nombre del cliente')
                    ->required()
                    ->placeholder('Ej. Juan Perez')
                    ->prefixIcon('heroicon-o-user')
                    ->disabled(fn (Forms\Get $get): bool => ! empty($get('customer_id')))
                    ->helperText(
                        fn (Forms\Get $get): string => ! empty($get('customer_id'))
                        ? 'Cliente encontrado en base de datos.'
                        : 'Ingresa el nombre del nuevo cliente.'
                    ),

                Forms\Components\Section::make('Direccion de entrega')
                    ->schema([
                        Forms\Components\Textarea::make('address')
                            ->label('')
                            ->required()
                            ->rows(3)
                            ->placeholder('Calle, numero y referencias breves')
                            ->disabled(fn (Forms\Get $get): bool => ! empty($get('customer_id')) && ! $get('edit_address_enabled'))
                            ->helperText(
                                fn (Forms\Get $get): string => ! empty($get('customer_id'))
                                ? 'Direccion del cliente registrado'.($get('edit_address_enabled') ? ' (editando)' : '')
                                : 'Ingresa la direccion de entrega'
                            )
                            ->columnSpanFull(),
                    ])
                    ->headerActions([
                        Forms\Components\Actions\Action::make('toggle_edit_address')
                            ->label(fn (Forms\Get $get): string => $get('edit_address_enabled') ? 'Guardar' : 'Editar')
                            ->icon(fn (Forms\Get $get): string => $get('edit_address_enabled') ? 'heroicon-m-check' : 'heroicon-m-pencil')
                            ->color(fn (Forms\Get $get): string => $get('edit_address_enabled') ? 'success' : 'primary')
                            ->visible(fn (Forms\Get $get): bool => ! empty($get('customer_id')))
                            ->action(function (Forms\Set $set, Forms\Get $get): void {
                                $isEditing = (bool) $get('edit_address_enabled');

                                if ($isEditing) {
                                    $customerId = $get('customer_id');
                                    $newAddress = $get('address');

                                    if ($customerId && $newAddress) {
                                        $customer = Customer::find($customerId);
                                        if ($customer) {
                                            $customer->update(['address' => $newAddress]);
                                        }
                                    }

                                    Notification::make()
                                        ->title('Direccion actualizada')
                                        ->success()
                                        ->send();
                                }

                                $set('edit_address_enabled', ! $isEditing);
                            }),
                    ])
                    ->columnSpanFull(),

                Forms\Components\Hidden::make('edit_address_enabled')
                    ->default(false),

                Forms\Components\Textarea::make('reference')
                    ->label('Referencias (opcional)')
                    ->rows(2)
                    ->placeholder('Color de casa, piso, indicaciones')
                    ->columnSpanFull(),

                Forms\Components\Section::make('Persona que recibe el delivery (Opcional)')
                    ->description('Informacion de contacto de quien recibira el pedido')
                    ->icon('heroicon-o-user-circle')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('recipient_name')
                            ->label('Nombre completo')
                            ->placeholder('Ej. Maria Gonzales')
                            ->prefixIcon('heroicon-o-user')
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('recipient_phone')
                            ->label('Telefono de contacto')
                            ->tel()
                            ->placeholder('9XXXXXXXX')
                            ->prefixIcon('heroicon-o-phone'),

                        Forms\Components\Textarea::make('recipient_address')
                            ->label('Direccion exacta de entrega')
                            ->rows(3)
                            ->placeholder('Calle, numero, departamento, referencias')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Hidden::make('delivery_latitude'),
                Forms\Components\Hidden::make('delivery_longitude'),
            ])
            ->statePath('simple')
            ->columns(1);
    }

    public function createSimpleDelivery(): void
    {
        $data = $this->getForm('simpleForm')->getState();

        if (empty($data['customer_name'])) {
            $data['customer_name'] = $this->simple['customer_name'] ?? '';
        }
        if (empty($data['phone'])) {
            $data['phone'] = $this->simple['phone'] ?? '';
        }
        if (empty($data['address'])) {
            $data['address'] = $this->simple['address'] ?? '';
        }

        $this->validate([
            'simple.phone' => 'required|string|min:6',
            'simple.customer_name' => 'required|string|min:3',
            'simple.address' => 'required|string|min:5',
            'simple.recipient_name' => 'nullable|string|min:3',
            'simple.recipient_phone' => 'nullable|string|min:6',
            'simple.recipient_address' => 'nullable|string|min:5',
            'simple.delivery_latitude' => 'nullable|numeric|between:-90,90',
            'simple.delivery_longitude' => 'nullable|numeric|between:-180,180',
        ]);

        try {
            DB::transaction(function () use ($data): void {
                if (empty($data['customer_name']) || empty($data['phone']) || empty($data['address'])) {
                    throw new \Exception('Faltan datos obligatorios del cliente (nombre, telefono o direccion).');
                }

                if (! empty($data['customer_id'])) {
                    $customer = Customer::find($data['customer_id']);
                    if ($customer) {
                        $customer->update([
                            'name' => $data['customer_name'] ?: $customer->name,
                            'phone' => $data['phone'] ?: $customer->phone,
                            'address' => $data['address'] ?: $customer->address,
                            'address_references' => $data['reference'] ?? $customer->address_references,
                        ]);
                    }
                } else {
                    $customer = Customer::where('phone', $data['phone'])->first();

                    if (! $customer) {
                        $customer = Customer::create([
                            'name' => $data['customer_name'],
                            'phone' => $data['phone'],
                            'address' => $data['address'],
                            'address_references' => $data['reference'] ?? '',
                        ]);
                    } else {
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
                    'delivery_latitude' => $data['delivery_latitude'] ?? null,
                    'delivery_longitude' => $data['delivery_longitude'] ?? null,
                    'status' => DeliveryOrder::STATUS_PENDING,
                ]);

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
                    ],
                ]);

                Notification::make()
                    ->title('Pedido de Delivery Creado')
                    ->body('Redirigiendo al POS para cargar productos...')
                    ->success()
                    ->send();

                $this->redirect('/admin/pos-interface?from=delivery&order_id='.$order->id);
            });
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al crear')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function logMapDebug(string $event, array $context = []): void
    {
        Log::info('delivery.simple.map.debug', [
            'event' => $event,
            'context' => $context,
            'user_id' => Auth::id(),
            'url' => request()->fullUrl(),
        ]);
    }

    public function geocodeAddressForMap(string $query): array
    {
        $normalized = $this->normalizeAddressQuery($query);
        if ($normalized === '') {
            return [
                'ok' => false,
                'message' => 'Consulta vacia',
            ];
        }

        $candidates = $this->buildGeocodeCandidates($normalized);
        $service = app(DeliveryGeocodingService::class);

        foreach ($candidates as $candidate) {
            try {
                $coordinates = $service->geocodeAddress($candidate);
            } catch (\Throwable $e) {
                $this->logMapDebug('geocode.backend.error', [
                    'candidate' => $candidate,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            if (! is_array($coordinates) || ! isset($coordinates['lat'], $coordinates['lng'])) {
                $this->logMapDebug('geocode.backend.no_result', [
                    'candidate' => $candidate,
                ]);

                continue;
            }

            $this->logMapDebug('geocode.backend.success', [
                'candidate' => $candidate,
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng'],
            ]);

            return [
                'ok' => true,
                'candidate' => $candidate,
                'lat' => $coordinates['lat'],
                'lng' => $coordinates['lng'],
            ];
        }

        return [
            'ok' => false,
            'message' => 'Sin coincidencias',
            'candidate' => $candidates[count($candidates) - 1] ?? $normalized,
        ];
    }

    private function normalizeAddressQuery(string $query): string
    {
        $normalized = preg_replace('/[\r\n]+/', ' ', $query);
        $normalized = preg_replace('/\s+/', ' ', (string) $normalized);
        $normalized = trim((string) $normalized);

        if ($normalized === '') {
            return '';
        }

        $cleaned = preg_replace('/\b(calle|jr|jiron|av|avenida)\s*$/iu', '', $normalized);
        $cleaned = trim((string) $cleaned);

        return $cleaned !== '' ? $cleaned : $normalized;
    }

    /**
     * @return array<int, string>
     */
    private function buildGeocodeCandidates(string $normalized): array
    {
        $candidates = [$normalized];

        if (! preg_match('/\bperu\b/i', $normalized)) {
            $candidates[] = $normalized.', Peru';
        }

        return array_values(array_unique($candidates));
    }
}
