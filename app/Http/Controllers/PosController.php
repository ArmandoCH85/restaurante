<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\Table;
use App\Models\Order;
use App\Models\Invoice;
use App\Models\InvoiceDetail;
use App\Models\CashRegister;
use App\Models\Employee;

class PosController extends Controller
{


    /**
     * Mostrar la interfaz POS principal
     */
    public function index(Request $request)
    {
        // SOLUCIÓN DIRECTA: Manejar parámetros de consulta table_id y preserve_cart
        if ($request->has('table_id')) {
            $tableId = $request->input('table_id');
            $preserveCart = $request->input('preserve_cart', false);

            // Registrar información para depuración
            Log::info('Cargando mesa específica en POS con parámetros de consulta', [
                'table_id' => $tableId,
                'preserve_cart' => $preserveCart,
                'request_url' => $request->fullUrl()
            ]);

            // Verificar si la mesa existe
            $table = \App\Models\Table::find($tableId);

            if ($table) {
                // Registrar información sobre la mesa que estamos cargando
                Log::info('Cargando mesa desde el controlador sin cambiar estado', [
                    'table_id' => $tableId,
                    'table_number' => $table->number,
                    'current_status' => $table->status
                ]);

                // Buscar una orden activa para esta mesa
                $order = \App\Models\Order::where('table_id', $tableId)
                    ->where('status', '!=', 'completed')
                    ->where('status', '!=', 'cancelled')
                    ->where('billed', false)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if (!$order) {
                    // Obtener la caja registradora activa
                    $activeCashRegister = CashRegister::getOpenRegister();

                    if (!$activeCashRegister) {
                        throw new \Exception('No hay una caja registradora abierta. Por favor, abra una caja antes de crear una orden.');
                    }

                    // Crear una nueva orden para esta mesa
                    $order = new \App\Models\Order();
                    $order->table_id = $tableId;
                    $order->service_type = 'dine_in';
                    $order->employee_id = \Illuminate\Support\Facades\Auth::id();
                    $order->order_datetime = now();
                    $order->status = 'open';
                    $order->subtotal = 0;
                    $order->tax = 0;
                    $order->total = 0;
                    $order->billed = false;
                    $order->cash_register_id = $activeCashRegister->id;
                    $order->save();

                    Log::info('Nueva orden creada para la mesa desde el controlador', [
                        'table_id' => $tableId,
                        'order_id' => $order->id
                    ]);
                }

                // Pasar los parámetros a la vista
                return view('pos.index', [
                    'tableId' => $tableId,
                    'orderId' => $order->id,
                    'preserveCart' => $preserveCart
                ]);
            }
        }

        // Verificar si se está cargando una orden específica (para Ver Detalles)
        if ($request->has('order_id')) {
            $orderId = $request->input('order_id');
            $preserveCart = $request->input('preserve_cart', false);
            $invoice = $request->input('invoice', false);

            // Verificar si el usuario tiene rol Delivery
            $user = \Illuminate\Support\Facades\Auth::user();
            $hasDeliveryRole = $user && $user->roles->where('name', 'Delivery')->count() > 0;

            // Registrar información para depuración
            Log::info('Cargando orden específica en POS', [
                'order_id' => $orderId,
                'preserve_cart' => $preserveCart,
                'user_id' => $user ? $user->id : 'no_user',
                'name' => $user ? $user->name : 'no_name',
                'roles' => $user ? $user->roles->pluck('name')->toArray() : [],
                'has_delivery_role' => $hasDeliveryRole ? 'Sí' : 'No'
            ]);

            // Intentar obtener la orden
            try {
                $order = \App\Models\Order::with(['orderDetails.product', 'table', 'deliveryOrder'])
                    ->findOrFail($orderId);

                // Registrar información para depuración
                Log::info('Orden encontrada', [
                    'order_id' => $orderId,
                    'service_type' => $order->service_type,
                    'table_id' => $order->table_id
                ]);

                // Si es una orden de delivery, pasar el tipo de servicio
                if ($order->service_type === 'delivery') {
                    return view('pos.index', [
                        'orderId' => $orderId,
                        'serviceType' => 'delivery',
                        'preserveCart' => $preserveCart,
                        'invoice' => $invoice
                    ]);
                }

                // Si es una orden de mesa, pasar el ID de la mesa
                if ($order->table_id) {
                    return view('pos.index', [
                        'tableId' => $order->table_id,
                        'orderId' => $orderId,
                        'preserveCart' => $preserveCart,
                        'invoice' => $invoice
                    ]);
                }

                // Si no tiene mesa ni es delivery, solo pasar el ID de la orden
                return view('pos.index', [
                    'orderId' => $orderId,
                    'preserveCart' => $preserveCart,
                    'invoice' => $invoice
                ]);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                // Registrar el error
                Log::error('Pedido no encontrado', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage()
                ]);

                // Redirigir a la página principal del POS con un mensaje de error
                return redirect()->route('pos.index')
                    ->with('error', "El pedido #{$orderId} no existe o ha sido eliminado.");
            }
        }

        // Si se especifica un tipo de servicio (para nuevo pedido de delivery)
        if ($request->has('serviceType')) {
            return view('pos.index', [
                'serviceType' => $request->input('serviceType')
            ]);
        }

