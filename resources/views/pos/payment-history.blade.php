<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pagos - Orden #{{ $order->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .history-container {
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
        .payment-card {
            border-left: 4px solid #0d6efd;
            margin-bottom: 15px;
            transition: all 0.2s;
        }
        .payment-card:hover {
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .payment-card.voided {
            border-left-color: #dc3545;
            background-color: #f8d7da;
            opacity: 0.8;
        }
        .payment-method-icon {
            font-size: 24px;
            margin-right: 10px;
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
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="history-container">
        <div class="order-header">
            <div class="row">
                <div class="col-md-6">
                    <h2>Historial de Pagos</h2>
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
                    <p class="mb-0">Pagado: S/ {{ number_format($order->getTotalPaid(), 2) }}</p>
                    <p class="mb-0">Pendiente: S/ {{ number_format($order->getRemainingBalance(), 2) }}</p>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success no-print">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger no-print">
                {{ session('error') }}
            </div>
        @endif

        <div class="balance-info {{ $order->getRemainingBalance() <= 0 ? 'paid' : 'pending' }}">
            <h4>Resumen</h4>
            <div class="row">
                <div class="col-6">Total de la Orden:</div>
                <div class="col-6 text-end">S/ {{ number_format($order->total, 2) }}</div>
            </div>
            <div class="row">
                <div class="col-6">Total Pagado:</div>
                <div class="col-6 text-end">S/ {{ number_format($order->getTotalPaid(), 2) }}</div>
            </div>
            <div class="row">
                <div class="col-6"><strong>Saldo Pendiente:</strong></div>
                <div class="col-6 text-end"><strong>S/ {{ number_format($order->getRemainingBalance(), 2) }}</strong></div>
            </div>
        </div>

        <h4 class="mt-4">Pagos Registrados</h4>
        
        @if($payments->isEmpty())
            <div class="alert alert-info">
                No hay pagos registrados para esta orden.
            </div>
        @else
            @foreach($payments as $payment)
                <div class="card payment-card {{ $payment->voided_at ? 'voided' : '' }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5 class="card-title">
                                    @switch($payment->payment_method)
                                        @case('cash')
                                            <i class="fas fa-money-bill-wave payment-method-icon text-success"></i> Efectivo
                                            @break
                                        @case('credit_card')
                                            <i class="fas fa-credit-card payment-method-icon text-primary"></i> Tarjeta de Crédito
                                            @break
                                        @case('debit_card')
                                            <i class="fas fa-credit-card payment-method-icon text-info"></i> Tarjeta de Débito
                                            @break
                                        @case('transfer')
                                            <i class="fas fa-exchange-alt payment-method-icon text-warning"></i> Transferencia
                                            @break
                                        @case('yape')
                                            <i class="fas fa-mobile-alt payment-method-icon text-danger"></i> Yape
                                            @break
                                        @case('plin')
                                            <i class="fas fa-mobile-alt payment-method-icon text-primary"></i> Plin
                                            @break
                                        @default
                                            <i class="fas fa-money-bill-alt payment-method-icon"></i> {{ $payment->payment_method }}
                                    @endswitch
                                </h5>
                                <p class="card-text">
                                    <strong>Fecha:</strong> {{ $payment->payment_datetime->format('d/m/Y H:i') }}<br>
                                    @if($payment->reference_number)
                                        <strong>Referencia:</strong> {{ $payment->reference_number }}<br>
                                    @endif
                                    <strong>Recibido por:</strong> {{ $payment->receivedBy->name ?? 'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <h4>S/ {{ number_format($payment->amount, 2) }}</h4>
                            </div>
                            <div class="col-md-2 text-end">
                                @if($payment->voided_at)
                                    <span class="badge bg-danger">Anulado</span>
                                    <small class="d-block mt-1">{{ $payment->voided_at->format('d/m/Y H:i') }}</small>
                                    <small class="d-block">{{ $payment->void_reason }}</small>
                                @else
                                    <form action="{{ route('pos.payment.void', $payment->id) }}" method="POST" class="no-print" onsubmit="return confirm('¿Está seguro de anular este pago?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-times"></i> Anular
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif

        <div class="mt-4 text-center no-print">
            <button type="button" class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <button type="button" class="btn btn-secondary" onclick="window.close()">Cerrar</button>
            
            @if($order->getRemainingBalance() > 0)
                <a href="{{ route('pos.payment.form', $order->id) }}" class="btn btn-success">Registrar Nuevo Pago</a>
            @else
                <a href="{{ route('pos.invoice.form', $order->id) }}" class="btn btn-info">Generar Factura</a>
            @endif
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
