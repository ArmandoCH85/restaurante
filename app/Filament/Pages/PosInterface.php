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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Filament\Actions\ActionSize;
use App\Models\OrderDetail;

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
    public ?int $selectedSubcategoryId = null;
    public array $cartItems = [];
    public float $total = 0.0;
    public float $subtotal = 0.0;
    public float $tax = 0.0;
    public ?int $current_diners = null;
    public bool $canClearCart = true;
    public bool $canAddProducts = true;
    public int $numberOfGuests = 1;
    public bool $isCartDisabled = false;
    public array $transferItems = [];
    public ?Order $order = null;
    public ?int $selectedCustomerId = null;
    public string $selectedDocumentType = 'receipt';
    public string $customerName = '';
    public string $customerDocument = '';
    public string $customerAddress = '';
    public string $selectedPaymentMethod = 'cash';
    public float $paymentAmount = 0.0;
    public float $cashReceived = 0.0;
    public ?array $originalCustomerData = null;
    public string $customerNameForComanda = '';
    public ?Collection $products = null;
    public ?Collection $categories = null;
    public ?Collection $subcategories = null;
    public ?Collection $tables = null;
    public ?Collection $customers = null;
    public ?TableModel $selectedTable = null;
    public string $search = '';
    public ?string $customerNote = null;
    public bool $productsLoaded = false;

    // Propiedades de modales
    public bool $showTransferModal = false;
    public bool $showSplitModal = false;
    public bool $showBillingModal = false;
    public bool $showPrintModal = false;
    public bool $showPreBillModal = false;
    public bool $showPaymentModal = false;
    public bool $showOrderDetailsModal = false;
    public bool $showOrderHistoryModal = false;
    public bool $showOrderNotesModal = false;
    public bool $showTableSelectionModal = false;
    public bool $showGuestSelectionModal = false;
    public bool $showCustomerNoteModal = false;

    // Propiedades de datos
    public array $selectedProductsForTransfer = [];
    public array $selectedProductsForSplit = [];
    public array $splitGroups = [];
    public array $paymentData = [];
    public array $orderHistory = [];
    public array $orderNotes = [];
    public array $tableStatus = [];
    public array $guestCounts = [];
    public array $customerNotes = [];
    public array $printData = [];
    public array $preBillData = [];
    public array $orderDetailsData = [];
    public array $transferData = [];
    public array $splitData = [];
    public array $billingData = [];

    // 🎯 LISTENERS PARA FUNCIONALIDAD NATIVA
    protected $listeners = [
        // Ya no necesitamos listeners del widget personalizado
    ];

    /**
     * Verifica si un producto pertenece a una categoría de bebidas que pueden ser heladas
     */
    protected function isColdDrinkCategory(Product $product): bool
    {
        $drinkCategories = [
            'Bebidas Naturales',
            'Naturales Clásicas',
            'Gaseosas',
            'Cervezas',
            'Vinos',
            'Sangrías'
        ];

        // Obtener el nombre de la categoría del producto
        $categoryName = $product->category?->name ?? '';
        
        return in_array($categoryName, $drinkCategories);
    }
    
    /**
     * Verifica si un producto pertenece a la categoría Parrillas o sus subcategorías
     * o si es uno de los productos específicos de Fusion Q'RICO que requieren punto de cocción
     */
    protected function isGrillCategory(Product $product): bool
    {
        // Productos específicos de Fusion Q'RICO que requieren punto de cocción
        $specificProducts = [
            'Lomo Fino Saltado',
            'Medallón',
            'Spaghetti Saltado de Lomo',
            'Medallón de Lomo al Grill c/ Pasta'
        ];
        
        // Verificar si es uno de los productos específicos
        if (in_array($product->name, $specificProducts)) {
            return true;
        }
        
        // Categoría principal "Parrillas"
        $grillCategory = 'Parrillas';
        
        // Obtener el nombre de la categoría del producto
        $categoryName = $product->category?->name ?? '';
        
        // Verificar si es la categoría principal de parrillas
        if ($categoryName === $grillCategory) {
            return true;
        }
        
        // Verificar si es una subcategoría de Parrillas
        if ($product->category && $product->category->parent) {
            return $product->category->parent->name === $grillCategory;
        }
        
        return false;
    }

    /**
     * Verifica si un producto requiere selección de tipo de presa (pecho o pierna)
     * Aplica a productos específicos de "Gustitos a la leña" y "Promociones Familiares"
     */
    protected function isChickenCutCategory(Product $product): bool
    {
        // Productos específicos que requieren selección de tipo de presa
        $specificProducts = [
            '¼ Anticuchero',
            '¼ Campestre',
            '¼ Chaufero',
            '¼ En Pasta',
            '¼ Parrillero',
            '¼ Pollo'
        ];
        
        // Verificar si es uno de los productos específicos
        if (in_array($product->name, $specificProducts)) {
            return true;
        }
        
        // IDs de las subcategorías "Gustitos a la leña" y "Promociones Familiares"
        $chickenSubcategories = [132, 133]; // Gustitos a la leña (132) y Promociones Familiares (133)
        
        // Verificar si pertenece a alguna de estas subcategorías
        if ($product->category && in_array($product->category->id, $chickenSubcategories)) {
            return true;
        }
        
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('printComanda')
                ->label('Comanda')
                ->icon('heroicon-o-printer')
                ->color('warning')
                ->size('lg')
                ->modal()
                ->modalHeading('👨‍🍳 Comanda')
                ->modalDescription('Orden para la cocina')
                ->modalWidth('md')
                ->form(function () {
                    // ✅ Si es venta directa (sin mesa), solicitar nombre del cliente
                    if ($this->selectedTableId === null && empty($this->customerNameForComanda)) {
                        return [
                            Forms\Components\TextInput::make('customerNameForComanda')
                                ->label('Nombre del Cliente')
                                ->placeholder('Ingrese el nombre del cliente')
                                ->required()
                                ->maxLength(100)
                                ->helperText('Este nombre aparecerá en la comanda')
                                ->default($this->customerNameForComanda)
                        ];
                    }
                    return [];
                })
                ->modalContent(function () {
                    // Si necesita solicitar nombre del cliente, no mostrar contenido aún
                    if ($this->selectedTableId === null && empty($this->customerNameForComanda)) {
                        return null; // El formulario se mostrará primero
                    }

                    // Crear la orden si no existe
                    if (!$this->order && !empty($this->cartItems)) {
                        $this->order = $this->createOrderFromCart();
                    }

                    if (!$this->order) {
                        return view('components.empty-state', [
                            'message' => 'No hay orden para mostrar'
                        ]);
                    }

                    // Obtener datos de la orden con productos
                    $order = $this->order->load(['orderDetails.product', 'table', 'customer']);
                    
                    return view('filament.modals.comanda-content', [
                        'order' => $order,
                        'customerNameForComanda' => $this->customerNameForComanda,
                        'isDirectSale' => $this->selectedTableId === null
                    ]);
                })
                ->action(function (array $data) {
                    // ✅ Guardar el nombre del cliente si es venta directa
                    if ($this->selectedTableId === null && isset($data['customerNameForComanda'])) {
                        $this->customerNameForComanda = $data['customerNameForComanda'];
                    }

                    // ✅ Crear la orden si no existe (ahora CON el nombre del cliente guardado)
                    if (!$this->order && !empty($this->cartItems)) {
                        $this->order = $this->createOrderFromCart();
                    }

                    // Solo proceder si ya tenemos orden y (si es venta directa) el nombre del cliente
                    if ($this->order && ($this->selectedTableId !== null || !empty($this->customerNameForComanda))) {
                        // Cerrar el modal para mostrar el contenido
                        $this->dispatch('refreshModalContent');
                        
                        // ✅ REDIRIGIR AL MAPA DE MESAS SI TIENE MESA (PARA TODOS LOS ROLES)
                        if ($this->selectedTableId) {
                            $redirectUrl = TableMap::getUrl();

                            Notification::make()
                                ->title('Comanda Enviada')
                                ->body('Pedido guardado correctamente. Regresando al mapa de mesas...')
                                ->success()
                                ->duration(2000)
                                ->send();

                            $this->js("
                                setTimeout(function() {
                                    window.location.href = '{$redirectUrl}';
                                }, 500);
                            ");
                        }
                    }
                })
                ->modalSubmitActionLabel('Confirmar')
                ->extraModalFooterActions([
                    Action::make('printComanda')
                        ->label('🖨️ Imprimir')
                        ->color('primary')
                        ->action(function () {
                            $url = route('orders.comanda.pdf', [
                                'order' => $this->order,
                                'customerName' => $this->customerNameForComanda
                            ]);
                            $this->js("window.open('$url', 'comanda_print', 'width=800,height=600,scrollbars=yes,resizable=yes')");
                        })
                        ->visible(fn() => $this->order && ($this->selectedTableId !== null || !empty($this->customerNameForComanda))),
                    Action::make('downloadComanda')
                        ->label('📥 Descargar')
                        ->color('success')
                        ->action(function () {
                            $url = route('orders.comanda.pdf', [
                                'order' => $this->order,
                                'customerName' => $this->customerNameForComanda
                            ]);
                            $this->js("window.open('$url', '_blank')");
                        })
                        ->visible(fn() => $this->order && ($this->selectedTableId !== null || !empty($this->customerNameForComanda))),
                ])
                ->button()
                ->outlined()
                ->visible(fn(): bool => (bool) $this->order || !empty($this->cartItems)),

            Action::make('printPreBillNew')
                ->label('Pre-Cuenta')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->size('lg')
                ->modal()
                ->modalHeading('📄 Pre-Cuenta')
                ->modalDescription('Resumen de la orden antes del pago')
                ->modalWidth('md')
                ->modalContent(function () {
                    // Crear la orden si no existe
                    if (!$this->order && !empty($this->cartItems)) {
                        $this->order = $this->createOrderFromCart();
                    }

                    if (!$this->order) {
                        return view('components.empty-state', [
                            'message' => 'No hay orden para mostrar'
                        ]);
                    }

                    // Obtener datos de la orden con productos
                    $order = $this->order->load(['orderDetails.product', 'table', 'customer']);
                    
                    return view('filament.modals.pre-bill-content', [
                        'order' => $order,
                        'subtotal' => $this->subtotal,
                        'tax' => $this->tax,
                        'total' => $this->total
                    ]);
                })
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->extraModalFooterActions([
                    Action::make('printPreBill')
                        ->label('🖨️ Imprimir')
                        ->color('primary')
                        ->action(function () {
                            $url = route('print.prebill', ['order' => $this->order->id]);
                            $this->js("window.open('$url', 'prebill_print', 'width=800,height=600,scrollbars=yes,resizable=yes')");
                        }),
                    Action::make('downloadPreBill')
                        ->label('📥 Descargar')
                        ->color('success')
                        ->action(function () {
                            $url = route('print.prebill', ['order' => $this->order->id]);
                            $this->js("window.open('$url', '_blank')");
                        }),
                ])
                ->visible(fn (): bool => (bool) $this->order || !empty($this->cartItems))
                ->disabled(fn (): bool => !$this->order && empty($this->cartItems))
                ->tooltip('Mostrar Pre-Cuenta')
                ->button(),

            Action::make('reopen_order_for_editing')
                ->label('Reabrir Orden')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->button()
                ->action(function () {
                    $this->isCartDisabled = false;
                    $this->canAddProducts = true; // Permitir agregar productos nuevamente
                    $this->canClearCart = true; // Permitir limpiar el carrito
                    Notification::make()
                        ->title('Orden reabierta para edición')
                        ->warning()
                        ->send();
                })
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
                        'target_table_id' => null, // Añadido para inicializar el campo
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

            Action::make('split_items')
                ->label('Dividir Cuenta')
                ->icon('heroicon-o-calculator')
                ->color('warning')
                ->size('lg')
                ->button()
                ->outlined()
                ->slideOver()
                ->modalWidth('2xl')
                ->form([
                    Forms\Components\Section::make('Productos a Dividir')
                        ->description('Selecciona la cantidad de cada producto que deseas mover a la nueva cuenta')
                        ->icon('heroicon-o-shopping-cart')
                        ->schema([
                            Forms\Components\Repeater::make('split_items')
                                ->schema([
                                    Forms\Components\Grid::make(12)
                                        ->schema([
                                            Forms\Components\Hidden::make('product_id'),
                                            Forms\Components\Hidden::make('name'),
                                            Forms\Components\Hidden::make('quantity'),
                                            Forms\Components\Hidden::make('unit_price'),
                                            Forms\Components\TextInput::make('product_name')
                                                ->label('Producto')
                                                ->disabled()
                                                ->columnSpan(5),
                                            Forms\Components\TextInput::make('available_quantity')
                                                ->label('Disponible')
                                                ->disabled()
                                                ->columnSpan(3),
                                            Forms\Components\TextInput::make('split_quantity')
                                                ->label('Mover')
                                                ->numeric()
                                                ->default(0)
                                                ->minValue(0)
                                                ->columnSpan(4)
                                        ])
                                ])
                                ->disabled(fn () => !$this->order)
                                ->defaultItems(0)
                        ])
                ])
                ->fillForm(function(): array {
                    if (!$this->order) {
                        return ['split_items' => []];
                    }

                    return [
                        'split_items' => $this->order->orderDetails->map(function($detail) {
                            return [
                                'product_id' => $detail->product_id,
                                'name' => $detail->product->name,
                                'product_name' => $detail->product->name,
                                'quantity' => $detail->quantity,
                                'available_quantity' => $detail->quantity,
                                'unit_price' => $detail->unit_price,
                                'split_quantity' => 0,
                            ];
                        })->toArray()
                    ];
                })
                ->action(function(array $data): void {
                    // Validar que al menos un producto tenga cantidad mayor a 0
                    $hasItemsToSplit = collect($data['split_items'])
                        ->some(fn($item) => ($item['split_quantity'] ?? 0) > 0);

                    if (!$hasItemsToSplit) {
                        Notification::make()
                            ->title('Error')
                            ->body('Debes seleccionar al menos un producto para dividir')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Validar que las cantidades a dividir no excedan las disponibles
                    foreach ($data['split_items'] as $item) {
                        $splitQuantity = $item['split_quantity'] ?? 0;
                        $availableQuantity = $item['quantity'] ?? 0;

                        if ($splitQuantity > $availableQuantity) {
                            Notification::make()
                                ->title('Error')
                                ->body("No puedes dividir más de {$availableQuantity} unidades de {$item['product_name']}")
                                ->danger()
                                ->send();
                            return;
                        }
                    }

                    DB::beginTransaction();
                    try {
                        // Crear la nueva orden
                        $newOrder = Order::create([
                            'table_id' => $this->order->table_id,
                            'customer_id' => $this->order->customer_id,
                            'employee_id' => $this->order->employee_id,
                            'status' => Order::STATUS_OPEN,
                            'created_by' => auth()->guard('web')->id(),
                            'order_datetime' => now(),
                            'service_type' => $this->order->service_type,
                            'number_of_guests' => 1,
                            'parent_id' => $this->order->id,
                            'subtotal' => 0,
                            'tax' => 0,
                            'total' => 0,
                            'discount' => 0,
                            'billed' => false,
                        ]);

                        // Mover los productos seleccionados a la nueva orden
                        foreach ($data['split_items'] as $item) {
                            $splitQuantity = $item['split_quantity'] ?? 0;
                            if ($splitQuantity > 0) {
                                // Crear el detalle en la nueva orden
                                $newOrder->orderDetails()->create([
                                    'product_id' => $item['product_id'],
                                    'quantity' => $splitQuantity,
                                    'unit_price' => $item['unit_price'],
                                    'subtotal' => $splitQuantity * $item['unit_price'],
                                ]);

                                // Actualizar la cantidad en la orden original
                                $this->order->orderDetails()
                                    ->where('product_id', $item['product_id'])
                                    ->update([
                                        'quantity' => DB::raw("quantity - {$splitQuantity}")
                                    ]);
                            }
                        }

                        // Eliminar detalles con cantidad 0
                        $this->order->orderDetails()->where('quantity', 0)->delete();

                        // Recalcular totales
                        $this->order->recalculateTotals();
                        $newOrder->recalculateTotals();

                        DB::commit();

                        $this->refreshOrderData();

                        Notification::make()
                            ->title('Cuenta Dividida')
                            ->body('La cuenta se ha dividido correctamente')
                            ->success()
                            ->send();

                        // Redirigir al mapa de mesas después de dividir la cuenta
                        $redirectUrl = TableMap::getUrl();
                        $this->js("
                            console.log('Redirigiendo a mapa de mesas después de dividir cuenta');
                            setTimeout(function() {
                                window.location.href = '{$redirectUrl}';
                            }, 500);
                        ");

                    } catch (\Exception $e) {
                        DB::rollBack();
                        Notification::make()
                            ->title('Error')
                            ->body('Hubo un error al dividir la cuenta: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn (): bool => $this->order !== null && count($this->order->orderDetails) > 0),


        ];
    }

    /**
     * Registrar acciones adicionales para mountAction()
     */
    protected function getActions(): array
    {
        return [
            $this->processBillingAction(),
        ];
    }

    public function mount(): void
    {
        // Obtener parámetros de la URL
        $this->selectedTableId = request()->get('table_id');
        $orderId = request()->get('order_id');

        // Inicializar canClearCart como true por defecto
        $this->canClearCart = true;

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
                    ->title('Orden cargada correctamente')
                    ->success()
                    ->send();

                $this->order = $activeOrder;
                $this->selectedTableId = $activeOrder->table_id;
                $this->cartItems = [];

                $this->canClearCart = false; // Deshabilitar limpiar carrito porque ya hay orden

                // ✅ LÓGICA KISS: Si la orden tiene detalles, significa que fue "guardada" previamente
                // Por lo tanto, debe estar bloqueada hasta que se use "Reabrir Orden"
                $this->canAddProducts = false; // Bloquear hasta reabrir explícitamente

                foreach ($activeOrder->orderDetails as $detail) {
                    // Analizar las notas para detectar si es una bebida con temperatura
                    $isColdDrink = false;
                    $temperature = null;
                    $isGrillItem = false;
                    $cookingPoint = null;
                    
                    // Verificar si el producto es una bebida que puede ser helada
                    if ($detail->product) {
                        $isColdDrink = $this->isColdDrinkCategory($detail->product);
                        $isGrillItem = $this->isGrillCategory($detail->product);
                        
                        // Si es una bebida, detectar la temperatura desde las notas
                        if ($isColdDrink && $detail->notes) {
                            if (strpos($detail->notes, 'HELADA') !== false) {
                                $temperature = 'HELADA';
                            } elseif (strpos($detail->notes, 'AL TIEMPO') !== false) {
                                $temperature = 'AL TIEMPO';
                            }
                        }
                        
                        // Si es bebida pero no tiene temperatura especificada, por defecto HELADA
                        if ($isColdDrink && !$temperature) {
                            $temperature = 'HELADA';
                        }
                        
                        // Si es un producto de parrilla, detectar el punto de cocción desde las notas
                        if ($isGrillItem && $detail->notes) {
                            $cookingPoints = ['AZUL', 'ROJO', 'MEDIO', 'TRES CUARTOS', 'BIEN COCIDO'];
                            foreach ($cookingPoints as $point) {
                                if (strpos($detail->notes, $point) !== false) {
                                    $cookingPoint = $point;
                                    break;
                                }
                            }
                        }
                        
                        // Si es parrilla pero no tiene punto de cocción especificado, por defecto MEDIO
                        if ($isGrillItem && !$cookingPoint) {
                            $cookingPoint = 'MEDIO';
                        }
                        
                        // Si es un producto de pollo, detectar el tipo de presa desde las notas
                        $chickenCutType = null;
                        if ($this->isChickenCutCategory($detail->product) && $detail->notes) {
                            $chickenCutTypes = ['PECHO', 'PIERNA'];
                            foreach ($chickenCutTypes as $cutType) {
                                if (strpos($detail->notes, $cutType) !== false) {
                                    $chickenCutType = $cutType;
                                    break;
                                }
                            }
                        }
                        
                        // Si es pollo pero no tiene tipo de presa especificado, por defecto PECHO
                        if ($this->isChickenCutCategory($detail->product) && !$chickenCutType) {
                            $chickenCutType = 'PECHO';
                        }
                    }
                    
                    $this->cartItems[] = [
                        'product_id' => $detail->product_id,
                        'name' => $detail->product ? $detail->product->name : 'Producto eliminado',
                        'quantity' => $detail->quantity,
                        'unit_price' => $detail->unit_price,
                        'notes' => $detail->notes ?? '',
                        'is_cold_drink' => $isColdDrink,
                        'temperature' => $temperature,
                        'is_grill_item' => $isGrillItem,
                        'cooking_point' => $cookingPoint,
                        'is_chicken_cut' => $this->isChickenCutCategory($detail->product),
                        'chicken_cut_type' => $chickenCutType,
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
                }
            }
        }
        // *** LÓGICA PARA CARGAR ORDEN EXISTENTE POR MESA (SOLO SI NO HAY ORDER_ID) ***
        elseif ($this->selectedTableId) {
            // Buscar la orden abierta para esta mesa
            $activeOrder = Order::with('orderDetails.product')
                ->where('table_id', $this->selectedTableId)
                ->where('status', Order::STATUS_OPEN)
                ->first();

            if ($activeOrder) {
                $this->order = $activeOrder;
                $this->cartItems = []; // Limpiar por si acaso
                $this->canClearCart = false;
                
                // ✅ LÓGICA KISS: Si la orden tiene detalles, debe estar bloqueada
                $this->canAddProducts = false; // Bloquear hasta reabrir explícitamente

                foreach ($activeOrder->orderDetails as $detail) {
                    // Analizar las notas para detectar si es una bebida con temperatura
                    $isColdDrink = false;
                    $temperature = null;
                    $isGrillItem = false;
                    $cookingPoint = null;
                    
                    // Verificar si el producto es una bebida que puede ser helada
                    if ($detail->product) {
                        $isColdDrink = $this->isColdDrinkCategory($detail->product);
                        $isGrillItem = $this->isGrillCategory($detail->product);
                        
                        // Si es una bebida, detectar la temperatura desde las notas
                        if ($isColdDrink && $detail->notes) {
                            if (strpos($detail->notes, 'HELADA') !== false) {
                                $temperature = 'HELADA';
                            } elseif (strpos($detail->notes, 'AL TIEMPO') !== false) {
                                $temperature = 'AL TIEMPO';
                            }
                        }
                        
                        // Si es bebida pero no tiene temperatura especificada, por defecto HELADA
                        if ($isColdDrink && !$temperature) {
                            $temperature = 'HELADA';
                        }
                        
                        // Si es un producto de parrilla, detectar el punto de cocción desde las notas
                        if ($isGrillItem && $detail->notes) {
                            $cookingPoints = ['AZUL', 'ROJO', 'MEDIO', 'TRES CUARTOS', 'BIEN COCIDO'];
                            foreach ($cookingPoints as $point) {
                                if (strpos($detail->notes, $point) !== false) {
                                    $cookingPoint = $point;
                                    break;
                                }
                            }
                        }
                        
                        // Si es parrilla pero no tiene punto de cocción especificado, por defecto MEDIO
                        if ($isGrillItem && !$cookingPoint) {
                            $cookingPoint = 'MEDIO';
                        }
                        
                        // Si es un producto de pollo, detectar el tipo de presa desde las notas
                        $chickenCutType = null;
                        if ($this->isChickenCutCategory($detail->product) && $detail->notes) {
                            $chickenCutTypes = ['PECHO', 'PIERNA'];
                            foreach ($chickenCutTypes as $cutType) {
                                if (strpos($detail->notes, $cutType) !== false) {
                                    $chickenCutType = $cutType;
                                    break;
                                }
                            }
                        }
                        
                        // Si es pollo pero no tiene tipo de presa especificado, por defecto PECHO
                        if ($this->isChickenCutCategory($detail->product) && !$chickenCutType) {
                            $chickenCutType = 'PECHO';
                        }
                    }
                    
                    $this->cartItems[] = [
                        'product_id' => $detail->product_id,
                        'name' => $detail->product ? $detail->product->name : 'Producto eliminado',
                        'quantity' => $detail->quantity,
                        'unit_price' => $detail->unit_price,
                        'notes' => $detail->notes ?? '',
                        'is_cold_drink' => $isColdDrink,
                        'temperature' => $temperature,
                        'is_grill_item' => $isGrillItem,
                        'cooking_point' => $cookingPoint,
                        'is_chicken_cut' => $this->isChickenCutCategory($detail->product),
                        'chicken_cut_type' => $chickenCutType,
                    ];
                }
                $this->numberOfGuests = $this->order->number_of_guests ?? 1;
                $this->isCartDisabled = true;
            }
        }

        // 🏎️ OPTIMIZACIÓN KISS: cachear el árbol de categorías por 1 h
        $this->categories = Cache::remember('pos_categories', 3600, fn () =>
            ProductCategory::with('children')
                ->whereNull('parent_category_id')
                ->where('visible_in_menu', true)
                ->get()
        );

        // Cargar productos iniciales (sin filtro de categoría)
        $this->products = Product::select('id', 'name', 'sale_price', 'category_id')
            ->with('category:id,name')
            ->when($this->search !== '', fn($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('id')
            ->limit(150)
            ->get();

        $this->productsLoaded = true; // Marcar como cargados

        // Inicializar subcategorías según la categoría activa (si existe)
        $this->subcategories = $this->selectedCategoryId
            ? ProductCategory::where('parent_category_id', $this->selectedCategoryId)->get()
            : collect();

        // Calcular totales basados en el carrito (si lo hubiera)
        $this->calculateTotals();
        $this->loadInitialData();
    }

    /**
     * Maneja la selección de una categoría y carga sus productos
     */
    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
        $this->selectedSubcategoryId = null; // Resetear subcategoría al cambiar categoría

        // Cargar subcategorías si se seleccionó una categoría
        $this->subcategories = $categoryId
            ? ProductCategory::where('parent_category_id', $categoryId)->get()
            : collect();

        $this->loadProductsLazy();
    }

    /**
     * Maneja la selección de una subcategoría y carga sus productos
     */
    public function selectSubcategory(?int $subcategoryId): void
    {
        $this->selectedSubcategoryId = $subcategoryId;
        $this->loadProductsLazy();
    }

    public function loadInitialData(): void
    {
        // Solo categorías raíz para evitar que las subcategorías se muestren como principales
        $this->categories = ProductCategory::with('children')
            ->whereNull('parent_category_id')
            ->where('visible_in_menu', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();
        $this->customers = Customer::all();
    }

    public function updatedSelectedCategoryId($value)
    {
        $this->loadProductsLazy();
    }

    public function updatedSearch()
    {
        $this->loadProductsLazy();
    }

    public function loadProductsLazy(): void
    {
        // Si no se pueden agregar productos, retornar
        if (!$this->canAddProducts) {
            Notification::make()
                ->title('No se pueden agregar productos')
                ->body('La orden ya está guardada. Debe reabrir la orden para agregar más productos.')
                ->warning()
                ->duration(3000)
                ->send();
            return;
        }

        $query = Product::query()
            ->select('id', 'name', 'sale_price', 'category_id')
            ->with('category:id,name');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        } elseif ($this->selectedSubcategoryId) {
            $query->where('category_id', $this->selectedSubcategoryId);
        } elseif ($this->selectedCategoryId) {
            // Si hay una categoría seleccionada, obtener todas las subcategorías
            $subcategoryIds = ProductCategory::where('parent_category_id', $this->selectedCategoryId)
                ->pluck('id')
                ->push($this->selectedCategoryId) // Incluir también la categoría principal
                ->toArray();

            $query->whereIn('category_id', $subcategoryIds);
        }

        $this->products = $query->orderBy('name')->limit(150)->get();
        $this->productsLoaded = true;
    }

    public function addToCart(Product $product)
    {
        // Verificar si se pueden agregar productos
        if (!$this->canAddProducts) {
            Notification::make()
                ->title('No se pueden agregar productos')
                ->body('La orden está guardada. Debe reabrir la orden para agregar más productos.')
                ->warning()
                ->duration(3000)
                ->send();
            return;
        }

        $existingItemKey = collect($this->cartItems)->search(fn($item) => $item['product_id'] === $product->id);

        if ($existingItemKey !== false) {
            $this->cartItems[$existingItemKey]['quantity']++;
        } else {
            $isColdDrink = $this->isColdDrinkCategory($product);
            $isGrillItem = $this->isGrillCategory($product);
            $isChickenCut = $this->isChickenCutCategory($product);
            
            $this->cartItems[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'unit_price' => $product->sale_price,
                'notes' => '',
                'temperature' => $isColdDrink ? 'HELADA' : null, // Por defecto HELADA para bebidas
                'is_cold_drink' => $isColdDrink,
                'is_grill_item' => $isGrillItem,
                'cooking_point' => $isGrillItem ? 'MEDIO' : null, // Por defecto MEDIO para parrillas
                'is_chicken_cut' => $isChickenCut,
                'chicken_cut_type' => $isChickenCut ? 'PECHO' : null, // Por defecto PECHO para pollos
            ];
        }
        $this->calculateTotals();
    }

    public function updateQuantity(int $index, int $quantity)
    {
        // Verificar si se puede modificar el carrito
        if (!$this->canClearCart) {
            Notification::make()
                ->title('No se puede modificar la cantidad')
                ->body('La orden está guardada. Debe reabrir la orden primero.')
                ->warning()
                ->duration(3000)
                ->send();
            return;
        }

        if (isset($this->cartItems[$index])) {
            if ($quantity <= 0) {
                unset($this->cartItems[$index]);
                $this->cartItems = array_values($this->cartItems);
            } else {
                $this->cartItems[$index]['quantity'] = $quantity;
                $this->cartItems[$index]['subtotal'] = $quantity * $this->cartItems[$index]['unit_price'];
            }
        }
        $this->calculateTotals();
    }

    public function calculateTotals()
    {
        // ✅ CÁLCULO CORRECTO: Los precios en BD YA incluyen IGV
        $totalConIGV = collect($this->cartItems)->sum(function ($item) {
            return $item['unit_price'] * $item['quantity'];
        });
        
        // 🧮 CÁLCULO INVERSO DEL IGV (18%)
        $this->subtotal = $totalConIGV / 1.18;  // Base imponible sin IGV
        $this->tax = $this->subtotal * 0.18;    // IGV calculado
        $this->total = $this->subtotal + $this->tax; // Total (igual al precio original)
    }

    public function clearCart(): void
    {
        $this->cartItems = [];
        $this->calculateTotals();
        $this->isCartDisabled = false;
        $this->numberOfGuests = 1;
        Notification::make()->title('Carrito limpiado')->success()->send();
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

        // Validar que se haya ingresado el número de comensales
        if (!$this->numberOfGuests || $this->numberOfGuests < 1) {
            Notification::make()
                ->title('Error')
                ->body('Debe ingresar el número de comensales')
                ->danger()
                ->duration(5000)
                ->send();
            return;
        }

        try {
            if ($this->order) {
                // Actualizar orden existente
                DB::transaction(function () {
                    // Primero, obtener los IDs de los productos en el carrito
                    $cartProductIds = collect($this->cartItems)->pluck('product_id')->toArray();

                    // Eliminar detalles de productos que ya no están en el carrito
                    $this->order->orderDetails()
                        ->whereNotIn('product_id', $cartProductIds)
                        ->delete();

                    // Actualizar o crear detalles para los productos en el carrito
                    foreach ($this->cartItems as $item) {
                        $notes = $item['notes'] ?? '';
                        
                        // Agregar información de bebida helada/al tiempo si corresponde
                        if (($item['is_cold_drink'] ?? false) === true && !empty($item['temperature'])) {
                            $temperatureNote = $item['temperature'];
                            $notes = trim($notes . ' ' . $temperatureNote);
                        }
                        
                        // Agregar información de punto de cocción para parrillas si corresponde
                        if (($item['is_grill_item'] ?? false) === true && !empty($item['cooking_point'])) {
                            $cookingPointNote = $item['cooking_point'];
                            $notes = trim($notes . ' ' . $cookingPointNote);
                        }
                        
                        // Agregar información de tipo de presa para pollos si corresponde
                        if (($item['is_chicken_cut'] ?? false) === true && !empty($item['chicken_cut_type'])) {
                            $chickenCutNote = $item['chicken_cut_type'];
                            $notes = trim($notes . ' ' . $chickenCutNote);
                        }
                        
                        $this->order->orderDetails()->updateOrCreate(
                            ['product_id' => $item['product_id']],
                            [
                                'quantity' => $item['quantity'],
                                'unit_price' => $item['unit_price'],
                                'subtotal' => $item['quantity'] * $item['unit_price'],
                                'notes' => $notes
                            ]
                        );
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

                    // Limpiar el carrito después de guardar
                    $this->cartItems = [];
                    $this->calculateTotals();

                    // Deshabilitar el botón Limpiar Carrito después de guardar
                    $this->canClearCart = false;
                    $this->canAddProducts = false;

                    Notification::make()
                        ->title('🎉 Orden actualizada exitosamente')
                        ->body('Orden #' . $this->order->id . ' actualizada con éxito')
                        ->success()
                        ->send();

                    // Forzar recarga de la orden para actualizar el estado
                    $this->order = $this->order->fresh(['orderDetails.product', 'table', 'invoices']);
                });
            } else {
                // Crear nueva orden
                DB::transaction(function () {
                    // ✅ PASO 1: Buscar el empleado correspondiente al usuario logueado.
                    $employee = Employee::where('user_id', Auth::id())->first();

                    // Si no se encuentra un empleado, detener la operación.
                    if (!$employee) {
                        Notification::make()
                            ->title('Error de Empleado')
                            ->body('El usuario actual no tiene un registro de empleado válido para crear órdenes.')
                            ->danger()
                            ->send();
                        throw new Halt();
                    }

                    $orderData = [
                        'table_id' => $this->selectedTableId,
                        'customer_id' => null,
                        'employee_id' => $employee->id,
                        'status' => Order::STATUS_OPEN,
                        'total_price' => $this->total,
                        'order_datetime' => now(),
                        'number_of_guests' => $this->numberOfGuests,
                        'order_type' => $this->selectedTableId ? 'in_place' : 'direct_sale',
                    ];

                    $this->order = Order::create($orderData);

                    foreach ($this->cartItems as $item) {
                        $notes = $item['notes'] ?? '';
                        
                        // Agregar información de bebida helada/al tiempo si corresponde
                        if (($item['is_cold_drink'] ?? false) === true && !empty($item['temperature'])) {
                            $temperatureNote = $item['temperature'];
                            $notes = trim($notes . ' ' . $temperatureNote);
                        }
                        
                        // Agregar información de punto de cocción para parrillas si corresponde
                        if (($item['is_grill_item'] ?? false) === true && !empty($item['cooking_point'])) {
                            $cookingPointNote = $item['cooking_point'];
                            $notes = trim($notes . ' ' . $cookingPointNote);
                        }
                        
                        // Agregar información de tipo de presa para pollos si corresponde
                        if (($item['is_chicken_cut'] ?? false) === true && !empty($item['chicken_cut_type'])) {
                            $chickenCutNote = $item['chicken_cut_type'];
                            $notes = trim($notes . ' ' . $chickenCutNote);
                        }
                        
                        $this->order->orderDetails()->create([
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'subtotal' => $item['quantity'] * $item['unit_price'],
                            'price' => $item['unit_price'] * $item['quantity'],
                            'notes' => $notes,
                        ]);
                    }

                    if ($this->selectedTableId) {
                        TableModel::find($this->selectedTableId)->update(['status' => TableModel::STATUS_OCCUPIED]);
                    }

                    $this->isCartDisabled = true;

                    // Deshabilitar el botón Limpiar Carrito después de guardar
                    $this->canClearCart = false;
                    $this->canAddProducts = false;

                    Notification::make()
                        ->title('🎉 Orden creada exitosamente')
                        ->body('Orden #' . $this->order->id . ' creada con éxito')
                        ->success()
                        ->send();
                });
            }

            // Refrescar datos y UI
            $this->refreshOrderData();
            $this->dispatch('$refresh');

        } catch (Halt $e) {
            // Detiene la ejecución sin registrar un error grave, ya que la notificación ya se envió.
        } catch (\Exception $e) {
            Log::error('Error al procesar la orden en TPV: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine());
            Notification::make()->title('Error al guardar la orden')->body('Ocurrió un error inesperado. Revisa los logs.')->danger()->send();
        }
    }

    public function reopenOrderForEditing(): void
    {
        $this->isCartDisabled = false;
        $this->canAddProducts = true; // Permitir agregar productos nuevamente
        $this->canClearCart = true; // Permitir limpiar el carrito
        Notification::make()->title('Orden reabierta para edición')->warning()->send();
    }

    public function createOrderFromCart(?Customer $customer = null, array $orderItems = []): Order
    {
        try {
            DB::beginTransaction();

            // Usar los items proporcionados o los del carrito si no se proporcionan
            $items = !empty($orderItems) ? $orderItems : $this->cartItems;

            // Crear la orden
            $order = new Order([
                'table_id' => $this->selectedTableId,
                'customer_id' => $customer?->id,
                'user_id' => auth()->id(),
                'status' => Order::STATUS_OPEN,
                'number_of_guests' => $this->numberOfGuests,
                'notes' => $this->customerNote,
                'subtotal' => $this->subtotal,
                'tax' => $this->tax,
                'total' => $this->total,
            ]);

            $order->save();

            // Crear los detalles de la orden
            foreach ($items as $item) {
                $notes = $item['notes'] ?? '';
                
                // Agregar información de bebida helada/al tiempo si corresponde
                if (($item['is_cold_drink'] ?? false) === true && !empty($item['temperature'])) {
                    // Asegurarse de que la temperatura esté en mayúsculas y destacada
                    $temperatureNote = $item['temperature'];
                    $notes = trim($notes . ' ' . $temperatureNote);
                }
                
                // Agregar información de punto de cocción para parrillas si corresponde
                if (($item['is_grill_item'] ?? false) === true && !empty($item['cooking_point'])) {
                    // Asegurarse de que el punto de cocción esté en mayúsculas y destacado
                    $cookingPointNote = $item['cooking_point'];
                    $notes = trim($notes . ' ' . $cookingPointNote);
                }
                
                // Agregar información de tipo de presa para pollos si corresponde
                if (($item['is_chicken_cut'] ?? false) === true && !empty($item['chicken_cut_type'])) {
                    // Asegurarse de que el tipo de presa esté en mayúsculas y destacado
                    $chickenCutNote = $item['chicken_cut_type'];
                    $notes = trim($notes . ' ' . $chickenCutNote);
                }
                
                $order->orderDetails()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['quantity'] * $item['unit_price'],
                    'notes' => $notes,
                ]);
            }

            DB::commit();
            return $order;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear orden desde carrito: ' . $e->getMessage());
            throw $e;
        }
    }

    public function processTransfer(array $data): void
    {
        try {
            // 🔍 VALIDACIÓN INICIAL SEGÚN DOCUMENTACIÓN DE FILAMENT
            if (!$this->order) {
                Notification::make()
                    ->title('❌ Error')
                    ->body('No hay una orden activa para transferir.')
                    ->danger()
                    ->send();
                return;
            }

            if (empty($data['transferItems'])) {
                Notification::make()
                    ->title('❌ Error de Validación')
                    ->body('No se han seleccionado productos para transferir.')
                    ->danger()
                    ->send();
                return;
            }

            if (empty($data['new_table_id'])) {
                Notification::make()
                    ->title('❌ Error de Validación')
                    ->body('Debe seleccionar una mesa de destino.')
                    ->danger()
                    ->send();
                return;
            }

            // 🔍 VERIFICAR MESA DESTINO
            $targetTable = TableModel::find($data['new_table_id']);
            if (!$targetTable) {
                Notification::make()
                    ->title('❌ Mesa No Encontrada')
                    ->body('La mesa de destino no existe.')
                    ->danger()
                    ->send();
                return;
            }

            // 🔄 INICIAR TRANSACCIÓN SEGÚN DOCUMENTACIÓN DE FILAMENT
            DB::beginTransaction();

            $transferredItems = 0;
            $targetOrder = null;

            // 🎯 BUSCAR O CREAR ORDEN EN MESA DESTINO
            $existingOrder = Order::where('table_id', $targetTable->id)
                ->where('status', Order::STATUS_OPEN)
                ->where('billed', false)
                ->first();

            if ($existingOrder) {
                $targetOrder = $existingOrder;
            } else {
                // Crear nueva orden en mesa destino
                $employee = Employee::where('user_id', Auth::id())->first();
                if (!$employee) {
                    DB::rollBack();
                    Notification::make()
                        ->title('❌ Error de Empleado')
                        ->body('El usuario actual no tiene un registro de empleado válido.')
                        ->danger()
                        ->send();
                    return;
                }

                $targetOrder = Order::create([
                    'service_type' => 'dine_in',
                    'table_id' => $targetTable->id,
                    'customer_id' => $this->order->customer_id,
                    'employee_id' => $employee->id,
                    'order_datetime' => now(),
                    'status' => Order::STATUS_OPEN,
                    'subtotal' => 0,
                    'tax' => 0,
                    'total' => 0,
                    'discount' => 0,
                    'billed' => false,
                    'cash_register_id' => $this->order->cash_register_id,
                ]);

                // Actualizar estado de la mesa destino
                $targetTable->update([
                    'status' => TableModel::STATUS_OCCUPIED,
                    'occupied_at' => now()
                ]);
            }

            // 🚀 PROCESAR CADA PRODUCTO A TRANSFERIR
            foreach ($data['transferItems'] as $item) {
                if ($item['quantity_to_move'] <= 0) {
                    continue; // Saltar productos sin cantidad a mover
                }

                $orderDetail = OrderDetail::find($item['order_detail_id']);
                if (!$orderDetail || $orderDetail->order_id !== $this->order->id) {
                    continue; // Saltar detalles inválidos
                }

                $quantityToMove = min($item['quantity_to_move'], $orderDetail->quantity);

                // Verificar si el producto ya existe en la orden destino
                $existingDetailInTarget = OrderDetail::where('order_id', $targetOrder->id)
                    ->where('product_id', $orderDetail->product_id)
                    ->first();

                if ($existingDetailInTarget) {
                    // Combinar con producto existente
                    $existingDetailInTarget->quantity += $quantityToMove;
                    $existingDetailInTarget->subtotal = $existingDetailInTarget->quantity * $existingDetailInTarget->unit_price;
                    $existingDetailInTarget->save();
                } else {
                    // Crear nuevo detalle en orden destino
                    OrderDetail::create([
                        'order_id' => $targetOrder->id,
                        'product_id' => $orderDetail->product_id,
                        'quantity' => $quantityToMove,
                        'unit_price' => $orderDetail->unit_price,
                        'subtotal' => $quantityToMove * $orderDetail->unit_price,
                        'notes' => $orderDetail->notes,
                        'status' => 'pending'
                    ]);
                }

                // Actualizar orden origen
                if ($quantityToMove >= $orderDetail->quantity) {
                    // Eliminar detalle completo
                    $orderDetail->delete();
                } else {
                    // Reducir cantidad
                    $orderDetail->quantity -= $quantityToMove;
                    $orderDetail->subtotal = $orderDetail->quantity * $orderDetail->unit_price;
                    $orderDetail->save();
                }

                $transferredItems++;
            }

            if ($transferredItems === 0) {
                DB::rollBack();
                Notification::make()
                    ->title('⚠️ Sin Transferencias')
                    ->body('No se pudo transferir ningún producto. Verifique las cantidades.')
                    ->warning()
                    ->send();
                return;
            }

            // 🧮 RECALCULAR TOTALES DE AMBAS ÓRDENES
            $this->order->recalculateTotals();
            $targetOrder->recalculateTotals();

            // 🔍 VERIFICAR SI LA ORDEN ORIGEN QUEDÓ VACÍA
            if ($this->order->orderDetails()->count() === 0) {
                // Liberar mesa origen
                $sourceTable = $this->order->table;
                if ($sourceTable) {
                    $sourceTable->update([
                        'status' => TableModel::STATUS_AVAILABLE,
                        'occupied_at' => null
                    ]);
                }
                
                // Eliminar orden vacía
                $this->order->delete();
                
                // ✅ CONFIRMAR TRANSACCIÓN Y REDIRECCIONAR
                DB::commit();

                Notification::make()
                    ->title('✅ Transferencia Completa')
                    ->body("Se transfirieron {$transferredItems} productos a la mesa {$targetTable->number}. La mesa actual quedó libre.")
                    ->success()
                    ->send();

                // Redireccionar al mapa de mesas
                $this->redirect(TableMap::getUrl());
            }

            // ✅ CONFIRMAR TRANSACCIÓN
            DB::commit();

            // 📝 ACTUALIZAR ESTADO LOCAL
            $this->refreshOrderData();

            // 🎉 NOTIFICACIÓN DE ÉXITO SEGÚN DOCUMENTACIÓN DE FILAMENT
            Notification::make()
                ->title('✅ Transferencia Exitosa')
                ->body("Se transfirieron {$transferredItems} productos a la mesa {$targetTable->number}.")
                ->success()
                ->duration(5000)
                ->send();

        } catch (\Exception $e) {
            // 🔙 ROLLBACK EN CASO DE ERROR SEGÚN DOCUMENTACIÓN
            DB::rollBack();

            Log::error('Error en transferencia de productos', [
                'order_id' => $this->order?->id,
                'target_table_id' => $data['new_table_id'] ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            Notification::make()
                ->title('❌ Error en Transferencia')
                ->body('Ocurrió un error al transferir los productos: ' . $e->getMessage())
                ->danger()
                ->persistent()
                ->send();
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
            // Detectar si es bebida con temperatura o producto de parrilla
            $isColdDrink = false;
            $temperature = null;
            $isGrillItem = false;
            $cookingPoint = null;
            
            // Verificar si el producto es una bebida que puede ser helada o un producto de parrilla
            if ($detail->product) {
                $isColdDrink = $this->isColdDrinkCategory($detail->product);
                $isGrillItem = $this->isGrillCategory($detail->product);
                
                // Si es una bebida, detectar la temperatura desde las notas
                if ($isColdDrink && $detail->notes) {
                    if (strpos($detail->notes, 'HELADA') !== false) {
                        $temperature = 'HELADA';
                    } elseif (strpos($detail->notes, 'AL TIEMPO') !== false) {
                        $temperature = 'AL TIEMPO';
                    }
                }
                
                // Si es bebida pero no tiene temperatura especificada, por defecto HELADA
                if ($isColdDrink && !$temperature) {
                    $temperature = 'HELADA';
                }
                
                // Si es un producto de parrilla, detectar el punto de cocción desde las notas
                if ($isGrillItem && $detail->notes) {
                    $cookingPoints = ['AZUL', 'ROJO', 'MEDIO', 'TRES CUARTOS', 'BIEN COCIDO'];
                    foreach ($cookingPoints as $point) {
                        if (strpos($detail->notes, $point) !== false) {
                            $cookingPoint = $point;
                            break;
                        }
                    }
                }
                
                // Si es parrilla pero no tiene punto de cocción especificado, por defecto MEDIO
                if ($isGrillItem && !$cookingPoint) {
                    $cookingPoint = 'MEDIO';
                }
                
                // Si es un producto de pollo, detectar el tipo de presa desde las notas
                $chickenCutType = null;
                if ($this->isChickenCutCategory($detail->product) && $detail->notes) {
                    $chickenCutTypes = ['PECHO', 'PIERNA'];
                    foreach ($chickenCutTypes as $cutType) {
                        if (strpos($detail->notes, $cutType) !== false) {
                            $chickenCutType = $cutType;
                            break;
                        }
                    }
                }
                
                // Si es pollo pero no tiene tipo de presa especificado, por defecto PECHO
                if ($this->isChickenCutCategory($detail->product) && !$chickenCutType) {
                    $chickenCutType = 'PECHO';
                }
            }
            
            $this->cartItems[] = [
                'product_id' => $detail->product_id,
                'name'       => $detail->product->name,
                'quantity'   => $detail->quantity,
                'unit_price' => $detail->unit_price,
                'subtotal'   => $detail->subtotal,
                'notes'      => $detail->notes ?? '',
                'is_cold_drink' => $isColdDrink,
                'temperature' => $temperature,
                'is_grill_item' => $isGrillItem,
                'cooking_point' => $cookingPoint,
                'is_chicken_cut' => $this->isChickenCutCategory($detail->product),
                'chicken_cut_type' => $chickenCutType,
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
            ->modal()
            ->modalWidth('7xl')
            ->modalAlignment('center')
            ->extraModalWindowAttributes(['style' => 'max-height: 90vh; overflow-y: auto;'])
            ->visible(fn() => Auth::user()->hasRole(['cashier', 'admin', 'super_admin']))
            ->form(function () {
                return [
                    // LAYOUT HORIZONTAL - DIVIDIR EN 2 COLUMNAS
                    Forms\Components\Grid::make(2)->schema([
                        // COLUMNA IZQUIERDA: RESUMEN + MÉTODO DE PAGO
                        Forms\Components\Section::make('💳 Información de Pago')
                            ->compact()
                            ->schema([
                                // Resumen compacto
                                Forms\Components\Placeholder::make('payment_summary')
                                    ->label('Total a Pagar')
                                    ->content(function () {
                                        return new \Illuminate\Support\HtmlString(
                                            '<div class="text-center p-4 bg-green-50 border border-green-200 rounded">' .
                                            '<span class="text-2xl font-bold text-green-700">S/ ' . number_format($this->total, 2) . '</span>' .
                                            '<div class="text-sm text-green-600">(IGV incluido)</div>' .
                                            '</div>'
                                        );
                                    }),

                                // Método de pago en grid horizontal
                                Forms\Components\Grid::make(3)->schema([
                                    Forms\Components\Select::make('primary_payment_method')
                                        ->label('Método de Pago')
                                        ->options([
                                            'cash' => '💵 Efectivo',
                                            'card' => '💳 Tarjeta',
                                            'yape' => '📱 Yape',
                                            'plin' => '💙 Plin',
                                        ])
                                        ->default('cash')
                                        ->live()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('primary_payment_amount')
                                        ->label('Monto Recibido')
                                        ->numeric()
                                        ->prefix('S/')
                                        ->live()
                                        ->default($this->total)
                                        ->step(0.01)
                                        ->minValue(0.01)
                                        ->columnSpan(1),

                                    Forms\Components\Placeholder::make('payment_change')
                                        ->label('Vuelto')
                                        ->content(function (Get $get) {
                                            $amount = (float) ($get('primary_payment_amount') ?? 0);
                                            $change = $amount - $this->total;

                                            if ($change > 0) {
                                                return new \Illuminate\Support\HtmlString(
                                                    "<div class='p-2 bg-green-50 border border-green-200 rounded text-center'>" .
                                                        "<span class='text-green-700 font-bold'>S/ " . number_format($change, 2) . "</span>" .
                                                        "</div>"
                                                );
                                            } elseif ($change < 0) {
                                                return new \Illuminate\Support\HtmlString(
                                                    "<div class='p-2 bg-red-50 border border-red-200 rounded text-center'>" .
                                                        "<span class='text-red-700 font-bold'>Falta: S/ " . number_format(abs($change), 2) . "</span>" .
                                                        "</div>"
                                                );
                                            } else {
                                                return new \Illuminate\Support\HtmlString(
                                                    "<div class='p-2 bg-blue-50 border border-blue-200 rounded text-center'>" .
                                                        "<span class='text-blue-700 font-bold'>Exacto ✓</span>" .
                                                        "</div>"
                                                );
                                            }
                                        })
                                        ->live()
                                        ->visible(fn(Get $get) => $get('primary_payment_method') === 'cash')
                                        ->columnSpan(1),
                                ]),

                                // Botones de denominaciones
                                Forms\Components\Actions::make([
                                    Forms\Components\Actions\Action::make('exact')
                                        ->label('Exacto')
                                        ->color('success')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set) {
                                            $set('primary_payment_amount', $this->total);
                                        }),
                                    Forms\Components\Actions\Action::make('10')
                                        ->label('10')
                                        ->color('gray')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set) {
                                            $set('primary_payment_amount', 10);
                                        }),
                                    Forms\Components\Actions\Action::make('20')
                                        ->label('20')
                                        ->color('gray')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set) {
                                            $set('primary_payment_amount', 20);
                                        }),
                                    Forms\Components\Actions\Action::make('50')
                                        ->label('50')
                                        ->color('gray')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set) {
                                            $set('primary_payment_amount', 50);
                                        }),
                                    Forms\Components\Actions\Action::make('100')
                                        ->label('100')
                                        ->color('gray')
                                        ->size('xs')
                                        ->action(function (Forms\Set $set) {
                                            $set('primary_payment_amount', 100);
                                        }),
                                ])
                                    ->visible(fn(Get $get) => $get('primary_payment_method') === 'cash')
                                    ->extraAttributes(['class' => 'flex flex-wrap gap-1 justify-center']),

                                // Tipo de comprobante
                                Forms\Components\ToggleButtons::make('document_type')
                                    ->label('📄 Comprobante')
                                    ->options([
                                        'sales_note' => '📝 Nota',
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
                                    ->inline()
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
                            ])
                            ->columnSpan(1),

                        // COLUMNA DERECHA: PRODUCTOS + CLIENTE
                        Forms\Components\Section::make('🛒 Detalle de la Venta')
                            ->compact()
                            ->schema([
                                // Resumen de productos en tabla compacta
                                Forms\Components\Placeholder::make('order_summary')
                                    ->label('Productos')
                                    ->content(function () {
                                        // ✅ USAR CARRITO SI NO HAY ORDEN (VENTA DIRECTA)
                                        $items = [];
                                        if ($this->order && $this->order->orderDetails) {
                                            // Usar orden existente
                                            foreach ($this->order->orderDetails as $detail) {
                                                $items[] = [
                                                    'name' => $detail->product->name ?? 'N/A',
                                                    'quantity' => $detail->quantity,
                                                    'price' => $detail->unit_price,
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

                                        $html = '<div class="border border-gray-200 rounded overflow-hidden max-h-48 overflow-y-auto">';
                                        $html .= '<table class="w-full text-xs">';
                                        $html .= '<thead class="bg-gray-50"><tr>';
                                        $html .= '<th class="px-2 py-1 text-left">Producto</th>';
                                        $html .= '<th class="px-2 py-1 text-center">Cant.</th>';
                                        $html .= '<th class="px-2 py-1 text-right">Precio</th>';
                                        $html .= '<th class="px-2 py-1 text-right">Total</th>';
                                        $html .= '</tr></thead><tbody>';

                                        foreach ($items as $item) {
                                            $html .= '<tr class="border-b border-gray-100">';
                                            $html .= '<td class="px-2 py-1 text-sm">' . substr(htmlspecialchars($item['name']), 0, 20) . '</td>';
                                            $html .= '<td class="px-2 py-1 text-center text-sm">' . $item['quantity'] . '</td>';
                                            $priceValue = $item['price'] ?? $item['unit_price'] ?? 0;
                                            $html .= '<td class="px-2 py-1 text-right text-sm">S/ ' . number_format($priceValue, 2) . '</td>';
                                            $subtotalValue = $item['subtotal'] ?? ($item['quantity'] * $priceValue);
                                            $html .= '<td class="px-2 py-1 text-right text-sm font-medium">S/ ' . number_format($subtotalValue, 2) . '</td>';
                                            $html .= '</tr>';
                                        }

                                        $html .= '</tbody></table></div>';
                                        return new \Illuminate\Support\HtmlString($html);
                                    }),

                                // Cliente información compacta
                                Forms\Components\Select::make('customer_id')
                                    ->label('👤 Cliente')
                                    ->required(fn(Get $get) => in_array($get('document_type'), ['receipt', 'invoice']))
                                    ->searchable()
                                    ->options(function (): array {
                                        return Customer::limit(20)->pluck('name', 'id')->toArray();
                                    })
                                    ->getSearchResultsUsing(function (string $search): array {
                                        return Customer::where('name', 'like', "%{$search}%")
                                            ->orWhere('document_number', 'like', "%{$search}%")
                                            ->limit(20)
                                            ->pluck('name', 'id')
                                            ->toArray();
                                    })
                                    ->visible(fn(Get $get) => in_array($get('document_type'), ['receipt', 'invoice'])),
                            ])
                            ->columnSpan(1),
                    ])
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
                    ];
                }

                // ✅ SIN DATOS ORIGINALES: USAR CLIENTE GENÉRICO Y PAGO EN EFECTIVO  
                return [
                    'primary_payment_method' => 'cash',
                    'primary_payment_amount' => $this->total,
                    'document_type' => 'sales_note', // ✅ Iniciar con Nota de Venta por defecto
                    'customer_id' => 1,
                    'customer_name' => 'Cliente General',
                    'customer_document_type' => 'DNI',
                    'customer_document' => '',
                    'customer_address' => '',
                    'customer_phone' => '',
                    'customer_email' => '',
                ];
            })
            ->action(function (array $data) {
                return $this->handlePayment($data);
            })
            ->modalHeading('💳 Procesar Pago de Orden')
            ->modalSubmitActionLabel('💳 Pagar e Imprimir')
            ->extraAttributes([
                'class' => 'fi-modal-window-xl'
            ]);
    }

    protected function handlePayment(array $data)
    {
        try {
            DB::transaction(function () use ($data) {
                // Lógica de procesamiento de pago
                // Crear factura, actualizar orden, registrar pago, etc.
                
                Notification::make()
                    ->title('✅ Pago procesado')
                    ->body('El pago se ha registrado correctamente')
                    ->success()
                    ->send();
            });
            
            return true;
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Error en el pago')
                ->body('No se pudo procesar el pago: ' . $e->getMessage())
                ->danger()
                ->send();
                
            return false;
        }
    }

    public function reimprimirComprobante(): void
    {
        if ($this->order && $this->order->invoices()->exists()) {
            $lastInvoice = $this->order->invoices()->latest()->first();
            
            Log::info('🖨️ Reimprimiendo comprobante desde vista', [
                'invoice_id' => $lastInvoice->id,
                'invoice_type' => $lastInvoice->invoice_type,
                'order_id' => $this->order->id
            ]);

            $this->dispatch('open-print-window', ['id' => $lastInvoice->id]);

            Notification::make()
                ->title('🖨️ Abriendo impresión...')
                ->body("Comprobante {$lastInvoice->series}-{$lastInvoice->number}")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('❌ Sin comprobantes')
                ->body('No hay comprobantes generados para reimprimir')
                ->warning()
                ->send();
        }
    }
}