        // Vista normal sin parámetros - seleccionar automáticamente "Para Llevar"
        // ya que al no tener mesa seleccionada, se interpreta como un pedido para llevar
        return view('pos.index', [
            'serviceType' => 'takeout'
        ]);
    }

    /**
     * Mostrar la interfaz POS con una mesa específica
     */
    public function showTable(Table $table)
    {
        return view('pos.index', ['tableId' => $table->id]);
    }

    /**
     * Generar la comanda como HTML imprimible
     */
    public function generateCommandPdf(Order $order)
    {
        // Cargar relaciones necesarias
        $order->load('orderDetails.product', 'table');

        // Mostrar vista HTML directamente con meta print
        return view('pos.command-print', [
            'order' => $order,
            'table' => $order->table,
            'date' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Generar la pre-cuenta como HTML imprimible
     */
    public function generatePreBillPdf(Order $order)
    {
        // Cargar relaciones necesarias
        $order->load('orderDetails.product', 'table');

        // Mostrar vista HTML directamente con meta print
        return view('pos.pre-bill-print', [
            'order' => $order,
            'table' => $order->table,
            'date' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Vista previa térmica para comandas (desarrollo/pruebas)
     */
    public function thermalPreviewCommand(Order $order)
    {
        // Cargar relaciones necesarias
        $order->load('orderDetails.product', 'table');

        return view('pos.command-print', [
            'order' => $order,
            'table' => $order->table,
            'date' => now()->format('d/m/Y H:i:s'),
            'thermal_preview' => true
        ])->with('thermalPreviewMode', true);
    }

    /**
     * Vista previa térmica para pre-cuentas (desarrollo/pruebas)
     */
    public function thermalPreviewPreBill(Order $order)
    {
        // Cargar relaciones necesarias
        $order->load('orderDetails.product', 'table');

        return view('pos.pre-bill-print', [
            'order' => $order,
            'table' => $order->table,
            'date' => now()->format('d/m/Y H:i:s'),
            'thermal_preview' => true
        ])->with('thermalPreviewMode', true);
    }

    /**
     * Crear una orden desde la sesión y mostrar la comanda
     */
    public function createAndShowCommand(Request $request)
    {
        // Obtener el componente Livewire
        $component = app(\App\Livewire\Pos\PointOfSale::class);

        // Si hay una mesa seleccionada
        if ($request->has('table_id')) {
            $component->tableId = $request->table_id;
            $component->mount($request->table_id);
        }

        // Crear la orden
        $order = $component->createOrder();

        if (!$order) {
            return redirect()->route('pos.index')->with('error', 'No hay productos en el carrito');
        }

        // Redirigir a la vista de comanda
        return redirect()->route('pos.command.pdf', ['order' => $order->id]);
    }

    /**
     * Crear una orden desde la sesión y mostrar la pre-cuenta
     */
    public function createAndShowPreBill(Request $request)
    {
        // Obtener el componente Livewire
        $component = app(\App\Livewire\Pos\PointOfSale::class);

        // Si hay una mesa seleccionada
        if ($request->has('table_id')) {
            $component->tableId = $request->table_id;
            $component->mount($request->table_id);
        }

        // Crear la orden
        $order = $component->createOrder();

        if (!$order) {
            return redirect()->route('pos.index')->with('error', 'No hay productos en el carrito');
        }

        // Redirigir a la vista de pre-cuenta
        return redirect()->route('pos.prebill.pdf', ['order' => $order->id]);
    }

    /**
     * Crear una orden desde la sesión y mostrar el formulario unificado de pago y facturación
     */
    public function createAndShowInvoiceForm(Request $request)
    {
        // Obtener el componente Livewire
        $component = app(\App\Livewire\Pos\PointOfSale::class);

        // Si hay una mesa seleccionada
        if ($request->has('table_id')) {
            $component->tableId = $request->table_id;
            $component->mount($request->table_id);
        }

        // Crear la orden
        $order = $component->createOrder();

        if (!$order) {
            return redirect()->route('pos.index')->with('error', 'No hay productos en el carrito');
        }

        // Redirigir al formulario unificado de pago y facturación
        return redirect()->route('pos.unified.form', ['order' => $order->id]);
    }

    /**
     * Crear una orden y devolver su ID como JSON (para peticiones JavaScript)
     */
    public function createOrderFromJS(Request $request)
    {
        try {
            // Verificar si hay productos en la solicitud
            if ($request->has('cart_items') && !empty($request->cart_items)) {
                // Log para depuración
                Log::info('Productos recibidos:', ['productos' => $request->cart_items]);

                // Obtener el nombre del cliente si se proporcionó (para pedidos para llevar)
                $customerName = $request->customer_name ?? null;

                // Obtener el tipo de servicio si se proporcionó
                $serviceType = $request->service_type ?? null;

                // Log para depuración del nombre del cliente y tipo de servicio
                if ($customerName) {
                    Log::info('Nombre del cliente recibido:', ['customer_name' => $customerName]);
                }
                if ($serviceType) {
                    Log::info('Tipo de servicio recibido:', ['service_type' => $serviceType]);
                }

                // Verificar si es un pedido para llevar sin nombre de cliente
                if ($serviceType === 'takeout' && !$customerName) {
                    Log::warning('Pedido para llevar sin nombre de cliente');
                }

                // Crear la orden usando los datos de la solicitud
                $order = $this->createOrderFromCartItems($request->cart_items, $request->table_id ?? null, $customerName, $serviceType);

                if ($order) {
                    Log::info('Orden creada correctamente', ['orderId' => $order->id]);
                    return response()->json([
                        'success' => true,
                        'orderId' => $order->id,
                        'message' => 'Orden creada correctamente'
                    ]);
                } else {
                    Log::error('Error al crear la orden a pesar de tener productos');
                }
            } else {
                Log::warning('No hay productos en la solicitud', ['request' => $request->all()]);
            }

            // Fallback al método anterior (por si acaso)
            $component = app(\App\Livewire\Pos\PointOfSale::class);

            if ($request->has('table_id')) {
                $component->tableId = $request->table_id;
                $component->mount($request->table_id);
            }

            $order = $component->createOrder();

            if (!$order) {
                Log::error('No se pudo crear la orden con el método fallback');
                return response()->json([
                    'success' => false,
                    'message' => 'No hay productos en el carrito o hubo un error al crear la orden.'
                ], 400);
            }

            Log::info('Orden creada con método fallback', ['orderId' => $order->id]);
            return response()->json([
                'success' => true,
                'orderId' => $order->id,
                'message' => 'Orden creada correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Excepción al crear la orden', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la orden: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crea una orden a partir de los elementos del carrito recibidos
     */
    private function createOrderFromCartItems($cartItems, $tableId = null, $customerName = null, $serviceType = null)
    {
        // Log para debugging
        Log::info('Creating order from cart items', [
            'cart_items_count' => count($cartItems),
            'table_id' => $tableId,
            'customer_name' => $customerName,
            'service_type' => $serviceType
        ]);

        // Verificar que hay productos
        if (empty($cartItems)) {
            Log::warning('No cart items provided');
            return null;
        }

        // Obtener la caja registradora activa
        $activeCashRegister = CashRegister::getOpenRegister();

        if (!$activeCashRegister) {
            throw new \Exception('No hay una caja registradora abierta. Por favor, abra una caja antes de crear una orden.');
        }

        // Crear la orden
        $order = new Order();
        $order->order_datetime = now(); // Usando el campo correcto order_datetime
        $order->table_id = $tableId;
        $order->status = 'open'; // Usando 'open' que es un valor válido según la definición de la tabla
        $order->billed = false;
        $order->cash_register_id = $activeCashRegister->id;

        // Obtener el empleado asociado al usuario autenticado
        if (Auth::check()) {
            $employee = Employee::where('user_id', Auth::id())->first();

            if (!$employee) {
                throw new \Exception('No se encontró un empleado asociado al usuario actual. Por favor, contacte al administrador.');
            }

            $order->employee_id = $employee->id;
        } else {
            throw new \Exception('No hay un usuario autenticado.');
        }

        // Si se proporcionó un nombre de cliente, guardarlo en las notas de la orden
        if ($customerName) {
            $order->notes = "Cliente: " . $customerName;
        }

        // Asignar el tipo de servicio
        $order->service_type = $serviceType ?: 'takeout'; // Valor predeterminado es 'takeout'

        $order->save();

        // Log después de guardar para confirmar los datos
        Log::info('Order saved successfully', [
            'order_id' => $order->id,
            'table_id' => $order->table_id,
            'service_type' => $order->service_type,
            'order_attributes' => $order->getAttributes()
        ]);

        // Calcular el total
        $total = 0;

        // Agregar productos a la orden
        foreach ($cartItems as $item) {
            $productId = $item['id'];

            // Si el ID viene como "cart-item-X", extraer solo el número
            if (is_string($productId) && strpos($productId, 'cart-item-') === 0) {
                $productId = str_replace('cart-item-', '', $productId);
            }

            $orderDetail = new \App\Models\OrderDetail();
            $orderDetail->order_id = $order->id;
            $orderDetail->product_id = $productId;
            $orderDetail->quantity = $item['quantity'];
            $orderDetail->unit_price = $item['price'];
            $orderDetail->subtotal = $item['subtotal'];
            $orderDetail->save();

            $total += $item['subtotal'];
        }

        // Los precios ya incluyen IGV, usar el método correcto para recalcular
        $order->recalculateTotals();

        // Si es delivery, crear el registro de delivery con información del cliente
        if ($serviceType === 'delivery') {
            try {
                Log::info('Creando registro de delivery desde PosController', [
                    'order_id' => $order->id,
                    'service_type' => $serviceType,
                    'customer_id' => $order->customer_id
                ]);

                // Obtener información del cliente si existe
                $customer = $order->customer;
                $deliveryAddress = 'Dirección pendiente de completar';
                $deliveryReferences = 'Referencias pendientes';

                if ($customer) {
                    // Usar la dirección del cliente si está disponible
                    if (!empty($customer->address)) {
                        $deliveryAddress = $customer->address;
                    }

                    // Usar las referencias del cliente si están disponibles
                    if (!empty($customer->address_references)) {
                        $deliveryReferences = $customer->address_references;
                    }

                    Log::info('Información del cliente obtenida para delivery', [
                        'customer_name' => $customer->name,
                        'customer_address' => $customer->address,
                        'customer_references' => $customer->address_references,
                        'delivery_address' => $deliveryAddress,
                        'delivery_references' => $deliveryReferences
                    ]);
                }

                $deliveryOrder = new \App\Models\DeliveryOrder();
                $deliveryOrder->order_id = $order->id;
                $deliveryOrder->delivery_address = $deliveryAddress;
                $deliveryOrder->delivery_references = $deliveryReferences;
                $deliveryOrder->status = 'pending';
                $deliveryOrder->save();

                Log::info('Registro de delivery creado exitosamente', [
                    'delivery_id' => $deliveryOrder->id,
                    'order_id' => $order->id,
                    'final_address' => $deliveryOrder->delivery_address,
                    'final_references' => $deliveryOrder->delivery_references
                ]);

                // Disparar evento de cambio de estado
                event(new \App\Events\DeliveryStatusChanged($deliveryOrder));
            } catch (\Exception $e) {
                Log::error('Error al crear registro de delivery desde PosController', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'order_id' => $order->id
                ]);
            }
        }

        return $order;
    }

    /**
     * Mostrar el formulario de facturación
     */
    public function invoiceForm(Order $order)
    {
        // Cargar relaciones necesarias
        $order->load('orderDetails.product', 'table');

        // Obtener series activas para cada tipo de documento
        $invoiceSeries = \App\Models\DocumentSeries::where('document_type', 'invoice')
            ->where('active', true)
            ->first();

        $receiptSeries = \App\Models\DocumentSeries::where('document_type', 'receipt')
            ->where('active', true)
            ->first();

        $salesNoteSeries = \App\Models\DocumentSeries::where('document_type', 'sales_note')
            ->where('active', true)
            ->first();

        // Obtener información de los próximos correlativos
        $nextNumbers = [
            'invoice' => $invoiceSeries ? $invoiceSeries->series . '-' . str_pad($invoiceSeries->current_number, 8, '0', STR_PAD_LEFT) : 'F001-00000001',
            'receipt' => $receiptSeries ? $receiptSeries->series . '-' . str_pad($receiptSeries->current_number, 8, '0', STR_PAD_LEFT) : 'B001-00000001',
            'sales_note' => $salesNoteSeries ? $salesNoteSeries->series . '-' . str_pad($salesNoteSeries->current_number, 8, '0', STR_PAD_LEFT) : 'NV001-00000001',
        ];

        // Cargar cliente genérico para notas de venta
        $genericCustomer = \App\Models\Customer::firstOrCreate(
            ['document_type' => 'DNI', 'document_number' => '00000000'],
            [
                'name' => 'Cliente Genérico',
                'address' => 'Consumidor Final',
                'tax_validated' => false
            ]
        );

        return view('pos.invoice-form', [
            'order' => $order,
            'date' => now()->format('d/m/Y H:i:s'),
            'nextNumbers' => $nextNumbers,
            'genericCustomer' => $genericCustomer
        ]);
    }

    /**
     * Buscar cliente por número de documento
     */
    public function findCustomer(Request $request)
    {
        $document = $request->input('document');
        $type = $request->input('type', null);

        if (empty($document)) {
            return response()->json(['success' => false, 'message' => 'Documento vacío']);
        }

        $query = \App\Models\Customer::where('document_number', $document);

        if ($type) {
            $query->where('document_type', $type);
        }

        $customer = $query->first();

        if ($customer) {
            return response()->json([
                'success' => true,
                'customer' => $customer
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cliente no encontrado'
        ]);
    }

    /**
     * Buscar clientes por nombre o número de documento
     */
    public function searchCustomers(Request $request)
    {
        $term = $request->input('term');

        if (empty($term) || strlen($term) < 3) {
            return response()->json([
                'success' => false,
                'message' => 'Ingrese al menos 3 caracteres para buscar'
            ]);
        }

        // Buscar por nombre o número de documento
        $customers = \App\Models\Customer::where('name', 'like', "%{$term}%")
            ->orWhere('document_number', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'customers' => $customers
        ]);
    }

    /**
     * Crear nuevo cliente
     */
    public function storeCustomer(Request $request)
    {
        // Verificar si la solicitud es JSON
        if ($request->isJson()) {
            $data = $request->json()->all();
        } else {
            $data = $request->all();
        }

        try {
            $validated = $request->validate([
                'document_type' => 'required|string|max:10',
                'document_number' => 'required|string|max:15',
                'name' => 'required|string|max:255',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
            ]);

            // Verificar si ya existe un cliente con ese documento
            $existingCustomer = \App\Models\Customer::where('document_number', $validated['document_number'])
                ->where('document_type', $validated['document_type'])
                ->first();

            if ($existingCustomer) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cliente ya existe en el sistema',
                    'customer' => $existingCustomer
                ]);
            }

            // Crear el cliente
            $customer = \App\Models\Customer::create($validated);

            // TODO: Implementar validación con SUNAT/RENIEC en el futuro
            // Por ahora, solo crear el cliente sin validación externa
            /*
            $sunatService = new \App\Services\SunatService();

            if ($validated['document_type'] === 'RUC') {
                $validated = $sunatService->validateRuc($customer);
            } elseif ($validated['document_type'] === 'DNI') {
                $validated = $sunatService->validateDni($customer);
            }
            */

            // Recargar el cliente para obtener los datos actualizados
            $customer->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Cliente creado correctamente' . ($customer->tax_validated ? ' y validado con SUNAT/RENIEC' : ''),
                'customer' => $customer
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error al crear cliente: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar el comprobante según el tipo seleccionado
     */
    public function generateInvoice(Request $request, Order $order)
    {
        // Verificar que solo cashiers puedan generar comprobantes
        if (!Auth::user()->hasRole(['cashier', 'admin', 'super_admin'])) {
            return back()->with('error', 'No tienes permisos para generar comprobantes. Solo los cajeros pueden realizar esta acción.');
        }

        // Validaciones según tipo de comprobante
        $rules = [
            'invoice_type' => 'required|in:receipt,invoice,sales_note',
            'payment_method' => 'required|string|in:cash,card,transfer,yape,plin,multiple',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'customer_id' => 'nullable|exists:customers,id'
        ];

        // Para facturas, se requiere RUC válido (11 dígitos)
        if ($request->input('invoice_type') === 'invoice') {
            $rules['client_name'] = 'required|string|max:255';
            $rules['client_document'] = 'required|digits:11';
            $rules['client_address'] = 'required|string|max:255';
        }
        // Para boletas, solo se necesita DNI opcional (8 dígitos)
        elseif ($request->input('invoice_type') === 'receipt') {
            $rules['client_name'] = 'required|string|max:255';
            $rules['client_document'] = 'nullable|digits:8';
            $rules['client_address'] = 'nullable|string|max:255';
        }
        // Para notas de venta, datos opcionales
        else {
            $rules['client_name'] = 'nullable|string|max:255';
            $rules['client_document'] = 'nullable|string|max:20';
            $rules['client_address'] = 'nullable|string|max:255';
        }

        // Si es pago en efectivo, validar monto
        if ($request->input('payment_method') === 'cash') {
            $rules['payment_amount'] = 'required|numeric|min:' . $order->total;
        }
        // Si es pago dividido, validar detalles
        elseif ($request->input('payment_method') === 'multiple') {
            $rules['split_methods'] = 'required|array|min:1';
            $rules['split_methods.*'] = 'required|in:cash,card,transfer,yape,plin';
            $rules['split_amounts'] = 'required|array|min:1';
            $rules['split_amounts.*'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        // CORRECCIÓN: Los precios YA INCLUYEN IGV
        // El total de la orden ya incluye IGV, necesitamos calcular el desglose
        $totalWithIgv = $order->total; // Este ya incluye IGV
        $discountPercent = $validated['discount_percent'] ?? 0;
        $discountAmount = $totalWithIgv * ($discountPercent / 100);
        $totalAfterDiscount = $totalWithIgv - $discountAmount;

        // Calcular desglose según tipo de comprobante
        if (in_array($validated['invoice_type'], ['receipt', 'invoice'])) {
            // Calcular IGV incluido en el precio
            $subtotalAfterDiscount = round($totalAfterDiscount / 1.18, 2);
            $taxAmount = round($totalAfterDiscount / 1.18 * 0.18, 2);
            $total = $totalAfterDiscount; // El total no cambia, ya incluye IGV
        } else {
            // Nota de venta no tiene IGV
            $subtotalAfterDiscount = $totalAfterDiscount;
            $taxAmount = 0;
            $total = $totalAfterDiscount;
        }

        // Determinar cliente o usar cliente genérico
        $customerId = $validated['customer_id'] ?? null;

        // Si no hay customer_id, intentar buscar por documento
        if (!$customerId && !empty($validated['client_document'])) {
            $customer = \App\Models\Customer::where('document_number', $validated['client_document'])->first();
            if ($customer) {
                $customerId = $customer->id;
            }
        }

        // Si aún no hay cliente, crear uno nuevo o usar el genérico
        if (!$customerId) {
            if ($validated['invoice_type'] === 'sales_note') {
                // Para nota de venta, usar cliente genérico
                $customer = \App\Models\Customer::where('document_type', 'DNI')
                    ->where('document_number', '00000000')
                    ->first();

                if (!$customer) {
                    // Crear cliente genérico si no existe
                    $customer = \App\Models\Customer::create([
                        'document_type' => 'DNI',
                        'document_number' => '00000000',
                        'name' => 'Cliente Genérico',
                        'address' => 'Consumidor Final',
                        'tax_validated' => false
                    ]);
                }

                $customerId = $customer->id;
            } else {
                // Para boletas o facturas, crear un cliente nuevo
                $customerData = [
                    'document_type' => $validated['invoice_type'] === 'invoice' ? 'RUC' : 'DNI',
                    'document_number' => $validated['client_document'] ?? ($validated['invoice_type'] === 'invoice' ? '00000000000' : '00000000'),
                    'name' => $validated['client_name'] ?? 'Cliente Final',
                    'address' => $validated['client_address'] ?? null,
                    'tax_validated' => false
                ];

                // Verificar si ya existe un cliente con ese documento
                $customer = \App\Models\Customer::where('document_number', $customerData['document_number'])->first();

                if (!$customer) {
                    try {
                        $customer = \App\Models\Customer::create($customerData);
                    } catch (\Exception $e) {
                                            // Si falla la creación, usar cliente genérico
                    $customer = \App\Models\Customer::where('document_type', 'DNI')->where('document_number', '00000000')->first();
                    }
                }

                $customerId = $customer->id;
            }
        }

        // Actualizar customer_id en orden
        $order->customer_id = $customerId;
        $order->save();

        // Si es delivery y existe un DeliveryOrder, actualizar con información del cliente
        if ($order->service_type === 'delivery' && $order->deliveryOrder) {
            try {
                $customer = \App\Models\Customer::find($customerId);
                if ($customer) {
                    $deliveryOrder = $order->deliveryOrder;

                    // Solo actualizar si aún tiene los valores por defecto
                    if ($deliveryOrder->delivery_address === 'Dirección pendiente de completar' && !empty($customer->address)) {
                        $deliveryOrder->delivery_address = $customer->address;
                    }

                    if ($deliveryOrder->delivery_references === 'Referencias pendientes' && !empty($customer->address_references)) {
                        $deliveryOrder->delivery_references = $customer->address_references;
                    }

                    $deliveryOrder->save();

                    Log::info('DeliveryOrder actualizado con información del cliente', [
                        'delivery_id' => $deliveryOrder->id,
                        'customer_id' => $customerId,
                        'customer_name' => $customer->name,
                        'updated_address' => $deliveryOrder->delivery_address,
                        'updated_references' => $deliveryOrder->delivery_references
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error al actualizar DeliveryOrder con información del cliente', [
                    'error' => $e->getMessage(),
                    'order_id' => $order->id,
                    'customer_id' => $customerId
                ]);
            }
        }

        // Mapear tipos de comprobante (sales_note no existe en el enum, se maneja como receipt)
        $invoiceTypeForDb = $validated['invoice_type'] === 'sales_note' ? 'receipt' : $validated['invoice_type'];

        // Crear comprobante según tipo de pago
        if ($validated['payment_method'] === 'multiple') {
            // Verificar que la suma de los montos divididos sea igual al total
            $splitTotal = array_sum($validated['split_amounts']);
            if (abs($splitTotal - $total) > 0.01) {
                return back()->withErrors(['split_amounts' => 'La suma de los pagos debe ser igual al total de la venta']);
            }

            // Para pagos divididos, solo se genera un comprobante (no varios como antes)
            $series = $this->getNextSeries($validated['invoice_type']);
            $number = $this->getNextNumber($series);

            // Crear el comprobante con el total
            $invoice = new \App\Models\Invoice();
            $invoice->invoice_type = $invoiceTypeForDb;
            $invoice->series = $series;
            $invoice->number = $number;
            $invoice->issue_date = now()->format('Y-m-d');
            $invoice->customer_id = $customerId;
            $invoice->taxable_amount = $subtotalAfterDiscount;
            $invoice->tax = $taxAmount;
            $invoice->total = $total;
            $invoice->tax_authority_status = 'pending';

            // Establecer estado SUNAT según el tipo de comprobante
            if (in_array($validated['invoice_type'], ['invoice', 'receipt'])) {
                $invoice->sunat_status = 'PENDIENTE'; // Para Boletas y Facturas
            } else {
                $invoice->sunat_status = null; // Para Notas de Venta (no van a SUNAT)
            }

            $invoice->order_id = $order->id;
            $invoice->save();

            // Guardar los detalles del comprobante
            foreach ($order->orderDetails as $detail) {
                $invoiceDetail = new \App\Models\InvoiceDetail();
                $invoiceDetail->invoice_id = $invoice->id;
                $invoiceDetail->product_id = $detail->product_id;
                $invoiceDetail->description = $detail->product->name;
                $invoiceDetail->quantity = $detail->quantity;
                $invoiceDetail->unit_price = $detail->unit_price;
                $invoiceDetail->subtotal = $detail->quantity * $detail->unit_price;
                $invoiceDetail->save();
            }

            // Enviar el comprobante a SUNAT si es factura o boleta electrónica
            if (in_array($validated['invoice_type'], ['invoice', 'receipt'])) {
                $sunatService = new \App\Services\SunatService();
                $sunatService->sendInvoice($invoice);
            } else {
                // Para notas de venta, solo generar código QR
                $invoice->qr_code = $invoice->generateQRCode();
                $invoice->save();
            }

            // Devolver la vista
            $view = $this->getInvoiceTemplateByType($validated['invoice_type']);

            // Cambiar el estado de la mesa a disponible si existe
            if ($order->table) {
                $order->table->status = 'available';
                $order->table->occupied_at = null;
                $order->table->save();

                \Illuminate\Support\Facades\Log::info('✅ Mesa liberada en PosController al generar comprobante', [
                    'table_id' => $order->table->id,
                    'table_number' => $order->table->number,
                    'order_id' => $order->id,
                    'invoice_type' => $validated['invoice_type']
                ]);
            }

            // Marcar el pedido como facturado y completado
            $order->billed = true;
            $order->status = \App\Models\Order::STATUS_COMPLETED;
            $order->save();

            \Illuminate\Support\Facades\Log::info('✅ Orden completada automáticamente al generar comprobante', [
                'order_id' => $order->id,
                'previous_status' => $order->getOriginal('status'),
                'new_status' => $order->status,
                'invoice_type' => $validated['invoice_type']
            ]);

            // Generar URL de pre-cuenta automáticamente para pagos divididos
            $preBillUrl = route('pos.prebill.pdf', ['order' => $order->id]);

            // Establecer flags de sesión para redirección al mapa de mesas
            session([
                'clear_cart_after_print' => true,
                'table_id' => $order->table_id,
                'order_id' => $order->id,
                'generate_prebill' => true
            ]);

            return view($view, [
                'invoice' => $invoice,
                'date' => now()->format('d/m/Y H:i:s'),
                'qr_code' => $invoice->qr_code,
                'split_payment' => true,
                'payment_methods' => implode(', ', $validated['split_methods']),
                'change_amount' => 0,
                'document_number' => $invoice->series . '-' . $invoice->number,
                'document_type' => match ($validated['invoice_type']) {
                    'invoice' => 'Factura Electrónica',
                    'receipt' => 'Boleta Electrónica',
                    'sales_note' => 'Nota de Venta',
                    default => 'Comprobante',
                },
                'prebill_url' => $preBillUrl // URL para generar automáticamente la pre-cuenta
            ]);
        } else {
            // Generar serie y número según tipo
            $series = $this->getNextSeries($validated['invoice_type']);
            $number = $this->getNextNumber($series);

            // Crear el comprobante normal (pago único)
            $invoice = new \App\Models\Invoice();
            $invoice->invoice_type = $invoiceTypeForDb;
            $invoice->series = $series;
            $invoice->number = $number;
            $invoice->issue_date = now()->format('Y-m-d');
            $invoice->customer_id = $customerId;
            $invoice->taxable_amount = $subtotalAfterDiscount;
            $invoice->tax = $taxAmount;
            $invoice->total = $total;
            $invoice->tax_authority_status = 'pending';

            // Establecer estado SUNAT según el tipo de comprobante
            if (in_array($validated['invoice_type'], ['invoice', 'receipt'])) {
                $invoice->sunat_status = 'PENDIENTE'; // Para Boletas y Facturas
            } else {
                $invoice->sunat_status = null; // Para Notas de Venta (no van a SUNAT)
            }

            $invoice->order_id = $order->id;
            $invoice->save();

            // Guardar detalles del comprobante
            foreach ($order->orderDetails as $detail) {
                $invoiceDetail = new \App\Models\InvoiceDetail();
                $invoiceDetail->invoice_id = $invoice->id;
                $invoiceDetail->product_id = $detail->product_id;
                $invoiceDetail->description = $detail->product->name;
                $invoiceDetail->quantity = $detail->quantity;
                $invoiceDetail->unit_price = $detail->unit_price;
                $invoiceDetail->subtotal = $detail->quantity * $detail->unit_price;
                $invoiceDetail->save();
            }

            // Generar código QR para el comprobante
            $qrCode = $this->generateQRCode($invoice);
            $invoice->qr_code = $qrCode;
            $invoice->save();

            // Mostrar vista según el tipo
            $view = $this->getInvoiceTemplateByType($validated['invoice_type']);

            // Cambiar el estado de la mesa a disponible si existe
            if ($order->table) {
                $order->table->status = 'available';
                $order->table->occupied_at = null;
                $order->table->save();

                \Illuminate\Support\Facades\Log::info('✅ Mesa liberada en PosController al generar comprobante (segunda instancia)', [
                    'table_id' => $order->table->id,
                    'table_number' => $order->table->number,
                    'order_id' => $order->id,
                    'invoice_type' => $validated['invoice_type']
                ]);
            }

            // Marcar el pedido como facturado y completado
            $order->billed = true;
            $order->status = \App\Models\Order::STATUS_COMPLETED;
            $order->save();

            \Illuminate\Support\Facades\Log::info('✅ Orden completada automáticamente al generar comprobante (segunda instancia)', [
                'order_id' => $order->id,
                'previous_status' => $order->getOriginal('status'),
                'new_status' => $order->status,
                'invoice_type' => $validated['invoice_type']
            ]);

            // Generar URL de pre-cuenta automáticamente
            $preBillUrl = route('pos.prebill.pdf', ['order' => $order->id]);

            // Establecer flags de sesión para redirección al mapa de mesas
            session([
                'clear_cart_after_print' => true,
                'table_id' => $order->table_id,
                'order_id' => $order->id,
                'generate_prebill' => true
            ]);

            return view($view, [
                'invoice' => $invoice,
                'date' => now()->format('d/m/Y H:i:s'),
                'qr_code' => $qrCode,
                'split_payment' => false,
                'change_amount' => $validated['payment_method'] === 'cash' ? $validated['payment_amount'] - $total : 0,
                'document_number' => $invoice->series . '-' . $invoice->number,
                'document_type' => match ($validated['invoice_type']) {
                    'invoice' => 'Factura Electrónica',
                    'receipt' => 'Boleta Electrónica',
                    'sales_note' => 'Nota de Venta',
                    default => 'Comprobante',
                },
                'prebill_url' => $preBillUrl // URL para generar automáticamente la pre-cuenta
            ]);
        }
    }

    /**
     * Generar código QR para facturas electrónicas
     */
    private function generateQRCode($invoice)
    {
        // Para una implementación real de SUNAT, se genera el contenido del QR con los datos requeridos
        // Formato: RUC|TIPO DOC|SERIE|NUMERO|MTO IGV|MTO TOTAL|FECHA EMISIÓN|TIPO DOC ADQUIRIENTE|NRO DOC ADQUIRIENTE
        $customer = $invoice->customer;
        $docType = $customer->document_type === 'RUC' ? '6' : '1'; // 6 para RUC, 1 para DNI

        $qrContent = "20123456789|"; // RUC de la empresa (ejemplo)
        $qrContent .= ($invoice->invoice_type === 'invoice' ? "01|" : "03|"); // 01 para factura, 03 para boleta
        $qrContent .= $invoice->series . "|";
        $qrContent .= $invoice->number . "|";
        $qrContent .= number_format($invoice->tax, 2, '.', '') . "|";
        $qrContent .= number_format($invoice->total, 2, '.', '') . "|";
        $qrContent .= $invoice->issue_date . "|";
        $qrContent .= $docType . "|";
        $qrContent .= $customer->document_number;

        // En un entorno real, aquí se generaría una imagen QR basada en $qrContent
        // Para este ejemplo, devolvemos el contenido codificado o una imagen base64 estándar
        return base64_encode($qrContent);
    }

    /**
     * Generar PDF del comprobante
     */
    public function downloadInvoicePdf(\App\Models\Invoice $invoice)
    {
        // Cargar relaciones necesarias
        $invoice->load('details', 'order.table', 'order.deliveryOrder', 'customer');

        // Seleccionar la vista según el tipo de comprobante
        $view = $this->getInvoiceTemplateByType($invoice->invoice_type);

        // Mostrar vista HTML directamente
        return view($view, [
            'invoice' => $invoice,
            'date' => now()->format('d/m/Y H:i:s'),
        ]);
    }

    /**
     * Obtener la serie según el tipo de comprobante
     */
    private function getNextSeries($type)
    {
        // Buscar la primera serie activa para este tipo de documento
        $series = \App\Models\DocumentSeries::where('document_type', $type)
            ->where('active', true)
            ->first();

        // Si no se encuentra una serie, usar valores por defecto
        if (!$series) {
            return match ($type) {
                'sales_note' => 'NV001',
                'receipt' => 'B001',
                'invoice' => 'F001',
                default => 'NV001',
            };
        }

        return $series->series;
    }

    /**
     * Obtener el siguiente número correlativo
     */
    private function getNextNumber($series)
    {
        // Buscar la serie en la tabla de series
        $seriesModel = \App\Models\DocumentSeries::where('series', $series)
            ->where('active', true)
            ->first();

        // Si se encuentra la serie, usar su método getNextNumber()
        if ($seriesModel) {
            return $seriesModel->getNextNumber();
        }

        // Fallback al método anterior
        $lastInvoice = Invoice::where('series', $series)
            ->orderBy('number', 'desc')
            ->first();

        $nextNumber = $lastInvoice ? $lastInvoice->number + 1 : 1;

        return str_pad($nextNumber, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Obtener la plantilla según el tipo de comprobante
     */
    private function getInvoiceTemplateByType($type)
    {
        switch ($type) {
            case 'sales_note':
                return 'pos.sales-note-print';
            case 'receipt':
                return 'pos.receipt-print';
            case 'invoice':
                return 'pos.invoice-print';
            default:
                return 'pos.sales-note-print';
        }
    }

    /**
     * Muestra el formulario para anular un comprobante
     */
    public function showVoidForm(\App\Models\Invoice $invoice)
    {
        // Verificar si el comprobante puede ser anulado
        if (!$invoice->canBeVoided()) {
            return redirect()->back()->with('error', 'Este comprobante no puede ser anulado. Verifique que no hayan pasado más de 7 días desde su emisión y que no haya sido anulado previamente.');
        }

        // Cargar relaciones necesarias
        $invoice->load('details', 'customer', 'order.table');

        return view('pos.void-form', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Procesa la anulación de un comprobante
     */
    public function processVoid(Request $request, \App\Models\Invoice $invoice)
    {
        // Validar el formulario
        $validated = $request->validate([
            'reason' => 'required|string|min:5|max:255',
            'confirm' => 'required|boolean|accepted',
        ]);

        // Verificar si el comprobante puede ser anulado
        if (!$invoice->canBeVoided()) {
            return redirect()->back()->with('error', 'Este comprobante no puede ser anulado. Verifique que no hayan pasado más de 7 días desde su emisión y que no haya sido anulado previamente.');
        }

        // Anular el comprobante
        if ($invoice->void($validated['reason'])) {
            // Aquí se podría implementar integración con SUNAT para comunicar la anulación
            // Por ahora, simplemente marcamos como anulado en el sistema

            return redirect()->route('pos.void.success', $invoice)->with('success', 'Comprobante anulado correctamente.');
        }

        return redirect()->back()->with('error', 'No se pudo anular el comprobante. Intente nuevamente.');
    }

    /**
     * Muestra la confirmación de anulación
     */
    public function voidSuccess(\App\Models\Invoice $invoice)
    {
        if ($invoice->tax_authority_status !== \App\Models\Invoice::STATUS_VOIDED) {
            return redirect()->route('pos.index');
        }

        return view('pos.void-success', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Lista los comprobantes con opciones de filtrado
     */
    public function invoicesList(Request $request)
    {
        $query = \App\Models\Invoice::query()->with('customer');

        // Filtrar por tipo de comprobante
        if ($request->filled('type')) {
            $query->where('invoice_type', $request->type);
        }

        // Filtrar por estado
        if ($request->filled('status')) {
            $query->where('tax_authority_status', $request->status);
        }

        // Filtrar por rango de fechas
        if ($request->filled('date_from')) {
            $query->whereDate('issue_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('issue_date', '<=', $request->date_to);
        }

        // Ordenar por fecha de emisión descendente (más recientes primero)
        $query->orderBy('issue_date', 'desc')
            ->orderBy('id', 'desc');

        // Paginar resultados
        $invoices = $query->paginate(15);

        return view('pos.invoices-list', [
            'invoices' => $invoices
        ]);
    }
}
