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

    protected $queryString = [
        'tableId' => ['except' => ''],
    ];

    public function mount(?string $tableId = null): void
    {
        $this->tableId = $tableId;
        $this->products = collect(); // Inicializar como una colección vacía

        if ($this->tableId) {
            $this->table = Table::find($this->tableId);
        }

        $this->loadCategories();
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
        Log::info('Iniciando carga de productos por categoría', ['category_id' => $categoryId]);

        $this->selectedCategoryId = $categoryId;

        $query = Product::where('category_id', $categoryId)
            ->where('active', true)
            ->where('available', true)
            ->where('product_type', '!=', 'ingredient')
            ->select(['id', 'name', 'sale_price', 'image_path', 'available', 'category_id']);

        // Aplicar filtro de búsqueda si existe
        if (!empty($this->searchQuery)) {
            $query->where('name', 'like', "%{$this->searchQuery}%");
        }

        $this->products = $query->orderBy('name')->get();

                // Log detallado de cada producto
        foreach ($this->products as $product) {
            Log::info('Producto cargado', [
                'id' => $product->id,
                'name' => $product->name,
                'image_path' => $product->image_path,
                'full_image_url' => $product->image_path ? url('storage/' . $product->image_path) : null
            ]);
        }

        // Asegurar que $products nunca sea null
        if ($this->products === null) {
            $this->products = collect();
            Log::warning('No se encontraron productos para la categoría', ['category_id' => $categoryId]);
        }

        Log::info('Carga de productos completada', [
            'category_id' => $categoryId,
            'total_products' => $this->products->count()
        ]);

        // Forzar actualización de la vista
        $this->dispatch('products-updated');
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

        $this->updateCartTotal();
    }

    public function removeFromCart(string $productId): void
    {
        if (isset($this->cart[$productId])) {
            unset($this->cart[$productId]);
            $this->updateCartTotal();
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
        }
    }

    public function updateCartTotal(): void
    {
        $this->cartTotal = 0;

        foreach ($this->cart as $item) {
            $this->cartTotal += $item['subtotal'];
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->cartTotal = 0;
        $this->customerNote = null;
    }

    private function createOrder(): ?Order
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

    public function generateCommand(): void
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

            // Disparar evento para abrir el modal de comanda
            $url = route('pos.command.pdf', ['order' => $order->id]);
            Log::info('Dispatching open-command-modal', ['url' => $url]);
            $this->dispatch('open-command-modal', ['url' => $url]);

                        // ✅ Si es waiter, redirigir automáticamente al mapa de mesas después de un breve delay (TEMPORALMENTE DESHABILITADO)
            /*
            if (Auth::user()->hasRole('waiter')) {
                $this->dispatch('notification', [
                    'type' => 'success',
                    'title' => 'Comanda Enviada',
                    'message' => 'Pedido guardado correctamente. Regresando al mapa de mesas...'
                ]);

                // Redirigir después de un breve delay para que se abra la ventana de impresión
                $this->dispatch('redirect-to-table-map');
            }
            */

            // NO limpiar carrito después de imprimir para mantener productos para venta
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al generar la comanda: ' . $e->getMessage()
            ]);
        }
    }

    public function generatePreBill(): void
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

            // Disparar evento para abrir el modal de pre-cuenta
            $url = route('pos.prebill.pdf', ['order' => $order->id]);
            Log::info('Dispatching open-prebill-modal', ['url' => $url]);
            $this->dispatch('open-prebill-modal', ['url' => $url]);

                        // ✅ Si es waiter, redirigir automáticamente al mapa de mesas después de un breve delay (TEMPORALMENTE DESHABILITADO)
            /*
            if (Auth::user()->hasRole('waiter')) {
                $this->dispatch('notification', [
                    'type' => 'success',
                    'title' => 'Pre-cuenta Impresa',
                    'message' => 'Pre-cuenta generada correctamente. Regresando al mapa de mesas...'
                ]);

                // Redirigir después de un breve delay para que se abra la ventana de impresión
                $this->dispatch('redirect-to-table-map');
            }
            */

            // NO limpiar carrito después de imprimir para mantener productos para venta
        } catch (\Exception $e) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'Error al generar la pre-cuenta: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generar comprobante de venta y mostrarlo en ventana flotante
     */
    public function confirmSale(): void
    {
        // Si el carrito está vacío, no hacemos nada
        if (empty($this->cart)) {
            $this->dispatch('notification', [
                'type' => 'error',
                'title' => 'Error',
                'message' => 'El carrito está vacío. Añade productos para completar la venta.'
            ]);
            return;
        }

        try {
            // Si no hay orden actual, crear una
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

            // Disparar evento para abrir el modal de facturación
            $url = route('pos.invoice.form', ['order' => $order->id]);
            Log::info('Dispatching open-invoice-modal', ['url' => $url]);
            $this->dispatch('open-invoice-modal', ['url' => $url]);

            // No limpiar el carrito hasta que se procese el comprobante
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
     * Métodos para controlar modales
     */
    public function openCommandModal(): void
    {
        $this->showCommandModal = true;
    }

    public function openPreBillModal(): void
    {
        $this->showPreBillModal = true;
    }

    public function openInvoiceModal(): void
    {
        $this->showInvoiceModal = true;
    }

    public function closeModals(): void
    {
        $this->showCommandModal = false;
        $this->showPreBillModal = false;
        $this->showInvoiceModal = false;
    }
}
