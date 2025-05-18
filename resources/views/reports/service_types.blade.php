<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas por Tipo de Servicio</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            margin: 0;
            color: #f59e0b;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f3f4f6;
            border-radius: 5px;
        }
        .summary h2 {
            margin-top: 0;
            font-size: 18px;
            color: #1f2937;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .value {
            font-size: 20px;
            font-weight: bold;
            color: #f59e0b;
        }
        .summary-item .label {
            font-size: 12px;
            color: #6b7280;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
        }
        .totals {
            margin-top: 20px;
            font-weight: bold;
        }
        .totals td {
            border-top: 2px solid #f59e0b;
        }
        .chart-container {
            margin: 20px 0;
            text-align: center;
        }
        .bar-chart {
            display: flex;
            height: 200px;
            align-items: flex-end;
            justify-content: space-around;
            margin-top: 20px;
        }
        .bar {
            width: 60px;
            background-color: #f59e0b;
            margin: 0 10px;
            position: relative;
            border-radius: 4px 4px 0 0;
        }
        .bar-label {
            position: absolute;
            bottom: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
        }
        .bar-value {
            position: absolute;
            top: -25px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ventas por Tipo de Servicio</h1>
        <p>Período: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
    </div>
    
    <div class="summary">
        <h2>Resumen</h2>
        <div class="summary-grid">
            <div class="summary-item">
                <div class="value">S/ {{ number_format($totalSales, 2) }}</div>
                <div class="label">Ventas Totales</div>
            </div>
            <div class="summary-item">
                <div class="value">{{ $totalOrders }}</div>
                <div class="label">Órdenes Totales</div>
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Tipo de Servicio</th>
                <th class="text-right">Ventas (S/)</th>
                <th class="text-right">Órdenes</th>
                <th class="text-right">Ticket Promedio (S/)</th>
                <th class="text-right">% del Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $data)
            <tr>
                <td>{{ $data['service_type_name'] }}</td>
                <td class="text-right">{{ number_format($data['total'], 2) }}</td>
                <td class="text-right">{{ $data['count'] }}</td>
                <td class="text-right">{{ number_format($data['average'], 2) }}</td>
                <td class="text-right">
                    {{ number_format(($data['total'] / $totalSales) * 100, 2) }}%
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="totals">
            <tr>
                <td>TOTALES</td>
                <td class="text-right">{{ number_format($totalSales, 2) }}</td>
                <td class="text-right">{{ $totalOrders }}</td>
                <td class="text-right">
                    {{ number_format($totalSales / $totalOrders, 2) }}
                </td>
                <td class="text-right">100.00%</td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
