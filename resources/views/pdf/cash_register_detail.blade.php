<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Caja #{{ $record->id }}</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        h1 { text-align: center; color: #333; }
        .details, .movements, .orders { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        .total-summary { margin-top: 20px; padding-top: 10px; border-top: 1px solid #ccc; }
        .total-summary p { margin: 5px 0; font-size: 1.1em; }
        .total-summary strong { float: right; }
    </style>
</head>
<body>
    <h1>Reporte de Caja #{{ $record->id }}</h1>

    <div class="details">
        <p><strong>Fecha Apertura:</strong> {{ $record->opening_datetime ? $record->opening_datetime->format('d/m/Y H:i') : 'N/A' }}</p>
        <p><strong>Fecha Cierre:</strong> {{ $record->closing_datetime ? $record->closing_datetime->format('d/m/Y H:i') : 'En Curso' }}</p>
        <p><strong>Usuario:</strong> {{ $record->user->name ?? 'Sin usuario' }}</p>
        <p><strong>Monto Inicial:</strong> S/ {{ number_format($record->opening_amount, 2) }}</p>
        <p><strong>Monto Registrado (Cierre):</strong> S/ {{ number_format($record->closing_amount, 2) }}</p>
        <p><strong>Monto Real (Cierre):</strong> S/ {{ number_format($record->actual_amount, 2) }}</p>
        <p><strong>Diferencia:</strong> S/ {{ number_format($record->actual_amount - $record->closing_amount, 2) }}</p>
    </div>

    <h2>Movimientos de Caja</h2>
    <div class="movements">
        @if($movements->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Fecha/Hora</th>
                    <th>Tipo</th>
                    <th class="text-right">Monto</th>
                    <th>Motivo</th>
                    <th>Aprobado por</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movements as $movement)
                <tr>
                    <td>{{ $movement->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                    <td>{{ ucfirst($movement->movement_type) }}</td>
                    <td class="text-right">{{ $movement->movement_type === 'ingreso' ? '+' : '-' }} S/ {{ number_format($movement->amount, 2) }}</td>
                    <td>{{ $movement->reason ?? 'N/A' }}</td>
                    <td>{{ $movement->approvedByUser->name ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No hay movimientos registrados.</p>
        @endif
    </div>

    <h2>Ventas Registradas</h2>
    <div class="orders">
        @if($orders->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Fecha/Hora</th>
                    <th>N° Orden</th>
                    <th class="text-right">Total</th>
                    <th>Método Pago</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orders as $order)
                <tr>
                    <td>{{ $order->order_datetime?->format('d/m/Y H:i') ?? 'N/A' }}</td>
                    <td>#{{ $order->id }}</td>
                    <td class="text-right">S/ {{ number_format($order->total, 2) }}</td>
                    <td>
                        @foreach($order->payments as $payment)
                            {{ $payment->payment_method }}@if(!$loop->last), @endif
                        @endforeach
                    </td>
                    <td>{{ $order->user->name ?? 'N/A' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p>No hay ventas registradas.</p>
        @endif
    </div>

    <div class="total-summary">
        <p>Total Ventas: <strong>S/ {{ number_format($orders->sum('total'), 2) }}</strong></p>
        <p>Total Ingresos (Movimientos): <strong>S/ {{ number_format($movements->where('movement_type', 'ingreso')->sum('amount'), 2) }}</strong></p>
        <p>Total Egresos (Movimientos): <strong>S/ {{ number_format($movements->where('movement_type', 'egreso')->sum('amount'), 2) }}</strong></p>
        <hr>
        <p>Monto Esperado en Caja (Apertura + Ventas + Ingresos - Egresos): 
            <strong>S/ {{ number_format($record->opening_amount + $orders->sum('total') + $movements->where('movement_type', 'ingreso')->sum('amount') - $movements->where('movement_type', 'egreso')->sum('amount'), 2) }}</strong>
        </p>
    </div>

</body>
</html>
