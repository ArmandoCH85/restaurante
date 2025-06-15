<?php

namespace App\Filament\Pages;

use App\Models\ProductCategory;
use App\Models\Product;
use App\Models\Table as TableModel;
use App\Models\Customer;
use App\Models\Order;
use App\Models\DocumentSeries;
use App\Models\Employee;
use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Support\Colors\Color;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\CashMovement;
use App\Filament\Pages\TableMap;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\DB;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\CheckboxList;
use Filament\Actions\ActionGroup;
use App\Models\Invoice;
use App\Models\CashRegister;
use Filament\Support\Exceptions\Halt;
use Filament\Forms\Components\Section;
use Illuminate\Support\Collection;

class PosInterface extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';
    protected static string $view = 'filament.pages.pos-interface';
    protected static ?string $title = 'Punto de Venta';
    protected static ?string $navigationLabel = 'Venta Directa';
    protected static ?string $navigationGroup = '🏪 Operaciones Diarias';
    protected static ?string $slug = 'pos-interface';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // ✅ ARREGLO: Verificar primero si es waiter específicamente
        if ($user->hasRole('waiter')) {
            return true;
        }

        // Super admin, admin, cashier pueden acceder siempre
        if ($user->hasRole(['super_admin', 'admin', 'cashier'])) {
            return true;
        }

        // Para otros roles, verificar permisos
        return $user->can('access_pos');
    }

    // Propiedades del estado
    public ?int $selectedTableId = null;
    public ?int $selectedCategoryId = null;
    public array $cartItems = [];
    public float $total = 0.0;
    public float $subtotal = 0.0;
    public float $tax = 0.0;

    // Propiedades para la transferencia
    public array $transferItems = [];

    // Propiedad para mantener la orden activa
    public ?Order $order = null;

    // Propiedades para facturación
    public ?int $selectedCustomerId = null;
    public string $selectedDocumentType = 'receipt'; // 'receipt', 'invoice', 'sales_note'
    public string $customerName = '';
    public string $customerDocument = '';
    public string $customerAddress = '';
    public string $selectedPaymentMethod = 'cash';
    public float $paymentAmount = 0.0;
    public float $cashReceived = 0.0;

    // Propiedades para datos originales del cliente (para preservar al cambiar tipo de documento)
    public ?array $originalCustomerData = null;

    // Propiedad para el nombre del cliente en venta directa
    public string $customerNameForComanda = '';

    // Datos cargados
    public $categories;
    public $products;
    public $tables;
    public $customers;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reopen_order')
                ->label('Reabrir Orden')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->button()
                ->url(fn () => $this->order instanceof Order ? url("/admin/pos-interface?table_id={$this->selectedTableId}&order_id={$this->order->getKey()}") : '')
                ->visible(fn () =>
                    $this->order instanceof Order &&
                    !$this->order->invoices()->exists()
                ),

            // 🖨️ BOTÓN DE IMPRESIÓN ÚLTIMO COMPROBANTE
            Action::make('printLastInvoice')
                ->label('🖨️ Imprimir Último Comprobante')
                ->icon('heroicon-o-printer')
                ->color('success')
                ->size('lg')
                ->button()
                ->outlined()
                ->tooltip('Imprimir el último comprobante generado para esta orden')
                ->action(function () {
                    if ($this->order && $this->order->invoices()->exists()) {
                        $lastInvoice = $this->order->invoices()->latest()->first();
                        // Registrar intento de impresión en el log
                        Log::info('🖨️ Intentando abrir ventana de impresión desde PosInterface', [
                            'invoice_id' => $lastInvoice->id,
                            'invoice_type' => $lastInvoice->invoice_type,
                            'invoice_url' => route('filament.admin.resources.facturacion.comprobantes.print', ['record' => $lastInvoice->id])
                        ]);

                        $this->dispatch('open-print-window', ['id' => $lastInvoice->id]);

                        Notification::make()
                            ->title('🖨️ Abriendo impresión...')
                            ->body('Se abrió la ventana de impresión del comprobante')
                            ->success()
                            ->duration(3000)
                            ->send();
                    } else {
                        Notification::make()
                            ->title('❌ Sin comprobantes')
                            ->body('No hay comprobantes generados para imprimir')
                            ->warning()
                            ->duration(4000)
                            ->send();
                    }
                })
                ->visible(fn(): bool => $this->order && $this->order->invoices()->exists()),

            Action::make('backToTableMap')
                ->label('Mapa de Mesas')
                ->icon('heroicon-o-map')
                ->color('gray')
                ->size('lg')
                ->button()
                ->outlined()
                ->tooltip('Volver a la vista del mapa de mesas')
                ->url(fn(): string => TableMap::getUrl())
                ->visible(fn(): bool => $this->order && $this->order->table_id !== null), // ✅ Solo para órdenes con mesa

            Action::make('releaseTable')
                ->label('Liberar Mesa')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Liberación')
                ->modalDescription('¿Estás seguro de que deseas marcar esta orden como PAGADA y liberar la mesa? Esta acción no se puede deshacer.')
                ->action(function () {
                    if ($this->order) {
                        $table = $this->order->table;
                        $this->order->update(['status' => Order::STATUS_COMPLETED]);
                        if ($table) {
                            $table->update(['status' => TableModel::STATUS_AVAILABLE]);
                        }
                        Notification::make()->title('Mesa Liberada')->success()->send();
                        return redirect(TableMap::getUrl());
                    }
                })
                ->visible(fn(): bool => $this->order && $this->order->table_id !== null && $this->order->status === Order::STATUS_OPEN && !Auth::user()->hasRole(['waiter', 'cashier'])), // ✅ Solo para órdenes con mesa y no visible para waiter/cashier

            Action::make('cancelOrder')
                ->label('Cancelar Orden')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Confirmar Cancelación')
                ->modalDescription('¿Estás seguro de que deseas CANCELAR esta orden? Los productos no se cobrarán y la mesa quedará libre. Esta acción no se puede deshacer.')
                ->action(function () {
                    if ($this->order) {
                        $table = $this->order->table;
                        $this->order->update(['status' => Order::STATUS_CANCELLED]);
                        if ($table) {
                            $table->update(['status' => TableModel::STATUS_AVAILABLE]);
                        }
                        Notification::make()->title('Orden Cancelada')->success()->send();
                        return redirect(TableMap::getUrl());
                    }
                })
                ->visible(fn(): bool => $this->order && $this->order->table_id !== null && $this->order->status === Order::STATUS_OPEN && !Auth::user()->hasRole(['waiter', 'cashier'])), // ✅ Solo para órdenes con mesa y no visible para waiter/cashier

            Action::make('transferOrder')
                ->label('Transferir')
                ->icon('heroicon-o-arrows-right-left')
                ->color('warning')
                ->slideOver()
                ->modalWidth('2xl')
                ->fillForm(function () {
                    $this->transferItems = [];
                    if ($this->order) {
                        foreach ($this->order->orderDetails as $detail) {
                            $this->transferItems[] = [
                                'order_detail_id' => $detail->id,
                                'product_name' => $detail->product->name,
                                'original_quantity' => $detail->quantity,
                                'quantity_to_move' => 0,
                            ];
                        }
                    }
                    return [
                        'transferItems' => $this->transferItems,
                    ];
                })
                ->form([
                    Repeater::make('transferItems')
                        ->label('Productos en la Mesa Actual')
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\Placeholder::make('product_name')
                                    ->label('Producto')
                                    ->content(fn($state) => $state)
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('quantity_to_move')
                                    ->label('Mover')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(fn(Get $get) => $get('original_quantity'))
                                    ->required()
                                    ->default(0)
                                    ->helperText(fn(Get $get) => 'Max: ' . $get('original_quantity'))
                                    ->columnSpan(1),
                            ]),
                            Forms\Components\Hidden::make('order_detail_id'),
                            Forms\Components\Hidden::make('original_quantity'),
                        ])
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->columnSpanFull()
                        ->itemLabel(''),

                    Select::make('new_table_id')
                        ->label('Mesa de Destino')
                        ->options(TableModel::where('id', '!=', $this->selectedTableId)->pluck('number', 'id'))
                        ->required()
                        ->searchable()
                        ->placeholder('Elige una mesa para la transferencia...'),
                ])
                ->action(function (array $data): void {
                    $this->processTransfer($data);
                })
                ->modalHeading('Transferir / Combinar Cuentas')
                ->modalDescription('Selecciona los productos y la cantidad a mover a otra mesa.')
                ->visible(fn(): bool => $this->order && $this->order->table_id && $this->order->status === Order::STATUS_OPEN && !Auth::user()->hasRole(['waiter', 'cashier'])), // ✅ No visible para waiter/cashier

            Action::make('splitBill')
                ->label('Dividir Cuenta')
                ->icon('heroicon-o-scissors')
                ->color('info')
                ->slideOver()
                ->modalWidth('xl')
                ->fillForm(function (): array {
                    if (!$this->order) return [];

                    $options = $this->order->orderDetails->mapWithKeys(function ($detail) {
                        return [$detail->id => sprintf(
                            '%d x %s  (Subtotal: S/. %s)',
                            $detail->quantity,
                            $detail->product->name,
                            number_format($detail->quantity * $detail->unit_price, 2)
                        )];
                    })->all();

                    return [
                        'items_to_split' => $options
                    ];
                })
                ->form([
                    Forms\Components\Placeholder::make('info')
                        ->content('Selecciona los productos que deseas mover a una nueva cuenta separada.'),
                    Forms\Components\CheckboxList::make('selected_details')
                        ->label('Productos en la cuenta actual')
                        ->options(fn(Get $get) => $get('items_to_split'))
                        ->required()
                        ->columns(1)
                        ->gridDirection('row'),
                ])
                ->action(function (array $data): void {
                    $this->processSimpleSplit($data);
                })
                ->modalHeading('Separar Productos en Nueva Cuenta')
                ->modalDescription('Los productos seleccionados se moverán a una cuenta nueva para ser pagados por separado.')
                ->visible(fn(): bool => $this->order && $this->order->table_id !== null && $this->order->status === Order::STATUS_OPEN && !Auth::user()->hasRole(['waiter', 'cashier'])), // ✅ Solo para órdenes con mesa y no visible para waiter/cashier

            Action::make('printComanda')
                ->label('Comanda')
                ->icon('heroicon-o-printer')
                ->form(function () {
                    // ✅ Si es venta directa (sin mesa), solicitar nombre del cliente
                    if ($this->selectedTableId === null && empty($this->customerNameForComanda)) {
                        return [
                            Forms\Components\TextInput::make('customerNameForComanda')
                                ->label('Nombre del Cliente')
                                ->placeholder('Ingrese el nombre del cliente')
                                ->required()
                                ->maxLength(100)
                                ->helperText('Este nombre aparecerá en la comanda impresa')
                                ->default($this->customerNameForComanda)
                        ];
                    }
                    return [];
                })
                ->action(function (array $data) {
                    // ✅ PRIMERO: Guardar el nombre del cliente si es venta directa
                    if ($this->selectedTableId === null && isset($data['customerNameForComanda'])) {
                        $this->customerNameForComanda = $data['customerNameForComanda'];
                    }

                    // ✅ SEGUNDO: Crear la orden si no existe (ahora CON el nombre del cliente guardado)
                    if (!$this->order && !empty($this->cartItems)) {
                        $this->order = $this->createOrderFromCart();
                    }

                    // Obtener un cliente genérico para asegurar que siempre haya un cliente disponible
                    $genericCustomer = \App\Models\Customer::getGenericCustomer();

                    // ✅ TERCERO: Abrir comanda en nueva ventana para imprimir (con datos de empresa)
                    $url = route('orders.comanda.pdf', [
                        'order' => $this->order,
                        'customerName' => $this->customerNameForComanda
                    ]);

                    $this->js("window.open('$url', '_blank', 'width=800,height=600')");

                    // ✅ REDIRIGIR AL MAPA DE MESAS SI TIENE MESA (PARA TODOS LOS ROLES)
                    if ($this->selectedTableId) {
                        $redirectUrl = TableMap::getUrl();

                        \Illuminate\Support\Facades\Log::debug('🔴 REDIRECCIÓN DESDE IMPRIMIR COMANDA', [
                            'table_id' => $this->selectedTableId,
                            'order_id' => $this->order?->id,
                            'redirect_url' => $redirectUrl,
                            'timestamp' => now()->format('Y-m-d H:i:s.u')
                        ]);

                        Notification::make()
                            ->title('Comanda Enviada')
                            ->body('Pedido guardado correctamente. Regresando al mapa de mesas...')
                            ->success()
                            ->duration(2000)
                            ->send();

                        $this->js("
                            console.log('Redirigiendo a mapa de mesas desde imprimir comanda');
                            setTimeout(function() {
                                window.location.href = '{$redirectUrl}';
                            }, 500);
                        ");
                    }
                })
                ->modalHeading('Imprimir Comanda')
                ->modalSubmitActionLabel('Confirmar')
                ->button()
                ->outlined()
                ->color('warning')
                ->size('lg')
                ->visible(fn(): bool => (bool) $this->order || !empty($this->cartItems)), // ✅ Visible para todos los roles

            Action::make('printPreBill')
                ->label('Pre-Cuenta')
                ->icon('heroicon-o-printer')
                ->action(function () {
                    // ✅ Crear la orden si no existe antes de mostrar la pre-cuenta
                    if (!$this->order && !empty($this->cartItems)) {
                        $this->order = $this->createOrderFromCart();
                    }
                })
                ->modalContent(function (): \Illuminate\Contracts\View\View {
                    // Obtener datos de empresa para la plantilla
                    return view('filament.modals.print-prebill', [
                        'order' => $this->order
                    ]);
                })
                ->modalSubmitAction(
                    Action::make('printPreBillSubmit')
                        ->label('Imprimir')
                        ->color('success')
                        ->icon('heroicon-o-printer')
                        ->extraAttributes([
                            'onclick' => 'window.print(); return false;',
                        ])
                        ->action(function () {
                            // ✅ Si es waiter, redirigir automáticamente al mapa de mesas después de imprimir
                            if (Auth::user()->hasRole('waiter')) {
                                Notification::make()
                                    ->title('Pre-cuenta Impresa')
                                    ->body('Pre-cuenta generada correctamente. Regresando al mapa de mesas...')
                                    ->success()
                                    ->duration(2000)
                                    ->send();

                                // Redirigir después de un breve delay para que se complete la impresión
                                $this->js("setTimeout(function() { window.location.href = '" . TableMap::getUrl() . "'; }, 2000);");
                            }
                        })
                )
                ->modalCancelActionLabel('Cerrar')
                ->modalHeading('Vista Previa de Pre-Cuenta')
                ->button()
                ->outlined()
                ->color('info')
                ->size('lg')
                ->visible(fn(): bool => (bool) $this->order || !empty($this->cartItems)), // ✅ Visible para todos los roles,
        ];
    }



    public function mount(): void
    {
        // Obtener parámetros de la URL
        $this->selectedTableId = request()->get('table_id');
        $orderId = request()->get('order_id');

        // *** LÓGICA PARA CARGAR ORDEN EXISTENTE POR ID ***
        if ($orderId) {
            $activeOrder = Order::with(['orderDetails.product', 'customer', 'invoices'])
                ->where('id', $orderId)
                ->first();

            if ($activeOrder) {
                // Solo verificar si tiene comprobantes
                if ($activeOrder->invoices()->exists()) {
                    Notification::make()
                        ->title('No se puede reabrir')
                        ->body('Esta orden ya tiene un comprobante emitido.')
                        ->danger()
                        ->send();

                    $this->redirect(TableMap::getUrl());
                    return;
                }

                // Reabrir la orden sin importar su estado previo
                $activeOrder->status = Order::STATUS_OPEN;
                $activeOrder->billed = false;
                $activeOrder->save();

                // Actualizar estado de la mesa si existe
                if ($activeOrder->table) {
                    $activeOrder->table->update(['status' => TableModel::STATUS_OCCUPIED]);
                }

                Notification::make()
                    ->title('Orden reabierta')
                    ->success()
                    ->send();

                $this->order = $activeOrder;
                $this->selectedTableId = $activeOrder->table_id;
                $this->cartItems = [];

                foreach ($activeOrder->orderDetails as $detail) {
                    $this->cartItems[] = [
                        'product_id' => $detail->product_id,
                        'name' => $detail->product ? $detail->product->name : 'Producto eliminado',
                        'quantity' => $detail->quantity,
                        'unit_price' => $detail->unit_price,
                        'subtotal' => $detail->subtotal,
                    ];
                }

                // ✅ CARGAR DATOS ORIGINALES DEL CLIENTE DE LA ORDEN DE DELIVERY
                if ($activeOrder->customer) {
                    $this->originalCustomerData = [
                        'customer_id' => $activeOrder->customer->id,
                        'customer_name' => $activeOrder->customer->name,
                        'customer_document_type' => $activeOrder->customer->document_type ?: 'DNI',
                        'customer_document' => $activeOrder->customer->document_number ?: '',
                        'customer_address' => $activeOrder->customer->address ?: '',
                        'customer_phone' => $activeOrder->customer->phone ?: '',
                        'customer_email' => $activeOrder->customer->email ?: '',
                    ];

                    \Illuminate\Support\Facades\Log::info('🔍 DATOS ORIGINALES DEL CLIENTE CARGADOS:', $this->originalCustomerData);
                }
            }
        }
        // *** LÓGICA PARA CARGAR ORDEN EXISTENTE POR MESA ***
        elseif ($this->selectedTableId) {
            // Buscar la orden abierta para esta mesa
            $activeOrder = Order::with('orderDetails.product')
                ->where('table_id', $this->selectedTableId)
                ->where('status', Order::STATUS_OPEN)
                ->first();

            if ($activeOrder) {
                $this->order = $activeOrder;
                $this->cartItems = []; // Limpiar por si acaso

                foreach ($activeOrder->orderDetails as $detail) {
                    $this->cartItems[] = [
                        'product_id' => $detail->product_id,
                        'name' => $detail->product ? $detail->product->name : 'Producto eliminado',
                        'quantity' => $detail->quantity,
                        'unit_price' => $detail->unit_price,
                        'subtotal' => $detail->subtotal,
                    ];
                }
            }
        }

        // Cargar datos generales
        $this->categories = ProductCategory::with('products')->get();
        $this->products = Product::with('category')->get();
        $this->tables = TableModel::all();
        $this->customers = Customer::all();

        // Filtrar productos si hay categoría seleccionada
        if ($this->selectedCategoryId) {
            $this->products = $this->products->where('category_id', $this->selectedCategoryId);
        }

        // Calcular totales (se hará con el carrito cargado si existe)
        $this->calculateTotals();
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;

        if ($categoryId) {
            $this->products = Product::where('category_id', $categoryId)->with('category')->get();
        } else {
            $this->products = Product::with('category')->get();
        }
    }

    public function addToCart(int $productId): void
    {
        $product = Product::find($productId);

        if (!$product) {
            Notification::make()
                ->title('Producto no encontrado')
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }

        // Verificar si el producto ya está en el carrito
        $existingItemKey = null;
        foreach ($this->cartItems as $key => $item) {
            if ($item['product_id'] === $productId) {
                $existingItemKey = $key;
                break;
            }
        }

        if ($existingItemKey !== null) {
            // Incrementar cantidad si ya existe
            $this->cartItems[$existingItemKey]['quantity']++;
            $this->cartItems[$existingItemKey]['subtotal'] =
                $this->cartItems[$existingItemKey]['quantity'] *
                $this->cartItems[$existingItemKey]['unit_price'];
        } else {
            // Agregar nuevo producto al carrito
            $this->cartItems[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'unit_price' => $product->price,
                'subtotal' => $product->price,
            ];
        }

        $this->calculateTotals();

        // Notificación discreta y rápida
        Notification::make()
            ->title($product->name . ' agregado')
            ->success()
            ->duration(2000)
            ->send();
    }

    public function removeFromCart(int $index): void
    {
        if (isset($this->cartItems[$index])) {
            $productName = $this->cartItems[$index]['name'];
            unset($this->cartItems[$index]);
            $this->cartItems = array_values($this->cartItems);
            $this->calculateTotals();

            // Notificación muy breve para eliminación
            Notification::make()
                ->title('Producto eliminado')
                ->warning()
                ->duration(1500)
                ->send();
        }
    }

    public function updateQuantity(int $index, int $quantity): void
    {
        if (isset($this->cartItems[$index])) {
            if ($quantity <= 0) {
                $this->removeFromCart($index);
                return;
            }

            $this->cartItems[$index]['quantity'] = $quantity;
            $this->cartItems[$index]['subtotal'] =
                $quantity * $this->cartItems[$index]['unit_price'];

            $this->calculateTotals();

            // Sin notificación para actualización de cantidad (muy frecuente)
            // Solo feedback visual automático
        }
    }

    public function clearCart(): void
    {
        $this->cartItems = [];
        $this->calculateTotals();

        Notification::make()
            ->title('Carrito limpiado')
            ->success()
            ->duration(1500)
            ->send();
    }

    protected function calculateTotals(): void
    {
        // KISS: El precio del producto ya incluye IGV.
        // 1. El total es la suma de los precios de los productos.
        $this->total = collect($this->cartItems)->sum('subtotal');

        // 2. Calculamos la base imponible (subtotal) dividiendo el total por 1.18.
        $this->subtotal = $this->total / 1.18;

        // 3. El impuesto es la diferencia.
        $this->tax = $this->total - $this->subtotal;
    }

    /**
     * Método para crear orden desde el carrito - reutilizable para comanda y pre-cuenta
     */
    protected function createOrderFromCart(): ?Order
    {
        if (empty($this->cartItems)) {
            return null;
        }

        try {
            // Obtener la caja registradora activa
            $activeCashRegister = CashRegister::getOpenRegister();

            if (!$activeCashRegister) {
                throw new \Exception('No hay una caja registradora abierta. Por favor, abra una caja antes de crear una orden.');
            }

            // Iniciar una transacción para garantizar la atomicidad
            return DB::transaction(function () use ($activeCashRegister) {
                // Crear la orden
                $userId = Auth::id();
                $employee = DB::table('employees')->where('user_id', $userId)->first();

                if (!$employee) {
                    Notification::make()
                        ->title('Error')
                        ->body('El usuario no tiene un empleado asociado en la tabla employees')
                        ->danger()
                        ->send();
                    return null;
                }

                $order = Order::create([
                    'table_id' => $this->selectedTableId,
                    'employee_id' => $employee->id,
                    'customer_id' => null, // Por ahora sin cliente específico
                    'cash_register_id' => $activeCashRegister->id,
                    'service_type' => $this->selectedTableId ? 'dine_in' : 'takeout',
                    'subtotal' => $this->subtotal,
                    'tax' => $this->tax,
                    'total' => $this->total,
                    'status' => Order::STATUS_OPEN,
                    'order_datetime' => now(),
                ]);

                // Agregar productos a la orden usando el método del modelo
                foreach ($this->cartItems as $item) {
                    $order->addProduct(
                        $item['product_id'],
                        $item['quantity'],
                        $item['unit_price']
                    );
                }

                // Recalcular totales de la orden
                $order->recalculateTotals();

                // *** MEJORA DE UX: Cambiar estado de la mesa a ocupada ***
                if ($this->selectedTableId) {
                    $table = TableModel::find($this->selectedTableId);
                    if ($table && $table->status === TableModel::STATUS_AVAILABLE) {
                        $table->update(['status' => TableModel::STATUS_OCCUPIED]);
                    }
                }

                return $order;
            });
        } catch (\Exception $e) {
            // Si algo falla, la transacción hará rollback automáticamente
            throw new \Exception('Error al crear la orden: ' . $e->getMessage());
        }
    }

    public function processOrder(): void
    {
        if (empty($this->cartItems)) {
            Notification::make()
                ->title('Error')
                ->body('No hay productos en el carrito')
                ->danger()
                ->send();
            return;
        }

        try {
            if ($this->order) {
                // Actualizar orden existente
                DB::transaction(function () {
                    // Agregar nuevos productos a la orden existente
                    foreach ($this->cartItems as $item) {
                        // Verificar si el producto ya existe en la orden
                        $existingDetail = $this->order->orderDetails()
                            ->where('product_id', $item['product_id'])
                            ->first();

                        if ($existingDetail) {
                            // Si existe, actualizar la cantidad directamente con la del carrito
                            $existingDetail->update([
                                'quantity' => $item['quantity'],
                                'subtotal' => $item['quantity'] * $item['unit_price']
                            ]);
                        } else {
                            // Si no existe, agregar como nuevo
                            $this->order->addProduct(
                                $item['product_id'],
                                $item['quantity'],
                                $item['unit_price']
                            );
                        }
                    }

                    // Recalcular totales
                    $this->order->recalculateTotals();

                    // Asegurar que la orden esté abierta
                    $this->order->update([
                        'status' => Order::STATUS_OPEN,
                        'billed' => false
                    ]);

                    // Actualizar estado de la mesa si es necesario
                    if ($this->selectedTableId) {
                        $table = TableModel::find($this->selectedTableId);
                        if ($table) {
                            $table->update(['status' => TableModel::STATUS_OCCUPIED]);
                        }
                    }
                });

                // Limpiar el carrito después de guardar
                $this->cartItems = [];
                $this->calculateTotals();

                Notification::make()
                    ->title('🎉 Orden actualizada exitosamente')
                    ->body('Orden #' . $this->order->id . ' actualizada con éxito')
                    ->success()
                    ->send();

                // Forzar recarga de la orden para actualizar el estado
                $this->order = $this->order->fresh(['orderDetails.product', 'table', 'invoices']);
            } else {
                // Crear nueva orden
                $order = $this->createOrderFromCart();

                if ($order) {
                    $this->order = $order;

                    Notification::make()
                        ->title('🎉 Orden creada exitosamente')
                        ->body('Orden #' . $order->id . ' creada con éxito')
                        ->success()
                        ->send();
                }
            }

            // Refrescar datos y UI
            $this->refreshOrderData();
            $this->dispatch('$refresh');

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Procesa la lógica de transferencia de items entre mesas.
     */
    public function processTransfer(array $data): void
    {
        try {
            DB::transaction(function () use ($data) {
                $originOrder = $this->order;
                $newTableId = $data['new_table_id'];
                $itemsToMove = collect($data['transferItems'])->where('quantity_to_move', '>', 0);

                if ($itemsToMove->isEmpty()) {
                    Notification::make()->title('Nada que mover')->body('No has seleccionado productos para transferir.')->warning()->send();
                    return;
                }

                // Obtener la caja registradora activa
                $activeCashRegister = CashRegister::getOpenRegister();

                if (!$activeCashRegister) {
                    throw new \Exception('No hay una caja registradora abierta. Por favor, abra una caja antes de crear una orden.');
                }

                $destinationOrder = Order::firstOrCreate(
                    ['table_id' => $newTableId, 'status' => Order::STATUS_OPEN],
                    [
                        'employee_id' => Auth::id(),
                        'service_type' => 'dine_in',
                        'subtotal' => 0,
                        'tax' => 0,
                        'total' => 0,
                        'order_datetime' => now(),
                        'cash_register_id' => $activeCashRegister->id
                    ]
                );

                foreach ($itemsToMove as $item) {
                    $originDetail = $originOrder->orderDetails()->find($item['order_detail_id']);
                    $quantityToMove = (int)$item['quantity_to_move'];

                    if ($quantityToMove >= $originDetail->quantity) {
                        $originDetail->update(['order_id' => $destinationOrder->id]);
                    } else {
                        $originDetail->decrement('quantity', $quantityToMove);
                        $destinationOrder->addProduct($originDetail->product_id, $quantityToMove, $originDetail->unit_price);
                    }
                }

                $originOrder->recalculateTotals();
                $destinationOrder->recalculateTotals();

                // Forzar la recarga de la relación para obtener el conteo correcto
                $originOrder->load('orderDetails');

                if ($originOrder->orderDetails()->count() === 0) {
                    $originOrder->update(['status' => Order::STATUS_CANCELLED]);
                    if ($originOrder->table) {
                        $originOrder->table->update(['status' => TableModel::STATUS_AVAILABLE]);
                    }
                }

                if ($destinationOrder->wasRecentlyCreated && $destinationOrder->table) {
                    $destinationOrder->table->update(['status' => TableModel::STATUS_OCCUPIED]);
                }

                Notification::make()->title('Transferencia Exitosa')->success()->send();
                $this->refreshOrderData(true); // Forzar recarga completa
            });
        } catch (\Exception $e) {
            Notification::make()->title('Error en la Transferencia')->body($e->getMessage())->danger()->send();
        }
    }

    public function processSimpleSplit(array $data): void
    {
        $originalOrder = $this->order;
        $selectedDetailIds = $data['selected_details'];

        if (empty($selectedDetailIds)) {
            Notification::make()->title('Error')->body('No has seleccionado ningún producto para separar.')->warning()->send();
            return;
        }

        try {
            DB::transaction(function () use ($originalOrder, $selectedDetailIds) {
                // Crear la nueva orden "hija"
                $childOrder = Order::create([
                    'parent_id' => $originalOrder->id,
                    'table_id' => $originalOrder->table_id,
                    'customer_id' => $originalOrder->customer_id,
                    'employee_id' => Auth::id(),
                    'service_type' => $originalOrder->service_type,
                    'status' => Order::STATUS_OPEN,
                    'order_datetime' => now(),
                    'notes' => 'Cuenta separada de la orden #' . $originalOrder->id,
                ]);

                // Mover los detalles de orden seleccionados
                $detailsToMove = $originalOrder->orderDetails()->whereIn('id', $selectedDetailIds)->get();

                foreach ($detailsToMove as $detail) {
                    $detail->update(['order_id' => $childOrder->id]);
                }

                // Recalcular totales para ambas órdenes
                $originalOrder->recalculateTotals();
                $childOrder->recalculateTotals();

                // Opcional: si la orden original queda vacía, cancelarla
                if ($originalOrder->orderDetails()->count() === 0) {
                    $originalOrder->update(['status' => Order::STATUS_CANCELLED]);
                }
            });

            Notification::make()->title('¡Cuenta Separada!')->body('Se ha creado una nueva cuenta con los productos seleccionados.')->success()->send();

            // Forzar actualización de la interfaz
            $this->refreshOrderData(true);
        } catch (\Exception $e) {
            Log::error('Error al dividir cuenta (simple): ' . $e->getMessage());
            Notification::make()->title('Error al Separar la Cuenta')->body('Ocurrió un error inesperado. Por favor, intenta de nuevo.')->danger()->send();
        }
    }

    // Getters para la vista
    public function getCategoriesProperty()
    {
        return $this->categories;
    }

    public function getProductsProperty()
    {
        if ($this->selectedCategoryId) {
            return Product::where('category_id', $this->selectedCategoryId)->get();
        }
        return Product::all();
    }

    public function getCartItemsProperty()
    {
        return $this->cartItems;
    }

    public function getTotalProperty()
    {
        return $this->total;
    }

    /**
     * Recarga los datos de la orden activa desde la base de datos y reconstruye el carrito.
     */
    public function refreshOrderData(bool $forceRedirectOnEmpty = false): void
    {
        if (!$this->order) {
            return;
        }

        $this->order = $this->order->fresh(['orderDetails.product', 'table', 'invoices']);

        if (!$this->order || $this->order->status !== Order::STATUS_OPEN) {
            $this->clearCart();
            if ($forceRedirectOnEmpty) {
                $this->redirect(TableMap::getUrl());
            }
            return;
        }

        // 🔄 RECONSTRUIR CARRITO DESDE LA ORDEN
        $this->cartItems = [];
        foreach ($this->order->orderDetails as $detail) {
            $this->cartItems[] = [
                'product_id' => $detail->product_id,
                'name'       => $detail->product->name,
                'quantity'   => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'subtotal'   => $detail->subtotal,
            ];
        }

        $this->calculateTotals();

        // 🎯 ASEGURAR QUE LA MESA ESTÉ SELECCIONADA SI EXISTE
        if ($this->order->table_id) {
            $this->selectedTableId = $this->order->table_id;
        }
    }

    public function processBillingAction(): Action
    {
        return Action::make('processBilling')
            ->label('💳 Procesar Pago')
            ->slideOver()
            ->modalWidth('4xl')
            ->visible(fn() => Auth::user()->hasRole(['cashier', 'admin', 'super_admin']))
            ->form(function () {
                return [
                    // 🛒 RESUMEN COMPACTO: PRODUCTOS + TOTAL
                    Section::make('🛒 Resumen de la Venta')
                        ->description('TOTAL: S/ ' . number_format($this->total, 2) . ' (IGV incluido)')
                        ->compact()
                        ->collapsible()
                        ->collapsed(true)
                        ->schema([
                            Forms\Components\Placeholder::make('order_summary')
                                ->label('')
                                ->content(function () {
                                    // ✅ USAR CARRITO SI NO HAY ORDEN (VENTA DIRECTA)
                                    $items = [];
                                    if ($this->order && $this->order->orderDetails) {
                                        // Usar orden existente
                                        foreach ($this->order->orderDetails as $detail) {
                                            $items[] = [
                                                'name' => $detail->product->name ?? 'N/A',
                                                'quantity' => $detail->quantity,
                                                'unit_price' => $detail->unit_price,
                                                'subtotal' => $detail->subtotal,
                                            ];
                                        }
                                    } elseif (!empty($this->cartItems)) {
                                        // Usar carrito para venta directa
                                        $items = $this->cartItems;
                                    }

                                    if (empty($items)) {
                                        return new \Illuminate\Support\HtmlString('<div class="p-2 text-center text-gray-500 text-sm">❌ No hay productos</div>');
                                    }

                                    $html = '<div class="border border-gray-200 rounded overflow-hidden">';
                                    $html .= '<table class="w-full text-xs">';
                                    $html .= '<thead class="bg-gray-50"><tr>';
                                    $html .= '<th class="px-2 py-1 text-left">Producto</th>';
                                    $html .= '<th class="px-2 py-1 text-center">Cant.</th>';
                                    $html .= '<th class="px-2 py-1 text-right">Precio</th>';
                                    $html .= '<th class="px-2 py-1 text-right">Total</th>';
                                    $html .= '</tr></thead><tbody>';

                                    foreach ($items as $item) {
                                        $html .= '<tr class="border-b border-gray-100">';
                                        $html .= '<td class="px-2 py-1 text-sm">' . substr(htmlspecialchars($item['name']), 0, 25) . '</td>';
                                        $html .= '<td class="px-2 py-1 text-center text-sm">' . $item['quantity'] . '</td>';
                                        $html .= '<td class="px-2 py-1 text-right text-sm">S/ ' . number_format($item['unit_price'], 2) . '</td>';
                                        $html .= '<td class="px-2 py-1 text-right text-sm font-medium">S/ ' . number_format($item['subtotal'], 2) . '</td>';
                                        $html .= '</tr>';
                                    }

                                    // FILA DE TOTAL MÁS PROMINENTE
                                    $html .= '<tr class="bg-green-50 border-t-2 border-green-200">';
                                    $html .= '<td colspan="3" class="px-2 py-2 text-right font-bold text-green-700">TOTAL A PAGAR:</td>';
                                    $html .= '<td class="px-2 py-2 text-right text-lg font-bold text-green-700">S/ ' . number_format($this->total, 2) . '</td>';
                                    $html .= '</tr>';

                                    $html .= '</tbody></table></div>';
                                    return new \Illuminate\Support\HtmlString($html);
                                }),
                        ]),

                    Section::make('💳 ¿Cómo va a pagar?')
                        ->description('Seleccione el método de pago - Total: S/ ' . number_format($this->total, 2))
                        ->compact()
                        ->schema([
                            Forms\Components\Select::make('primary_payment_method')
                                ->label('Método de Pago Principal')
                                ->options([
                                    'cash' => '💵 Efectivo',
                                    'card' => '💳 Tarjeta',
                                    'yape' => '📱 Yape',
                                    'plin' => '💙 Plin',
                                ])
                                ->default('cash')
                                ->live()
                                ->placeholder('Seleccione método de pago'),

                            // MONTO Y DENOMINACIONES
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\TextInput::make('primary_payment_amount')
                                    ->label('Monto Recibido')
                                    ->numeric()
                                    ->prefix('S/')
                                    ->live()
                                    ->default($this->total)
                                    ->step(0.01)
                                    ->minValue(0.01)
                                    ->placeholder('0.00'),

                                Forms\Components\Placeholder::make('payment_change')
                                    ->label('Vuelto')
                                    ->content(function (Get $get) {
                                        $amount = (float) ($get('primary_payment_amount') ?? 0);
                                        $change = $amount - $this->total;

                                        if ($change > 0) {
                                            return new \Illuminate\Support\HtmlString(
                                                "<div class='p-2 bg-green-50 border border-green-200 rounded text-center'>" .
                                                    "<span class='text-green-700 font-bold text-lg'>S/ " . number_format($change, 2) . "</span>" .
                                                    "</div>"
                                            );
                                        } elseif ($change < 0) {
                                            return new \Illuminate\Support\HtmlString(
                                                "<div class='p-2 bg-red-50 border border-red-200 rounded text-center'>" .
                                                    "<span class='text-red-700 font-bold text-sm'>Falta: S/ " . number_format(abs($change), 2) . "</span>" .
                                                    "</div>"
                                            );
                                        } else {
                                            return new \Illuminate\Support\HtmlString(
                                                "<div class='p-2 bg-blue-50 border border-blue-200 rounded text-center'>" .
                                                    "<span class='text-blue-700 font-bold text-sm'>Exacto ✓</span>" .
                                                    "</div>"
                                            );
                                        }
                                    })
                                    ->live()
                                    ->visible(fn(Get $get) => $get('primary_payment_method') === 'cash'),
                            ]),

                            // DENOMINACIONES RÁPIDAS PARA EFECTIVO
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('exact')
                                    ->label('Exacto')
                                    ->color('success')
                                    ->size('sm')
                                    ->action(function (Forms\Set $set) {
                                        $set('primary_payment_amount', $this->total);
                                    }),
                                Forms\Components\Actions\Action::make('10')
                                    ->label('S/ 10')
                                    ->color('gray')
                                    ->size('sm')
                                    ->action(function (Forms\Set $set) {
                                        $set('primary_payment_amount', 10);
                                    }),
                                Forms\Components\Actions\Action::make('20')
                                    ->label('S/ 20')
                                    ->color('gray')
                                    ->size('sm')
                                    ->action(function (Forms\Set $set) {
                                        $set('primary_payment_amount', 20);
                                    }),
                                Forms\Components\Actions\Action::make('50')
                                    ->label('S/ 50')
                                    ->color('gray')
                                    ->size('sm')
                                    ->action(function (Forms\Set $set) {
                                        $set('primary_payment_amount', 50);
                                    }),
                                Forms\Components\Actions\Action::make('100')
                                    ->label('S/ 100')
                                    ->color('gray')
                                    ->size('sm')
                                    ->action(function (Forms\Set $set) {
                                        $set('primary_payment_amount', 100);
                                    }),
                                Forms\Components\Actions\Action::make('200')
                                    ->label('S/ 200')
                                    ->color('gray')
                                    ->size('sm')
                                    ->action(function (Forms\Set $set) {
                                        $set('primary_payment_amount', 200);
                                    }),
                            ])
                                ->visible(fn(Get $get) => $get('primary_payment_method') === 'cash')
                                ->extraAttributes(['class' => 'flex flex-wrap gap-2']),

                            Forms\Components\Placeholder::make('payment_helper')
                                ->label('')
                                ->content(fn(Get $get) => match ($get('primary_payment_method')) {
                                    'cash' => '💡 Use los botones de denominaciones para ir más rápido',
                                    'card' => '💳 El pago será por el monto exacto',
                                    'yape' => '📱 El cliente debe transferir el monto exacto',
                                    'plin' => '💙 El cliente debe transferir el monto exacto',
                                    default => '💡 Para pagos mixtos use la sección opcional abajo',
                                })
                                ->extraAttributes(['class' => 'text-sm text-gray-600']),
                        ]),

                    Section::make('📄 ¿Qué tipo de comprobante necesita?')
                        ->description('Elija el documento que va a entregar al cliente')
                        ->compact()
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\ToggleButtons::make('document_type')
                                    ->label('')
                                    ->options([
                                        'sales_note' => '📝 Nota de Venta',
                                        'receipt' => '🧾 Boleta',
                                        'invoice' => '📋 Factura',
                                    ])
                                    ->colors([
                                        'sales_note' => 'info',
                                        'receipt' => 'warning',
                                        'invoice' => 'success',
                                    ])
                                    ->default('sales_note')
                                    ->live()
                                    ->afterStateUpdated(function (Forms\Set $set, Get $get, $state) {
                                        // ✅ LÓGICA PARA ALTERNAR DATOS DEL CLIENTE SEGÚN TIPO DE DOCUMENTO
                                        if ($state === 'sales_note') {
                                            // SIEMPRE usar Cliente Genérico para Nota de Venta
                                            $genericCustomer = \App\Models\Customer::getGenericCustomer();
                                            $set('customer_id', $genericCustomer->id);
                                            $set('customer_name', $genericCustomer->name);
                                            $set('customer_document_type', $genericCustomer->document_type);
                                            $set('customer_document', $genericCustomer->document_number);
                                            $set('customer_address', $genericCustomer->address);
                                            $set('customer_phone', $genericCustomer->phone ?? '');
                                            $set('customer_email', $genericCustomer->email ?? '');
                                        } else {
                                            // Restaurar datos originales del cliente si existen
                                            if ($this->originalCustomerData) {
                                                $set('customer_id', $this->originalCustomerData['customer_id']);
                                                $set('customer_name', $this->originalCustomerData['customer_name']);
                                                $set('customer_document_type', $this->originalCustomerData['customer_document_type']);
                                                $set('customer_document', $this->originalCustomerData['customer_document']);
                                                $set('customer_address', $this->originalCustomerData['customer_address']);
                                                $set('customer_phone', $this->originalCustomerData['customer_phone']);
                                                $set('customer_email', $this->originalCustomerData['customer_email']);
                                            } else {
                                                // Sin datos originales, usar cliente genérico por defecto
                                                $customer = Customer::find(1);
                                                $set('customer_id', 1);
                                                $set('customer_name', $customer?->name ?? 'Cliente General');
                                                $set('customer_document_type', $customer?->document_type ?? 'DNI');
                                                $set('customer_document', $customer?->document_number ?? '');
                                                $set('customer_address', $customer?->address ?? '');
                                                $set('customer_phone', $customer?->phone ?? '');
                                                $set('customer_email', $customer?->email ?? '');
                                            }
                                        }
                                    })
                                    ->columnSpan(3)
                                    ->extraAttributes(['class' => 'text-base'])
                                    ->inline(),
                            ]),
                            Forms\Components\Placeholder::make('document_info')
                                ->label('')
                                ->content('💡 Nota de Venta: Para ventas rápidas • Boleta: Para personas • Factura: Para empresas')
                                ->extraAttributes(['class' => 'text-sm text-gray-600']),
                        ]),

                    Section::make('👤 ¿Para quién es la venta?')
                        ->description(function (Get $get) {
                            return $get('document_type') === 'sales_note'
                                ? '💡 Nota de Venta: Se usará "Cliente Genérico"'
                                : '🧾 Ingrese o modifique los datos del cliente';
                        })
                        ->compact()
                        ->schema([
                            // ✅ LÓGICA CONDICIONAL SEGÚN TIPO DE DOCUMENTO
                            Forms\Components\Placeholder::make('generic_customer_info')
                                ->label('Cliente')
                                ->content('👤 Cliente Genérico')
                                ->extraAttributes(['class' => 'text-lg font-medium text-gray-700'])
                                ->visible(fn(Get $get) => $get('document_type') === 'sales_note'),

                            // ✅ CAMPOS INDIVIDUALES DEL CLIENTE (VISIBLES SOLO PARA BOLETA/FACTURA)
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('customer_id')
                                    ->label('Cliente')
                                    ->required(fn(Get $get) => in_array($get('document_type'), ['receipt', 'invoice']))
                                    ->searchable()
                                    ->preload()
                                    ->options(function (): array {
                                        return Customer::limit(50)->pluck('name', 'id')->toArray();
                                    })
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return Customer::where('name', 'like', "%{$search}%")
                                            ->orWhere('document_number', 'like', "%{$search}%")
                                            ->limit(50)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->getOptionLabelUsing(fn($value): ?string => Customer::find($value)?->name)
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('name')
                                            ->label('Nombre/Razón Social')
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Select::make('document_type')
                                            ->label('Tipo de Documento')
                                            ->options([
                                                'DNI' => 'DNI',
                                                'RUC' => 'RUC',
                                                'CE' => 'Carnet de Extranjería',
                                                'PAS' => 'Pasaporte'
                                            ])
                                            ->default('DNI')
                                            ->required(),
                                        Forms\Components\TextInput::make('document_number')
                                            ->label('Número de Documento')
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('phone')
                                            ->label('Teléfono')
                                            ->tel()
                                            ->maxLength(20),
                                        Forms\Components\TextInput::make('email')
                                            ->label('Email')
                                            ->email()
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('address')
                                            ->label('Dirección')
                                            ->maxLength(500)
                                            ->rows(3),
                                    ])
                                    ->createOptionUsing(function (array $data): int {
                                        return Customer::create($data)->getKey();
                                    })
                                    ->placeholder('Buscar cliente existente o crear nuevo...')
                                    ->disabled(fn(Get $get) => $get('document_type') === 'sales_note')
                                    ->afterStateUpdated(function (Forms\Set $set, Get $get, $state) {
                                        // ✅ LÓGICA PARA ALTERNAR ENTRE CLIENTE REAL Y GENÉRICO
                                        if ($get('document_type') === 'sales_note') {
                                            // SIEMPRE usar Cliente Genérico para Nota de Venta
                                            $genericCustomer = \App\Models\Customer::getGenericCustomer();
                                            $set('customer_id', $genericCustomer->id);
                                        } else {
                                            // ✅ CARGAR DATOS DEL CLIENTE SELECCIONADO POR ID
                                            if (!empty($state) && is_numeric($state)) {
                                                $selectedCustomer = Customer::find($state);
                                                if ($selectedCustomer) {
                                                    $set('customer_document_type', $selectedCustomer->document_type ?: 'DNI');
                                                    $set('customer_document', $selectedCustomer->document_number ?: '');
                                                    $set('customer_phone', $selectedCustomer->phone ?: '');
                                                    $set('customer_email', $selectedCustomer->email ?: '');
                                                    $set('customer_address', $selectedCustomer->address ?: '');

                                                    \Illuminate\Support\Facades\Log::info('✅ DATOS DEL CLIENTE CARGADOS AUTOMÁTICAMENTE', [
                                                        'customer_id' => $selectedCustomer->id,
                                                        'customer_name' => $selectedCustomer->name,
                                                        'document_type' => $selectedCustomer->document_type,
                                                        'document_number' => $selectedCustomer->document_number,
                                                        'phone' => $selectedCustomer->phone,
                                                        'email' => $selectedCustomer->email,
                                                        'address' => $selectedCustomer->address,
                                                    ]);
                                                }
                                            }

                                            // Si hay datos originales y volvemos a boleta/factura, restaurar
                                            if ($this->originalCustomerData && empty($state)) {
                                                $set('customer_id', $this->originalCustomerData['customer_id']);
                                                $set('customer_document_type', $this->originalCustomerData['customer_document_type']);
                                                $set('customer_document', $this->originalCustomerData['customer_document']);
                                                $set('customer_phone', $this->originalCustomerData['customer_phone']);
                                                $set('customer_email', $this->originalCustomerData['customer_email']);
                                                $set('customer_address', $this->originalCustomerData['customer_address']);
                                            }
                                        }
                                    })
                                    ->live(onBlur: true),

                                Forms\Components\Select::make('customer_document_type')
                                    ->label('Tipo Documento')
                                    ->options([
                                        'DNI' => 'DNI',
                                        'RUC' => 'RUC',
                                        'CE' => 'Carnet Extranjería',
                                        'PAS' => 'Pasaporte',
                                    ])
                                    ->default('DNI')
                                    ->required(fn(Get $get) => in_array($get('document_type'), ['receipt', 'invoice'])),
                            ])
                                ->visible(fn(Get $get) => in_array($get('document_type'), ['receipt', 'invoice'])),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('customer_document')
                                    ->label('N° Documento')
                                    ->placeholder('12345678')
                                    ->maxLength(20),
                                Forms\Components\TextInput::make('customer_phone')
                                    ->label('Teléfono')
                                    ->tel()
                                    ->placeholder('999 888 777'),
                                Forms\Components\TextInput::make('customer_email')
                                    ->label('Email')
                                    ->email()
                                    ->placeholder('cliente@email.com'),
                            ])
                                ->visible(fn(Get $get) => in_array($get('document_type'), ['receipt', 'invoice'])),

                            Forms\Components\TextInput::make('customer_address')
                                ->label('Dirección')
                                ->placeholder('Dirección del cliente')
                                ->columnSpanFull()
                                ->visible(fn(Get $get) => in_array($get('document_type'), ['receipt', 'invoice'])),

                            // ✅ CAMPO OCULTO PARA CUSTOMER_ID
                            Forms\Components\Hidden::make('customer_id')
                                ->default(1),
                        ]),

                    Section::make('🔄 Pagos Mixtos')
                        ->description(fn(Get $get) => $get('use_only_mixed')
                            ? 'Pago completo con métodos combinados - Total: S/ ' . number_format($this->total, 2)
                            : 'Opcional: Combinar métodos de pago - Total: S/ ' . number_format($this->total, 2))
                        ->compact()
                        ->collapsible()
                        ->collapsed(false)
                        ->schema([
                            Forms\Components\Toggle::make('use_only_mixed')
                                ->label('Usar solo pagos mixtos')
                                ->inline(false)
                                ->default(false)
                                ->live()
                                ->columnSpanFull(),
                            Repeater::make('payments')
                                ->label('')
                                ->schema([
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\Select::make('payment_method')
                                            ->label('Método')
                                            ->options([
                                                'cash' => '💵 Efectivo',
                                                'card' => '💳 Tarjeta',
                                                'yape' => '📱 Yape',
                                                'plin' => '💙 Plin',
                                            ])
                                            ->default('cash')
                                            ->required()
                                            ->live(),
                                        Forms\Components\TextInput::make('amount')
                                            ->label('Monto S/')
                                            ->numeric()
                                            ->required()
                                            ->prefix('S/')
                                            ->placeholder('0.00')
                                            ->live()
                                            ->minValue(0.01)
                                            ->step(0.01),
                                    ]),
                                ])
                                ->addActionLabel('➕ Pago mixto')
                                ->reorderableWithButtons()
                                ->collapsed()
                                ->defaultItems(1)
                                ->minItems(1)
                                ->maxItems(3)
                                ->itemLabel(
                                    fn(array $state): ?string =>
                                    isset($state['payment_method']) && isset($state['amount'])
                                        ? match ($state['payment_method']) {
                                            'cash' => '💵 S/ ' . number_format((float)$state['amount'], 2),
                                            'card' => '💳 S/ ' . number_format((float)$state['amount'], 2),
                                            'yape' => '📱 S/ ' . number_format((float)$state['amount'], 2),
                                            'plin' => '💙 S/ ' . number_format((float)$state['amount'], 2),
                                            default => '💳 S/ ' . number_format((float)$state['amount'], 2),
                                        }
                                        : '💳 Método de pago'
                                ),

                            Forms\Components\Placeholder::make('payment_status')
                                ->label('')
                                ->content(function (Get $get) {
                                    $payments = collect($get('payments') ?? []);
                                    $paidAmount = $payments->sum(function($payment) {
                                        return is_numeric($payment['amount']) ? (float)$payment['amount'] : 0;
                                    });
                                    $remaining = $this->total - $paidAmount;

                                    if ($remaining > 0) {
                                        return new \Illuminate\Support\HtmlString(
                                            "<div class='p-2 bg-red-50 border border-red-200 rounded text-center'>" .
                                                "<span class='text-red-700 font-semibold text-sm'>⚠️ Falta: S/ " . number_format($remaining, 2) . "</span>" .
                                                "</div>"
                                        );
                                    } elseif ($remaining < 0) {
                                        return new \Illuminate\Support\HtmlString(
                                            "<div class='p-2 bg-orange-50 border border-orange-200 rounded text-center'>" .
                                                "<span class='text-orange-700 font-semibold text-sm'>💰 Vuelto: S/ " . number_format(abs($remaining), 2) . "</span>" .
                                                "</div>"
                                        );
                                    } else {
                                        return new \Illuminate\Support\HtmlString(
                                            "<div class='p-2 bg-green-50 border border-green-200 rounded text-center'>" .
                                                "<span class='text-green-700 font-semibold text-sm'>✅ Pago exacto</span>" .
                                                "</div>"
                                        );
                                    }
                                })
                                ->live(),
                        ]),
                ];
            })
            ->fillForm(function () {
                // ✅ SI HAY DATOS ORIGINALES DEL CLIENTE DE DELIVERY, USARLOS
                if ($this->originalCustomerData) {
                    \Illuminate\Support\Facades\Log::info('🔍 USANDO DATOS ORIGINALES DEL CLIENTE:', $this->originalCustomerData);
                    return [
                        'primary_payment_method' => 'cash',
                        'primary_payment_amount' => $this->total,
                        'document_type' => 'receipt', // ✅ Iniciar con Boleta para mostrar datos del cliente
                        'customer_id' => $this->originalCustomerData['customer_id'],
                        'customer_name' => $this->originalCustomerData['customer_name'],
                        'customer_document_type' => $this->originalCustomerData['customer_document_type'],
                        'customer_document' => $this->originalCustomerData['customer_document'],
                        'customer_address' => $this->originalCustomerData['customer_address'],
                        'customer_phone' => $this->originalCustomerData['customer_phone'],
                        'customer_email' => $this->originalCustomerData['customer_email'],
                        'payments' => [
                            ['payment_method' => 'cash', 'amount' => $this->total]
                        ],
                    ];
                }

                // ✅ CASO DEFAULT PARA POS NORMAL (SIN DELIVERY)
                $customer = Customer::find(1);
                return [
                    'primary_payment_method' => 'cash',
                    'primary_payment_amount' => $this->total,
                    'document_type' => 'sales_note',
                    'customer_id' => 1,
                    'customer_name' => $customer?->name ?? 'Cliente General',
                    'customer_document_type' => $customer?->document_type ?? 'DNI',
                    'customer_document' => $customer?->document_number ?? '',
                    'customer_address' => $customer?->address ?? '',
                    'customer_phone' => $customer?->phone ?? '',
                    'customer_email' => $customer?->email ?? '',
                    'payments' => [
                        ['payment_method' => 'cash', 'amount' => $this->total]
                    ],
                ];
            })
            ->action(function (array $data) {
                try {
                    // DEBUG: Ver qué datos están llegando
                    \Illuminate\Support\Facades\Log::info('🔍 DATOS DEL FORMULARIO COMPLETOS:', $data);

                    // Mostrar notificación temporal para debug
                    Notification::make()
                        ->title('🔍 DEBUG - Datos recibidos')
                        ->body('Método: ' . ($data['primary_payment_method'] ?? 'NO_SET') . ' | Monto: ' . ($data['primary_payment_amount'] ?? 'NO_SET'))
                        ->info()
                        ->duration(3000)
                        ->send();

                    // Validar método de pago principal - CORREGIDO
                    if (!isset($data['primary_payment_method']) || empty($data['primary_payment_method']) || $data['primary_payment_method'] === '') {
                        Notification::make()
                            ->title('⚠️ Método de Pago Requerido')
                            ->body('Debe seleccionar un método de pago. DEBUG: ' . json_encode($data['primary_payment_method'] ?? 'NO_SET'))
                            ->danger()
                            ->duration(5000)
                            ->send();
                        return;
                    }

                    // Validar monto mínimo para efectivo
                    if (
                        $data['primary_payment_method'] === 'cash' &&
                        (!isset($data['primary_payment_amount']) || $data['primary_payment_amount'] <= 0)
                    ) {
                        Notification::make()
                            ->title('⚠️ Monto Inválido')
                            ->body('El monto recibido debe ser mayor a cero.')
                            ->danger()
                            ->duration(5000)
                            ->send();
                        return;
                    }

                    // Para métodos digitales, usar el total exacto
                    if (in_array($data['primary_payment_method'], ['card', 'yape', 'plin']) && !isset($data['primary_payment_amount'])) {
                        $data['primary_payment_amount'] = $this->total;
                    }

                    $this->handlePayment($data);
                } catch (Halt $e) {
                    return;
                } catch (\Exception $e) {
                    Notification::make()
                        ->title('❌ Error Inesperado')
                        ->body($e->getMessage())
                        ->danger()
                        ->duration(8000)
                        ->send();
                }
            })
            ->modalHeading('💳 Procesar Pago de Orden')
            ->modalSubmitActionLabel('💳 Pagar e Imprimir')
            ->extraAttributes([
                'class' => 'fi-modal-window-xl'
            ]);
    }

    public function handlePayment(array $data): void
    {
        // Verificar que solo cashiers puedan generar comprobantes
        if (!Auth::user()->hasRole(['cashier', 'admin', 'super_admin'])) {
            Notification::make()
                ->title('❌ Acceso Denegado')
                ->body('No tienes permisos para generar comprobantes. Solo los cajeros pueden realizar esta acción.')
                ->danger()
                ->duration(5000)
                ->send();
            throw new Halt();
        }

        DB::transaction(function () use ($data) {
            $order = $this->order;

            // ✅ CREAR ORDEN AUTOMÁTICAMENTE PARA VENTA DIRECTA
            if (!$order) {
                \Illuminate\Support\Facades\Log::info('🔍 CREANDO ORDEN AUTOMÁTICAMENTE PARA VENTA DIRECTA');
                $order = $this->createOrderFromCart();
                if (!$order) {
                    Notification::make()->title('Error')->body('No se pudo crear la orden. Verifique que hay productos en el carrito.')->danger()->send();
                    throw new Halt();
                }
                $this->order = $order;
            }

            $activeCashRegister = CashRegister::getOpenRegister();
            if (!$activeCashRegister) {
                Notification::make()->title('Caja no abierta')->body('No hay una caja abierta para registrar el pago.')->danger()->send();
                throw new Halt();
            }

            // ✅ CREAR O ACTUALIZAR CLIENTE SI ES NECESARIO
            $customerId = $data['customer_id'];
            if (!$customerId && !empty($data['customer_name'])) {
                // Crear nuevo cliente
                $customer = Customer::create([
                    'name' => $data['customer_name'],
                    'document_type' => $data['customer_document_type'] ?? 'DNI',
                    'document_number' => $data['customer_document'] ?? '',
                    'phone' => $data['customer_phone'] ?? '',
                    'email' => $data['customer_email'] ?? '',
                    'address' => $data['customer_address'] ?? '',
                ]);
                $customerId = $customer->id;

                Notification::make()
                    ->title('✅ Cliente Creado')
                    ->body("Cliente '{$data['customer_name']}' creado exitosamente")
                    ->success()
                    ->duration(3000)
                    ->send();
            } elseif ($customerId && !empty($data['customer_name'])) {
                // Actualizar cliente existente si los datos han cambiado
                Customer::where('id', $customerId)->update([
                    'name' => $data['customer_name'],
                    'document_type' => $data['customer_document_type'] ?? 'DNI',
                    'document_number' => $data['customer_document'] ?? '',
                    'phone' => $data['customer_phone'] ?? '',
                    'email' => $data['customer_email'] ?? '',
                    'address' => $data['customer_address'] ?? '',
                ]);
            }

            $series = DocumentSeries::where('document_type', $data['document_type'])->first();
            if (!$series) {
                Notification::make()->title('Error')->body('No se encontró serie para el tipo de documento.')->danger()->send();
                throw new Halt();
            }
            $nextNumber = $series->getNextNumber();

            // ✅ OBTENER DATOS DEL CLIENTE PARA EL COMPROBANTE
            if ($data['document_type'] === 'sales_note') {
                // FORZAR Cliente Genérico para notas de venta
                $customer = Customer::getGenericCustomer();
                $customerId = $customer->id;

                Log::info('🔍 FORZANDO CLIENTE GENÉRICO PARA NOTA DE VENTA', [
                    'document_type' => $data['document_type'],
                    'customer_id_original' => $data['customer_id'] ?? 'N/A',
                    'customer_id_forzado' => $customerId,
                    'customer_name_forzado' => $customer->name,
                    'invoice_id_pendiente' => 'por crear'
                ]);
            } else {
                $customer = Customer::find($customerId);
                if (!$customer) {
                    $customer = Customer::getGenericCustomer(); // Fallback al cliente genérico
                }
            }

            // ✅ CREAR COMPROBANTE CON VERIFICACIÓN
            $invoice = Invoice::create([
                'order_id' => $order->id,
                'customer_id' => $customerId,
                'employee_id' => DB::table('employees')->where('user_id', Auth::id())->value('id'), // Obtener ID de empleado directamente
                'invoice_type' => $data['document_type'],
                'series' => $series->series,
                'number' => $nextNumber,
                'taxable_amount' => $this->subtotal,
                'tax' => $this->tax,
                'total' => $this->total,
                'tax_authority_status' => Invoice::STATUS_PENDING,
                'issue_date' => now(),
                // 🔥 CAMPOS CRÍTICOS PARA IMPRESIÓN
                'client_name' => $customer->name,
                'client_document' => $customer->document_number,
                'client_address' => $customer->address,
                'payment_method' => $data['primary_payment_method'],
                'payment_amount' => $data['primary_payment_amount'] ?? $this->total,
            ]);

            // 🔍 VERIFICAR QUE LA FACTURA SE CREÓ CORRECTAMENTE
            if (!$invoice || !$invoice->id) {
                Notification::make()
                    ->title('❌ Error al crear comprobante')
                    ->body('No se pudo crear el comprobante. Intente nuevamente.')
                    ->danger()
                    ->duration(5000)
                    ->send();
                throw new Halt();
            }

            // 🔥 CREAR DETALLES DE LA FACTURA
            foreach ($this->cartItems as $item) {
                \App\Models\InvoiceDetail::create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['product_id'],
                    'description' => $item['name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // Procesar el pago principal
            $paymentMethod = $data['primary_payment_method'];
            $paymentAmount = $data['primary_payment_amount'] ?? $this->total;

            // Determinar si hay pagos mixtos válidos para procesar
            $hasValidMixedPayments = false;
            $totalMixedPayments = 0;

            if (isset($data['payments']) && count($data['payments']) > 0) {
                $validPayments = array_filter($data['payments'], function($payment) {
                    return isset($payment['amount']) && $payment['amount'] > 0;
                });

                $hasValidMixedPayments = count($validPayments) > 0;
                $totalMixedPayments = array_sum(array_column($validPayments, 'amount'));
            }

            // Procesar solo pagos mixtos si existen y cubren el total
            if ($hasValidMixedPayments && abs($totalMixedPayments - $this->total) < 0.01) {
                foreach ($data['payments'] as $payment) {
                    if (!isset($payment['amount']) || $payment['amount'] <= 0) {
                        continue;
                    }

                    $activeCashRegister->registerSale($payment['payment_method'], $payment['amount']);

                    CashMovement::create([
                        'cash_register_id' => $activeCashRegister->id,
                        'movement_type' => 'income',
                        'amount' => $payment['amount'],
                        'reason' => "Pago {$payment['payment_method']} - {$data['document_type']} {$series->series}-{$nextNumber}",
                        'approved_by' => Auth::id(),
                    ]);

                    \App\Models\Payment::create([
                        'order_id' => $order->id,
                        'cash_register_id' => $activeCashRegister->id,
                        'payment_method' => $payment['payment_method'],
                        'amount' => $payment['amount'],
                        'reference_number' => "Pago {$payment['payment_method']} - {$data['document_type']} {$series->series}-{$nextNumber}",
                        'payment_datetime' => now(),
                        'received_by' => Auth::id(),
                    ]);
                }
            }
            // Procesar solo el pago principal si no hay pagos mixtos válidos
            else {
                $activeCashRegister->registerSale($paymentMethod, $paymentAmount);

                CashMovement::create([
                    'cash_register_id' => $activeCashRegister->id,
                    'movement_type' => 'income',
                    'amount' => $paymentAmount,
                    'reason' => "Pago {$paymentMethod} - {$data['document_type']} {$series->series}-{$nextNumber}",
                    'approved_by' => Auth::id(),
                ]);

                \App\Models\Payment::create([
                    'order_id' => $order->id,
                    'cash_register_id' => $activeCashRegister->id,
                    'payment_method' => $paymentMethod,
                    'amount' => $paymentAmount,
                    'reference_number' => "Pago {$paymentMethod} - {$data['document_type']} {$series->series}-{$nextNumber}",
                    'payment_datetime' => now(),
                    'received_by' => Auth::id(),
                ]);
            }

            $order->update(['status' => Order::STATUS_COMPLETED, 'billed' => true]);

            $this->checkAndReleaseTable($order->table_id);

            // Calcular y mostrar información del vuelto si aplica
            $change = 0;
            $changeMessage = '';
            if ($data['primary_payment_method'] === 'cash' && isset($data['primary_payment_amount'])) {
                $change = $data['primary_payment_amount'] - $this->total;
                if ($change > 0) {
                    $changeMessage = " | 💰 Vuelto: S/ " . number_format($change, 2);
                }
            }

            Notification::make()
                ->title('🎉 ¡Pago Exitoso!')
                ->body("Se generó {$data['document_type']} {$series->series}-{$nextNumber} por S/ " . number_format($this->total, 2) . $changeMessage)
                ->success()
                ->duration(4000)
                ->actions([
                    \Filament\Notifications\Actions\Action::make('print')
                        ->label('🖨️ Imprimir Comprobante')
                        ->button()
                        ->color('primary')
                        ->action(function () use ($invoice) {
                            // LOGS AMPLIADOS Y MEJORADOS PARA DIAGNÓSTICO DE IMPRESIÓN
                            // Cargar relación del cliente si no está cargada
                            if (!$invoice->relationLoaded('customer') && $invoice->customer_id) {
                                $invoice->load('customer');
                            }

                            $debugData = [
                                'invoice_id' => $invoice->id,
                                'invoice_type' => $invoice->invoice_type,
                                'series' => $invoice->series,
                                'number' => $invoice->number,
                                'total' => $invoice->total,
                                'customer_id' => $invoice->customer_id,
                                'customer_name' => $invoice->client_name ?? ($invoice->customer ? $invoice->customer->name : 'N/A'),
                                'timestamp' => now()->format('Y-m-d H:i:s.u'),
                                'route' => route('invoices.print', ['invoice' => $invoice->id]),
                                'user_id' => auth()->id(),
                                'user_name' => auth()->user()->name,
                                'session_id' => session()->getId(),
                                'request_id' => str()->random(8),
                                'ip' => request()->ip()
                            ];

                            // 🔍 LOG DETALLADO DEL PROCESO
                            \Illuminate\Support\Facades\Log::info('🖨️ INICIANDO PROCESO DE IMPRESIÓN DESDE NOTIFICACIÓN', $debugData);

                            // Verificar que la factura exista en la base de datos
                            $invoiceExists = \App\Models\Invoice::where('id', $invoice->id)->exists();
                            if (!$invoiceExists) {
                                \Illuminate\Support\Facades\Log::error('❌ ERROR CRÍTICO: La factura no existe en la base de datos', [
                                    'invoice_id' => $invoice->id,
                                    'timestamp' => now()->format('Y-m-d H:i:s.u')
                                ]);
                            } else {
                                // Cargar relaciones necesarias
                                $invoice->load('customer');

                                \Illuminate\Support\Facades\Log::info('✅ Factura verificada en la base de datos', [
                                    'invoice_id' => $invoice->id,
                                    'cliente' => $invoice->customer ? $invoice->customer->name : 'Sin cliente',
                                    'verificado_en' => now()->format('Y-m-d H:i:s.u')
                                ]);
                            }

                            // 🖨️ LOGS PARA CADA PASO DEL PROCESO
                            \Illuminate\Support\Facades\Log::info('🖨️ PREPARANDO EVENTO PARA FRONTEND', [
                                'invoice_id' => $invoice->id,
                                'evento' => 'open-print-window',
                                'formato_datos' => 'ID directo: ' . $invoice->id,
                                'timestamp' => now()->format('Y-m-d H:i:s.u'),
                                'request_id' => $debugData['request_id']
                            ]);

                            // Emitir el evento con el ID del comprobante (formato consistente)
                            \Illuminate\Support\Facades\Log::info('🖨️ ENVIANDO EVENTO AL FRONTEND', [
                                'invoice_id' => $invoice->id,
                                'timestamp' => now()->format('Y-m-d H:i:s.u'),
                                'método' => 'dispatch directo'
                            ]);
                            $this->dispatch('open-print-window', $invoice->id);

                            // Método alternativo de apertura de ventana como respaldo
                            $printUrl = route('invoices.print', ['invoice' => $invoice->id, 'src' => 'fallback', 't' => time()]);
                            \Illuminate\Support\Facades\Log::info('🖨️ CONFIGURANDO MÉTODO DE RESPALDO (JAVASCRIPT)', [
                                'invoice_id' => $invoice->id,
                                'url' => $printUrl,
                                'timestamp' => now()->format('Y-m-d H:i:s.u'),
                                'método' => 'javascript window.open'
                            ]);
                            $this->js("console.log('%c🖨️ EJECUTANDO MÉTODO DE RESPALDO PARA IMPRESIÓN', 'background: #8e44ad; color: white; padding: 4px;'); setTimeout(function() { const w = window.open('{$printUrl}', 'print_{$invoice->id}', 'width=800,height=600,scrollbars=yes'); if(w) console.log('%c✅ Ventana abierta por método de respaldo', 'color:#27ae60'); else console.error('%c❌ Falló apertura por método de respaldo', 'color:#e74c3c'); }, 800);");

                            // ✅ NOTIFICACIÓN ADICIONAL DE CONFIRMACIÓN
                            Notification::make()
                                ->title('🖨️ Abriendo impresión...')
                                ->body("Comprobante {$invoice->series}-{$invoice->number}")
                                ->info()
                                ->duration(3000)
                                ->send();
                        }),
                    \Filament\Notifications\Actions\Action::make('view_tables')
                        ->label('🪑 Ver Mesas')
                        ->url(TableMap::getUrl())
                        ->button()
                        ->color('secondary'),
                ])
                ->persistent() // No auto-cerrar para que el usuario pueda elegir
                ->send();

            // 🎯 ACTUALIZAR ESTADO Y REDIRIGIR
            $tableId = $this->order->table_id ?? null;
            $this->clearCart();
            $this->order = null;
            $this->selectedTableId = null;

            // ✅ FORZAR REDIRECCIÓN INMEDIATA CON MÁS LOGS
            $redirectUrl = $tableId ? TableMap::getUrl() : '/admin';

            \Illuminate\Support\Facades\Log::debug('🔴 ANTES DE REDIRECCIÓN', [
                'table_id' => $tableId,
                'order_id' => $this->order?->id,
                'redirect_url' => $redirectUrl,
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);

            $this->js("
                console.log('Iniciando redirección a: {$redirectUrl}');
                setTimeout(function() {
                    window.location.href = '{$redirectUrl}';
                }, 500);
            ");

            \Illuminate\Support\Facades\Log::debug('🟢 DESPUÉS DE REDIRECCIÓN', [
                'status' => 'Redirección programada',
                'timestamp' => now()->format('Y-m-d H:i:s.u')
            ]);

            // 🖨️ ABRIR VENTANA DE IMPRESIÓN AUTOMÁTICAMENTE
            // Registrar información detallada para diagnóstico
            Log::info('🖨️ Intentando abrir ventana de impresión desde procesamiento de pago', [
                'invoice_id' => $invoice->id,
                'invoice_type' => $invoice->invoice_type,
                'invoice_status' => $invoice->tax_authority_status,
                'print_url' => route('print.invoice', ['invoice' => $invoice->id]),
                'invoice_exists' => Invoice::where('id', $invoice->id)->exists(),
                'route_exists' => route('print.invoice', ['invoice' => $invoice->id])
            ]);

            // Establecer flags de sesión para redirección al mapa de mesas después de imprimir
            session([
                'clear_cart_after_print' => true,
                'table_id' => $order->table_id,
                'order_id' => $order->id,
                'generate_prebill' => true
            ]);

            // Mejorar la forma en que se pasa el ID para evitar errores de tipo
            $this->dispatch('open-print-window', ['id' => $invoice->id]);

            // 🔄 REFRESCAR DATOS SIN SALIR DE LA PÁGINA
            $this->refreshOrderData();
        });
    }

    protected function checkAndReleaseTable(?int $tableId): void
    {
        if (!$tableId) return;

        $openOrdersCount = Order::where('table_id', $tableId)
            ->where('status', Order::STATUS_OPEN)
            ->count();

        if ($openOrdersCount === 0) {
            $table = TableModel::find($tableId);
            if ($table) {
                $table->update(['status' => TableModel::STATUS_AVAILABLE]);
                Notification::make()->title('Mesa Liberada')->body("La mesa #{$table->number} ahora está disponible.")->info()->send();
            }
        }
    }
}
