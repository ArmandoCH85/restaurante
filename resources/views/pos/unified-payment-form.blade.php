<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pago y Facturación - Orden #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .unified-container {
            max-width: 1100px;
            margin: 20px auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .order-header {
            background-color: #f1f8ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .payment-methods {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .payment-method {
            flex: 1;
            min-width: 100px;
            padding: 12px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .payment-method:hover {
            background-color: #f8f9fa;
        }
        .payment-method.active {
            background-color: #e7f5ff;
            border-color: #4dabf7;
        }
        .payment-method i {
            font-size: 20px;
            margin-bottom: 6px;
            display: block;
        }
        .amount-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .amount-button {
            flex: 1;
            min-width: 70px;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .amount-button:hover {
            background-color: #f8f9fa;
        }
        .order-details {
            margin-top: 20px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
        }
        .product-row {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .balance-info {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .balance-info.paid {
            background-color: #d4edda;
        }
        .balance-info.pending {
            background-color: #fff3cd;
        }
        .invoice-type {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        .invoice-type-option {
            flex: 1;
            min-width: 120px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .invoice-type-option:hover {
            background-color: #f8f9fa;
        }
        .invoice-type-option.active {
            background-color: #e7f5ff;
            border-color: #4dabf7;
        }
        .invoice-type-option i {
            font-size: 24px;
            margin-bottom: 8px;
            display: block;
        }
        .client-data {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .next-document {
            background-color: #e7f5ff;
            padding: 10px;
            border-radius: 8px;
            margin-top: 10px;
            border: 1px solid #b8daff;
        }
    </style>
</head>
<body>
    <div class="unified-container">
        <div class="order-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>Pago y Facturación</h2>
                    <p class="mb-0">Orden #{{ $order->id }}</p>
                    <p class="mb-0">Fecha: {{ $order->order_datetime->format('d/m/Y H:i') }}</p>
                    @if($order->table)
                        <p class="mb-0">Mesa: {{ $order->table->number }}</p>
                    @endif
                    @if($order->customer)
                        <p class="mb-0">Cliente: {{ $order->customer->name }}</p>
                    @endif
                </div>
                <div class="col-md-6 text-end">
                    <h3>Total: S/ {{ number_format($order->total, 2) }}</h3>
                    <p class="mb-0">Pagado: S/ {{ number_format($totalPaid, 2) }}</p>
                    <p class="mb-0">Pendiente: S/ {{ number_format($remainingBalance, 2) }}</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('pos.unified.process', $order->id) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <h4>1. Método de Pago</h4>
                    <div class="payment-methods">
                        <div class="payment-method active" data-method="cash" onclick="selectPaymentMethod(this, 'cash')">
                            <i class="fas fa-money-bill-wave"></i>
                            <span>Efectivo</span>
                        </div>
                        <div class="payment-method" data-method="credit_card" onclick="selectPaymentMethod(this, 'credit_card')">
                            <i class="fas fa-credit-card"></i>
                            <span>T. Crédito</span>
                        </div>
                        <div class="payment-method" data-method="debit_card" onclick="selectPaymentMethod(this, 'debit_card')">
                            <i class="fas fa-credit-card"></i>
                            <span>T. Débito</span>
                        </div>
                        <div class="payment-method" data-method="bank_transfer" onclick="selectPaymentMethod(this, 'bank_transfer')">
                            <i class="fas fa-exchange-alt"></i>
                            <span>Transferencia</span>
                        </div>
                        <div class="payment-method" data-method="digital_wallet" data-wallet-type="yape" onclick="selectPaymentMethod(this, 'digital_wallet', 'yape')">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Yape</span>
                        </div>
                        <div class="payment-method" data-method="digital_wallet" data-wallet-type="plin" onclick="selectPaymentMethod(this, 'digital_wallet', 'plin')">
                            <i class="fas fa-mobile-alt"></i>
                            <span>Plin</span>
                        </div>
                    </div>

                    <input type="hidden" name="payment_method" id="payment_method" value="cash">

                    <div id="reference_container" style="display: none;" class="mb-3">
                        <label for="reference_number" class="form-label">Número de Referencia</label>
                        <input type="text" class="form-control" id="reference_number" name="reference_number" placeholder="Número de operación">
                    </div>

                    <h4>Monto a Pagar</h4>
                    <div class="amount-buttons">
                        <div class="amount-button" onclick="setAmount(10)">S/ 10</div>
                        <div class="amount-button" onclick="setAmount(20)">S/ 20</div>
                        <div class="amount-button" onclick="setAmount(50)">S/ 50</div>
                        <div class="amount-button" onclick="setAmount(100)">S/ 100</div>
                        <div class="amount-button" onclick="setAmount({{ $remainingBalance }})">S/ {{ number_format($remainingBalance, 2) }}</div>
                    </div>

                    <div class="mb-3">
                        <label for="amount" class="form-label">Monto</label>
                        <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" value="{{ $remainingBalance }}" required oninput="calculateChange()">
                    </div>

                    <div id="change_container" class="p-3 mb-3 rounded-md" style="display: none; background-color: #d1e7dd;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">Cambio a devolver:</span>
                            <span class="fs-5 fw-bold text-success">S/ <span id="change_amount">0.00</span></span>
                        </div>
                    </div>

                    <div id="additional_payments" class="mb-3">
                        <!-- Aquí se agregarán dinámicamente más formas de pago -->
                    </div>

                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="add_payment_btn">
                            <i class="fas fa-plus"></i> Agregar otra forma de pago
                        </button>
                    </div>

                    <div class="balance-info {{ $remainingBalance <= 0 ? 'paid' : 'pending' }}">
                        <h4>Resumen</h4>
                        <div class="row">
                            <div class="col-6">Total de la Orden:</div>
                            <div class="col-6 text-end">S/ {{ number_format($order->total, 2) }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6">Total Pagado:</div>
                            <div class="col-6 text-end">S/ {{ number_format($totalPaid, 2) }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <strong>
                                    @if($remainingBalance < 0)
                                        Vuelto/Cambio:
                                    @else
                                        Saldo Pendiente:
                                    @endif
                                </strong>
                            </div>
                            <div class="col-6 text-end">
                                <strong>
                                    @if($remainingBalance < 0)
                                        S/ {{ number_format(abs($remainingBalance), 2) }}
                                    @else
                                        S/ {{ number_format($remainingBalance, 2) }}
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <h4>2. Tipo de Comprobante</h4>
                    <div class="invoice-type">
                        <div class="invoice-type-option active" data-type="sales_note" onclick="selectInvoiceType(this, 'sales_note')">
                            <i class="fas fa-receipt"></i>
                            <span>Nota de Venta</span>
                        </div>
                        <div class="invoice-type-option" data-type="receipt" onclick="selectInvoiceType(this, 'receipt')">
                            <i class="fas fa-file-invoice"></i>
                            <span>Boleta</span>
                        </div>
                        <div class="invoice-type-option" data-type="invoice" onclick="selectInvoiceType(this, 'invoice')">
                            <i class="fas fa-file-invoice-dollar"></i>
                            <span>Factura</span>
                        </div>
                    </div>

                    <input type="hidden" name="invoice_type" id="invoice_type" value="sales_note">

                    <div class="next-document">
                        <div class="d-flex justify-content-between">
                            <span>Próximo comprobante:</span>
                            <span id="next-document-number">{{ $nextNumbers['sales_note'] }}</span>
                        </div>
                    </div>

                    <div class="mt-3 client-data">
                        <h5>3. Datos del Cliente</h5>

                        <!-- Campo oculto para el ID del cliente -->
                        <input type="hidden" id="customer_id" name="customer_id" value="{{ $genericCustomer->id }}">

                        <!-- Cliente genérico para Nota de Venta -->
                        <div id="generic_customer" class="mb-3" style="display: block;">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Cliente Genérico</h6>
                                    <p class="card-text">{{ $genericCustomer?->name ?? 'Cliente Genérico' }}</p>
                                    <p class="card-text">Documento: {{ $genericCustomer?->document_number ?? '00000000' }}</p>
                                    <p class="small text-muted">Para Nota de Venta se utiliza el cliente genérico por defecto</p>
                                </div>
                            </div>
                        </div>

                        <!-- Búsqueda de cliente (solo para Boleta y Factura) -->
                        <div id="customer_search_container" class="mb-3" style="display: none;">
                            <div class="input-group">
                                <input type="text" class="form-control" id="customer_search" placeholder="Buscar por nombre o documento" autocomplete="off">
                                <button class="btn btn-outline-secondary" type="button" id="search_customer_btn">
                                    <i class="fas fa-search"></i>
                                </button>
                                <button class="btn btn-outline-primary" type="button" id="new_customer_btn">
                                    <i class="fas fa-plus"></i> Nuevo
                                </button>
                            </div>
                            <div id="search_results" class="mt-2" style="display: none; max-height: 200px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 0.25rem;"></div>
                            <div id="search_message" class="mt-1 small" style="display: none;"></div>
                        </div>

                        <!-- Detalles del cliente seleccionado -->
                        <div id="customer_details" class="mb-3" style="display: none;">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title">Detalles del Cliente</h6>
                                    <p class="card-text" id="customer_name"></p>
                                    <p class="card-text" id="customer_document"></p>
                                    <p class="card-text" id="customer_address"></p>
                                    <p class="card-text" id="customer_phone"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal para nuevo cliente -->
                    <div class="modal fade" id="newCustomerModal" tabindex="-1" aria-labelledby="newCustomerModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="newCustomerModalLabel">Nuevo Cliente</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label for="new_document_type" class="form-label">Tipo de Documento</label>
                                        <select class="form-select" id="new_document_type">
                                            <option value="DNI">DNI</option>
                                            <option value="RUC">RUC</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_document_number" class="form-label">Número de Documento</label>
                                        <input type="text" class="form-control" id="new_document_number" maxlength="15">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_name" class="form-label">Nombre / Razón Social</label>
                                        <input type="text" class="form-control" id="new_name" maxlength="255">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_phone" class="form-label">Teléfono</label>
                                        <input type="text" class="form-control" id="new_phone" maxlength="20">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_address" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="new_address" maxlength="255">
                                    </div>
                                    <div class="mb-3">
                                        <label for="new_email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="new_email" maxlength="255">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="save_customer_btn">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">Procesar Pago y Generar Comprobante</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="mt-4 order-details">
            <h4>Detalle de la Orden</h4>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-end">Precio</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->orderDetails as $detail)
                            <tr class="product-row">
                                <td>{{ $detail->product->name }}</td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-end">S/ {{ number_format($detail->unit_price, 2) }}</td>
                                <td class="text-end">S/ {{ number_format($detail->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                            <td class="text-end" id="display-subtotal">S/ {{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>IGV (18%):</strong></td>
                            <td class="text-end" id="display-tax">S/ {{ number_format($order->subtotal * 0.18, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end" id="display-total"><strong>S/ {{ number_format($order->subtotal + ($order->subtotal * 0.18), 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-4 text-center">
            <button type="button" class="btn btn-secondary" onclick="window.close()">Cerrar</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Datos de clientes para mostrar detalles
        const customers = @json($customers);

        // Datos de próximos números de comprobantes
        const nextNumbers = @json($nextNumbers);

        function selectPaymentMethod(element, method, walletType) {
            // Remover clase active de todos los métodos
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('active');
            });

            // Añadir clase active al método seleccionado
            element.classList.add('active');

            // Actualizar el valor del campo oculto
            document.getElementById('payment_method').value = method;

            // Si hay un campo oculto para el tipo de billetera, actualizarlo
            if (walletType) {
                // Crear o actualizar el campo oculto para el tipo de billetera
                let walletTypeInput = document.getElementById('wallet_type');
                if (!walletTypeInput) {
                    walletTypeInput = document.createElement('input');
                    walletTypeInput.type = 'hidden';
                    walletTypeInput.id = 'wallet_type';
                    walletTypeInput.name = 'wallet_type';
                    document.querySelector('form').appendChild(walletTypeInput);
                }
                walletTypeInput.value = walletType;
            }

            // Mostrar/ocultar campo de referencia según el método
            if (method === 'cash') {
                document.getElementById('reference_container').style.display = 'none';
            } else {
                document.getElementById('reference_container').style.display = 'block';
            }

            // Recalcular el cambio cuando cambia el método de pago
            calculateChange();
        }

        function selectInvoiceType(element, type) {
            // Remover clase active de todos los tipos
            document.querySelectorAll('.invoice-type-option').forEach(el => {
                el.classList.remove('active');
            });

            // Añadir clase active al tipo seleccionado
            element.classList.add('active');

            // Actualizar el valor del campo oculto
            document.getElementById('invoice_type').value = type;

            // Actualizar el número de documento mostrado
            document.getElementById('next-document-number').textContent = nextNumbers[type];

            // Mostrar/ocultar opciones según el tipo de comprobante
            if (type === 'sales_note') {
                // Para nota de venta, seleccionar cliente genérico y ocultar búsqueda
                document.getElementById('customer_id').value = {{ $genericCustomer?->id ?? 0 }};
                document.getElementById('generic_customer').style.display = 'block';
                document.getElementById('customer_search_container').style.display = 'none';
                document.getElementById('customer_details').style.display = 'none';

                // Limpiar campo de búsqueda
                document.getElementById('customer_search').value = '';
                document.getElementById('search_results').style.display = 'none';
                document.getElementById('search_message').style.display = 'none';
            } else {
                // Para boleta o factura, mostrar opciones de búsqueda
                document.getElementById('generic_customer').style.display = 'none';
                document.getElementById('customer_search_container').style.display = 'block';

                // Si ya hay un cliente seleccionado que no es el genérico, mostrar sus detalles
                const customerId = document.getElementById('customer_id').value;
                if (customerId != {{ $genericCustomer?->id ?? 0 }}) {
                    updateCustomerDetails();
                    document.getElementById('customer_details').style.display = 'block';
                } else {
                    // Si no hay cliente seleccionado o es el genérico, limpiar y ocultar detalles
                    document.getElementById('customer_details').style.display = 'none';
                    document.getElementById('customer_search').value = '';
                }
            }
        }

        function setAmount(amount) {
            document.getElementById('amount').value = amount.toFixed(2);
            calculateChange();
        }

        function calculateChange() {
            const amountInput = document.getElementById('amount');
            const remainingBalance = {{ $remainingBalance }};
            const paymentMethod = document.getElementById('payment_method').value;

            // Solo calcular cambio si el método de pago es efectivo
            if (paymentMethod === 'cash') {
                const receivedAmount = parseFloat(amountInput.value) || 0;
                const changeAmount = Math.max(0, receivedAmount - remainingBalance).toFixed(2);

                // Mostrar u ocultar el contenedor de cambio según corresponda
                const changeContainer = document.getElementById('change_container');
                const changeAmountElement = document.getElementById('change_amount');

                if (receivedAmount > remainingBalance) {
                    changeAmountElement.textContent = changeAmount;
                    changeContainer.style.display = 'block';
                } else {
                    changeContainer.style.display = 'none';
                }
            } else {
                // Si no es efectivo, ocultar el contenedor de cambio
                document.getElementById('change_container').style.display = 'none';
            }
        }

        function updateCustomerDetails() {
            const customerId = document.getElementById('customer_id').value;
            const customer = customers.find(c => c.id == customerId);

            if (customer) {
                document.getElementById('customer_name').textContent = 'Nombre: ' + customer.name;
                document.getElementById('customer_document').textContent = 'Documento: ' + (customer.document_type || 'DNI') + ' ' + customer.document_number;
                document.getElementById('customer_address').textContent = 'Dirección: ' + (customer.address || 'No especificada');
                document.getElementById('customer_details').style.display = 'block';
            } else {
                document.getElementById('customer_details').style.display = 'none';
            }
        }

        // Manejo de múltiples formas de pago
        let paymentCounter = 0;

        document.getElementById('add_payment_btn').addEventListener('click', function() {
            paymentCounter++;

            const paymentDiv = document.createElement('div');
            paymentDiv.className = 'card mb-2 additional-payment';
            paymentDiv.id = `payment_${paymentCounter}`;

            paymentDiv.innerHTML = `
                <div class="card-body">
                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Forma de pago adicional</h6>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePayment(${paymentCounter})">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Método de pago</label>
                                <select class="form-select" name="additional_payment_method_${paymentCounter}" required>
                                    <option value="cash">Efectivo</option>
                                    <option value="credit_card">Tarjeta de Crédito</option>
                                    <option value="debit_card">Tarjeta de Débito</option>
                                    <option value="bank_transfer">Transferencia</option>
                                    <option value="digital_wallet" data-wallet-type="yape">Yape</option>
                                    <option value="digital_wallet" data-wallet-type="plin">Plin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label">Monto</label>
                                <input type="number" step="0.01" min="0.01" class="form-control additional-amount"
                                       name="additional_amount_${paymentCounter}" required>
                            </div>
                        </div>
                    </div>
                    <div class="mb-2 reference-container" style="display: none;">
                        <label class="form-label">Número de Referencia</label>
                        <input type="text" class="form-control" name="additional_reference_${paymentCounter}">
                    </div>
                    <input type="hidden" name="additional_wallet_type_${paymentCounter}" value="">
                </div>
            `;

            document.getElementById('additional_payments').appendChild(paymentDiv);

            // Agregar event listeners para los nuevos elementos
            const methodSelect = paymentDiv.querySelector('select');
            methodSelect.addEventListener('change', function() {
                const referenceContainer = paymentDiv.querySelector('.reference-container');
                const walletTypeInput = paymentDiv.querySelector(`input[name="additional_wallet_type_${paymentCounter}"]`);

                if (this.value === 'cash') {
                    referenceContainer.style.display = 'none';
                    walletTypeInput.value = '';
                } else {
                    referenceContainer.style.display = 'block';

                    // Si es billetera digital, obtener el tipo
                    if (this.value === 'digital_wallet') {
                        const option = this.options[this.selectedIndex];
                        walletTypeInput.value = option.getAttribute('data-wallet-type') || '';
                    } else {
                        walletTypeInput.value = '';
                    }
                }
            });

            // Actualizar el contador de formas de pago
            const existingCounter = document.querySelector('input[name="payment_count"]');
            if (existingCounter) {
                existingCounter.value = paymentCounter;
            } else {
                document.querySelector('form').insertAdjacentHTML('beforeend',
                    `<input type="hidden" name="payment_count" value="${paymentCounter}">`);
            }
        });

        function removePayment(id) {
            const paymentDiv = document.getElementById(`payment_${id}`);
            if (paymentDiv) {
                paymentDiv.remove();
            }
        }



        // Búsqueda de clientes
        document.getElementById('search_customer_btn').addEventListener('click', searchCustomers);
        document.getElementById('customer_search').addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                searchCustomers();
            }
        });

        // Abrir modal para nuevo cliente
        document.getElementById('new_customer_btn').addEventListener('click', function() {
            // Limpiar campos del formulario
            document.getElementById('new_document_type').value = 'DNI';
            document.getElementById('new_document_number').value = '';
            document.getElementById('new_name').value = '';
            document.getElementById('new_phone').value = '';
            document.getElementById('new_address').value = '';
            document.getElementById('new_email').value = '';

            // Mostrar modal
            const modal = new bootstrap.Modal(document.getElementById('newCustomerModal'));
            modal.show();
        });

        // Guardar nuevo cliente
        document.getElementById('save_customer_btn').addEventListener('click', saveNewCustomer);

        // Inicializar la interfaz según el tipo de comprobante seleccionado
        const initialInvoiceType = document.getElementById('invoice_type').value;
        if (initialInvoiceType === 'sales_note') {
            // Para nota de venta, mostrar cliente genérico y ocultar búsqueda
            document.getElementById('generic_customer').style.display = 'block';
            document.getElementById('customer_search_container').style.display = 'none';
            document.getElementById('customer_details').style.display = 'none';
        } else {
            // Para boleta o factura, mostrar opciones de búsqueda
            document.getElementById('generic_customer').style.display = 'none';
            document.getElementById('customer_search_container').style.display = 'block';

            // Si hay un cliente seleccionado que no es el genérico, mostrar sus detalles
            const customerId = document.getElementById('customer_id').value;
            if (customerId != {{ $genericCustomer?->id ?? 0 }}) {
                updateCustomerDetails();
                document.getElementById('customer_details').style.display = 'block';
            }
        }

        // Inicializar el cálculo del cambio
        calculateChange();

        // Función para buscar clientes
        function searchCustomers() {
            const searchTerm = document.getElementById('customer_search').value.trim();
            const searchResults = document.getElementById('search_results');
            const searchMessage = document.getElementById('search_message');

            if (searchTerm.length < 3) {
                searchMessage.textContent = 'Ingrese al menos 3 caracteres para buscar';
                searchMessage.style.display = 'block';
                searchMessage.style.color = '#dc3545';
                searchResults.style.display = 'none';
                return;
            }

            // Mostrar indicador de carga
            searchMessage.textContent = 'Buscando...';
            searchMessage.style.display = 'block';
            searchMessage.style.color = '#0d6efd';

            // Realizar búsqueda
            fetch(`/pos/customers/search?term=${encodeURIComponent(searchTerm)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.customers.length > 0) {
                        // Mostrar resultados
                        searchResults.innerHTML = '';
                        data.customers.forEach(customer => {
                            const customerElement = document.createElement('div');
                            customerElement.className = 'p-2 border-bottom customer-result';
                            customerElement.style.cursor = 'pointer';
                            customerElement.innerHTML = `
                                <div><strong>${customer.name}</strong></div>
                                <div class="small text-muted">${customer.document_type}: ${customer.document_number}</div>
                            `;
                            customerElement.addEventListener('click', function() {
                                selectCustomer(customer);
                            });
                            searchResults.appendChild(customerElement);
                        });

                        searchResults.style.display = 'block';
                        searchMessage.style.display = 'none';
                    } else {
                        // No se encontraron resultados
                        searchResults.style.display = 'none';
                        searchMessage.textContent = 'No se encontraron clientes. Puede crear uno nuevo.';
                        searchMessage.style.display = 'block';
                        searchMessage.style.color = '#dc3545';
                    }
                })
                .catch(error => {
                    console.error('Error al buscar clientes:', error);
                    searchResults.style.display = 'none';
                    searchMessage.textContent = 'Error al buscar clientes. Intente nuevamente.';
                    searchMessage.style.display = 'block';
                    searchMessage.style.color = '#dc3545';
                });
        }

        // Función para seleccionar un cliente
        function selectCustomer(customer) {
            // Actualizar campo oculto con el ID del cliente
            document.getElementById('customer_id').value = customer.id;

            // Actualizar campo de búsqueda
            document.getElementById('customer_search').value = customer.name;

            // Ocultar resultados y mensaje
            document.getElementById('search_results').style.display = 'none';
            document.getElementById('search_message').style.display = 'none';

            // Actualizar detalles del cliente
            document.getElementById('customer_name').textContent = 'Nombre: ' + customer.name;
            document.getElementById('customer_document').textContent = 'Documento: ' + customer.document_type + ' ' + customer.document_number;
            document.getElementById('customer_address').textContent = 'Dirección: ' + (customer.address || 'No especificada');
            document.getElementById('customer_phone').textContent = 'Teléfono: ' + (customer.phone || 'No especificado');

            // Mostrar detalles
            document.getElementById('customer_details').style.display = 'block';
        }

        // Función para guardar un nuevo cliente
        function saveNewCustomer() {
            const documentType = document.getElementById('new_document_type').value;
            const documentNumber = document.getElementById('new_document_number').value;
            const name = document.getElementById('new_name').value;
            const phone = document.getElementById('new_phone').value;
            const address = document.getElementById('new_address').value;
            const email = document.getElementById('new_email').value;

            // Validar campos requeridos
            if (!documentNumber || !name) {
                alert('El número de documento y el nombre son obligatorios');
                return;
            }

            // Validar formato del documento según el tipo
            if (documentType === 'DNI' && documentNumber.length !== 8) {
                alert('El DNI debe tener exactamente 8 dígitos');
                return;
            }

            if (documentType === 'RUC' && documentNumber.length !== 11) {
                alert('El RUC debe tener exactamente 11 dígitos');
                return;
            }

            // Validar que solo contenga números
            if (!/^\d+$/.test(documentNumber)) {
                alert('El número de documento solo debe contener números');
                return;
            }

            // Deshabilitar botón para evitar múltiples envíos
            const saveButton = document.getElementById('save_customer_btn');
            saveButton.disabled = true;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';

            // Enviar datos al servidor
            fetch('/pos/customers/store', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    document_type: documentType,
                    document_number: documentNumber,
                    name: name,
                    phone: phone,
                    address: address,
                    email: email
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cerrar modal
                    bootstrap.Modal.getInstance(document.getElementById('newCustomerModal')).hide();

                    // Seleccionar el cliente recién creado o existente
                    selectCustomer(data.customer);

                    // Mostrar mensaje de éxito
                    alert(data.message || 'Cliente guardado correctamente');
                } else {
                    // Mostrar mensaje de error detallado
                    let errorMessage = data.message || 'Error desconocido';

                    // Si hay errores de validación específicos, mostrarlos
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat();
                        errorMessage = errorList.join('\n');
                    }

                    alert('Error al guardar el cliente:\n' + errorMessage);
                }
            })
            .catch(error => {
                console.error('Error al guardar el cliente:', error);
                alert('Error de conexión. Intente nuevamente.');
            })
            .finally(() => {
                // Restaurar botón
                saveButton.disabled = false;
                saveButton.innerHTML = 'Guardar';
            });
        }
    </script>
</body>
</html>
