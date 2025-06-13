<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Caja</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
        }
        .header p {
            font-size: 14px;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f5f5f5;
        }
        .totals {
            margin-top: 20px;
        }
        .totals p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Caja</h1>
        <p>Período: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Fecha Apertura</th>
                <th>Fecha Cierre</th>
                <th>Usuario</th>
                <th>Monto Inicial</th>
                <th>Monto Final</th>
                <th>Total Movimientos</th>
                <th>Total Ventas</th>
                <th>Diferencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cashData as $register)
            @php
                $totalMovements = $register->cashMovements->sum('amount');
                $totalSales = $register->orders->sum('total');
                $expected = $register->initial_amount + $totalMovements + $totalSales;
                $difference = $register->final_amount - $expected;
            @endphp
            <tr>
                <td>{{ $register->opened_at->format('d/m/Y H:i') }}</td>
                <td>{{ $register->closed_at ? $register->closed_at->format('d/m/Y H:i') : 'En curso' }}</td>
                <td>{{ $register->user->name }}</td>
                <td>S/ {{ number_format($register->initial_amount, 2) }}</td>
                <td>S/ {{ number_format($register->final_amount, 2) }}</td>
                <td>S/ {{ number_format($totalMovements, 2) }}</td>
                <td>S/ {{ number_format($totalSales, 2) }}</td>
                <td>S/ {{ number_format($difference, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <h2>Totales</h2>
        <p><strong>Monto Inicial Total:</strong> S/ {{ number_format($totalInitial, 2) }}</p>
        <p><strong>Monto Final Total:</strong> S/ {{ number_format($totalFinal, 2) }}</p>
        <p><strong>Total Movimientos:</strong> S/ {{ number_format($totalMovements, 2) }}</p>
        <p><strong>Total Ventas:</strong> S/ {{ number_format($totalSales, 2) }}</p>
    </div>
</body>
</html>
