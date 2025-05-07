<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cierre de Caja #{{ $cashRegister->id }}</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 10mm;
            line-height: 1.5;
            font-size: 12px;
        }
        h1, h2, h3 {
            margin-top: 0;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .summary {
            margin-top: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 10px;
            color: #666;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .font-bold {
            font-weight: bold;
        }
        .bg-light {
            background-color: #f9f9f9;
        }
        .mt-3 {
            margin-top: 15px;
        }
        @media print {
            body {
                padding: 0;
            }
        }
    </style>
</head>
<body>
    <!-- El botón de impresión ahora está en el modal -->

    <div class="header">
        <div class="logo">
            <h1>Restaurante</h1>
            <p>Sistema de Punto de Venta</p>
        </div>
        <h2 class="text-center">CIERRE DE CAJA #{{ $cashRegister->id }}</h2>
    </div>

    <div>
        <table>
            <tr>
                <th colspan="2">Información General</th>
            </tr>
            <tr>
                <td width="50%"><strong>Fecha de Apertura:</strong></td>
                <td>{{ $cashRegister->opened_at ? $cashRegister->opened_at->format('d/m/Y H:i:s') : 'No registrado' }}</td>
            </tr>
            <tr>
                <td><strong>Apertura realizada por:</strong></td>
                <td>{{ $cashRegister->openedBy ? $cashRegister->openedBy->name : 'No registrado' }}</td>
            </tr>
            <tr>
                <td><strong>Monto Inicial:</strong></td>
                <td>S/ {{ number_format($cashRegister->opening_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Fecha de Cierre:</strong></td>
                <td>{{ $cashRegister->closed_at ? $cashRegister->closed_at->format('d/m/Y H:i:s') : 'No cerrado' }}</td>
            </tr>
            <tr>
                <td><strong>Cierre realizado por:</strong></td>
                <td>{{ $cashRegister->closedBy ? $cashRegister->closedBy->name : 'No cerrado' }}</td>
            </tr>
            <tr>
                <td><strong>Estado:</strong></td>
                <td>{{ $cashRegister->status === 'open' ? 'Abierto' : 'Cerrado' }}</td>
            </tr>
        </table>

        <table class="mt-3">
            <tr>
                <th colspan="2">Resumen de Ventas</th>
            </tr>
            <tr>
                <td width="50%"><strong>Ventas en Efectivo:</strong></td>
                <td>S/ {{ number_format($cashRegister->cash_sales ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Ventas con Tarjeta:</strong></td>
                <td>S/ {{ number_format($cashRegister->card_sales ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Otras Ventas:</strong></td>
                <td>S/ {{ number_format($cashRegister->other_sales ?? 0, 2) }}</td>
            </tr>
            <tr class="font-bold bg-light">
                <td><strong>TOTAL VENTAS:</strong></td>
                <td>S/ {{ number_format($cashRegister->total_sales ?? 0, 2) }}</td>
            </tr>
        </table>

        <table class="mt-3">
            <tr>
                <th colspan="2">Cuadre de Caja</th>
            </tr>
            <tr>
                <td width="50%"><strong>Monto Inicial:</strong></td>
                <td>S/ {{ number_format($cashRegister->opening_amount, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Ventas en Efectivo:</strong></td>
                <td>S/ {{ number_format($cashRegister->cash_sales ?? 0, 2) }}</td>
            </tr>
            <tr class="bg-light">
                <td><strong>Efectivo Esperado:</strong></td>
                <td>S/ {{ number_format($cashRegister->expected_cash ?? 0, 2) }}</td>
            </tr>
            <tr class="font-bold">
                <td><strong>Efectivo Real:</strong></td>
                <td>S/ {{ number_format($cashRegister->actual_cash ?? 0, 2) }}</td>
            </tr>
            <tr class="{{ ($cashRegister->difference ?? 0) < 0 ? 'bg-danger' : (($cashRegister->difference ?? 0) > 0 ? 'bg-warning' : 'bg-success') }}">
                <td><strong>Diferencia:</strong></td>
                <td>S/ {{ number_format($cashRegister->difference ?? 0, 2) }}</td>
            </tr>
        </table>

        @if(!empty($cashRegister->notes))
        <div class="mt-3">
            <h3>Notas:</h3>
            <p>{{ $cashRegister->notes }}</p>
        </div>
        @endif

        @if(count($payments) > 0)
        <h3 class="mt-3">Detalle de Pagos</h3>
        <table>
            <thead>
                <tr>
                    <th>Hora</th>
                    <th>Método</th>
                    <th>Orden</th>
                    <th>Recibido por</th>
                    <th class="text-right">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->payment_datetime ? $payment->payment_datetime->format('H:i:s') : 'No registrado' }}</td>
                    <td>{{ $payment->getPaymentMethodNameAttribute() }}</td>
                    <td>{{ $payment->order->id ?? 'N/A' }}</td>
                    <td>{{ $payment->receiver ? $payment->receiver->name : 'No registrado' }}</td>
                    <td class="text-right">S/ {{ number_format($payment->amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @if(count($paymentsByMethod) > 0)
        <h3 class="mt-3">Resumen por Método de Pago</h3>
        <table>
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($paymentsByMethod as $method => $methodPayments)
                <tr>
                    <td>{{ $method }}</td>
                    <td class="text-right">S/ {{ number_format($totalsByMethod[$method], 2) }}</td>
                </tr>
                @endforeach
                <tr class="font-bold bg-light">
                    <td>TOTAL</td>
                    <td class="text-right">S/ {{ number_format(array_sum($totalsByMethod), 2) }}</td>
                </tr>
            </tbody>
        </table>
        @endif
    </div>

    <div class="footer">
        <p class="text-center">Este documento es comprobante del cierre de caja realizado en la fecha indicada.</p>
        <p class="text-center">Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- El script de impresión automática se ha eliminado ya que ahora se maneja desde el modal -->
</body>
</html>
