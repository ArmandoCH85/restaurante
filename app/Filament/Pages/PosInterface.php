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
    protected static ?string $title = '';
    protected static ?string $navigationLabel = 'Venta Directa';
    protected static ?string $navigationGroup = '🏪 Operaciones Diarias';
    protected static ?string $slug = 'pos-interface';
    protected static ?int $navigationSort = 1;

    public function getMaxContentWidth(): ?string
    {
        return 'full'; // Usar todo el ancho disponible para el POS
    }

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
    public bool $hasOpenCashRegister = true;
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
    public ?string $customerPhone = null;

    protected function getEmployeeId(): int
    {
        $employee = Employee::where('user_id', auth()->id())->first();

        if (!$employee) {
            throw new \Exception('No se encontró un empleado asociado al usuario actual.');
        }

        return $employee->id;
    }

    /**
     * Verifica si se puede procesar un pago
     * Método auxiliar para validaciones antes del pago
     */
    protected function canProcessPayment(): bool
    {
        // Debe tener orden O items en carrito
        if (!$this->order && empty($this->cartItems)) {
            Log::warning('❌ No se puede procesar pago: Sin orden ni items en carrito');
            return false;
        }

        // Debe tener total mayor a 0
        if ($this->total <= 0) {
            Log::warning('❌ No se puede procesar pago: Total es 0 o negativo', ['total' => $this->total]);
            return false;
        }

        return true;
    }

    /**
     * Limpia órdenes abandonadas del empleado actual
     * Previene acumulación de órdenes inactivas durante la sesión
     */
    protected function cleanUserAbandonedOrders(): void
    {
        try {
            $employeeId = $this->getEmployeeId();

            $cleaned = Order::where('employee_id', $employeeId)
                ->where('status', Order::STATUS_OPEN)
                ->where('billed', false)
                ->where('created_at', '<', now()->subHours(2)) // 2 horas timeout
                ->delete();

            if ($cleaned > 0) {
                Log::info("🧹 Órdenes abandonadas limpiadas del empleado", [
                    'user_id' => auth()->id(),
                    'employee_id' => $employeeId,
                    'cleaned_count' => $cleaned,
                    'timeout_hours' => 2
                ]);
            }
        } catch (\Exception $e) {
            Log::warning("⚠️ Error limpiando órdenes abandonadas del empleado", [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);
        }
    }
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

        // Búsqueda por patrón: productos que contengan "¼" y "pollo" (case insensitive)
        if (str_contains($product->name, '¼') && str_contains(strtolower($product->name), 'pollo')) {
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

    /**
     * Verifica si la mesa actual tiene cuentas divididas
     */
    public function tieneCuentasDivididas(): bool
    {
        if (!$this->selectedTable) {
            return false;
        }

        // Verificar si hay órdenes hijas (cuentas divididas) en esta mesa
        return Order::where('table_id', $this->selectedTable->id)
            ->where('status', Order::STATUS_OPEN)
            ->whereNotNull('parent_id')
            ->exists();
    }

    /**
     * Une todas las cuentas divididas de la mesa actual
     */
    public function unirCuentas(): void
    {
        if (!$this->selectedTable) {
            Notification::make()
                ->title('Error')
                ->body('No hay mesa seleccionada')
                ->danger()
                ->send();
            return;
        }

        try {
            DB::beginTransaction();

            // Obtener cuenta principal (sin parent_id)
            $cuentaPrincipal = Order::where('table_id', $this->selectedTable->id)
                ->where('status', Order::STATUS_OPEN)
                ->whereNull('parent_id')
                ->first();

            // Obtener cuentas divididas (con parent_id)
            $cuentasDivididas = Order::where('table_id', $this->selectedTable->id)
                ->where('status', Order::STATUS_OPEN)
                ->whereNotNull('parent_id')
                ->get();

            if ($cuentasDivididas->isEmpty()) {
                Notification::make()
                    ->title('Información')
                    ->body('No hay cuentas divididas para unir')
                    ->info()
                    ->send();
                return;
            }

            // Si no hay cuenta principal, usar la primera cuenta dividida como principal
            if (!$cuentaPrincipal) {
                $cuentaPrincipal = $cuentasDivididas->first();
                $cuentaPrincipal->update(['parent_id' => null]);
                $cuentasDivididas = $cuentasDivididas->except($cuentaPrincipal->id);
            }

            // Transferir todos los productos de cuentas divididas a la principal
            foreach ($cuentasDivididas as $cuentaDividida) {
                foreach ($cuentaDividida->orderDetails as $detail) {
                    // Buscar si ya existe el mismo producto en la cuenta principal
                    $existingDetail = $cuentaPrincipal->orderDetails()
                        ->where('product_id', $detail->product_id)
                        ->where('unit_price', $detail->unit_price)
                        ->where('notes', $detail->notes)
                        ->first();

                    if ($existingDetail) {
                        // Combinar cantidades si el producto ya existe
                        $existingDetail->update([
                            'quantity' => $existingDetail->quantity + $detail->quantity,
                            'subtotal' => ($existingDetail->quantity + $detail->quantity) * $detail->unit_price
                        ]);
                    } else {
                        // Transferir el detalle a la cuenta principal
                        $detail->update(['order_id' => $cuentaPrincipal->id]);
                    }
                }

                // Eliminar la cuenta dividida vacía
                $cuentaDividida->delete();
            }

            // Recalcular totales de la cuenta principal
            $cuentaPrincipal->refresh();
            $subtotal = $cuentaPrincipal->orderDetails->sum('subtotal');
            $tax = $subtotal * 0.18; // IGV 18%
            $total = $subtotal + $tax;

            $cuentaPrincipal->update([
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total,
                'number_of_guests' => $cuentaPrincipal->orderDetails->sum('quantity')
            ]);

            // Actualizar la orden actual y carrito
            $this->order = $cuentaPrincipal;
            $this->updateCartFromOrder();

            DB::commit();

            Notification::make()
                ->title('Éxito')
                ->body('Cuentas unidas exitosamente')
                ->success()
                ->send();

        } catch (\Exception $e) {
            DB::rollBack();
            
            Notification::make()
                ->title('Error')
                ->body('Error al unir las cuentas: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        // Los botones ahora están integrados en el carrito para mejor UX
        return [
            /* TODOS LOS BOTONES COMENTADOS - AHORA EN EL CARRITO
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

                    // Recalcular totales para asegurar valores actualizados
                    $order->recalculateTotals();

                    // Usar los totales ya calculados del modelo Order
                    $subtotal = $order->subtotal;
                    $tax = $order->tax;
                    $total = $order->total;

                    // DEBUG: Log temporal para verificar valores
                    \Log::info('DEBUG Pre-Bill Values', [
                        'order_id' => $order->id,
                        'order_details_sum' => $order->orderDetails->sum('subtotal'),
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'total' => $total
                    ]);

                    return view('filament.modals.pre-bill-content', [
                        'order' => $order,
                        'subtotal' => $subtotal,
                        'tax' => $tax,
                        'total' => $total
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
                            'cash_register_id' => CashRegister::getActiveCashRegisterId(),
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
            */

        ];
    }

    /**
     * Registrar acciones adicionales para mountAction()
     */
    protected function getActions(): array
    {
        return [
            $this->processBillingAction(),
            $this->printComandaAction(),
            $this->printPreBillNewAction(),
            $this->reopen_order_for_editingAction(),
            $this->backToTableMapAction(),
            $this->releaseTableAction(),
            $this->cancelOrderAction(),
            $this->transferOrderAction(),
            $this->split_itemsAction(),
        ];
    }

    public function mount(): void
    {
        // Configurar memoria específicamente para POS
        @ini_set('memory_limit', '1024M');
        @ini_set('max_execution_time', 600);
        @ini_set('max_input_time', 600);

        // Limpiar memoria
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        // Estado de caja: bloquear UX si no hay caja abierta
        $this->hasOpenCashRegister = CashRegister::hasOpenRegister();
        if (!$this->hasOpenCashRegister) {
            // Endurecer controles de UI para evitar acciones
            $this->isCartDisabled = true;
            $this->canAddProducts = false;

            Notification::make()
                ->title('Caja no abierta')
                ->body('Abra una caja para crear órdenes o procesar pagos en el POS.')
                ->danger()
                ->persistent()
                ->send();
        }

        // Obtener parámetros de la URL
        $this->selectedTableId = request()->get('table_id');
        $orderId = request()->get('order_id');

        // Inicializar canClearCart como true por defecto
        $this->canClearCart = true;

        // *** MANEJO DE DATOS DE DELIVERY ***
        $fromDelivery = request()->get('from');
        if ($fromDelivery === 'delivery') {
            $deliveryData = session('delivery_data');

            if ($deliveryData) {
                Log::info('🚚 DATOS DE DELIVERY RECIBIDOS EN POS', $deliveryData);

                // Configurar cliente para delivery
                $this->selectedCustomerId = $deliveryData['customer_id'] ?? null;
                $this->customerName = $deliveryData['customer_name'] ?? '';
                // Prefijar nombre para comanda cuando viene de Delivery
                if (empty($this->customerNameForComanda)) {
                    $this->customerNameForComanda = $this->customerName;
                }

                // Configurar datos adicionales de delivery
                if (isset($deliveryData['customer_phone'])) {
                    $this->customerPhone = $deliveryData['customer_phone'];
                }

                // Obtener datos completos del cliente para facturación
                $customer = null;
                if (isset($deliveryData['customer_id'])) {
                    $customer = Customer::find($deliveryData['customer_id']);
                }

                // Guardar datos originales del cliente para facturación
                $this->originalCustomerData = [
                    'customer_id' => $deliveryData['customer_id'] ?? null,
                    'customer_name' => $deliveryData['customer_name'] ?? '',
                    'customer_phone' => $deliveryData['customer_phone'] ?? '',
                    'customer_address' => $deliveryData['delivery_address'] ?? '',
                    'customer_document_type' => $customer ? $customer->document_type : 'DNI',
                    'customer_document' => $customer ? $customer->document_number : '',
                    'customer_email' => $customer ? $customer->email : '',
                    'service_type' => 'delivery'
                ];

                Log::info('✅ CLIENTE CONFIGURADO PARA DELIVERY', [
                    'customer_id' => $this->selectedCustomerId,
                    'customer_name' => $this->customerName,
                    'customer_phone' => $this->customerPhone,
                    'original_data' => $this->originalCustomerData
                ]);

                // Mostrar notificación de confirmación
                Notification::make()
                    ->title('🚚 Delivery Configurado')
                    ->body("Cliente: {$this->customerName} - Listo para tomar pedido")
                    ->success()
                    ->send();

                // Limpiar datos de sesión después de usarlos
                session()->forget('delivery_data');
            }
        }

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
                            } elseif (strpos($detail->notes, 'FRESCA') !== false) {
                                $temperature = 'FRESCA';
                            }
                        }

                        // Sin temperatura por defecto si no está especificada
                        // $temperature mantiene su valor (null si no se encontró en las notas)

                        // Si es un producto de parrilla, detectar el punto de cocción desde las notas
                        if ($isGrillItem && $detail->notes) {
                            $cookingPoints = ['AZUL', 'Punto Azul', 'Término medio', 'tres cuartos', 'bien cocido'];
                            foreach ($cookingPoints as $point) {
                                if (strpos($detail->notes, $point) !== false) {
                                    $cookingPoint = $point;
                                    break;
                                }
                            }
                        }

                        // Si es parrilla pero no tiene punto de cocción especificado, por defecto MEDIO
                        if ($isGrillItem && !$cookingPoint) {
                            $cookingPoint = 'Término medio';
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

                        // Si es pollo pero no tiene tipo de presa especificado, sin valor por defecto
                        if ($this->isChickenCutCategory($detail->product) && !$chickenCutType) {
                            $chickenCutType = null;
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
                            } elseif (strpos($detail->notes, 'FRESCA') !== false) {
                                $temperature = 'FRESCA';
                            }
                        }

                        // Sin temperatura por defecto si no está especificada
                        // $temperature mantiene su valor (null si no se encontró en las notas)

                        // Si es un producto de parrilla, detectar el punto de cocción desde las notas
                        if ($isGrillItem && $detail->notes) {
                            $cookingPoints = ['AZUL', 'Punto Azul', 'Término medio', 'tres cuartos', 'bien cocido'];
                            foreach ($cookingPoints as $point) {
                                if (strpos($detail->notes, $point) !== false) {
                                    $cookingPoint = $point;
                                    break;
                                }
                            }
                        }

                        // Si es parrilla pero no tiene punto de cocción especificado, por defecto MEDIO
                        if ($isGrillItem && !$cookingPoint) {
                            $cookingPoint = 'Término medio';
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

                        // Si es pollo pero no tiene tipo de presa especificado, sin valor por defecto
                        if ($this->isChickenCutCategory($detail->product) && !$chickenCutType) {
                            $chickenCutType = null;
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

        // Cargar información de la mesa si hay selectedTableId
        $this->selectedTable = $this->selectedTableId ? TableModel::find($this->selectedTableId) : null;
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

    public function clearSearch()
    {
        $this->search = '';
        $this->loadProductsLazy();
    }

    public function loadProductsLazy(): void
    {
        // PERMITIR BÚSQUEDA SIEMPRE - Solo restringir al agregar al carrito

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
        } else {
            // Sin filtros - cargar productos limitados (como en mount)
            $query->orderBy('id')->limit(150);
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
                'temperature' => null, // Sin temperatura por defecto
                'is_cold_drink' => $isColdDrink,
                'is_grill_item' => $isGrillItem,
                'cooking_point' => $isGrillItem ? 'Término medio' : null, // Por defecto MEDIO para parrillas
                'is_chicken_cut' => $isChickenCut,
                'chicken_cut_type' => $isChickenCut ? null : null, // Sin selección por defecto para pollos
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

    public function removeItem(int $index, bool $pinOk = false): void
    {
    // Para waiter: pedir PIN cuando existe una orden (guardada o reabierta)
    // En carrito nuevo (sin orden aún), no solicitar PIN
    if (Auth::user()?->hasRole('waiter') && $this->order && !$pinOk) {
            $this->dispatch('pos-pin-required', action: 'removeItem', index: $index);
            return;
        }
        // Verificar si se puede modificar el carrito
        if (!$this->canClearCart) {
            Notification::make()
                ->title('No se puede eliminar el producto')
                ->body('La orden está guardada. Debe reabrir la orden primero.')
                ->warning()
                ->duration(3000)
                ->send();
            return;
        }

        if (isset($this->cartItems[$index])) {
            $productName = $this->cartItems[$index]['name'];
            unset($this->cartItems[$index]);
            $this->cartItems = array_values($this->cartItems);
            $this->calculateTotals();

            Notification::make()
                ->title('Producto eliminado')
                ->body($productName . ' fue eliminado del carrito')
                ->success()
                ->duration(2000)
                ->send();
        }
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

    public function clearCart(bool $pinOk = false): void
    {
    // Para waiter: pedir PIN cuando existe una orden (guardada o reabierta)
    // En carrito nuevo (sin orden aún), no solicitar PIN
    if (Auth::user()?->hasRole('waiter') && $this->order && !$pinOk) {
            $this->dispatch('pos-pin-required', action: 'clearCart');
            return;
        }
        $this->cartItems = [];
        $this->calculateTotals();
        $this->isCartDisabled = false;
        $this->numberOfGuests = 1;
        Notification::make()->title('Carrito limpiado')->success()->send();
    }

    /**
     * Verifica el PIN (123456) para rol waiter y ejecuta la acción.
     */
    public function verifyPinAndExecute(string $action, ?int $index, string $pin): void
    {
        $user = Auth::user();

        // Para roles distintos a waiter, ejecutar normal como fallback
        if (!$user?->hasRole('waiter')) {
            if ($action === 'removeItem' && $index !== null) {
                $this->removeItem((int) $index);
            } elseif ($action === 'clearCart') {
                $this->clearCart();
            }
            return;
        }

        if ($pin !== '123456') {
            Notification::make()
                ->title('PIN incorrecto')
                ->body('No se autorizó la operación.')
                ->danger()
                ->duration(3000)
                ->send();
            return;
        }

        if ($action === 'removeItem' && $index !== null) {
            $this->removeItem((int) $index, true);
        } elseif ($action === 'clearCart') {
            $this->clearCart(true);
        }
    }

    public function processOrder(): void
    {
        // Bloqueo por ausencia de caja abierta
        if (!CashRegister::hasOpenRegister()) {
            Notification::make()
                ->title('Caja cerrada')
                ->body('No puede crear o actualizar órdenes sin una caja abierta.')
                ->danger()
                ->send();
            return;
        }

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

                        // Agregar información de temperatura solo si se seleccionó una opción
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

                    // Determinar service_type válido según esquema (dine_in | takeout | delivery | drive_thru)
                    $serviceType = $this->selectedTableId ? 'dine_in' : 'takeout';
                    $allowedServiceTypes = ['dine_in', 'takeout', 'delivery', 'drive_thru'];
                    if (!in_array($serviceType, $allowedServiceTypes, true)) {
                        Log::warning('service_type inválido detectado al crear orden; aplicando fallback', [
                            'propuesto' => $serviceType,
                            'fallback' => 'takeout',
                        ]);
                        $serviceType = 'takeout';
                    }

                    $orderData = [
                        'service_type' => $serviceType,
                        'table_id' => $this->selectedTableId,
                        'customer_id' => null,
                        'employee_id' => $employee->id,
                        'cash_register_id' => CashRegister::getActiveCashRegisterId(),
                        'status' => Order::STATUS_OPEN,
                        'subtotal' => $this->subtotal,
                        'tax' => $this->tax,
                        'total' => $this->total,
                        'order_datetime' => now(),
                        'number_of_guests' => $this->numberOfGuests,
                    ];

                    $this->order = Order::create($orderData);

                    foreach ($this->cartItems as $item) {
                        $notes = $item['notes'] ?? '';

                        // Agregar información de temperatura solo si se seleccionó una opción
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
                            'notes' => $notes,
                        ]);
                    }

                    // Recalcular totales desde los detalles para garantizar consistencia
                    $this->order->recalculateTotals();

                    // Check adicional: verificar que el total almacenado sea coherente
                    $expectedTotal = (float) $this->total;
                    $storedTotal = (float) $this->order->total;
                    if (abs($storedTotal - $expectedTotal) > 0.01) {
                        Log::warning('Desajuste de totales al crear orden; se usará el total recalculado desde BD', [
                            'expected_ui_total' => $expectedTotal,
                            'stored_total' => $storedTotal,
                            'order_id' => $this->order->id ?? null,
                        ]);
                        // No arrojamos Halt; priorizamos el total recalculado por consistencia
                        Notification::make()
                            ->title('Aviso: total ajustado')
                            ->body('El total de la orden fue ajustado para coincidir con los detalles.')
                            ->warning()
                            ->duration(4000)
                            ->send();
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
            // Validación previa: requiere caja abierta
            $activeRegisterId = CashRegister::getActiveCashRegisterId();
            if (!$activeRegisterId) {
                Notification::make()
                    ->title('Caja no abierta')
                    ->body('Abra una caja antes de crear la orden.')
                    ->danger()
                    ->send();
                throw new Halt();
            }

            DB::beginTransaction();

            // Usar los items proporcionados o los del carrito si no se proporcionan
            $items = !empty($orderItems) ? $orderItems : $this->cartItems;

            // Crear la orden
            // Determinar service_type válido
            $serviceType = $this->selectedTableId ? 'dine_in' : 'takeout';
            $order = new Order([
                'service_type' => $serviceType,
                'table_id' => $this->selectedTableId,
                'customer_id' => $customer?->id,
                'employee_id' => $this->getEmployeeId(), // Obtener employee_id correcto
                'cash_register_id' => $activeRegisterId,
                'order_datetime' => now(), // Agregar order_datetime requerido
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

                // Agregar información de temperatura solo si se seleccionó una opción
                if (($item['is_cold_drink'] ?? false) === true && !empty($item['temperature'])) {
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

            // Recalcular totales después de crear todos los detalles
            $order->recalculateTotals();

            // Validación defensiva: no permitir totales <= 0 o sin detalles
            if ($order->orderDetails()->count() === 0 || $order->total <= 0) {
                DB::rollBack();
                Notification::make()
                    ->title('❌ No se pudo crear la orden')
                    ->body('La orden no tiene ítems o el total es 0. Agregue productos válidos.')
                    ->danger()
                    ->send();
                throw new Halt();
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

                // Sin temperatura por defecto si no está especificada
                // $temperature mantiene su valor (null si no se encontró en las notas)

                // Si es un producto de parrilla, detectar el punto de cocción desde las notas
                if ($isGrillItem && $detail->notes) {
                    $cookingPoints = ['AZUL', 'Punto Azul', 'Término medio', 'tres cuartos', 'bien cocido'];
                    foreach ($cookingPoints as $point) {
                        if (strpos($detail->notes, $point) !== false) {
                            $cookingPoint = $point;
                            break;
                        }
                    }
                }

                // Si es parrilla pero no tiene punto de cocción especificado, por defecto MEDIO
                if ($isGrillItem && !$cookingPoint) {
                    $cookingPoint = 'Término medio';
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

                // Si es pollo pero no tiene tipo de presa especificado, sin valor por defecto
                if ($this->isChickenCutCategory($detail->product) && !$chickenCutType) {
                    $chickenCutType = null;
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
                'temperature_selected' => !is_null($temperature), // Si hay temperatura, está seleccionada
                'is_grill_item' => $isGrillItem,
                'cooking_point' => $cookingPoint,
                'cooking_point_selected' => !is_null($cookingPoint), // Si hay punto de cocción, está seleccionado
                'is_chicken_cut' => $this->isChickenCutCategory($detail->product),
                'chicken_cut_type' => $chickenCutType,
                'chicken_cut_type_selected' => !is_null($chickenCutType), // Si hay tipo de presa, está seleccionado
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
            ->modalWidth('5xl')
            ->modalAlignment('center')
            ->modalHeading('💳 Procesar Pago de Orden')
            ->modalDescription('Revisa totales, métodos y datos del cliente antes de confirmar.')
            ->modalSubmitActionLabel('💳 Pagar e Imprimir')
            ->extraModalWindowAttributes(['style' => 'max-height:80vh;overflow-y:auto;max-width:1180px;width:1180px;background:#f8fafc;border:1px solid #e2e8f0;padding:0.75rem;box-shadow:0 4px 18px -2px rgba(0,0,0,0.15);border-radius:0.9rem;'])
            ->visible(fn() => Auth::user()->hasRole(['cashier','admin','super_admin']))
            ->before(function () {
                if (!CashRegister::hasOpenRegister()) {
                    $this->hasOpenCashRegister = false;
                    Notification::make()->title('Caja no abierta')->body('Abra una caja para procesar pagos.')->danger()->persistent()->send();
                    throw new Halt();
                }
                $this->cleanUserAbandonedOrders();
                if (!$this->order && !empty($this->cartItems)) {
                    try {
                        $customer = $this->selectedCustomerId ? Customer::find($this->selectedCustomerId) : null;
                        $this->order = $this->createOrderFromCart($customer);
                        if ($this->originalCustomerData && ($this->originalCustomerData['service_type'] ?? '') === 'delivery') {
                            $this->order->update(['service_type' => 'delivery','table_id' => null]);
                        }
                        Notification::make()->title('📋 Orden Preparada')->body("Orden #{$this->order->id} lista para pago")->success()->duration(3000)->send();
                    } catch (\Exception $e) {
                        Log::error('❌ Error auto-creando orden para pago', ['error' => $e->getMessage()]);
                        Notification::make()->title('❌ Error')->body('No se pudo preparar la orden: '.$e->getMessage())->danger()->persistent()->send();
                        throw $e;
                    }
                }
            })
            ->form(function () {
                $card = 'bg-white/95 backdrop-blur-sm rounded-lg border border-gray-200 shadow-sm ring-1 ring-white/40';
                $innerPad = 'p-4';
                return [
                    Forms\Components\Grid::make(12)
                        ->extraAttributes(['class' => 'fi-pos-billing-grid gap-4'])
                        ->schema([
                            Forms\Components\Group::make([
                                Forms\Components\Placeholder::make('payment_summary')
                                    ->label('Total a Pagar')
                                    ->content(function () { return new \Illuminate\Support\HtmlString('<div class="text-center sticky top-0 z-10 p-4 rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100/55 border border-emerald-200 shadow-sm ring-1 ring-emerald-100/60"><div class="text-[11px] font-semibold tracking-widest text-emerald-600/70 mb-1 uppercase">Total a Pagar</div><div class="text-3xl font-extrabold leading-none tracking-tight text-emerald-600 drop-shadow">S/ ' . number_format((float)$this->total, 2) . '</div><div class="mt-1 text-[10px] font-semibold text-emerald-500/80 uppercase">IGV incluido</div></div>'); }),
                                Forms\Components\Toggle::make('split_payment')->label('💰 Dividir Pago')->helperText('Activa si usarás varios métodos.')->live()->default(false)->inline(false)->extraAttributes(['class' => 'mt-2']),
                                Forms\Components\ToggleButtons::make('document_type')
                                    ->label('📄 Comprobante')
                                    ->options(['sales_note' => '📝 Nota','receipt' => '🧾 Boleta','invoice' => '📋 Factura'])
                                    ->colors(['sales_note' => 'info','receipt' => 'warning','invoice' => 'success'])
                                    ->extraAttributes(['class' => 'w-full'])
                                    ->default('sales_note')
                                    ->live()->inline()->columnSpanFull(),
                            ])->columnSpan(['md' => 4,'lg' => 3])->extraAttributes(['class' => 'space-y-4']),
                            Forms\Components\Group::make([
                                Forms\Components\Section::make('Método de Pago')
                                    ->compact()->extraAttributes(['class' => $card.' '.$innerPad.' space-y-3 relative'])
                                    ->visible(fn(Get $get) => !$get('split_payment'))
                                    ->schema([
                                        Forms\Components\Grid::make(3)->extraAttributes(['class'=>'gap-3'])->schema([
                                            Forms\Components\Select::make('payment_method')->label('Método')->options(['cash'=>'💵 Efectivo','card'=>'💳 Tarjeta','yape'=>'📱 Yape','plin'=>'💙 Plin','pedidos_ya'=>'🛵 Pedidos Ya','didi_food'=>'🚗 Didi Food'])->default('cash')->live()->columnSpan(1),
                                            Forms\Components\TextInput::make('payment_amount')->label('Monto Recibido')->numeric()->prefix('S/')->live()->default((float)$this->total)->step(0.01)->minValue(fn(Get $get) => $get('payment_method')==='cash' ? (float)$this->total : 0.01)->rule(fn(Get $get) => function(string $a,$v,\Closure $fail) use($get){ if($get('payment_method')==='cash'){ $val=(float)$v; if($val + 1e-6 < (float)$this->total){ $fail('El monto recibido debe ser mayor o igual al total.'); } } })->columnSpan(1),
                                            Forms\Components\Placeholder::make('change_display')->label('Vuelto')->content(function (Get $get){ $amount=(float)($get('payment_amount')??0); $totalAmount = is_numeric($this->total) ? (float)$this->total : 0.0; $change=$amount-$totalAmount; if($change>0){return new \Illuminate\Support\HtmlString("<div class='p-1 bg-green-50 border border-green-200 rounded text-center'><span class='text-green-700 font-bold text-xs'>S/ ".number_format((float)$change,2)."</span></div>");} elseif($change<0){return new \Illuminate\Support\HtmlString("<div class='p-1 bg-red-50 border border-red-200 rounded text-center'><span class='text-red-700 font-bold text-xs'>Falta: S/ ".number_format(abs((float)$change),2)."</span></div>");} return new \Illuminate\Support\HtmlString("<div class='p-1 bg-blue-50 border border-blue-200 rounded text-center'><span class='text-blue-700 font-bold text-xs'>Exacto ✓</span></div>"); })->live()->visible(fn(Get $get)=>$get('payment_method')==='cash')->columnSpan(1),
                                        ]),
                                        Forms\Components\TextInput::make('voucher_code')->label('🎫 Código de Voucher')->placeholder('Código del terminal POS')->helperText('Visible solo para tarjeta')->visible(fn(Get $get)=>$get('payment_method')==='card')->required(fn(Get $get)=>$get('payment_method')==='card')->maxLength(50)->live(),
                                        Forms\Components\Actions::make([
                                            Forms\Components\Actions\Action::make('exactPayment')->label('Exacto')->color('success')->size('xs')->action(fn(Forms\Set $set)=>$set('payment_amount',(float)$this->total)),
                                            Forms\Components\Actions\Action::make('10')->label('10')->color('gray')->size('xs')->action(fn(Forms\Set $set)=>$set('payment_amount',10)),
                                            Forms\Components\Actions\Action::make('20')->label('20')->color('gray')->size('xs')->action(fn(Forms\Set $set)=>$set('payment_amount',20)),
                                            Forms\Components\Actions\Action::make('50')->label('50')->color('gray')->size('xs')->action(fn(Forms\Set $set)=>$set('payment_amount',50)),
                                            Forms\Components\Actions\Action::make('100')->label('100')->color('gray')->size('xs')->action(fn(Forms\Set $set)=>$set('payment_amount',100)),
                                        ])->visible(fn(Get $get)=>$get('payment_method')==='cash')->extraAttributes(['class'=>'flex flex-wrap gap-1 justify-center']),
                                    ]),
                                Forms\Components\Section::make('Métodos de Pago Múltiples')
                                    ->compact()->extraAttributes(['class' => $card.' '.$innerPad.' space-y-4'])
                                    ->visible(fn(Get $get) => $get('split_payment'))
                                    ->schema([
                                        Forms\Components\Repeater::make('payment_methods')
                                            ->schema([
                                                Forms\Components\Grid::make(3)->extraAttributes(['class'=>'gap-3'])->schema([
                                                    Forms\Components\Select::make('method')->label('Método')->options(['cash'=>'💵 Efectivo','card'=>'💳 Tarjeta','yape'=>'📱 Yape','plin'=>'💙 Plin','pedidos_ya'=>'🛵 Pedidos Ya','didi_food'=>'🚗 Didi Food'])->required()->live()->columnSpan(1),
                                                    Forms\Components\TextInput::make('amount')->label('Monto')->numeric()->prefix('S/')->step(0.01)->minValue(0.01)->required()->live()->columnSpan(1),
                                                    Forms\Components\Placeholder::make('remaining')->label('Estado')->content(function (Get $get){ $payments=$get('../../payment_methods')??[]; $totalPaid=0; foreach($payments as $p){ if(isset($p['amount']) && is_numeric($p['amount'])) $totalPaid+=(float)$p['amount']; } $totalAmount = is_numeric($this->total) ? (float)$this->total : 0.0; $remaining=$totalAmount - $totalPaid; if(abs($remaining) < 0.01){return new \Illuminate\Support\HtmlString("<div class='p-1 bg-green-50 border border-green-200 rounded text-center'><span class='text-green-700 font-bold text-[11px]'>Completo ✓</span></div>");} elseif($remaining > 0.01){return new \Illuminate\Support\HtmlString("<div class='p-1 bg-orange-50 border border-orange-200 rounded text-center'><span class='text-orange-700 font-bold text-[11px]'>Falta: S/ ".number_format((float)$remaining,2)."</span></div>");} else {return new \Illuminate\Support\HtmlString("<div class='p-1 bg-red-50 border border-red-200 rounded text-center'><span class='text-red-700 font-bold text-[11px]'>Exceso: S/ ".number_format(abs((float)$remaining),2)."</span></div>");} })->live()->columnSpan(1),
                                                ]),
                                                Forms\Components\TextInput::make('voucher_code')->label('🎫 Código de Voucher')->placeholder('Código del voucher')->helperText('Solo para tarjeta')->visible(fn(Get $get)=>$get('method')==='card')->required(fn(Get $get)=>$get('method')==='card')->maxLength(50)->live(),
                                            ])->defaultItems(1)->addActionLabel('+ Agregar Método de Pago')
                                            ->deleteAction(fn(Forms\Components\Actions\Action $action) => $action
                                                ->requiresConfirmation()
                                                ->modalHeading('Eliminar Método')
                                                ->modalDescription('¿Eliminar este método?')
                                                ->modalSubmitActionLabel('Eliminar')
                                                ->modalCancelActionLabel('Cancelar')
                                            )
                                            ->itemLabel(fn(array $state): ?string => ($state['method'] ?? 'Método').': S/ '.number_format((float)($state['amount'] ?? 0),2))
                                            ->collapsible()
                                            ->cloneAction(fn(Forms\Components\Actions\Action $action) => $action
                                                ->label('Agregar')
                                                ->tooltip('Duplicar método')
                                            )
                                            ->cloneable(),
                                        Forms\Components\Placeholder::make('split_summary')->label('Resumen de Pago')->content(function (Get $get){
                                                $payments = $get('payment_methods') ?? [];
                                                $totalPaid = 0;
                                                foreach ($payments as $p) {
                                                    if (isset($p['amount']) && is_numeric($p['amount'])) {
                                                        $totalPaid += (float) $p['amount'];
                                                    }
                                                }
                                                $remaining = $this->total - $totalPaid;
                                                $status = abs($remaining) < 0.01 ? 'success' : ($remaining > 0 ? 'warning' : 'danger');
                                                $statusText = abs($remaining) < 0.01 ? 'Pago Completo' : ($remaining > 0 ? 'Pago Incompleto' : 'Pago Excedido');
                                                $color = $status === 'success' ? 'green' : ($status === 'warning' ? 'orange' : 'red');
                                                return new \Illuminate\Support\HtmlString(
                                                    '<div class="p-2 bg-gray-50 border border-gray-200 rounded text-[11px] space-y-1">'
                                                    .'<div class="flex justify-between"><span>Total:</span><span class="font-semibold">S/ '.number_format((float)$this->total,2).'</span></div>'
                                                    .'<div class="flex justify-between"><span>Pagado:</span><span class="font-semibold">S/ '.number_format((float)$totalPaid,2).'</span></div>'
                                                    .'<div class="flex justify-between border-t pt-1"><span>Estado:</span><span class="font-semibold text-'.$color.'-700">'.$statusText.'</span></div>'
                                                    .'</div>'
                                                );
                                            })->live(),
                                    ]),
                                Forms\Components\Section::make('👤 Cliente')
                                    ->compact()->extraAttributes(['class' => $card.' '.$innerPad.' space-y-4'])
                                    ->visible(fn(Get $get) => in_array($get('document_type'), ['receipt','invoice']))
                                    ->schema([
                                        Forms\Components\Select::make('customer_id')->label('Cliente Existente')->searchable()->options(fn():array => Customer::limit(20)->pluck('name','id')->toArray())->getSearchResultsUsing(fn(string $search):array => Customer::where('name','like',"%{$search}%")->orWhere('document_number','like',"%{$search}%")->limit(20)->pluck('name','id')->toArray())->live()->afterStateUpdated(function (Set $set,$state){ if($state){ $set('new_customer_name',''); $set('new_customer_phone',''); $set('new_customer_address',''); $set('new_customer_document',''); } }),
                                        Forms\Components\Fieldset::make('Registrar Cliente Nuevo')->schema([
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\TextInput::make('new_customer_name')->label('Nombre Completo')->placeholder('Nombre del cliente')->live()->afterStateUpdated(function (Set $set,$state){ if($state){ $set('customer_id',null); } })->columnSpan(1),
                                                Forms\Components\TextInput::make('new_customer_document')->label('DNI/RUC (Opcional)')->placeholder('Número de documento')->helperText('DNI (8) o RUC (11)')->columnSpan(1),
                                            ]),
                                            Forms\Components\Grid::make(2)->schema([
                                                Forms\Components\TextInput::make('new_customer_phone')->label('Teléfono')->placeholder('Número de teléfono')->tel()->columnSpan(1),
                                                Forms\Components\TextInput::make('new_customer_address')->label('Dirección')->placeholder('Dirección completa')->columnSpan(1),
                                            ]),
                                        ])->columns(1),
                                    ]),
                            ])->columnSpan(['md' => 8,'lg' => 9])->extraAttributes(['class' => 'space-y-4']),
                        ]),
                ];
            })
            ->fillForm(function () {
                if ($this->originalCustomerData) {
                    return [
                        'split_payment' => false,
                        'payment_method' => 'cash',
                        'payment_amount' => $this->total,
                        'payment_methods' => [ ['method' => 'cash','amount' => $this->total] ],
                        'document_type' => 'receipt',
                        'customer_id' => $this->originalCustomerData['customer_id'],
                        'new_customer_name' => '',
                    ];
                }
                return [
                    'split_payment' => false,
                    'payment_method' => 'cash',
                    'payment_amount' => $this->total,
                    'payment_methods' => [ ['method' => 'cash','amount' => $this->total] ],
                    'document_type' => 'sales_note',
                    'customer_id' => null,
                    'new_customer_name' => '',
                ];
            })
            ->action(fn(array $data) => $this->handlePayment($data))
            ->extraAttributes(['style' => 'padding:0.25rem;']);
    }

    protected function handlePayment(array $data)
    {
        try {
            $invoice = null;

            DB::transaction(function () use ($data, &$invoice) {
                // Verificar caja abierta antes de pagar
                if (!CashRegister::hasOpenRegister()) {
                    $this->hasOpenCashRegister = false;
                    Notification::make()
                        ->title('Caja no abierta')
                        ->body('Abra una caja para procesar pagos.')
                        ->danger()
                        ->persistent()
                        ->send();
                    throw new Halt();
                }

                if (!$this->order) {
                    throw new \Exception('No hay orden activa para procesar el pago');
                }

                // Validación previa: recalcular y asegurar que hay detalles y total > 0
                $this->order->recalculateTotals();
                if ($this->order->orderDetails()->count() === 0) {
                    Notification::make()
                        ->title('❌ No se puede facturar')
                        ->body('La orden no tiene productos. Agregue ítems antes de pagar.')
                        ->danger()
                        ->send();
                    throw new Halt();
                }
                if ($this->order->total <= 0) {
                    Notification::make()
                        ->title('❌ Total inválido')
                        ->body('El total de la orden es 0. Verifique los ítems antes de pagar.')
                        ->danger()
                        ->send();
                    throw new Halt();
                }

                // Validar pago dividido si está activado
                if ($data['split_payment'] ?? false) {
                    $this->validateSplitPayment($data['payment_methods'] ?? []);
                }

                // Obtener o crear cliente
                $customerId = $data['customer_id'] ?? $this->selectedCustomerId;

                // Si hay datos de cliente nuevo, crear cliente
                if (!empty($data['new_customer_name'])) {
                    // Preparar datos del cliente
                    $customerData = [
                        'name' => $data['new_customer_name'],
                        'phone' => $data['new_customer_phone'] ?? '',
                        'address' => $data['new_customer_address'] ?? '',
                        'email' => '',
                    ];

                    // Solo agregar document_type y document_number si hay documento
                    $documentNumber = $data['new_customer_document'] ?? '';
                    if (!empty($documentNumber)) {
                        $customerData['document_number'] = $documentNumber;
                        $customerData['document_type'] = strlen($documentNumber) === 11 ? 'RUC' : 'DNI';
                    } else {
                        // Si no hay documento, usar NULL para evitar violación de UNIQUE constraint
                        $customerData['document_number'] = null;
                        $customerData['document_type'] = null;
                    }

                    $newCustomer = Customer::create($customerData);
                    $customerId = $newCustomer->id;

                    Log::info('👤 Cliente nuevo creado', [
                        'customer_id' => $newCustomer->id,
                        'name' => $newCustomer->name,
                        'document_type' => $newCustomer->document_type,
                        'document_number' => $newCustomer->document_number
                    ]);
                }

                // Si no hay cliente seleccionado ni nuevo, usar "Público General"
                if (!$customerId) {
                    $defaultCustomer = Customer::firstOrCreate(
                        ['document_number' => '99999999'],
                        [
                            'document_type' => 'DNI',
                            'name' => 'Público General',
                            'email' => 'publico@general.com',
                            'address' => 'Lima, Perú'
                        ]
                    );
                    $customerId = $defaultCustomer->id;
                }

                // Determinar método de pago y monto PRIMERO
                $paymentMethod = 'cash';
                $paymentAmount = $this->order->total;

                if ($data['split_payment'] ?? false) {
                    // Pago dividido - usar "multiple" como método
                    $paymentMethod = 'multiple';
                    $paymentAmount = $this->order->total;
                } else {
                    // Pago simple
                    $paymentMethod = $data['payment_method'] ?? 'cash';
                    $paymentAmount = $data['payment_amount'] ?? $this->order->total;
                }

                // Seguridad: si es efectivo y el monto es menor al total, bloquear
                if (($data['split_payment'] ?? false) === false) {
                    $methodTmp = $data['payment_method'] ?? 'cash';
                    $amountTmp = (float) ($data['payment_amount'] ?? $this->order->total);
                    if ($methodTmp === 'cash' && $amountTmp + 1e-6 < (float) $this->order->total) {
                        throw new \Exception('El monto recibido debe ser mayor o igual al total a pagar.');
                    }
                }

                // Actualizar orden con método de pago PERO SIN marcar como facturada aún
                $this->order->update([
                    'payment_method' => $paymentMethod,
                    'payment_amount' => $paymentAmount,
                ]);

                // Obtener la serie correcta según el tipo de documento
                $series = $this->getNextSeries($data['document_type'] ?? 'receipt');

                // Crear la factura usando el método del modelo Order
                $invoice = $this->order->generateInvoice(
                    $data['document_type'] ?? 'receipt',
                    $series,
                    $customerId
                );

                if (!$invoice) {
                    throw new \Exception('No se pudo generar la factura');
                }

                // AHORA SÍ marcar orden como completada y facturada (después de generar factura exitosamente)
                $this->order->update([
                    'status' => 'completed',
                    'billed' => true,
                ]);

                // Para pagos divididos, guardar detalles en las notas de la factura
                if ($data['split_payment'] ?? false) {
                    $paymentDetails = [];
                    foreach ($data['payment_methods'] as $payment) {
                        $paymentDetails[] = $payment['method'] . ': S/ ' . number_format((float)$payment['amount'], 2);
                    }
                    $invoice->update([
                        'notes' => 'Pago dividido: ' . implode(', ', $paymentDetails)
                    ]);
                }

                // Registrar el pago en la tabla payments
                if ($data['split_payment'] ?? false) {
                    // Para pagos divididos, registrar cada método por separado
                    foreach ($data['payment_methods'] as $payment) {
                        // Para pagos con tarjeta, usar el voucher_code como referencia
                        $reference = null;
                        if ($payment['method'] === 'card' && !empty($payment['voucher_code'])) {
                            $reference = $payment['voucher_code'];
                        } else {
                            $reference = $payment['reference'] ?? null;
                        }

                        $this->order->registerPayment(
                            $payment['method'],
                            (float) $payment['amount'],
                            $reference
                        );
                    }
                } else {
                    // Para pago simple, registrar un solo pago
                    $reference = null;
                    if ($paymentMethod === 'card' && !empty($data['voucher_code'])) {
                        $reference = $data['voucher_code'];
                    } else {
                        $reference = $data['payment_reference'] ?? null;
                    }

                    $this->order->registerPayment(
                        $paymentMethod,
                        $paymentAmount,
                        $reference
                    );
                }

                // Liberar mesa automáticamente después del pago
                if ($this->order->table) {
                    $this->order->table->update(['status' => TableModel::STATUS_AVAILABLE]);
                    Log::info('🟢 Mesa liberada automáticamente', [
                        'table_id' => $this->order->table->id,
                        'table_status' => 'available'
                    ]);
                }

                // Limpiar carrito y resetear estado
                $this->cartItems = [];
                $this->total = 0.0;
                $this->subtotal = 0.0;
                $this->tax = 0.0;
                $this->order = null;

                Log::info('💳 Pago procesado exitosamente', [
                    'order_id' => $this->order?->id,
                    'invoice_id' => $invoice->id,
                    'total' => $invoice->total,
                    'payment_method' => $paymentMethod,
                    'split_payment' => $data['split_payment'] ?? false
                ]);
            });

            // Mostrar notificación de éxito
            Notification::make()
                ->title('✅ Pago procesado')
                ->body($data['split_payment'] ? 'Pago dividido registrado correctamente' : 'El pago se ha registrado correctamente')
                ->success()
                ->send();

            // Dispatch evento para abrir ventana de impresión (solo si no se ha impreso antes)
            if ($invoice && !session()->has("invoice_printed_{$invoice->id}")) {
                Log::info('🖨️ Disparando evento de impresión', ['invoice_id' => $invoice->id]);
                session(["invoice_printed_{$invoice->id}" => true]);
                $this->dispatch('open-print-window', ['id' => $invoice->id]);
            } else if ($invoice && session()->has("invoice_printed_{$invoice->id}")) {
                Log::info('⚠️ Comprobante ya impreso, evitando duplicado', ['invoice_id' => $invoice->id]);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('❌ Error procesando pago', [
                'error' => $e->getMessage(),
                'order_id' => $this->order?->id
            ]);

            Notification::make()
                ->title('❌ Error en el pago')
                ->body('No se pudo procesar el pago: ' . $e->getMessage())
                ->danger()
                ->send();

            return false;
        }
    }

    /**
     * Valida que el pago dividido sume exactamente el total
     * Permite exceso solo en pagos de efectivo (vuelto)
     */
    protected function validateSplitPayment(array $paymentMethods): void
    {
        if (empty($paymentMethods)) {
            throw new \Exception('Debe agregar al menos un método de pago');
        }

        $totalPaid = 0;
        $cashAmount = 0;
        $hasCash = false;
        
        foreach ($paymentMethods as $payment) {
            if (!isset($payment['method']) || !isset($payment['amount'])) {
                throw new \Exception('Todos los métodos de pago deben tener método y monto');
            }

            if (!is_numeric($payment['amount']) || $payment['amount'] <= 0) {
                throw new \Exception('Todos los montos deben ser números positivos');
            }

            $amount = (float) $payment['amount'];
            $totalPaid += $amount;
            
            // Rastrear si hay pago en efectivo
            if ($payment['method'] === 'cash') {
                $cashAmount += $amount;
                $hasCash = true;
            }
        }

        $orderTotal = (float)$this->total;
        $difference = $totalPaid - $orderTotal;
        
        // Si hay diferencia negativa (falta dinero), siempre es error
        if ($difference < -0.01) {
            throw new \Exception('El total de los pagos (S/ ' . number_format($totalPaid, 2) . ') es insuficiente. Faltan S/ ' . number_format(abs($difference), 2));
        }
        
        // Si hay exceso positivo (sobra dinero)
        if ($difference > 0.01) {
            if (!$hasCash) {
                // Si no hay efectivo, no se permite exceso
                throw new \Exception('El total de los pagos (S/ ' . number_format($totalPaid, 2) . ') excede el total de la orden (S/ ' . number_format($orderTotal, 2) . '). Solo se permite exceso en pagos de efectivo como vuelto.');
            }
            // Si hay efectivo, el exceso es válido (será el vuelto)
        }
    }

    public function reimprimirComprobante(): void
    {
        if ($this->order && $this->order->invoices()->exists()) {
            $lastInvoice = $this->order->invoices()->latest()->first();

            // Validar que no se imprima duplicado incluso en reimpresión
            if (!session()->has("invoice_printed_{$lastInvoice->id}")) {
                Log::info('🖨️ Reimprimiendo comprobante desde vista', [
                    'invoice_id' => $lastInvoice->id,
                    'invoice_type' => $lastInvoice->invoice_type,
                    'order_id' => $this->order->id
                ]);

                session(["invoice_printed_{$lastInvoice->id}" => true]);
                $this->dispatch('open-print-window', ['id' => $lastInvoice->id]);

                Notification::make()
                    ->title('🖨️ Abriendo impresión...')
                    ->body("Comprobante {$lastInvoice->series}-{$lastInvoice->number}")
                    ->success()
                    ->send();
            } else {
                Log::info('⚠️ Comprobante ya impreso, evitando duplicado en reimpresión', [
                    'invoice_id' => $lastInvoice->id,
                    'invoice_type' => $lastInvoice->invoice_type
                ]);
                
                Notification::make()
                    ->title('⚠️ Comprobante ya impreso')
                    ->body("El {$lastInvoice->document_type} {$lastInvoice->series}-{$lastInvoice->number} ya fue impreso")
                    ->warning()
                    ->send();
            }
        } else {
            Notification::make()
                ->title('❌ Sin comprobantes')
                ->body('No hay comprobantes generados para reimprimir')
                ->warning()
                ->send();
        }
    }

    /**
     * Acción para imprimir comanda
     */
    protected function printComandaAction(): Action
    {
        return Action::make('printComanda')
            ->label('Comanda')
            ->icon('heroicon-o-printer')
            ->color('warning')
            ->size('lg')
            ->modal()
            ->modalHeading('👨‍🍳 Comanda')
            ->modalDescription('Resumen para cocina / barra')
            ->modalWidth('xl')
            ->modalAlignment('center')
            ->extraModalWindowAttributes(['style' => 'max-height:80vh;overflow-y:auto;max-width:1000px;width:1000px;background:#ffffff;border:1px solid #e5e7eb;padding:0.75rem;border-radius:0.75rem;'])
            ->slideOver(false)
            ->modalContent(function () {
                // Bloquear si no hay caja abierta
                if (!$this->hasOpenCashRegister) {
                    return view('components.empty-state', [
                        'message' => 'No hay caja abierta. Abra una caja para continuar.'
                    ]);
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
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->extraModalFooterActions([
                Action::make('saveComandaName')
                    ->label('Guardar')
                    ->color('success')
                    ->size('sm')
                    ->tooltip('Guardar nombre del cliente y la orden')
                    ->extraAttributes(['class' => 'font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm hover:shadow transition-all duration-200 border-0'])
                    ->action(function () {
                        // Validar nombre en venta directa
                        if ($this->selectedTableId === null) {
                            $this->customerNameForComanda = trim((string) $this->customerNameForComanda);
                            if ($this->customerNameForComanda === '') {
                                Notification::make()
                                    ->title('Nombre requerido')
                                    ->body('Ingrese el nombre del cliente para la comanda de venta directa.')
                                    ->warning()
                                    ->duration(3000)
                                    ->send();
                                return;
                            }
                            // Persistir temporalmente para impresión de Nota de Venta
                            session(['direct_sale_customer_name' => $this->customerNameForComanda]);
                        }

                        // Guardar/crear la orden sin cerrar el modal
                        $this->processOrder();
                        if (!$this->order) {
                            Notification::make()
                                ->title('No se pudo guardar la orden')
                                ->body('Revise requisitos: caja abierta, comensales y productos.')
                                ->danger()
                                ->send();
                            return;
                        }

                        // Persistir nombre en sesión para impresiones de Nota de Venta (solo venta directa)
                        if ($this->selectedTableId === null) {
                            session(['direct_sale_customer_name' => $this->customerNameForComanda]);
                        }

                        Notification::make()
                            ->title('Datos guardados')
                            ->body('Nombre del cliente y orden guardados correctamente.')
                            ->success()
                            ->duration(2000)
                            ->send();
                    })
                    ->visible(fn() => (bool) $this->order || !empty($this->cartItems)),
                Action::make('printComanda')
                    ->label('🖨️ Imprimir')
                    ->color('primary')
                    ->size('md')
                    ->tooltip('Imprimir comanda para cocina')
                    ->extraAttributes(['class' => 'font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm hover:shadow-lg transition-all duration-200 border-0'])
                    ->action(function () {
                        // Asegurar nombre de cliente en venta directa antes de guardar/imprimir
                        if ($this->selectedTableId === null) {
                            $this->customerNameForComanda = trim((string) $this->customerNameForComanda);
                            if ($this->customerNameForComanda === '') {
                                Notification::make()
                                    ->title('Nombre requerido')
                                    ->body('Ingrese el nombre del cliente para la comanda de venta directa.')
                                    ->warning()
                                    ->duration(3000)
                                    ->send();
                                return;
                            }
                        }
                        // 1) Guardar la orden antes de imprimir
                        $this->processOrder();

                        // 2) Verificar que la orden exista tras el guardado
                        if (!$this->order) {
                            Notification::make()
                                ->title('No se pudo guardar la orden')
                                ->body('Revise requisitos: caja abierta, comensales y productos.')
                                ->danger()
                                ->send();
                            return;
                        }
                        $url = route('orders.comanda.pdf', [
                            'order' => $this->order,
                            'customerName' => $this->customerNameForComanda
                        ]);
                        $this->js("window.open('$url', 'comanda_print', 'width=900,height=700,scrollbars=yes,resizable=yes,toolbar=no,menubar=no,status=no')");

                        // 3) Notificación de éxito y abrir ventana de impresión
                        Notification::make()
                            ->title('Comanda enviada a impresión')
                            ->body('La comanda se ha abierto en una nueva ventana para imprimir')
                            ->success()
                            ->duration(3000)
                            ->send();

                        // 4) Cerrar modal; redirigir solo si NO es venta directa
                        //    Usar el ID estándar del modal de acciones de Filament para esta página
                        $this->dispatch('close-modal', id: "{$this->getId()}-action");
                        if ($this->selectedTableId) {
                            $redirectUrl = TableMap::getUrl();
                            $this->js("setTimeout(function(){ window.location.href = '{$redirectUrl}'; }, 600);");
                        }
                    })
                    // Mostrar si ya hay orden o al menos items en carrito (primer uso)
                    ->visible(fn() => (bool) $this->order || !empty($this->cartItems))
                    ->disabled(function () {
                        // Reglas de deshabilitado: sin caja abierta, sin orden ni items, o venta directa sin nombre
                        if (!$this->hasOpenCashRegister) return true;
                        if (!$this->order && empty($this->cartItems)) return true;
                        if ($this->selectedTableId === null) {
                            return empty($this->customerNameForComanda);
                        }
                        return false;
                    }),
            ])
            ->visible(fn(): bool => (bool) $this->order || !empty($this->cartItems));
    }

    /**
     * Acción para imprimir pre-cuenta
     */
    protected function printPreBillNewAction(): Action
    {
        return Action::make('printPreBillNew')
            ->label('Pre-Cuenta')
            ->icon('heroicon-o-document-text')
            ->color('warning')
            ->size('lg')
            ->modal()
            ->modalHeading('📄 Pre-Cuenta')
            ->modalDescription('Vista previa antes de facturar')
            ->modalWidth('xl')
            ->modalAlignment('center')
            ->extraModalWindowAttributes(['style' => 'max-height:80vh;overflow-y:auto;max-width:1000px;width:1000px;background:#ffffff;border:1px solid #e5e7eb;padding:0.75rem;border-radius:0.75rem;'])
            ->modalContent(function () {
                // Bloquear si no hay caja abierta
                if (!$this->hasOpenCashRegister) {
                    return view('components.empty-state', [
                        'message' => 'No hay caja abierta. Abra una caja para continuar.'
                    ]);
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

                // Recalcular totales para asegurar valores actualizados
                $order->recalculateTotals();

                return view('filament.modals.pre-bill-content', [
                    'order' => $order,
                    'subtotal' => $order->subtotal,
                    'tax' => $order->tax,
                    'total' => $order->total
                ]);
            })
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Cerrar')
            ->extraModalFooterActions([
                Action::make('printPreBill')
                    ->label('🖨️ Imprimir Pre-Cuenta')
                    ->color('primary')
                    ->size('md')
                    ->extraAttributes(['class' => 'font-semibold bg-blue-600 hover:bg-blue-700 text-white shadow-sm'])
                    ->action(function () {
                        if ($this->order && $this->order->table) {
                            $this->order->table->update(['status' => TableModel::STATUS_PREBILL]);
                            Log::info('🔵 Mesa cambiada a PRE-CUENTA', ['table_id' => $this->order->table->id,'order_id' => $this->order->id,'status' => 'prebill']);
                            Notification::make()->title('Mesa en PRE-CUENTA')->body('La mesa ahora está marcada como PRE-CUENTA')->success()->duration(3000)->send();
                        }
                        $url = route('print.prebill', ['order' => $this->order->id]);
                        $this->js("window.open('$url', 'prebill_print', 'width=800,height=600,scrollbars=yes,resizable=yes')");
                    }),
                Action::make('downloadPreBill')
                    ->label('📥 Descargar')
                    ->color('success')
                    ->size('sm')
                    ->extraAttributes(['class' => 'font-semibold bg-emerald-600 hover:bg-emerald-700 text-white shadow-sm'])
                    ->action(function () { $url = route('print.prebill', ['order' => $this->order->id]); $this->js("window.open('$url', '_blank')"); }),
            ])
            ->visible(fn (): bool => (bool) $this->order || !empty($this->cartItems))
            ->disabled(fn (): bool => !$this->order && empty($this->cartItems));
    }

    /**
     * Acción para reabrir orden para edición
     */
    protected function reopen_order_for_editingAction(): Action
    {
        return Action::make('reopen_order_for_editing')
            ->label('Reabrir Orden')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
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
            );
    }

    /**
     * Acción para volver al mapa de mesas
     */
    protected function backToTableMapAction(): Action
    {
        return Action::make('backToTableMap')
            ->label('Mapa de Mesas')
            ->icon('heroicon-o-map')
            ->color('gray')
            ->size('lg')
            ->action(function () {
                return redirect(TableMap::getUrl());
            })
            ->visible(fn(): bool => true); // Siempre visible
    }

    /**
     * Acción para liberar mesa
     */
    protected function releaseTableAction(): Action
    {
        return Action::make('releaseTable')
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
            ->visible(fn(): bool => $this->order && $this->order->table_id !== null && $this->order->status === Order::STATUS_OPEN && !Auth::user()->hasRole(['waiter', 'cashier']));
    }

    /**
     * Acción para cancelar orden
     */
    protected function cancelOrderAction(): Action
    {
        return Action::make('cancelOrder')
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
            ->visible(fn(): bool => $this->order && $this->order->table_id !== null && $this->order->status === Order::STATUS_OPEN && !Auth::user()->hasRole(['waiter', 'cashier']));
    }

    /**
     * Acción para transferir orden
     */
    protected function transferOrderAction(): Action
    {
        return Action::make('transferOrder')
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
                    'target_table_id' => null,
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
            ->visible(fn(): bool => $this->order && $this->order->table_id && $this->order->status === Order::STATUS_OPEN && !Auth::user()->hasRole(['waiter', 'cashier']));
    }

    /**
     * Acción para dividir cuenta
     */
    protected function split_itemsAction(): Action
    {
        return Action::make('split_items')
            ->label('Dividir Cuenta')
            ->icon('heroicon-o-calculator')
            ->color('warning')
            ->size('lg')
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
            ->visible(fn (): bool => $this->order !== null && count($this->order->orderDetails) > 0);
    }

    /**
     * Método para seleccionar una temperatura para una bebida fría
     */
    public function selectTemperature(int $index, string $temperature): void
    {
        if (isset($this->cartItems[$index])) {
            $this->cartItems[$index]['temperature'] = $temperature;
            $this->cartItems[$index]['temperature_selected'] = true;
        }
    }

    /**
     * Método para seleccionar un punto de cocción para una parrilla
     */
    public function selectCookingPoint(int $index, string $cookingPoint): void
    {
        if (isset($this->cartItems[$index])) {
            $this->cartItems[$index]['cooking_point'] = $cookingPoint;
            $this->cartItems[$index]['cooking_point_selected'] = true;
        }
    }

    /**
     * Método para seleccionar un tipo de presa para pollo
     */
    public function selectChickenCutType(int $index, string $chickenCutType): void
    {
        if (isset($this->cartItems[$index])) {
            $this->cartItems[$index]['chicken_cut_type'] = $chickenCutType;
            $this->cartItems[$index]['chicken_cut_type_selected'] = true;
        }
    }

    /**
     * Obtiene la siguiente serie disponible para el tipo de documento especificado
     */
    private function getNextSeries(string $documentType): string
    {
        $series = \App\Models\DocumentSeries::where('document_type', $documentType)
            ->where('active', true)
            ->first();

        if ($series) {
            return $series->series;
        }

        // Fallback a series por defecto si no se encuentra una serie activa
        return match ($documentType) {
            'sales_note' => 'NV001',
            'receipt' => 'B001',
            'invoice' => 'F001',
            default => 'B001',
        };
    }
}
