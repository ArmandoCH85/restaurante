<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Productos Vendidos</title>
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
            color: #8b5cf6;
            font-size: 24px;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 8px;
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
        .positive {
            color: #10b981;
        }
        .negative {
            color: #ef4444;
        }
        .category {
            color: #6b7280;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Productos Vendidos</h1>
        <p>Período: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Ventas (S/)</th>
                <th class="text-right">Precio Prom. (S/)</th>
                <th class="text-right">Costo (S/)</th>
                <th class="text-right">Ganancia (S/)</th>
                <th class="text-right">Margen (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $data)
            <tr>
                <td>
                    {{ $data['product_name'] }}
                    <div class="category">{{ $data['category'] }}</div>
                </td>
                <td class="text-right">{{ $data['quantity_sold'] }}</td>
                <td class="text-right">{{ number_format($data['total_sales'], 2) }}</td>
                <td class="text-right">{{ number_format($data['average_price'], 2) }}</td>
                <td class="text-right">{{ number_format($data['total_cost'], 2) }}</td>
                <td class="text-right {{ $data['profit'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($data['profit'], 2) }}
                </td>
                <td class="text-right {{ $data['margin'] >= 0 ? 'positive' : 'negative' }}">
                    {{ number_format($data['margin'], 2) }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Total de productos: {{ count($reportData) }}</p>
    </div>
</body>
</html>
