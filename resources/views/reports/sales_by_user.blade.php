<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas por Usuario</title>
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
        <h1>Reporte de Ventas por Usuario</h1>
        <p>Período: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Usuario</th>
                <th>N° Ventas</th>
                <th>Total Ventas (S/)</th>
                <th>Ticket Promedio (S/)</th>
                <th>% del Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($salesData as $data)
            @php
                $percentage = $totalSales > 0 ? ($data->total_sales / $totalSales) * 100 : 0;
            @endphp
            <tr>
                <td>{{ $data->user_name }}</td>
                <td>{{ $data->total_orders }}</td>
                <td>S/ {{ number_format($data->total_sales, 2) }}</td>
                <td>S/ {{ number_format($data->average_ticket, 2) }}</td>
                <td>{{ number_format($percentage, 2) }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <h2>Totales</h2>
        <p><strong>Total Ventas:</strong> S/ {{ number_format($totalSales, 2) }}</p>
        <p><strong>Total Órdenes:</strong> {{ $totalOrders }}</p>
        <p><strong>Ticket Promedio:</strong> S/ {{ number_format($averageTicket, 2) }}</p>
    </div>
</body>
</html>
