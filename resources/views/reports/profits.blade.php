<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ganancias</title>
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
            color: #10b981;
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
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-item .value {
            font-size: 20px;
            font-weight: bold;
        }
        .summary-item .label {
            font-size: 12px;
            color: #6b7280;
        }
        .sales .value {
            color: #2563eb;
        }
        .costs .value {
            color: #ef4444;
        }
        .profit .value {
            color: #10b981;
        }
        .margin .value {
            color: #8b5cf6;
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
            border-top: 2px solid #10b981;
        }
        .positive {
            color: #10b981;
        }
        .negative {
            color: #ef4444;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Ganancias</h1>
        <p>Período: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
    </div>
    
    <div class="summary">
        <h2>Resumen</h2>
        <div class="summary-grid">
            <div class="summary-item sales">
                <div class="value">S/ {{ number_format($totalSales, 2) }}</div>
                <div class="label">Ventas Totales</div>
            </div>
            <div class="summary-item costs">
                <div class="value">S/ {{ number_format($totalCosts, 2) }}</div>
                <div class="label">Costos Totales</div>
            </div>
            <div class="summary-item profit">
                <div class="value">S/ {{ number_format($totalProfit, 2) }}</div>
                <div class="label">Ganancia Total</div>
            </div>
            <div class="summary-item margin">
                <div class="value">{{ number_format($totalMargin, 2) }}%</div>
                <div class="label">Margen Promedio</div>
            </div>
        </div>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th class="text-right">Ventas (S/)</th>
                <th class="text-right">Costos (S/)</th>
                <th class="text-right">Ganancia (S/)</th>
                <th class="text-right">Margen (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $data)
            <tr>
                <td>{{ \Carbon\Carbon::parse($data['date'])->format('d/m/Y') }}</td>
                <td class="text-right">{{ number_format($data['sales'], 2) }}</td>
                <td class="text-right">{{ number_format($data['costs'], 2) }}</td>
                <td class="text-right {{ $data['profit'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($data['profit'], 2) }}
                </td>
                <td class="text-right {{ $data['margin'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($data['margin'], 2) }}%
                </td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="totals">
            <tr>
                <td>TOTALES</td>
                <td class="text-right">{{ number_format($totalSales, 2) }}</td>
                <td class="text-right">{{ number_format($totalCosts, 2) }}</td>
                <td class="text-right {{ $totalProfit >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($totalProfit, 2) }}
                </td>
                <td class="text-right {{ $totalMargin >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($totalMargin, 2) }}%
                </td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>
