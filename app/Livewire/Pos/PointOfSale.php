<?php

namespace App\Livewire\Pos;

use Livewire\Component;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Table;
use App\Models\Order;
use App\Models\OrderDetail;
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
    public ?string $commandUrl = null;
    public ?string $preBillUrl = null;
    public ?string $invoiceUrl = null;
    public Collection $availableTables;

    protected $queryString = [
        'tableId' => ['except' => ''],
    ];

    public function mount(?string $tableId = null): void
    {
        $this->tableId = $tableId;
        $this->products = collect(); // Inicializar como una colección vacía
        $this->availableTables = collect(); // Inicializar como una colección vacía

        if ($this->tableId) {
            $this->table = Table::find($this->tableId);

            // Intentar cargar el carrito desde la sesión primero
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
        // Si hay una mesa seleccionada, asegurarse de cargar los productos
        if ($this->tableId && empty($this->cart)) {
            $this->loadExistingOrder();
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
        \Illuminate\Support\Facades\Log::info('Carrito cargado desde orden existente', [
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

        // Si hay una mesa seleccionada y está disponible, cambiarla a ocupada
        if ($this->table && $this->table->status === 'available') {
            $this->table->status = 'occupied';
            $this->table->occupied_at = now();
            $this->table->save();

            // Notificar al usuario
            $this->dispatch('notification', [
                'type' => 'info',
                'title' => 'Mesa Ocupada',
                'message' => "La mesa {$this->table->number} ahora está ocupada"
            ]);
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

            // Guardar el carrito en la sesión
            $this->saveCartToSession();
        }
    }

    public function updateCartItemQuantity(string $productId, int $quantity): void
    {
        if (isset($this->cart[$productId])) {
            // Asegurarse de que la cantidad no sea menor a 1
            $quantity = max(1, $quantity);

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

    public function clearCart(): void
    {
        $this->cart = [];
        $this->cartTotal = 0;
        $this->customerNote = null;

        // Limpiar el carrito de la sesión
        if ($this->tableId) {
            session()->forget('cart_' . $this->tableId);
        }
    }

    public function createOrder(): ?Order
    {
        Log::info('Attempting to create or update order', ['cart' => $this->cart, 'table_id' => $this->tableId]);

        if (empty($this->cart)) {
            Log::warning('createOrder called with empty cart.');
            return null;
        }

        // Calcular totales basados en el carrito actual
        $subtotal = $this->cartTotal;
        $taxAmount = $subtotal * 0.18; // Asumiendo 18% IGV
        $totalAmount = $subtotal + $taxAmount;

        // Buscar orden existente para la mesa que no esté facturada
        $order = Order::where('table_id', $this->tableId)
            ->where('status', '!=', 'billed') // Que no esté facturada
            ->orderBy('created_at', 'desc')
            ->first();

        if ($order) {
            // Si existe, actualizarla
            Log::info('Updating existing order', ['order_id' => $order->id]);
            $order->subtotal = $subtotal;
            $order->tax_amount = $taxAmount;
            $order->total_amount = $totalAmount;
            $order->notes = $this->customerNote;
            // No cambiar estado aquí, se maneja al facturar
            $order->save();

            // Actualizar o añadir detalles
            $existingDetailIds = $order->details()->pluck('product_id')->toArray();
            $cartProductIds = array_keys($this->cart);

            // Detalles a eliminar
            $detailsToRemove = array_diff($existingDetailIds, $cartProductIds);
            if (!empty($detailsToRemove)) {
                OrderDetail::where('order_id', $order->id)->whereIn('product_id', $detailsToRemove)->delete();
                Log::info('Removed order details', ['product_ids' => $detailsToRemove]);
            }

            // Detalles a actualizar o añadir
            foreach ($this->cart as $productId => $item) {
                OrderDetail::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $productId],
                    [
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'total_price' => $item['subtotal'],
                        'notes' => $item['notes'] ?? null,
                        'status' => 'pending' // Asegurar estado inicial
                    ]
                );
            }
            Log::info('Updated/Created order details', ['product_ids' => $cartProductIds]);

        } else {
            // Si no existe, crear una nueva
            Log::info('Creating new order');
            $order = new Order();
            $order->table_id = $this->tableId;
            $order->user_id = Auth::id(); // Asociar al usuario autenticado
            $order->order_number = Order::generateOrderNumber();
            $order->status = 'pending'; // Estado inicial
            $order->subtotal = $subtotal;
            $order->tax_amount = $taxAmount;
            $order->total_amount = $totalAmount;
            $order->notes = $this->customerNote;
            $order->save();
            Log::info('New order created', ['order_id' => $order->id]);

            // Crear detalles para la nueva orden
            foreach ($this->cart as $productId => $item) {
                $orderDetail = new OrderDetail();
                $orderDetail->order_id = $order->id;
                $orderDetail->product_id = $productId;
                $orderDetail->quantity = $item['quantity'];
                $orderDetail->unit_price = $item['price'];
                $orderDetail->total_price = $item['subtotal'];
                $orderDetail->notes = $item['notes'] ?? null;
                $orderDetail->status = 'pending';
                $orderDetail->save();
            }
            Log::info('Created details for new order', ['product_ids' => array_keys($this->cart)]);
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
            $order = $this->createOrder();
            if (!$order) {
                Log::warning('generateInvoice: createOrder returned null or false.');
                return;
            }

            // Notificar éxito
            $this->dispatch('notification', [
                'type' => 'success',
                'title' => 'Factura Preparada',
                'message' => 'El formulario de facturación se abrirá en una nueva ventana'
            ]);

            // Abrir ventana directamente con JavaScript
            $url = route('pos.invoice.form', ['order' => $order->id]);
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

            // Abrir ventana directamente con JavaScript
            $url = route('pos.invoice.form', ['order' => $order->id]);
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
        // Si hay una mesa seleccionada y el carrito está vacío, intentar cargar los productos
        if ($this->tableId && empty($this->cart)) {
            $this->loadExistingOrder();
        }

        return view('livewire.pos.point-of-sale');
    }

    /**
     * Método para limpiar todo después de completar la venta (llamado desde JavaScript)
     */
    #[\Livewire\Attributes\On('clearSale')]
    public function clearSale(): void
    {
        // Limpiar el carrito
        $this->clearCart();

        // Si hay una mesa seleccionada, actualizar su estado
        if ($this->table) {
            $this->table->refresh(); // Refrescar para obtener los últimos datos
        }

        // Mostrar mensaje de éxito
        $this->dispatch('notification', [
            'type' => 'success',
            'title' => 'Venta completada',
            'message' => 'La venta se ha completado correctamente'
        ]);
    }

    /**
     * Método para cancelar el pedido actual
     * @return \Illuminate\Http\RedirectResponse|void
     */
    public function cancelOrder()
    {
        // Verificar si hay productos en el carrito
        if (empty($this->cart)) {
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
                $this->currentOrder->status = 'cancelled';
                $this->currentOrder->save();

                // Registrar en el log
                \Illuminate\Support\Facades\Log::info('Orden cancelada', [
                    'order_id' => $this->currentOrder->id,
                    'table_id' => $this->tableId
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error al cancelar la orden', [
                    'error' => $e->getMessage(),
                    'order_id' => $this->currentOrder->id ?? null
                ]);
            }
        }

        // Limpiar el carrito
        $this->clearCart();

        // Si hay una mesa seleccionada, cambiar su estado a disponible
        if ($this->table) {
            $this->table->status = 'available';
            $this->table->occupied_at = null;
            $this->table->save();
        }

        // Mostrar mensaje de éxito
        $this->dispatch('notification', [
            'type' => 'success',
            'title' => 'Pedido Cancelado',
            'message' => 'El pedido ha sido cancelado correctamente.'
        ]);

        // Redireccionar al mapa de mesas
        return redirect()->route('tables.map');
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

            // Cambiar el estado de la mesa destino a ocupada
            $destinationTable->status = 'occupied';
            $destinationTable->occupied_at = now();
            $destinationTable->save();

            // Cambiar el estado de la mesa origen a disponible
            $sourceTable->status = 'available';
            $sourceTable->occupied_at = null;
            $sourceTable->save();

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
     * Guarda el carrito en la sesión
     */
    protected function saveCartToSession(): void
    {
        if (!$this->tableId) {
            return;
        }

        // Guardar el carrito en la sesión con una clave única para cada mesa
        session()->put('cart_' . $this->tableId, [
            'cart' => $this->cart,
            'cartTotal' => $this->cartTotal,
            'customerNote' => $this->customerNote,
            'timestamp' => now()->timestamp
        ]);

        // Registrar en el log para depuración
        \Illuminate\Support\Facades\Log::info('Carrito guardado en sesión', [
            'table_id' => $this->tableId,
            'cart_count' => count($this->cart),
            'cart_total' => $this->cartTotal
        ]);
    }

    /**
     * Carga el carrito desde la sesión
     * @return bool True si se cargó el carrito, false si no había carrito en la sesión
     */
    protected function loadCartFromSession(): bool
    {
        if (!$this->tableId) {
            return false;
        }

        // Intentar cargar el carrito desde la sesión
        $sessionCart = session('cart_' . $this->tableId);

        if (!$sessionCart || empty($sessionCart['cart'])) {
            return false;
        }

        // Cargar los datos del carrito desde la sesión
        $this->cart = $sessionCart['cart'];
        $this->cartTotal = $sessionCart['cartTotal'];
        $this->customerNote = $sessionCart['customerNote'] ?? null;

        // Registrar en el log para depuración
        \Illuminate\Support\Facades\Log::info('Carrito cargado desde sesión', [
            'table_id' => $this->tableId,
            'cart_count' => count($this->cart),
            'cart_total' => $this->cartTotal,
            'timestamp' => $sessionCart['timestamp'] ?? 'unknown'
        ]);

        return true;
    }
}
