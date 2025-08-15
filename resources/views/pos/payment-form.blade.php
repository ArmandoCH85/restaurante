<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Pago - Orden #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .payment-container {
            max-width: 900px;
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
            min-width: 120px;
            padding: 15px;
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
            font-size: 24px;
            margin-bottom: 8px;
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
            min-width: 80px;
            padding: 10px;
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
        .payment-history {
            margin-top: 20px;
            border-top: 1px solid #dee2e6;
            padding-top: 20px;
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
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="order-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>Registro de Pago</h2>
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

        @if($remainingBalance > 0)
            <form action="{{ route('pos.payment.process', $order->id) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h4>Método de Pago</h4>
                        <div class="payment-methods">
                            <div class="payment-method active" data-method="cash" onclick="selectPaymentMethod(this, 'cash')">
                                <i class="fas fa-money-bill-wave"></i>
                                <span>Efectivo</span>
                            </div>
                            <div class="payment-method" data-method="credit_card" onclick="selectPaymentMethod(this, 'credit_card')">
                                <i class="fas fa-credit-card"></i>
                                <span>Tarjeta de Crédito</span>
                            </div>
                            <div class="payment-method" data-method="debit_card" onclick="selectPaymentMethod(this, 'debit_card')">
                                <i class="fas fa-credit-card"></i>
                                <span>Tarjeta de Débito</span>
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
                            <input type="number" step="0.01" min="0.01" max="{{ $remainingBalance }}" class="form-control" id="amount" name="amount" value="{{ old('amount', is_numeric($remainingBalance) ? number_format($remainingBalance, 2, '.', '') : '0.00') }}" required>
                        </div>

                        <div class="mb-3">
                            <button type="submit" class="btn btn-primary btn-lg w-100">Registrar Pago</button>
                        </div>
                    </div>

                    <div class="col-md-6">
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
                                <div class="col-6"><strong>Saldo Pendiente:</strong></div>
                                <div class="col-6 text-end"><strong>S/ {{ number_format($remainingBalance, 2) }}</strong></div>
                            </div>
                        </div>

                        @if($order->payments->isNotEmpty())
                            <div class="payment-history">
                                <h4>Historial de Pagos</h4>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>Método</th>
                                                <th>Monto</th>
                                                <th>Referencia</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($order->payments as $payment)
                                                <tr>
                                                    <td>{{ $payment->payment_datetime->format('d/m/Y H:i') }}</td>
                                                    <td>
                                                        @switch($payment->payment_method)
                                                            @case('cash')
                                                                <i class="fas fa-money-bill-wave"></i> Efectivo
                                                                @break
                                                            @case('credit_card')
                                                                <i class="fas fa-credit-card"></i> T. Crédito
                                                                @break
                                                            @case('debit_card')
                                                                <i class="fas fa-credit-card"></i> T. Débito
                                                                @break
                                                            @case('bank_transfer')
                                                                <i class="fas fa-exchange-alt"></i> Transferencia
                                                                @break
                                                            @case('digital_wallet')
                                                                @if(strpos($payment->reference_number, 'Tipo: yape') !== false)
                                                                    <i class="fas fa-mobile-alt"></i> Yape
                                                                @elseif(strpos($payment->reference_number, 'Tipo: plin') !== false)
                                                                    <i class="fas fa-mobile-alt"></i> Plin
                                                                @else
                                                                    <i class="fas fa-mobile-alt"></i> Billetera Digital
                                                                @endif
                                                                @break
                                                            @default
                                                                {{ $payment->payment_method }}
                                                        @endswitch
                                                    </td>
                                                    <td>S/ {{ number_format($payment->amount, 2) }}</td>
                                                    <td>{{ $payment->reference_number }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </form>
        @else
            <div class="alert alert-success">
                <h4 class="alert-heading">¡Pago Completado!</h4>
                <p>Esta orden ha sido pagada completamente.</p>
                <hr>
                <p class="mb-0">
                    <a href="{{ route('pos.invoice.form', $order->id) }}" class="btn btn-primary">Generar Factura</a>
                    <button type="button" class="btn btn-secondary" onclick="window.close()">Cerrar</button>
                </p>
            </div>

            @if($order->payments->isNotEmpty())
                <div class="payment-history">
                    <h4>Historial de Pagos</h4>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Método</th>
                                    <th>Monto</th>
                                    <th>Referencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->payment_datetime->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @switch($payment->payment_method)
                                                @case('cash')
                                                    <i class="fas fa-money-bill-wave"></i> Efectivo
                                                    @break
                                                @case('credit_card')
                                                    <i class="fas fa-credit-card"></i> T. Crédito
                                                    @break
                                                @case('debit_card')
                                                    <i class="fas fa-credit-card"></i> T. Débito
                                                    @break
                                                @case('bank_transfer')
                                                    <i class="fas fa-exchange-alt"></i> Transferencia
                                                    @break
                                                @case('digital_wallet')
                                                    @if(strpos($payment->reference_number, 'Tipo: yape') !== false)
                                                        <i class="fas fa-mobile-alt"></i> Yape
                                                    @elseif(strpos($payment->reference_number, 'Tipo: plin') !== false)
                                                        <i class="fas fa-mobile-alt"></i> Plin
                                                    @else
                                                        <i class="fas fa-mobile-alt"></i> Billetera Digital
                                                    @endif
                                                    @break
                                                @default
                                                    {{ $payment->payment_method }}
                                            @endswitch
                                        </td>
                                        <td>S/ {{ number_format($payment->amount, 2) }}</td>
                                        <td>{{ $payment->reference_number }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        @endif

        <div class="order-details">
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
                            <td class="text-end">S/ {{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>IGV (18%):</strong></td>
                            <td class="text-end">S/ {{ number_format($order->tax, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total:</strong></td>
                            <td class="text-end"><strong>S/ {{ number_format($order->total, 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="mt-4 text-center">
            <button type="button" class="btn btn-secondary" onclick="window.close()">Cerrar</button>
            <a href="{{ route('pos.payment.history', $order->id) }}" class="btn btn-info">Ver Historial de Pagos</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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
        }

        function setAmount(amount) {
            document.getElementById('amount').value = amount.toFixed(2);
        }
    </script>
</body>
</html>
