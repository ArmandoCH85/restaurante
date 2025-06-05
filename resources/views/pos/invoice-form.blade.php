<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Comprobante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
            font-size: 16px;
            color: #1f2937;
        }
        .print-button {
            padding: 10px 20px;
            background-color: #1a56db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }
        .print-button:hover {
            background-color: #1e429f;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .payment-method {
            cursor: pointer;
            transition: all 0.2s;
            border-width: 2px;
        }
        .payment-method:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .payment-method.selected {
            border-color: #1a56db;
            background-color: #eef2ff;
            box-shadow: 0 0 0 2px rgba(26, 86, 219, 0.3);
        }
        .amount-suggestion {
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }
        .amount-suggestion:hover {
            background-color: #eef2ff;
            transform: translateY(-1px);
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
        }
        h2 {
            font-size: 22px;
        }
        input, select, textarea {
            font-size: 16px;
            padding: 10px;
        }
        button {
            font-size: 16px;
            padding: 12px 20px;
        }
    </style>
</head>
<body class="p-4">
    <div class="max-w-6xl mx-auto bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Generar Comprobante de Venta</h1>
            <div class="flex space-x-3">
                <a href="{{ url('/pos') }}" class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition-all duration-200 font-medium text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Volver al POS
                </a>
                <a href="{{ url('/admin') }}" class="flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition-all duration-200 font-medium text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Ir al Escritorio
                </a>
            </div>
        </div>

        <form action="{{ route('pos.invoice.generate', $order->id) }}" method="post">
            @csrf
            <input type="hidden" name="customer_id" id="customer_id" value="">

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Panel izquierdo: Tipo de comprobante y datos del cliente -->
                <div>
                    <div class="mb-4">
                        <h2 class="text-lg font-semibold mb-3">1. Tipo de Comprobante</h2>
                        <div class="bg-gray-50 p-4 rounded-md">
                            <div class="space-y-2">
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="invoice_type" value="sales_note" class="mr-2" checked onchange="toggleRucValidation(); updateNextNumber();">
                                    <div>
                                        <span class="font-medium">Nota de Venta</span>
                                        <p class="text-sm text-gray-500">Documento interno, no reportable a SUNAT</p>
                                    </div>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="invoice_type" value="receipt" class="mr-2" onchange="toggleRucValidation(); updateNextNumber();">
                                    <div>
                                        <span class="font-medium">Boleta de Venta</span>
                                        <p class="text-sm text-gray-500">Para clientes sin RUC (incluye IGV)</p>
                                    </div>
                                </label>
                                <label class="flex items-center cursor-pointer">
                                    <input type="radio" name="invoice_type" value="invoice" class="mr-2" onchange="toggleRucValidation(); updateNextNumber();">
                                    <div>
                                        <span class="font-medium">Factura</span>
                                        <p class="text-sm text-gray-500">Requiere RUC válido (incluye IGV)</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="bg-blue-50 p-3 rounded-md border border-blue-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-600">Próximo comprobante:</span>
                                <span class="text-sm font-bold text-blue-700" id="next-document-number">{{ $nextNumbers['sales_note'] }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 client-data">
                        <h2 class="text-lg font-semibold mb-3">2. Datos del Cliente</h2>
                        <div class="flex justify-between mb-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="client_document" id="document_label">Documento</label>
                                <div class="flex gap-1">
                                    <select id="document_type" class="py-2 px-2 border border-gray-300 rounded-md bg-gray-50" onchange="updateDocumentLabel()">
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                    </select>
                                    <div class="flex-1 relative">
                                        <input type="text" id="client_document" name="client_document" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <button type="button" class="absolute right-2 top-2 text-blue-600" onclick="searchCustomer()">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                <div id="ruc_message" class="hidden mt-1 text-sm text-red-600">El RUC debe tener 11 dígitos</div>
                                <div id="search_message" class="hidden mt-1 text-sm"></div>
                            </div>
                            <div>
                                <button type="button" class="px-3 py-2 bg-green-600 text-white rounded-md text-sm h-full mt-6" onclick="showNewCustomerForm()">
                                    Nuevo Cliente
                                </button>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="client_name">Nombre/Razón Social</label>
                                <input type="text" id="client_name" name="client_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1" for="client_address">Dirección</label>
                                <input type="text" id="client_address" name="client_address" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                            </div>
                        </div>
                    </div>

                    <!-- Modal para nuevo cliente -->
                    <div id="newCustomerModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-lg p-6 max-w-md w-full">
                            <h3 class="text-lg font-semibold mb-4">Registrar Nuevo Cliente</h3>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Documento</label>
                                    <select id="new_document_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Número de Documento</label>
                                    <input type="text" id="new_document_number" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre/Razón Social</label>
                                    <input type="text" id="new_name" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Dirección</label>
                                    <input type="text" id="new_address" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono (opcional)</label>
                                    <input type="text" id="new_phone" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email (opcional)</label>
                                    <input type="email" id="new_email" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                </div>
                            </div>
                            <div class="flex justify-end mt-4 gap-2">
                                <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md" onclick="closeNewCustomerModal()">
                                    Cancelar
                                </button>
                                <button type="button" class="px-4 py-2 bg-blue-600 text-white rounded-md" onclick="saveNewCustomer()">
                                    Guardar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="text-lg font-semibold mb-3">3. Forma de Pago</h2>
                        <div class="grid grid-cols-3 gap-2 mb-3">
                            <div class="payment-method border border-gray-300 rounded-md p-3 text-center" data-method="cash" onclick="selectPaymentMethod('cash')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="text-sm">Efectivo</span>
                            </div>
                            <div class="payment-method border border-gray-300 rounded-md p-3 text-center" data-method="card" onclick="selectPaymentMethod('card')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                </svg>
                                <span class="text-sm">Tarjeta</span>
                            </div>
                            <div class="payment-method border border-gray-300 rounded-md p-3 text-center" data-method="transfer" onclick="selectPaymentMethod('transfer')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                                <span class="text-sm">Transferencia</span>
                            </div>
                            <div class="payment-method border border-gray-300 rounded-md p-3 text-center" data-method="yape" onclick="selectPaymentMethod('yape')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm">Yape</span>
                            </div>
                            <div class="payment-method border border-gray-300 rounded-md p-3 text-center" data-method="plin" onclick="selectPaymentMethod('plin')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="text-sm">Plin</span>
                            </div>
                            <div class="payment-method border border-gray-300 rounded-md p-3 text-center" data-method="multiple" onclick="selectPaymentMethod('multiple')">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mx-auto mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="text-sm">Pago Mixto</span>
                            </div>
                        </div>
                        <input type="hidden" name="payment_method" id="payment_method" value="cash">

                        <!-- Opciones de efectivo -->
                        <div id="cash_options" class="border border-gray-200 rounded-md p-3 mb-3">
                            <div class="mb-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Monto recibido</label>
                                <div class="flex gap-2 mb-2 flex-wrap">
                                    <div class="amount-suggestion px-3 py-1 bg-gray-100 rounded text-sm" onclick="setAmount({{ $order->total }})">Exacto</div>
                                    <div class="amount-suggestion px-3 py-1 bg-gray-100 rounded text-sm" onclick="setAmount(10)">S/10</div>
                                    <div class="amount-suggestion px-3 py-1 bg-gray-100 rounded text-sm" onclick="setAmount(20)">S/20</div>
                                    <div class="amount-suggestion px-3 py-1 bg-gray-100 rounded text-sm" onclick="setAmount(50)">S/50</div>
                                    <div class="amount-suggestion px-3 py-1 bg-gray-100 rounded text-sm" onclick="setAmount(100)">S/100</div>
                                    <div class="amount-suggestion px-3 py-1 bg-gray-100 rounded text-sm" onclick="setAmount(200)">S/200</div>
                                </div>
                                <div class="flex gap-2 items-center">
                                    <input type="number" name="payment_amount" id="payment_amount" min="{{ $order->total }}" step="0.10" value="{{ $order->total }}"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md" onchange="calculateChange()">
                                </div>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span>Vuelto:</span>
                                <span id="change_amount" class="font-bold">S/ 0.00</span>
                            </div>
                        </div>

                        <!-- Opciones de pago mixto -->
                        <div id="multiple_options" class="border border-gray-200 rounded-md p-3 mb-3 hidden">
                            <div class="mb-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">División de pago</label>
                                <div class="space-y-2" id="payment_splits">
                                    <div class="flex gap-2 items-center">
                                        <select name="split_methods[]" class="px-2 py-1 border border-gray-300 rounded">
                                            <option value="cash">Efectivo</option>
                                            <option value="card">Tarjeta</option>
                                            <option value="transfer">Transferencia</option>
                                            <option value="yape">Yape</option>
                                            <option value="plin">Plin</option>
                                        </select>
                                        <input type="number" name="split_amounts[]" class="w-full px-2 py-1 border border-gray-300 rounded split-amount"
                                            step="0.10" min="0" placeholder="Monto" onchange="updateSplitTotal()">
                                    </div>
                                </div>
                                <button type="button" onclick="addPaymentSplit()" class="mt-2 text-sm text-blue-600 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    Añadir método de pago
                                </button>
                            </div>
                            <div class="flex justify-between items-center text-sm font-medium pt-2 border-t">
                                <span>Total dividido:</span>
                                <span id="split_total" class="font-bold">S/ 0.00</span>
                            </div>
                            <div class="flex justify-between items-center text-sm font-medium pt-1">
                                <span>Total a pagar:</span>
                                <span id="total_to_pay" class="font-bold">S/ {{ number_format($order->total, 2) }}</span>
                            </div>
                            <div id="split_error" class="hidden text-sm text-red-600 mt-1">
                                El total dividido debe ser igual al total a pagar
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h2 class="text-lg font-semibold mb-3">4. Descuento</h2>
                        <div class="flex items-center gap-2">
                            <input type="number" name="discount_percent" id="discount_percent" min="0" max="100" step="0.01" value="0"
                                class="w-20 px-3 py-2 border border-gray-300 rounded-md" oninput="calculateTotal()">
                            <span class="mx-1">%</span>
                            <div class="flex-1">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-500">Aplicado al total</span>
                                    <span class="text-sm font-medium">-S/ <span id="discount_amount">0.00</span></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel derecho: Productos y totales -->
                <div>
                    <h2 class="text-lg font-semibold mb-3">Detalle de Productos</h2>
                    <div class="border rounded-md overflow-hidden mb-4">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                                    <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Cant.</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Precio</th>
                                    <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->orderDetails as $detail)
                                    <tr>
                                        <td class="px-3 py-2 text-sm text-gray-900">
                                            {{ $detail->product->name }}
                                        </td>
                                        <td class="px-3 py-2 text-sm text-center text-gray-900">
                                            {{ $detail->quantity }}
                                        </td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-900">
                                            S/ {{ number_format($detail->unit_price, 2) }}
                                        </td>
                                        <td class="px-3 py-2 text-sm text-right text-gray-900">
                                            S/ {{ number_format($detail->quantity * $detail->unit_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-md mb-6">
                        <div class="flex justify-between py-1">
                            <span>Subtotal:</span>
                            <span>S/ {{ number_format($order->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-1 text-red-600 discount-row hidden">
                            <span>Descuento: <span id="discount-percent-display">0</span>%</span>
                            <span>- S/ <span id="discount-amount-display">0.00</span></span>
                        </div>
                        <div class="flex justify-between py-1 tax-row">
                            <span>I.G.V. (18%):</span>
                            <span id="tax-amount">S/ {{ number_format($order->subtotal * 0.18, 2) }}</span>
                        </div>
                        <div class="flex justify-between py-1 font-bold">
                            <span>Total:</span>
                            <span id="total-amount">S/ {{ number_format($order->total, 2) }}</span>
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <button type="button" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md" onclick="window.close()">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md" id="submit-button">
                            Generar Comprobante
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        // Variables globales para cálculos
        const subtotal = {{ $order->subtotal }};
        let total = {{ $order->total }};
        const nextNumbers = {
            sales_note: "{{ $nextNumbers['sales_note'] }}",
            receipt: "{{ $nextNumbers['receipt'] }}",
            invoice: "{{ $nextNumbers['invoice'] }}"
        };
        const genericCustomerId = {{ $genericCustomer->id }};

        function toggleRucValidation() {
            const invoiceType = document.querySelector('input[name="invoice_type"]:checked').value;
            const documentLabel = document.getElementById('document_label');
            const rucMessage = document.getElementById('ruc_message');
            const taxRow = document.querySelector('.tax-row');
            const clientData = document.querySelector('.client-data');
            const documentTypeSelect = document.getElementById('document_type');

            if (invoiceType === 'invoice') {
                documentLabel.textContent = 'RUC';
                rucMessage.classList.remove('hidden');
                documentTypeSelect.value = 'RUC';
                documentTypeSelect.disabled = true;
                clientData.classList.remove('hidden');
            } else if (invoiceType === 'receipt') {
                documentLabel.textContent = 'DNI';
                rucMessage.classList.add('hidden');
                documentTypeSelect.value = 'DNI';
                documentTypeSelect.disabled = true;
                clientData.classList.remove('hidden');
            } else {
                documentLabel.textContent = 'Documento (Opcional)';
                rucMessage.classList.add('hidden');
                documentTypeSelect.disabled = false;

                // Para notas de venta, usar cliente genérico
                useGenericCustomer();
            }

            if (invoiceType === 'sales_note') {
                taxRow.classList.add('hidden');
            } else {
                taxRow.classList.remove('hidden');
            }

            calculateTotal();
        }

        function useGenericCustomer() {
            document.getElementById('customer_id').value = genericCustomerId;
            document.getElementById('client_name').value = "{{ $genericCustomer->name }}";
            document.getElementById('client_document').value = "{{ $genericCustomer->document_number }}";
            document.getElementById('client_address').value = "{{ $genericCustomer->address }}";
        }

        function updateDocumentLabel() {
            const documentType = document.getElementById('document_type').value;
            const documentLabel = document.getElementById('document_label');
            const rucMessage = document.getElementById('ruc_message');

            if (documentType === 'RUC') {
                documentLabel.textContent = 'RUC';
                rucMessage.classList.remove('hidden');
            } else {
                documentLabel.textContent = 'DNI';
                rucMessage.classList.add('hidden');
            }
        }

        function searchCustomer() {
            const documentNumber = document.getElementById('client_document').value;
            const documentType = document.getElementById('document_type').value;
            const searchMessage = document.getElementById('search_message');

            if (!documentNumber) {
                searchMessage.textContent = 'Ingrese un número de documento';
                searchMessage.classList.remove('hidden', 'text-green-600');
                searchMessage.classList.add('text-red-600');
                return;
            }

            // Validar documento según el tipo
            if ((documentType === 'RUC' && documentNumber.length !== 11) ||
                (documentType === 'DNI' && documentNumber.length !== 8)) {
                searchMessage.textContent = documentType === 'RUC' ?
                    'El RUC debe tener 11 dígitos' : 'El DNI debe tener 8 dígitos';
                searchMessage.classList.remove('hidden', 'text-green-600');
                searchMessage.classList.add('text-red-600');
                return;
            }

            // Realizar la búsqueda
            fetch(`{{ route('pos.customers.find') }}?document=${documentNumber}&type=${documentType}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Cliente encontrado
                        document.getElementById('customer_id').value = data.customer.id;
                        document.getElementById('client_name').value = data.customer.name;
                        document.getElementById('client_address').value = data.customer.address || '';

                        searchMessage.textContent = 'Cliente encontrado';
                        searchMessage.classList.remove('hidden', 'text-red-600');
                        searchMessage.classList.add('text-green-600');
                    } else {
                        // Cliente no encontrado
                        document.getElementById('customer_id').value = '';
                        document.getElementById('client_name').value = '';
                        document.getElementById('client_address').value = '';

                        searchMessage.textContent = 'Cliente no encontrado. Puede registrarlo como nuevo.';
                        searchMessage.classList.remove('hidden', 'text-green-600');
                        searchMessage.classList.add('text-red-600');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchMessage.textContent = 'Error al buscar cliente';
                    searchMessage.classList.remove('hidden', 'text-green-600');
                    searchMessage.classList.add('text-red-600');
                });
        }

        function showNewCustomerForm() {
            // Prellenar con datos ya ingresados si hay
            const documentNumber = document.getElementById('client_document').value;
            const documentType = document.getElementById('document_type').value;
            const name = document.getElementById('client_name').value;
            const address = document.getElementById('client_address').value;

            document.getElementById('new_document_type').value = documentType;
            document.getElementById('new_document_number').value = documentNumber;
            document.getElementById('new_name').value = name;
            document.getElementById('new_address').value = address;

            document.getElementById('newCustomerModal').classList.remove('hidden');
        }

        function closeNewCustomerModal() {
            document.getElementById('newCustomerModal').classList.add('hidden');
        }

        function saveNewCustomer() {
            const docType = document.getElementById('new_document_type').value;
            const docNumber = document.getElementById('new_document_number').value;
            const name = document.getElementById('new_name').value;
            const address = document.getElementById('new_address').value;
            const phone = document.getElementById('new_phone').value;
            const email = document.getElementById('new_email').value;

            // Validaciones básicas
            if (!docNumber || !name) {
                alert('El documento y nombre son obligatorios');
                return;
            }

            // Validar formato según tipo
            if ((docType === 'RUC' && docNumber.length !== 11) ||
                (docType === 'DNI' && docNumber.length !== 8)) {
                alert(docType === 'RUC' ?
                    'El RUC debe tener 11 dígitos' : 'El DNI debe tener 8 dígitos');
                return;
            }

            // Crear el cliente
            fetch('{{ route('pos.customers.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    document_type: docType,
                    document_number: docNumber,
                    name: name,
                    address: address,
                    phone: phone,
                    email: email
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Asignar datos al formulario principal
                    document.getElementById('customer_id').value = data.customer.id;
                    document.getElementById('document_type').value = data.customer.document_type;
                    document.getElementById('client_document').value = data.customer.document_number;
                    document.getElementById('client_name').value = data.customer.name;
                    document.getElementById('client_address').value = data.customer.address || '';

                    // Mostrar mensaje y cerrar modal
                    const searchMessage = document.getElementById('search_message');
                    searchMessage.textContent = 'Cliente registrado exitosamente';
                    searchMessage.classList.remove('hidden', 'text-red-600');
                    searchMessage.classList.add('text-green-600');

                    closeNewCustomerModal();
                } else {
                    alert('Error al registrar cliente: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al registrar cliente');
            });
        }

        function updateNextNumber() {
            const invoiceType = document.querySelector('input[name="invoice_type"]:checked').value;
            document.getElementById('next-document-number').textContent = nextNumbers[invoiceType];
        }

        function calculateTotal() {
            const invoiceType = document.querySelector('input[name="invoice_type"]:checked').value;
            const discountPercent = parseFloat(document.getElementById('discount_percent').value) || 0;

            // Actualizar los elementos de descuento
            document.getElementById('discount-percent-display').textContent = discountPercent.toFixed(2);

            const discountAmount = (subtotal * discountPercent / 100);
            document.getElementById('discount_amount').textContent = discountAmount.toFixed(2);
            document.getElementById('discount-amount-display').textContent = discountAmount.toFixed(2);

            if (discountPercent > 0) {
                document.querySelector('.discount-row').classList.remove('hidden');
            } else {
                document.querySelector('.discount-row').classList.add('hidden');
            }

            // CORRECCIÓN: Los precios YA INCLUYEN IGV
            const totalWithIgvAfterDiscount = subtotal - discountAmount;

            let finalTotal = totalWithIgvAfterDiscount;
            if (invoiceType !== 'sales_note') {
                // Calcular IGV incluido en el precio
                const taxAmount = totalWithIgvAfterDiscount / 1.18 * 0.18;
                document.getElementById('tax-amount').textContent = 'S/ ' + taxAmount.toFixed(2);
                // El total no cambia, ya incluye IGV
                finalTotal = totalWithIgvAfterDiscount;
            }

            // Actualizar total
            total = finalTotal;
            document.getElementById('total-amount').textContent = 'S/ ' + finalTotal.toFixed(2);
            document.getElementById('total_to_pay').textContent = 'S/ ' + finalTotal.toFixed(2);

            // Actualizar monto mínimo del pago
            document.getElementById('payment_amount').min = finalTotal;
            if (parseFloat(document.getElementById('payment_amount').value) < finalTotal) {
                document.getElementById('payment_amount').value = finalTotal;
            }

            calculateChange();
            updateSplitTotal();
        }

        function selectPaymentMethod(method) {
            // Actualizar método seleccionado
            document.getElementById('payment_method').value = method;

            // Resaltar el método seleccionado
            document.querySelectorAll('.payment-method').forEach(el => {
                if (el.dataset.method === method) {
                    el.classList.add('selected');
                } else {
                    el.classList.remove('selected');
                }
            });

            // Mostrar/ocultar opciones específicas
            if (method === 'cash') {
                document.getElementById('cash_options').classList.remove('hidden');
                document.getElementById('multiple_options').classList.add('hidden');
            } else if (method === 'multiple') {
                document.getElementById('cash_options').classList.add('hidden');
                document.getElementById('multiple_options').classList.remove('hidden');
                updateSplitTotal();
            } else {
                document.getElementById('cash_options').classList.add('hidden');
                document.getElementById('multiple_options').classList.add('hidden');
            }
        }

        function setAmount(amount) {
            document.getElementById('payment_amount').value = amount;
            calculateChange();
        }

        function calculateChange() {
            const paymentAmount = parseFloat(document.getElementById('payment_amount').value) || 0;
            const changeAmount = paymentAmount - total;

            document.getElementById('change_amount').textContent = 'S/ ' + Math.max(0, changeAmount).toFixed(2);
        }

        function addPaymentSplit() {
            const container = document.getElementById('payment_splits');
            const newSplit = document.createElement('div');
            newSplit.className = 'flex gap-2 items-center';
            newSplit.innerHTML = `
                <select name="split_methods[]" class="px-2 py-1 border border-gray-300 rounded">
                    <option value="cash">Efectivo</option>
                    <option value="card">Tarjeta</option>
                    <option value="transfer">Transferencia</option>
                    <option value="yape">Yape</option>
                    <option value="plin">Plin</option>
                </select>
                <input type="number" name="split_amounts[]" class="w-full px-2 py-1 border border-gray-300 rounded split-amount"
                    step="0.10" min="0" placeholder="Monto" onchange="updateSplitTotal()">
                <button type="button" class="text-red-500" onclick="this.parentNode.remove(); updateSplitTotal();">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            `;
            container.appendChild(newSplit);
        }

        function updateSplitTotal() {
            if (document.getElementById('payment_method').value !== 'multiple') return;

            let splitTotal = 0;
            document.querySelectorAll('.split-amount').forEach(input => {
                splitTotal += parseFloat(input.value) || 0;
            });

            document.getElementById('split_total').textContent = 'S/ ' + splitTotal.toFixed(2);

            // Validar que el total dividido sea igual al total a pagar
            const submitButton = document.getElementById('submit-button');
            const splitError = document.getElementById('split_error');

            if (Math.abs(splitTotal - total) < 0.01) {
                submitButton.disabled = false;
                splitError.classList.add('hidden');
            } else {
                submitButton.disabled = true;
                splitError.classList.remove('hidden');
            }
        }

        // Inicializar al cargar
        document.addEventListener('DOMContentLoaded', function() {
            toggleRucValidation();
            calculateTotal();
            selectPaymentMethod('cash');
            calculateChange();
            updateNextNumber();
            document.querySelector('head').insertAdjacentHTML('beforeend',
                '<meta name="csrf-token" content="{{ csrf_token() }}">');
        });

        /* Funciones para el formulario */
        document.addEventListener("DOMContentLoaded", function() {
            updateDocumentLabel();
            selectPaymentMethod('cash');
            updateNextNumber();

            // Interceptar envío del formulario para abrir en el marco actual
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(response => response.text())
                    .then(html => {
                        // Reemplazar contenido del formulario con el comprobante generado
                        document.body.innerHTML = html;
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                });
            }
        });
    </script>
</body>
</html>
