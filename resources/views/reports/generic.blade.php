<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportTitle }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }
        .header .period {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            text-align: center;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .stat-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .stat-value {
            font-size: 16px;
            color: #0066cc;
            font-weight: bold;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th,
        .data-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #333;
        }
        .data-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .money {
            text-align: right;
            font-weight: bold;
        }
        .center {
            text-align: center;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $reportTitle }}</h1>
        <p class="period">Período: {{ $stats['period'] ?? 'N/A' }}</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total Órdenes</div>
            <div class="stat-value">{{ number_format($stats['total_orders'] ?? 0) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Ventas Totales</div>
            <div class="stat-value">S/ {{ number_format($stats['total_sales'] ?? 0, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Ticket Promedio</div>
            <div class="stat-value">S/ {{ number_format($stats['average_ticket'] ?? 0, 2) }}</div>
        </div>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                @foreach($columns as $column)
                    <th>{{ $column->getLabel() }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($data as $row)
                <tr>
                    @foreach($columns as $column)
                        <td class="{{ str_contains($column->getName(), 'total') || str_contains($column->getName(), 'money') ? 'money' : '' }}">
                            @if(str_contains($column->getName(), 'total') || str_contains($column->getName(), 'sales') || str_contains($column->getName(), 'average'))
                                S/ {{ number_format($row->{$column->getName()} ?? 0, 2) }}
                            @elseif(str_contains($column->getName(), 'order_datetime'))
                                {{ \Carbon\Carbon::parse($row->{$column->getName()})->format('d/m/Y H:i') }}
                            @else
                                {{ $row->{$column->getName()} ?? 'N/A' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="center">No hay datos disponibles para el período seleccionado</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Reporte generado el {{ now()->format('d/m/Y H:i:s') }} | Sistema de Gestión Restaurante</p>
    </div>
</body>
</html>