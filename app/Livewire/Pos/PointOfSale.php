<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Table;
use App\Models\Order;
use App\Models\OrderDetail as OrderDetailModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class PointOfSale extends Component
{
    public ?Table $table = null;
    public ?string $tableId = null;
    public ?string $selectedCategoryId = null;
    public Collection $categories;
    public Collection $products;
    public string $searchQuery = '';
    public array $cart = [];
    public array $cartItems = [];
    public float $cartTotal = 0;
    public ?string $customerNote = null;
    public ?Order $currentOrder = null;
    public bool $showEditPriceModal = false;
    public ?string $editingProductId = null;
    public ?float $newPrice = null;
    public bool $showCommandModal = false;
    public bool $showPreBillModal = false;
    public bool $showInvoiceModal = false;
    public bool $showTransferModal = false;
    public bool $showDeliveryModal = false;
    public ?string $commandUrl = null;
    public ?string $preBillUrl = null;
    public ?string $invoiceUrl = null;
    public Collection $availableTables;

    // Propiedades para delivery
    public string $serviceType = 'dine_in';
    public ?string $customerId = null;
    public ?string $customerName = null;
    public ?string $customerPhone = null;
    public ?string $customerDocument = null;
    public ?string $customerDocumentType = 'DNI';
    public ?string $deliveryAddress = null;
    public ?string $deliveryReferences = null;

    protected $queryString = [
        'tableId' => ['except' => ''],
        'serviceType' => ['except' => 'dine_in'],
    ];

    public function mount(?string $tableId = null, ?string $serviceType = null, ?string $orderId = null, bool $preserveCart = false): void
    {
        // Inicializar propiedades
        $this->tableId = $tableId;
        $this->products = collect(); // Inicializar como una colección vacía
        $this->availableTables = collect(); // Inicializar como una colección vacía

        // Registrar información para depuración
        Log::info('Montando componente PointOfSale', [
            'tableId' => $tableId,
            'serviceType' => $serviceType,
            'orderId' => $orderId,
            'preserveCart' => $preserveCart
        ]);

        // Configurar el tipo de servicio si se proporciona
        if ($serviceType) {
            $this->serviceType = $serviceType;

            // Si es delivery, mostrar el modal de delivery automáticamente
            if ($serviceType === 'delivery') {
                // Asegurarse de que el modal se abra después de que el componente esté completamente cargado
                $this->showDeliveryModal = true;
                Log::info('Modal de delivery activado en mount', ['serviceType' => $serviceType]);

                // Programar la apertura del modal después de la renderización
                $this->dispatch('open-delivery-modal-after-render');
            }
        }

        // Si se proporciona un ID de orden específico (Ver Detalles)
        if ($orderId) {
            // Cargar la orden específica
            $order = Order::with(['orderDetails.product', 'table', 'deliveryOrder', 'customer'])
                ->findOrFail($orderId);

            Log::info('Cargando orden específica', [
                'order_id' => $orderId,
                'service_type' => $order->service_type,
                'table_id' => $order->table_id
            ]);

            // Si la orden tiene una mesa asociada, establecerla
            if ($order->table_id) {
                $this->tableId = $order->table_id;
                $this->table = $order->table;
            }

            // Si es una orden de delivery, configurar el tipo de servicio y datos del cliente
            if ($order->service_type === 'delivery') {
                $this->serviceType = 'delivery';

                // Configurar datos del cliente si existe
                if ($order->customer) {
                    $this->customerId = $order->customer->id;
                    $this->customerName = $order->customer->name;
                    $this->customerPhone = $order->customer->phone;
                    $this->customerDocument = $order->customer->document_number;
                    $this->customerDocumentType = $order->customer->document_type;
                }

                // Configurar datos de delivery si existe
                if ($order->deliveryOrder) {
                    $this->deliveryAddress = $order->deliveryOrder->delivery_address;
                    $this->deliveryReferences = $order->deliveryOrder->delivery_references;
                }
            }

            // Guardar la orden actual
            $this->currentOrder = $order;

            // Limpiar el carrito antes de cargar los productos
            $this->cart = [];

            // Cargar los productos de la orden al carrito
            foreach ($order->orderDetails as $detail) {
                $product = $detail->product;

                if (!$product) {
                    continue;
                }

                // Agregar al carrito
                $this->cart[$product->id] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (float) $detail->unit_price,
                    'quantity' => $detail->quantity,
                    'subtotal' => (float) $detail->subtotal,
                    'notes' => $detail->notes ?? '',
                ];
            }

            // Actualizar el total del carrito
            $this->updateCartTotal();

            // Si se debe preservar el carrito, guardarlo en la sesión
            if ($preserveCart && $this->tableId) {
                $this->saveCartToSession();
            }
        }
        // Si no hay ID de orden pero hay mesa
        else if ($this->tableId) {
            $this->table = Table::find($this->tableId);

            // Intentar cargar el carrito desde la sesión
            if (!$this->loadCartFromSession()) {
                // Si no hay carrito en la sesión, cargar desde la orden existente
                $this->loadExistingOrder();
            }
        }

        $this->loadCategories();
    }

    /**
     * Método que se ejecuta cuando el componente se hidrata (cuando Livewire lo reconstruye)
     */
    public function hydrate(): void
    {
        // Si hay una mesa seleccionada, intentar cargar el carrito desde la sesión
        // independientemente de si el carrito está vacío o no
        if ($this->tableId) {
            // Intentar cargar el carrito desde la sesión
            if (!$this->loadCartFromSession()) {
                // Si no hay carrito en la sesión, cargar desde la orden existente
                $this->loadExistingOrder();
            }
        }
    }

    /**
     * Carga una orden existente para la mesa actual
     */
    protected function loadExistingOrder(): void
    {
        if (!$this->tableId) {
            return;
        }

        // Buscar orden existente para la mesa que no esté facturada
        $order = Order::where('table_id', $this->tableId)
            ->where('status', '!=', 'billed') // Que no esté facturada
            ->orderBy('created_at', 'desc')
            ->with('orderDetails.product') // Cargar detalles y productos
            ->first();

        if (!$order) {
            return;
        }

        // Guardar la orden actual
        $this->currentOrder = $order;

        // No cambiar el estado de la mesa automáticamente al cargar una orden existente
        // La mesa solo debe cambiar a ocupada cuando se haga clic en el botón "Iniciar Mesa"

        Log::info('Cargando orden existente sin cambiar estado de la mesa', [
            'table_id' => $this->tableId,
            'table_number' => $this->table ? $this->table->number : null,
            'table_status' => $this->table ? $this->table->status : null,
            'order_id' => $order->id,
            'product_count' => $order->orderDetails()->count()
        ]);

        // Limpiar el carrito antes de cargar los productos
        $this->cart = [];

        // Cargar los productos de la orden al carrito
        foreach ($order->orderDetails as $detail) {
            $product = $detail->product;

            if (!$product) {
                continue;
            }

            // Agregar al carrito
            $this->cart[$product->id] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => (float) $detail->unit_price,
                'quantity' => $detail->quantity,
                'subtotal' => (float) $detail->subtotal,
                'notes' => $detail->notes ?? '',
            ];
        }

        // Actualizar el total del carrito
        $this->updateCartTotal();

        // Registrar en el log para depuración
        Log::info('Carrito cargado desde orden existente', [
            'table_id' => $this->tableId,
            'order_id' => $order->id,
            'cart_count' => count($this->cart),
            'cart_total' => $this->cartTotal
        ]);
    }

    public function loadCategories(): void
    {
        // Cargar categorías ordenadas por display_order
        $this->categories = ProductCategory::where('visible_in_menu', true)
            ->orderBy('display_order')
            ->get();

        // Si hay categorías, seleccionar la primera por defecto
        if ($this->categories->isNotEmpty() && !$this->selectedCategoryId) {
            $this->selectedCategoryId = $this->categories->first()->id;
            $this->loadProductsByCategory($this->selectedCategoryId);
        }
    }

    public function loadProductsByCategory(string $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;

        $query = Product::where('category_id', $categoryId)
            ->where('active', true)
            ->where('available', true)
            ->where('product_type', '!=', 'ingredient');

        // Aplicar filtro de búsqueda si existe
        if (!empty($this->searchQuery)) {
            $query->where('name', 'like', "%{$this->searchQuery}%");
        }

        $this->products = $query->orderBy('name')->get();

        // Asegurar que $products nunca sea null
        if ($this->products === null) {
            $this->products = collect();
        }
    }

    public function searchProducts(): void
    {
        if ($this->selectedCategoryId) {
            $this->loadProductsByCategory($this->selectedCategoryId);
        }
    }

    public function resetProductSearch(): void
    {
        $this->searchQuery = '';
        if ($this->selectedCategoryId) {
            $this->loadProductsByCategory($this->selectedCategoryId);
        }
    }

    public function addToCart($productId): void
    {
        $product = Product::find($productId);

        if (!$product) {
            return;
        }

        // Si ya existe en el carrito, aumentar cantidad
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['quantity']++;
            $this->cart[$productId]['subtotal'] = $this->cart[$productId]['quantity'] * $this->cart[$productId]['price'];
        } else {
            // Si no existe, agregarlo al carrito
            $this->cart[$productId] = [
                'id' => $productId,
                'name' => $product->name,
                'price' => (float) $product->sale_price,
                'quantity' => 1,
                'subtotal' => (float) $product->sale_price,
                'notes' => '',
            ];
        }

        // No cambiar el estado de la mesa automáticamente al agregar productos
        // Solo actualizar la mesa y crear una orden si es necesario
        if ($this->tableId) {
            // Obtener la mesa directamente de la base de datos para asegurar datos actualizados
            $table = Table::find($this->tableId);

            if ($table) {
                // Actualizar la propiedad table con los datos más recientes
                $this->table = $table;

                // Si la mesa ya está ocupada, verificar si necesitamos crear una orden
                if ($table->status === Table::STATUS_OCCUPIED && !$this->currentOrder) {
                    // Crear una orden sin cambiar el estado de la mesa
                    $order = $table->occupy(Auth::id());
                    $this->currentOrder = $order;

                    Log::info('Orden creada para mesa ocupada desde addToCart', [
                        'table_id' => $this->tableId,
                        'table_number' => $table->number,
                        'order_id' => $order->id
                    ]);
                }
            }
        }

        $this->updateCartTotal();

        // Guardar el carrito en la sesión
        $this->saveCartToSession();
    }

    public function removeFromCart(string $productId): void
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            $this->updateCartTotal();

            // No cambiar el estado de la mesa automáticamente al eliminar productos
            // La mesa solo debe cambiar a disponible cuando se genere un comprobante
            if (empty($this->cart) && $this->tableId) {
                $table = Table::find($this->tableId);

                if ($table) {
                    // Actualizar la propiedad table con los datos más recientes
                    $this->table = $table;

                    Log::info('Carrito vacío pero la mesa permanece ocupada', [
                        'table_id' => $this->tableId,
                        'table_number' => $table->number,
                        'table_status' => $table->status
                    ]);
                }
            }

            // Guardar el carrito en la sesión
            $this->saveCartToSession();
        }
    }

    public function updateCartItemQuantity(string $productId, int $quantity): void
    {
        if (isset($this->cart[$productId])) {
            // Si la cantidad es 0 o negativa, eliminar el producto del carrito
            if ($quantity <= 0) {
                $this->removeFromCart($productId);
                return;
            }

            $this->cart[$productId]['quantity'] = $quantity;
            $this->cart[$productId]['subtotal'] = $quantity * $this->cart[$productId]['price'];

            $this->updateCartTotal();

            // Guardar el carrito en la sesión
            $this->saveCartToSession();
        }
    }

    public function openEditPriceModal($productId): void
    {
        // Solo permitir edición de precios a administradores
        if (!Gate::allows('admin')) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Acceso denegado',
                'message' => 'No tienes permisos para editar precios'
            ]);
            return;
        }

        $this->editingProductId = $productId;
        $this->newPrice = $this->cart[$productId]['price'];
        $this->showEditPriceModal = true;
    }

    public function saveNewPrice(): void
    {
        // Validar permiso nuevamente
        if (!Gate::allows('admin')) {
            $this->showEditPriceModal = false;
            return;
        }

        // Validar precio
        if ($this->newPrice <= 0) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'El precio debe ser mayor que cero'
            ]);
            return;
        }

        // Actualizar precio
        $productId = $this->editingProductId;
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['price'] = $this->newPrice;
            $this->cart[$productId]['subtotal'] = $this->cart[$productId]['quantity'] * $this->newPrice;
            $this->updateCartTotal();

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Precio actualizado',
                'message' => 'El precio ha sido actualizado correctamente'
            ]);

            // Guardar el carrito en la sesión
            $this->saveCartToSession();
        }

        // Cerrar modal
        $this->showEditPriceModal = false;
        $this->editingProductId = null;
        $this->newPrice = null;
    }

    public function closeEditPriceModal(): void
    {
        $this->showEditPriceModal = false;
        $this->editingProductId = null;
        $this->newPrice = null;
    }

    public function updateCartItemNote(string $productId, string $note): void
    {
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['notes'] = $note;

            // Guardar el carrito en la sesión
            $this->saveCartToSession();
        }
    }

    public function updateCartTotal(): void
    {
        $this->cartTotal = collect($this->cart)->sum('subtotal');

        // Si hay items en el carrito, crear o actualizar la orden
        if (!empty($this->cart)) {
            try {
                $this->currentOrder = $this->createOrder();

                // Guardar el carrito en la sesión después de actualizar la orden
                $this->saveCartToSession();
            } catch (\Exception $e) {
                Log::error('Error creating order: ' . $e->getMessage());
            }
        }
    }

    /**
     * Limpia el carrito sin cambiar el estado de la mesa
     * La mesa solo debe cambiar a disponible cuando se genere un comprobante
     */
    public function clearCart(): void
    {
        // Limpiar el carrito
        $this->cart = [];
        $this->cartTotal = 0;
        $this->customerNote = null;

        // Limpiar la sesión
        if ($this->tableId) {
            session()->forget('cart_' . $this->tableId);
        }

        // Registrar en el log
        \Illuminate\Support\Facades\Log::info('Carrito limpiado, mesa permanece en su estado actual', [
            'table_id' => $this->tableId,
            'table_status' => $this->table ? $this->table->status : 'null'
        ]);
    }

    public function createOrder(): ?Order
    {
        Log::info('Attempting to create or update order', [
            'cart' => $this->cart,
            'table_id' => $this->tableId,
            'service_type' => $this->serviceType,
            'current_order_id' => $this->currentOrder ? $this->currentOrder->id : null
        ]);

        if (empty($this->cart)) {
            Log::warning('createOrder called with empty cart.');
            return null;
        }

        // Verificar que hay productos en el carrito
        if ($this->cartTotal <= 0) {
            Log::warning('createOrder called with zero total cart.');
        }

        // Si ya tenemos una orden actual cargada, usarla directamente
        if ($this->currentOrder) {
            Log::info('Using existing loaded order', [
                'order_id' => $this->currentOrder->id,
                'service_type' => $this->currentOrder->service_type
            ]);

            $order = Order::find($this->currentOrder->id);

            // Verificar que la orden existe y no está completada o cancelada
            if ($order && !in_array($order->status, [Order::STATUS_COMPLETED, Order::STATUS_CANCELLED])) {
                Log::info('Found existing order to update', ['order_id' => $order->id]);
                // Continuamos con esta orden existente
            } else {
                // Si la orden no existe o está completada/cancelada, buscar otra o crear una nueva
                $this->currentOrder = null;
                $order = null;
            }
        } else {
            $order = null;
        }

        // Si no tenemos una orden válida, buscar una existente
        if (!$order) {
            // Si es consumo en local, buscar una orden activa para la mesa
            if ($this->serviceType === 'dine_in' && $this->tableId) {
                $table = Table::find($this->tableId);
                if ($table && $table->hasActiveOrder()) {
                    $order = $table->activeOrder()->first();
                    Log::info('Found active order for table', ['order_id' => $order->id, 'table_id' => $this->tableId]);
                }
            } else {
                // Buscar orden existente según el tipo de servicio
                $query = Order::where('status', '!=', Order::STATUS_COMPLETED)
                            ->where('status', '!=', Order::STATUS_CANCELLED)
                            ->where('service_type', $this->serviceType)
                            ->orderBy('created_at', 'desc');

                // Si es delivery, filtrar por cliente
                if ($this->serviceType === 'delivery' && $this->customerId) {
                    $query->where('customer_id', $this->customerId);
                }

                $order = $query->first();
            }
        }

        if ($order) {
            // Si existe, actualizarla usando los nuevos métodos
            Log::info('Updating existing order', ['order_id' => $order->id]);

            // Limpiar los productos existentes y añadir los nuevos
            $order->orderDetails()->delete();

            // Añadir los productos del carrito
            foreach ($this->cart as $productId => $item) {
                $order->addProduct(
                    $productId,
                    $item['quantity'],
                    $item['price'],
                    $item['notes'] ?? null
                );
            }

            // Actualizar notas
            $order->notes = $this->customerNote;
            $order->save();

            // Actualizar cliente si es delivery
            if ($this->serviceType === 'delivery' && $this->customerId) {
                $order->customer_id = $this->customerId;
                $order->save();

                // Actualizar o crear el registro de delivery
                $deliveryOrder = \App\Models\DeliveryOrder::where('order_id', $order->id)->first();

                if ($deliveryOrder) {
                    // Actualizar el registro existente
                    $deliveryOrder->delivery_address = $this->deliveryAddress;
                    $deliveryOrder->delivery_references = $this->deliveryReferences;
                    $deliveryOrder->save();

                    Log::info('Updated delivery order', ['delivery_id' => $deliveryOrder->id]);
                } else {
                    // Crear un nuevo registro de delivery
                    $deliveryOrder = new \App\Models\DeliveryOrder();
                    $deliveryOrder->order_id = $order->id;
                    $deliveryOrder->delivery_address = $this->deliveryAddress;
                    $deliveryOrder->delivery_references = $this->deliveryReferences;
                    $deliveryOrder->status = 'pending';
                    $deliveryOrder->save();

                    Log::info('Created delivery order for existing order', ['delivery_id' => $deliveryOrder->id]);

                    // Disparar evento de cambio de estado
                    event(new \App\Events\DeliveryStatusChanged($deliveryOrder));
                }
            }

        } else {
            // Si no existe, crear una nueva usando los métodos de la mesa
            Log::info('Creating new order');

            if ($this->serviceType === 'dine_in' && $this->tableId) {
                // Si es consumo en local, usar el método occupy de la mesa
                $table = Table::find($this->tableId);
                if (!$table) {
                    Log::error('Table not found', ['table_id' => $this->tableId]);
                    return null;
                }

                // Ocupar la mesa y crear una nueva orden
                $order = $table->occupy(Auth::id());
                Log::info('Table occupied and new order created', ['order_id' => $order->id, 'table_id' => $this->tableId]);

            } else {
                // Para otros tipos de servicio, crear la orden manualmente
                $order = new Order();
                $order->service_type = $this->serviceType;

                if ($this->serviceType === 'delivery' && $this->customerId) {
                    $order->customer_id = $this->customerId;
                }

                $order->employee_id = Auth::id();
                $order->order_datetime = now();
                $order->status = Order::STATUS_OPEN;
                $order->subtotal = 0;
                $order->tax = 0;
                $order->total = 0;
                $order->notes = $this->customerNote;
                $order->save();
                Log::info('New order created', ['order_id' => $order->id]);
            }

            // Añadir los productos del carrito
            foreach ($this->cart as $productId => $item) {
                $order->addProduct(
                    $productId,
                    $item['quantity'],
                    $item['price'],
                    $item['notes'] ?? null
                );
            }

            // Si es delivery, crear el registro de delivery
            if ($this->serviceType === 'delivery') {
                try {
                    // Registrar información detallada antes de crear el pedido de delivery
                    Log::info('Intentando crear pedido de delivery', [
                        'order_id' => $order->id,
                        'delivery_address' => $this->deliveryAddress,
                        'delivery_references' => $this->deliveryReferences,
                        'customer_id' => $this->customerId,
                        'service_type' => $this->serviceType
                    ]);

                    $deliveryOrder = new \App\Models\DeliveryOrder();
                    $deliveryOrder->order_id = $order->id;
                    $deliveryOrder->delivery_address = $this->deliveryAddress;
                    $deliveryOrder->delivery_references = $this->deliveryReferences;
                    $deliveryOrder->status = 'pending';
                    $deliveryOrder->save();

                    Log::info('Created delivery order', ['delivery_id' => $deliveryOrder->id]);

                    // Disparar evento de cambio de estado
                    event(new \App\Events\DeliveryStatusChanged($deliveryOrder));

                } catch (\Exception $e) {
                    // Registrar error detallado
                    Log::error('Error al crear pedido de delivery', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                        'order_id' => $order->id,
                        'line' => $e->getLine(),
                        'file' => $e->getFile()
                    ]);
                }
            }
        }

        $this->currentOrder = $order;
        Log::info('createOrder completed successfully', ['order_id' => $order->id]);
        return $order;
    }

    public function generateCommand()
    {
        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'El carrito está vacío. Añade productos para generar una comanda.'
            ]);
            return;
        }

        try {
            $order = $this->createOrder();
            if (!$order) {
                Log::warning('generateCommand: createOrder returned null or false.');
                return;
            }

            // Notificar éxito
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Comanda Generada',
                'message' => 'La comanda se ha generado correctamente'
            ]);

            // Abrir ventana directamente con JavaScript
            $url = route('pos.command.pdf', ['order' => $order->id]);
            $this->js("window.open('$url', '_blank', 'width=800,height=600')");

        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al generar la comanda: ' . $e->getMessage()
            ]);
        }
    }

    public function generatePreBill()
    {
        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'El carrito está vacío. Añade productos para generar una pre-cuenta.'
            ]);
            return;
        }

        try {
            $order = $this->createOrder();
            if (!$order) {
                Log::warning('generatePreBill: createOrder returned null or false.');
                return;
            }

            // Notificar éxito
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Pre-Cuenta Generada',
                'message' => 'La pre-cuenta se ha generado correctamente'
            ]);

            // Abrir ventana directamente con JavaScript
            $url = route('pos.prebill.pdf', ['order' => $order->id]);
            $this->js("window.open('$url', '_blank', 'width=800,height=600')");

        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al generar la pre-cuenta: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar factura y mostrarla en ventana flotante
     */
    public function generateInvoice()
    {
        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'El carrito está vacío. Añade productos para generar una factura.'
            ]);
            return;
        }

        try {
            // Verificar si hay una orden existente
            if (!$this->currentOrder) {
                $order = $this->createOrder();
                if (!$order) {
                    Log::warning('generateInvoice: createOrder returned null or false.');
                    return;
                }
            } else {
                $order = $this->currentOrder;
            }

            // Ya no necesitamos verificar si la orden está pagada
            // porque el flujo unificado maneja tanto el pago como la facturación

            // Si la orden ya está facturada, mostrar la factura existente
            if ($order->billed) {
                $invoice = $order->invoices()->latest()->first();
                if ($invoice) {
                    $this->dispatch('notification', [
                        'type' => 'info',
                        'title' => 'Factura Existente',
                        'message' => 'Esta orden ya tiene una factura generada.'
                    ]);

                    // Abrir la factura existente
                    $url = route('invoices.print', ['invoice' => $invoice->id]);
                    $this->js("window.open('$url', '_blank', 'width=800,height=600')");
                    return;
                }
            }

            // Notificar éxito
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Factura Preparada',
                'message' => 'El formulario de facturación se abrirá en una nueva ventana'
            ]);

            // Abrir ventana directamente con JavaScript usando el nuevo flujo unificado
            $url = route('pos.unified.form', ['order' => $order->id]);
            $this->js("window.open('$url', '_blank', 'width=1000,height=700')");

        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al generar la factura: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar comprobante de venta y mostrarlo en ventana flotante
     */
    public function confirmSale()
    {
        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'El carrito está vacío. Añade productos para completar la venta.'
            ]);
            return;
        }

        try {
            if (!$this->currentOrder) {
                $order = $this->createOrder();
                if (!$order) {
                    Log::warning('confirmSale: createOrder returned null or false.');
                    return;
                }
            } else {
                $order = $this->currentOrder;
            }

            // Notificar éxito
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Venta Preparada',
                'message' => 'Seleccione el tipo de comprobante a generar'
            ]);

            // Abrir ventana directamente con JavaScript usando el nuevo flujo unificado
            $url = route('pos.unified.form', ['order' => $order->id]);
            $this->js("window.open('$url', '_blank', 'width=1000,height=700')");

        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al preparar la venta: ' . $e->getMessage()
            ]);
        }
    }

    public function submitOrder(): void
    {
        // Esta función se implementará en el futuro para guardar el pedido
        // Por ahora simplemente limpiamos el carrito como demostración
        $this->clearCart();
    }

    public function render()
    {
        // Si hay una mesa seleccionada, intentar cargar el carrito desde la sesión
        // independientemente de si el carrito está vacío o no
        if ($this->tableId) {
            // Intentar cargar el carrito desde la sesión
            if (!$this->loadCartFromSession()) {
                // Si no hay carrito en la sesión, cargar desde la orden existente
                $this->loadExistingOrder();
            }
        }

        return view('livewire.pos.point-of-sale');
    }

    /**
     * Método para limpiar todo después de completar la venta (llamado desde JavaScript)
     * Este método cambia el estado de la mesa a disponible cuando se completa una venta
     */
    #[\Livewire\Attributes\On('clearSale')]
    public function clearSale(): void
    {
        // Limpiar el carrito
        $this->clearCart();

        // Si hay una mesa seleccionada, cambiarla a disponible
        if ($this->table) {
            $this->table->refresh(); // Refrescar para obtener los últimos datos

            // Cambiar el estado de la mesa a disponible
            $this->table->status = Table::STATUS_AVAILABLE;
            $this->table->occupied_at = null;
            $this->table->save();

            // Registrar en el log
            \Illuminate\Support\Facades\Log::info('Mesa marcada como disponible al completar la venta', [
                'table_id' => $this->tableId,
                'table_number' => $this->table->number
            ]);
        }

        // Mostrar mensaje de éxito
        $this->dispatch('notification', [
            'type' => 'success',
            'title' => 'Venta completada',
            'message' => 'La venta se ha completado correctamente y la mesa está disponible'
        ]);
    }

    /**
     * Método para cancelar el pedido actual
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function cancelOrder()
    {
        // Verificar si hay productos en el carrito
        if (empty($this->cart) && !$this->currentOrder) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No hay productos en el carrito para cancelar.'
            ]);
            return;
        }

        // La confirmación se maneja en la vista

        // Si hay una orden existente, marcarla como cancelada
        if ($this->currentOrder) {
            try {
                // Registrar información de depuración antes de cancelar
                \Illuminate\Support\Facades\Log::info('Intentando cancelar orden', [
                    'order_id' => $this->currentOrder->id,
                    'table_id' => $this->tableId,
                    'service_type' => $this->currentOrder->service_type,
                    'has_table_relation' => $this->currentOrder->table_id ? true : false
                ]);

                // Usar el método cancelOrder del modelo Order
                $result = $this->currentOrder->cancelOrder('Cancelado desde POS');

                if ($result) {
                    // Registrar en el log
                    \Illuminate\Support\Facades\Log::info('Orden cancelada exitosamente', [
                        'order_id' => $this->currentOrder->id,
                        'table_id' => $this->tableId
                    ]);

                    // Notificar éxito
                    $this->dispatch('notification', [
                        'type' => 'success',
                        'title' => 'Orden Cancelada',
                        'message' => 'La orden ha sido cancelada correctamente'
                    ]);

                    // Limpiar el carrito
                    $this->cart = [];
                    $this->cartTotal = 0;
                    $this->customerNote = null;

                    // Limpiar la sesión
                    if ($this->tableId) {
                        session()->forget('cart_' . $this->tableId);
                    }

                    // Recargar la mesa para reflejar el cambio de estado
                    if ($this->tableId) {
                        $this->table = \App\Models\Table::find($this->tableId);

                        // Verificar si la mesa ha sido liberada correctamente
                        if ($this->table && $this->table->status === \App\Models\Table::STATUS_AVAILABLE) {
                            \Illuminate\Support\Facades\Log::info('Mesa liberada correctamente al cancelar orden', [
                                'table_id' => $this->tableId,
                                'table_status' => $this->table->status
                            ]);
                        } else {
                            \Illuminate\Support\Facades\Log::warning('La mesa no cambió a estado disponible después de cancelar', [
                                'table_id' => $this->tableId,
                                'table_status' => $this->table ? $this->table->status : 'null'
                            ]);
                        }
                    }

                    // Redirigir al mapa de mesas si estamos en una mesa
                    if ($this->tableId) {
                        return redirect()->route('tables.map');
                    }
                } else {
                    // Notificar error
                    $this->dispatch('notification', [
                        'type' => 'error',
                        'title' => 'Error',
                        'message' => 'No se pudo cancelar la orden. Posiblemente ya está facturada.'
                    ]);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al cancelar la orden', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'order_id' => $this->currentOrder->id ?? null
                ]);

                // Notificar error
                $this->dispatch('notification', [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'Error al cancelar la orden: ' . $e->getMessage()
                ]);
            }
        } else {
            // Si no hay una orden existente, simplemente limpiar el carrito
            $this->cart = [];
            $this->cartTotal = 0;
            $this->customerNote = null;

            // Limpiar la sesión
            if ($this->tableId) {
                session()->forget('cart_' . $this->tableId);
            }

            // Mostrar mensaje de éxito
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Pedido Cancelado',
                'message' => 'El pedido ha sido cancelado correctamente.'
            ]);
        }
    }

    /**
     * Método para liberar una mesa en casos excepcionales (consumo en tienda)
     * Este método cambia el estado de la mesa de "ocupada" a "disponible"
     */
    public function releaseTable(): void
    {
        if (!$this->tableId || !$this->table) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No hay una mesa seleccionada para liberar.'
            ]);
            return;
        }

        // Verificar si la mesa está ocupada
        if ($this->table->status !== Table::STATUS_OCCUPIED) {
            $this->dispatch('notification', [
                'type' => 'info',
                'title' => 'Información',
                'message' => 'La mesa ya está disponible.'
            ]);
            return;
        }

        // Verificar si es consumo en tienda
        if ($this->currentOrder && $this->currentOrder->service_type !== 'dine_in') {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Solo se pueden liberar mesas para consumo en tienda.'
            ]);
            return;
        }

        try {
            // Cancelar la orden si existe
            if ($this->currentOrder) {
                $this->currentOrder->cancelOrder('Mesa liberada manualmente - Cliente se retiró');

                // Limpiar el carrito
                $this->cart = [];
                $this->cartTotal = 0;
                $this->customerNote = null;

                // Limpiar la sesión
                session()->forget('cart_' . $this->tableId);
            }

            // Cambiar el estado de la mesa a disponible
            $this->table->status = Table::STATUS_AVAILABLE;
            $this->table->occupied_at = null;
            $this->table->save();

            // Registrar en el log
            \Illuminate\Support\Facades\Log::info('Mesa liberada manualmente', [
                'table_id' => $this->tableId,
                'table_number' => $this->table->number,
                'order_id' => $this->currentOrder ? $this->currentOrder->id : null
            ]);

            // Notificar al usuario
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Mesa Liberada',
                'message' => "La mesa {$this->table->number} ha sido liberada y está disponible"
            ]);

            // Redirigir al mapa de mesas
            $this->redirect(route('tables.map'));

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al liberar la mesa', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'table_id' => $this->tableId
            ]);

            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al liberar la mesa: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Métodos para controlar modales
     */

    public function openTransferModal(): void
    {
        // Registrar en el log para depuración
        \Illuminate\Support\Facades\Log::info('Abriendo modal de transferencia', [
            'table_id' => $this->table ? $this->table->id : null,
            'cart_count' => count($this->cart),
            'tableId' => $this->tableId
        ]);

        // Verificar si hay una mesa seleccionada y productos en el carrito
        if (!$this->table && !$this->tableId) {
            \Illuminate\Support\Facades\Log::warning('No hay mesa seleccionada para transferir');
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No hay una mesa seleccionada para transferir.'
            ]);
            return;
        }

        // Si tenemos tableId pero no table, intentar cargar la mesa
        if (!$this->table && $this->tableId) {
            $this->table = Table::find($this->tableId);
            \Illuminate\Support\Facades\Log::info('Mesa cargada desde tableId', [
                'tableId' => $this->tableId,
                'table_loaded' => $this->table ? true : false
            ]);
        }

        if (empty($this->cart)) {
            \Illuminate\Support\Facades\Log::warning('Carrito vacío, no se puede transferir', [
                'table_id' => $this->table ? $this->table->id : null
            ]);
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No hay productos en el carrito para transferir.'
            ]);
            return;
        }

        // Cargar las mesas disponibles
        $this->loadAvailableTables();

        // Mostrar el modal
        $this->showTransferModal = true;
        \Illuminate\Support\Facades\Log::info('Modal de transferencia abierto', [
            'showTransferModal' => $this->showTransferModal,
            'availableTables_count' => $this->availableTables->count()
        ]);
    }

    public function loadAvailableTables(): void
    {
        // Verificar si hay una mesa seleccionada
        if (!$this->table) {
            \Illuminate\Support\Facades\Log::warning('Intentando cargar mesas disponibles sin una mesa seleccionada');
            $this->availableTables = collect(); // Inicializar como colección vacía
            return;
        }

        // Obtener todas las mesas disponibles excepto la mesa actual
        $this->availableTables = Table::where('status', 'available')
            ->where('id', '!=', $this->table->id)
            ->orderBy('number')
            ->get();

        // Registrar en el log para depuración
        \Illuminate\Support\Facades\Log::info('Mesas disponibles cargadas', [
            'count' => $this->availableTables->count(),
            'table_ids' => $this->availableTables->pluck('id'),
            'current_table_id' => $this->table->id
        ]);
    }

    public function transferTable(int $destinationTableId): void
    {
        // Registrar en el log para depuración
        \Illuminate\Support\Facades\Log::info('Iniciando transferencia de mesa', [
            'destination_table_id' => $destinationTableId,
            'current_table_id' => $this->table ? $this->table->id : null,
            'cart_count' => count($this->cart)
        ]);

        // Verificar si hay una mesa seleccionada y productos en el carrito
        if (!$this->table || empty($this->cart)) {
            \Illuminate\Support\Facades\Log::warning('No se puede realizar la transferencia', [
                'table' => $this->table ? $this->table->id : null,
                'cart_empty' => empty($this->cart)
            ]);

            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No se puede realizar la transferencia.'
            ]);
            return;
        }

        // Obtener la mesa destino
        $destinationTable = Table::find($destinationTableId);

        if (!$destinationTable) {
            \Illuminate\Support\Facades\Log::warning('Mesa destino no existe', [
                'destination_table_id' => $destinationTableId
            ]);

            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'La mesa destino no existe.'
            ]);
            return;
        }

        if ($destinationTable->status !== 'available') {
            \Illuminate\Support\Facades\Log::warning('Mesa destino no disponible', [
                'destination_table_id' => $destinationTableId,
                'status' => $destinationTable->status
            ]);

            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'La mesa destino no está disponible.'
            ]);
            return;
        }

        // Guardar la información de la mesa origen
        $sourceTable = $this->table;
        $sourceTableNumber = $sourceTable->number;

        try {
            \Illuminate\Support\Facades\Log::info('Realizando transferencia', [
                'source_table' => $sourceTable->id,
                'destination_table' => $destinationTable->id,
                'current_order' => $this->currentOrder ? $this->currentOrder->id : null
            ]);

            // Cambiar el estado de la mesa destino a ocupada solo si hay productos
            if (!empty($this->cart)) {
                $destinationTable->status = 'occupied';
                $destinationTable->occupied_at = now();
                $destinationTable->save();

                \Illuminate\Support\Facades\Log::info('Mesa destino marcada como ocupada con productos', [
                    'destination_table_id' => $destinationTable->id,
                    'destination_table_number' => $destinationTable->number,
                    'product_count' => count($this->cart)
                ]);
            } else {
                \Illuminate\Support\Facades\Log::info('No se cambió el estado de la mesa destino porque no hay productos', [
                    'destination_table_id' => $destinationTable->id,
                    'destination_table_number' => $destinationTable->number
                ]);
            }

            // No cambiamos el estado de la mesa origen a disponible
            // Solo se debe cambiar a disponible cuando se emite un comprobante

            // Si hay una orden asociada a la mesa origen, actualizarla
            if ($this->currentOrder) {
                $this->currentOrder->table_id = $destinationTable->id;
                $this->currentOrder->save();

                \Illuminate\Support\Facades\Log::info('Orden actualizada', [
                    'order_id' => $this->currentOrder->id,
                    'new_table_id' => $destinationTable->id
                ]);
            }

            // Actualizar la mesa actual en el componente
            $this->table = $destinationTable;
            $this->tableId = $destinationTable->id;

            // Cerrar el modal
            $this->showTransferModal = false;

            // Notificar al usuario
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Transferencia Exitosa',
                'message' => "Los productos se han transferido de la mesa {$sourceTableNumber} a la mesa {$destinationTable->number}."
            ]);

            \Illuminate\Support\Facades\Log::info('Transferencia completada, redirigiendo', [
                'redirect_url' => route('pos.table', ['table' => $destinationTable->id])
            ]);

            // Redirigir a la nueva mesa
            $this->redirect(route('pos.table', ['table' => $destinationTable->id]));

        } catch (\Exception $e) {
            // En caso de error, notificar al usuario
            \Illuminate\Support\Facades\Log::error('Error al transferir mesa', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al transferir la mesa: ' . $e->getMessage()
            ]);
        }
    }

    public function closeModals(): void
    {
        $this->showCommandModal = false;
        $this->showPreBillModal = false;
        $this->showInvoiceModal = false;
        $this->showTransferModal = false;
        $this->showDeliveryModal = false;
    }

    public function openDeliveryModal(): void
    {
        // Registrar información de depuración
        \Illuminate\Support\Facades\Log::info('Abriendo modal de delivery', [
            'service_type' => $this->serviceType,
            'cart' => $this->cart,
            'cart_count' => count($this->cart),
            'delivery_address' => $this->deliveryAddress,
            'customer_id' => $this->customerId
        ]);

        // Inicializar campos si están vacíos para evitar errores de validación
        if (empty($this->deliveryAddress)) {
            $this->deliveryAddress = '';
        }

        if (empty($this->deliveryReferences)) {
            $this->deliveryReferences = '';
        }

        // Asegurarse de que el tipo de servicio sea delivery
        $this->serviceType = 'delivery';

        // Mostrar el modal
        $this->showDeliveryModal = true;

        // Registrar en log
        $logPath = storage_path('logs/delivery_process.log');
        $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Modal de delivery abierto e inicializado';

        // Asegurarse de que el directorio existe
        if (!file_exists(dirname($logPath))) {
            mkdir(dirname($logPath), 0755, true);
        }

        file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

        // Si el carrito está vacío, mostrar un mensaje informativo
        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'info',
                'title' => 'Información',
                'message' => 'Añade productos al carrito antes de procesar el pedido de delivery.'
            ]);
        }
    }

    public function closeDeliveryModal(): void
    {
        // Siempre permitir cerrar el modal, incluso en modo delivery
        $this->showDeliveryModal = false;

        // Registrar en log
        Log::info('Modal de delivery cerrado', [
            'service_type' => $this->serviceType
        ]);

        // Registrar en archivo de log específico
        $logPath = storage_path('logs/delivery_process.log');
        $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Método closeDeliveryModal ejecutado - Modal cerrado';

        // Asegurarse de que el directorio existe
        if (!file_exists(dirname($logPath))) {
            mkdir(dirname($logPath), 0755, true);
        }

        file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

        // Forzar actualización de la UI
        $this->dispatch('delivery-modal-closed');
    }

    public function setServiceType(string $type): void
    {
        $this->serviceType = $type;

        // Si cambia a delivery, mostrar el formulario de delivery
        if ($type === 'delivery') {
            $this->openDeliveryModal();
        }
    }

    public function searchCustomer(): void
    {
        if (empty($this->customerDocument)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Ingrese un número de documento para buscar el cliente'
            ]);
            return;
        }

        $customer = \App\Models\Customer::where('document_number', $this->customerDocument)
            ->where('document_type', $this->customerDocumentType)
            ->first();

        if ($customer) {
            $this->customerId = $customer->id;
            $this->customerName = $customer->name;
            $this->customerPhone = $customer->phone;
            $this->deliveryAddress = $customer->address;
            $this->deliveryReferences = $customer->address_references;

            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Cliente encontrado',
                'message' => 'Se ha cargado la información del cliente'
            ]);
        } else {
            $this->dispatch('notification', [
                'type' => 'warning',
                'title' => 'Cliente no encontrado',
                'message' => 'El cliente no existe. Complete los datos para registrarlo.'
            ]);

            // Limpiar campos excepto documento
            $this->customerId = null;
            $this->customerName = null;
            $this->customerPhone = null;
            $this->deliveryAddress = null;
            $this->deliveryReferences = null;
        }
    }

    public function saveCustomer(): void
    {
        // Validar campos requeridos
        if (empty($this->customerName) || empty($this->customerDocument) || empty($this->deliveryAddress)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Nombre, documento y dirección son campos obligatorios'
            ]);
            return;
        }

        try {
            // Si ya existe un cliente con ese documento, actualizarlo
            $customer = \App\Models\Customer::where('document_number', $this->customerDocument)
                ->where('document_type', $this->customerDocumentType)
                ->first();

            if (!$customer) {
                // Crear nuevo cliente
                $customer = new \App\Models\Customer();
                $customer->document_type = $this->customerDocumentType;
                $customer->document_number = $this->customerDocument;
            }

            $customer->name = $this->customerName;
            $customer->phone = $this->customerPhone;
            $customer->address = $this->deliveryAddress;
            $customer->address_references = $this->deliveryReferences;
            $customer->save();

            $this->customerId = $customer->id;

            // Notificación más visible y detallada
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => '¡Cliente Guardado!',
                'message' => 'Cliente: ' . $this->customerName . ' (' . $this->customerDocumentType . ': ' . $this->customerDocument . ') guardado correctamente.',
                'timeout' => 5000, // Mostrar por 5 segundos
                'showModal' => true // Mostrar también como modal
            ]);

        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al guardar el cliente: ' . $e->getMessage()
            ]);
        }
    }

    public function processDeliveryOrder(): void
    {
        // Registrar información de depuración
        \Illuminate\Support\Facades\Log::info('Procesando pedido de delivery', [
            'cart' => $this->cart,
            'cart_count' => count($this->cart),
            'cart_total' => $this->cartTotal,
            'customer_id' => $this->customerId,
            'delivery_address' => $this->deliveryAddress,
            'service_type' => $this->serviceType
        ]);

        // Guardar en archivo de log específico usando ruta absoluta
        $logPath = storage_path('logs/delivery_process.log');
        $logContent = '[' . now()->format('Y-m-d H:i:s') . '] INICIO procesamiento de pedido delivery: ' .
            json_encode([
                'cart_count' => count($this->cart),
                'cart_total' => $this->cartTotal,
                'customer_id' => $this->customerId,
                'delivery_address' => $this->deliveryAddress
            ], JSON_PRETTY_PRINT);

        // Asegurarse de que el directorio existe
        if (!file_exists(dirname($logPath))) {
            mkdir(dirname($logPath), 0755, true);
        }

        // Escribir en el archivo
        file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

        // Validar campos requeridos
        if (empty($this->deliveryAddress)) {
            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ERROR: Dirección de entrega vacía';
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'La dirección de entrega es obligatoria'
            ]);
            return;
        }

        if (empty($this->customerId)) {
            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Cliente no seleccionado, intentando guardar cliente';
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            // Intentar guardar el cliente primero
            $this->saveCustomer();

            if (empty($this->customerId)) {
                $logPath = storage_path('logs/delivery_process.log');
                $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ERROR: No se pudo guardar el cliente';
                file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);
                return; // Si no se pudo guardar el cliente, salir
            }

            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Cliente guardado con ID: ' . $this->customerId;
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);
        }

        // Verificar si hay productos en el carrito
        if (empty($this->cart)) {
            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ERROR: Carrito vacío';
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'No hay productos en el carrito. Añade productos para procesar el pedido.'
            ]);
            return;
        }

        try {
            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Intentando crear orden';
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            // Crear o actualizar la orden
            $order = $this->createOrder();

            if (!$order) {
                $logPath = storage_path('logs/delivery_process.log');
                $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ERROR: createOrder devolvió null o false';
                file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

                $this->dispatch('notification', [
                    'type' => 'error',
                    'title' => 'Error',
                    'message' => 'Error al crear la orden de delivery'
                ]);
                return;
            }

            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Orden creada con ID: ' . $order->id;
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            // Registrar en log antes de cerrar el modal
            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Preparando para cerrar modal y mostrar notificación';
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            // Notificar éxito con mensaje más visible - ANTES de cerrar el modal
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => '¡Pedido de Delivery Registrado!',
                'message' => 'El pedido de delivery #' . $order->id . ' ha sido registrado correctamente y está listo para ser procesado. Dirección: ' . $this->deliveryAddress,
                'timeout' => 8000, // Mostrar por más tiempo (8 segundos)
                'showModal' => true // Mostrar también como modal
            ]);

            // Mostrar alerta directamente con SweetAlert2 y redirigir al mapa de mesas después
            $this->js('
                setTimeout(function() {
                    Swal.fire({
                        icon: "success",
                        title: "¡Pedido de Delivery Registrado!",
                        text: "El pedido #' . $order->id . ' ha sido registrado correctamente. Dirección: ' . $this->deliveryAddress . '",
                        confirmButtonText: "Aceptar",
                        confirmButtonColor: "#10b981"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Cerrar el modal
                            document.querySelector("button[wire\\\\:click=\'closeDeliveryModal\']").click();

                            // Redirigir al mapa de mesas
                            window.location.href = "' . route('tables.map') . '";
                        }
                    });
                }, 300);
            ');

            // No cerramos el modal aquí, ya que se cerrará después de hacer clic en Aceptar
            // $this->closeDeliveryModal();

            // En su lugar, solo registramos que el proceso se completó
            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Proceso completado, esperando confirmación del usuario';
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] Modal cerrado';
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            // Generar comanda
            $url = route('pos.command.pdf', ['order' => $order->id]);
            $this->commandUrl = $url;
            $this->showCommandModal = true;

            $logPath = storage_path('logs/delivery_process.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ÉXITO: Proceso completado, mostrando comanda para orden ID: ' . $order->id;
            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error en processDeliveryOrder', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            $logPath = storage_path('logs/delivery_errors.log');
            $logContent = '[' . now()->format('Y-m-d H:i:s') . '] ERROR en processDeliveryOrder: ' .
                $e->getMessage() . "\nLínea: " . $e->getLine() .
                "\nArchivo: " . $e->getFile() . "\nTrace: " . $e->getTraceAsString();

            // Asegurarse de que el directorio existe
            if (!file_exists(dirname($logPath))) {
                mkdir(dirname($logPath), 0755, true);
            }

            file_put_contents($logPath, $logContent . PHP_EOL, FILE_APPEND);

            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al procesar el pedido: ' . $e->getMessage()
            ]);
        }
    }

    #[\Livewire\Attributes\On('setCommandUrl')]
    public function setCommandUrl($params): void
    {
        if (isset($params['url'])) {
            $this->commandUrl = $params['url'];
        }
    }

    #[\Livewire\Attributes\On('openCommandModal')]
    public function openCommandModal(): void
    {
        $this->showCommandModal = true;
    }

    #[\Livewire\Attributes\On('setPreBillUrl')]
    public function setPreBillUrl($params): void
    {
        if (isset($params['url'])) {
            $this->preBillUrl = $params['url'];
        }
    }

    #[\Livewire\Attributes\On('openPreBillModal')]
    public function openPreBillModal(): void
    {
        $this->showPreBillModal = true;
    }

    #[\Livewire\Attributes\On('closeInvoiceModal')]
    public function closeInvoiceModal(): void
    {
        $this->showInvoiceModal = false;
    }

    #[\Livewire\Attributes\On('openInvoiceModal')]
    public function openInvoiceModal(): void
    {
        $this->showInvoiceModal = true;
    }

    #[\Livewire\Attributes\On('openTransferModal')]
    public function openTransferModalEvent(): void
    {
        $this->openTransferModal();
    }

    #[\Livewire\Attributes\On('open-delivery-modal-after-render')]
    public function openDeliveryModalAfterRender(): void
    {
        $this->openDeliveryModal();
        Log::info('Modal de delivery abierto después de renderizar');
    }

    #[\Livewire\Attributes\On('changeTableStatus')]
    public function changeTableStatusFromEvent($params): void
    {
        $this->changeTableStatus($params);
    }

    #[\Livewire\Attributes\On('guardarCarritoYRedirigir')]
    public function guardarCarritoYRedirigir(): void
    {
        try {
            // Si hay una mesa seleccionada y está disponible, cambiarla a ocupada SOLO si hay productos
            if ($this->table && $this->table->status === 'available' && !empty($this->cart)) {
                // Verificar que realmente hay productos en el carrito
                $productCount = count($this->cart);

                if ($productCount > 0) {
                    $this->table->status = 'occupied';
                    $this->table->occupied_at = now();
                    $this->table->save();

                    \Illuminate\Support\Facades\Log::info('Mesa marcada como ocupada antes de redirigir', [
                        'table_id' => $this->tableId,
                        'table_number' => $this->table->number,
                        'product_count' => $productCount
                    ]);
                } else {
                    \Illuminate\Support\Facades\Log::info('No se cambió el estado de la mesa porque no hay productos', [
                        'table_id' => $this->tableId,
                        'table_number' => $this->table->number
                    ]);
                }
            }

            // Guardar el carrito en la sesión
            $this->saveCartToSession();

            // Redirigir al mapa de mesas
            $this->redirect(route('tables.map'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al guardar carrito y redirigir', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Redirigir de todas formas para no dejar al usuario atrapado
            $this->redirect(route('tables.map'));
        }
    }

    /**
     * Inicia la mesa cambiando su estado a ocupada sin importar si hay productos en el carrito
     */
    public function iniciarMesa(): void
    {
        try {
            if (!$this->table) {
                $this->dispatch('notification', [
                    'type' => 'info',
                    'title' => 'Seleccionar Mesa',
                    'message' => 'Por favor, seleccione una mesa para iniciar'
                ]);
                return;
            }

            // Cambiar el estado de la mesa a ocupada sin verificar productos
            $this->table->status = 'occupied';
            $this->table->occupied_at = now();
            $this->table->save();

            // Guardar el carrito en la sesión
            $this->saveCartToSession();

            // Mostrar mensaje de éxito
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Mesa Iniciada',
                'message' => 'Mesa ' . $this->table->number . ' iniciada correctamente'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al iniciar la mesa: ' . $e->getMessage()
            ]);
        }
    }


    /**
     * Cambiar el estado de una mesa
     */
    public function changeTableStatus($params): void
    {
        if (!isset($params['tableId']) || !isset($params['status'])) {
            return;
        }

        $tableId = $params['tableId'];
        $status = $params['status'];

        $table = Table::find($tableId);
        if ($table) {
            // Si la mesa se está ocupando, registramos el tiempo de inicio
            if ($status === 'occupied') {
                $table->occupied_at = now();
            } else if ($table->status === 'occupied' && $status !== 'occupied') {
                // Si la mesa estaba ocupada y ahora cambia a otro estado, limpiamos el tiempo
                $table->occupied_at = null;
            }

            $table->status = $status;
            $table->save();

            // Si es la mesa actual, actualizarla
            if ($this->table && $this->table->id === $table->id) {
                $this->table = $table;
            }

            // Notificar al usuario
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Mesa Actualizada',
                'message' => "La mesa {$table->number} ahora está " . $this->getStatusName($status)
            ]);
        }
    }

    /**
     * Obtener el nombre del estado en español
     */
    private function getStatusName($status): string
    {
        $statusNames = [
            'available' => 'disponible',
            'occupied' => 'ocupada',
            'reserved' => 'reservada',
            'maintenance' => 'en mantenimiento'
        ];

        return $statusNames[$status] ?? $status;
    }



    /**
     * Guarda el carrito en la sesión y en la base de datos
     */
    #[\Livewire\Attributes\Js]
    public function saveCartToSession(): bool
    {
        if (!$this->tableId) {
            Log::error('No se puede guardar el carrito: tableId no definido');
            return false;
        }

        try {
            // SOLUCIÓN DIRECTA: Verificar si hay una orden activa para esta mesa
            if (!$this->currentOrder) {
                // Buscar una orden activa para esta mesa
                $table = Table::find($this->tableId);

                if ($table && $table->hasActiveOrder()) {
                    $this->currentOrder = $table->activeOrder()->first();
                    Log::info('Orden activa encontrada para la mesa en saveCartToSession', [
                        'table_id' => $this->tableId,
                        'order_id' => $this->currentOrder->id
                    ]);
                } else if ($table && !empty($this->cart)) {
                    // Si no hay orden activa pero hay productos en el carrito, crear una nueva orden
                    // Solo si la mesa ya está ocupada o si hay productos en el carrito
                    if ($table->status === Table::STATUS_OCCUPIED || count($this->cart) > 0) {
                        $this->currentOrder = $table->occupy(Auth::id());
                        Log::info('Nueva orden creada para la mesa en saveCartToSession', [
                            'table_id' => $this->tableId,
                            'order_id' => $this->currentOrder->id,
                            'product_count' => count($this->cart)
                        ]);
                    } else {
                        Log::info('No se creó orden porque no hay productos en el carrito', [
                            'table_id' => $this->tableId,
                            'table_status' => $table->status
                        ]);
                    }
                }
            }

            // Guardar el carrito en la base de datos si hay una orden activa
            if ($this->currentOrder && !empty($this->cart)) {
                // Eliminar los detalles existentes
                $this->currentOrder->orderDetails()->delete();

                // Añadir los productos del carrito como detalles de la orden
                foreach ($this->cart as $productId => $item) {
                    $orderDetail = new OrderDetailModel();
                    $orderDetail->order_id = $this->currentOrder->id;
                    $orderDetail->product_id = $productId;
                    $orderDetail->quantity = $item['quantity'];
                    $orderDetail->unit_price = $item['price'];
                    $orderDetail->subtotal = $item['subtotal'];
                    $orderDetail->notes = $item['notes'] ?? null;
                    $orderDetail->status = 'pending';
                    $orderDetail->save();
                }

                // Actualizar los totales de la orden
                $this->currentOrder->subtotal = $this->cartTotal;
                $this->currentOrder->tax = $this->cartTotal * 0.18;
                $this->currentOrder->total = $this->cartTotal * 1.18;
                $this->currentOrder->notes = $this->customerNote;
                $this->currentOrder->save();

                Log::info('Carrito guardado en la base de datos', [
                    'order_id' => $this->currentOrder->id,
                    'cart_count' => count($this->cart),
                    'cart_total' => $this->cartTotal
                ]);
            }

            // Guardar también en la sesión como respaldo
            session()->put('cart_' . $this->tableId, [
                'cart' => $this->cart,
                'cartTotal' => $this->cartTotal,
                'customerNote' => $this->customerNote,
                'timestamp' => now()->timestamp,
                'order_id' => $this->currentOrder ? $this->currentOrder->id : null
            ]);

            // Forzar que la sesión se guarde inmediatamente
            session()->save();

            // Registrar en el log para depuración
            Log::info('Carrito guardado en sesión correctamente', [
                'table_id' => $this->tableId,
                'cart_count' => count($this->cart),
                'cart_total' => $this->cartTotal,
                'order_id' => $this->currentOrder ? $this->currentOrder->id : null
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al guardar carrito en sesión', [
                'table_id' => $this->tableId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }
    /**
     * Carga el carrito desde la sesión o la base de datos
     * @return bool True si se cargó el carrito, false si no había carrito
     */
    protected function loadCartFromSession(): bool
    {
        if (!$this->tableId) {
            Log::info('No se puede cargar el carrito: tableId no definido');
            return false;
        }

        // Asegurarse de que la mesa esté cargada si tenemos tableId
        if (!$this->table || $this->table->id != $this->tableId) {
            $this->table = Table::find($this->tableId);
            Log::info('Mesa cargada en loadCartFromSession', [
                'table_id' => $this->tableId,
                'table_status' => $this->table ? $this->table->status : 'null'
            ]);
        }

        // SOLUCIÓN DIRECTA: Primero intentar cargar desde la base de datos si hay una orden activa
        if ($this->table && $this->table->hasActiveOrder()) {
            $order = $this->table->activeOrder()->with('orderDetails.product')->first();

            if ($order) {
                $this->currentOrder = $order;

                // Verificar si la orden tiene detalles
                if ($order->orderDetails->isNotEmpty()) {
                    // Construir el carrito a partir de los detalles de la orden
                    $this->cart = [];
                    $this->cartTotal = 0;

                    foreach ($order->orderDetails as $detail) {
                        // Verificar que el producto exista
                        if (!$detail->product) {
                            Log::warning('Producto no encontrado para el detalle de orden', [
                                'order_id' => $order->id,
                                'detail_id' => $detail->id,
                                'product_id' => $detail->product_id
                            ]);
                            continue;
                        }

                        $productId = $detail->product_id;
                        $this->cart[$productId] = [
                            'id' => $productId,
                            'name' => $detail->product->name,
                            'price' => (float) $detail->unit_price,
                            'quantity' => $detail->quantity,
                            'subtotal' => (float) $detail->subtotal,
                            'notes' => $detail->notes ?? '',
                        ];
                        $this->cartTotal += $detail->subtotal;
                    }

                    $this->customerNote = $order->notes;

                    Log::info('Carrito cargado desde la base de datos', [
                        'order_id' => $order->id,
                        'cart_count' => count($this->cart),
                        'cart_total' => $this->cartTotal
                    ]);

                    return true;
                } else {
                    Log::info('La orden activa no tiene detalles', [
                        'order_id' => $order->id,
                        'table_id' => $this->tableId
                    ]);
                }
            }
        }

        // Si no hay orden activa o está vacía, intentar cargar desde la sesión
        $sessionCart = session('cart_' . $this->tableId);

        if (!$sessionCart || empty($sessionCart['cart'])) {
            Log::info('No se encontró carrito en sesión ni en base de datos', ['table_id' => $this->tableId]);
            return false;
        }

        // Cargar los datos del carrito desde la sesión
        $this->cart = $sessionCart['cart'];
        $this->cartTotal = $sessionCart['cartTotal'];
        $this->customerNote = $sessionCart['customerNote'] ?? null;

        // Si hay un ID de orden en la sesión, intentar cargar la orden
        if (!empty($sessionCart['order_id']) && !$this->currentOrder) {
            $this->currentOrder = Order::with('orderDetails.product')->find($sessionCart['order_id']);

            // Si la orden existe pero no tiene detalles, actualizar los detalles desde el carrito
            if ($this->currentOrder && $this->currentOrder->orderDetails->isEmpty() && !empty($this->cart)) {
                $this->saveCartToSession();

                Log::info('Orden actualizada con detalles del carrito en sesión', [
                    'order_id' => $this->currentOrder->id,
                    'cart_count' => count($this->cart)
                ]);
            }
        }

        // Registrar en el log para depuración
        Log::info('Carrito cargado desde sesión', [
            'table_id' => $this->tableId,
            'cart_count' => count($this->cart),
            'cart_total' => $this->cartTotal,
            'timestamp' => $sessionCart['timestamp'] ?? 'unknown',
            'order_id' => $this->currentOrder ? $this->currentOrder->id : null
        ]);

        return true;
    }
}
