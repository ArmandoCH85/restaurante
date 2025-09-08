<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Ventas - {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Estilos generales */
        body {
            font-size: 14px;
            line-height: 1.6;
            color: #000;
            background-color: #fff;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #000;
            font-weight: 600;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .bg-white {
            background-color: #fff;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            color: #000;
            padding: 8px;
            border: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
            font-weight: 600;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: 700;
        }

        .font-semibold {
            font-weight: 600;
        }

        .font-medium {
            font-weight: 500;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-lg {
            font-size: 1.125rem;
        }

        .text-xl {
            font-size: 1.25rem;
        }

        .text-2xl {
            font-size: 1.5rem;
        }

        .text-3xl {
            font-size: 1.875rem;
        }

        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-6 { margin-top: 1.5rem; }
        .mt-8 { margin-top: 2rem; }
        .p-4 { padding: 1rem; }
        .p-6 { padding: 1.5rem; }

        .bg-gray-50 {
            background-color: #f8f8f8;
        }

        .bg-blue-50 {
            background-color: #eff6ff;
        }

        .text-blue-800 {
            color: #1e40af;
        }

        .text-indigo-800 {
            color: #3730a3;
        }

        .border {
            border: 1px solid #ccc;
        }

        .rounded {
            border-radius: 0.375rem;
        }

        .grid {
            display: grid;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .gap-4 {
            gap: 1rem;
        }

        .gap-6 {
            gap: 1.5rem;
        }

        .flex {
            display: flex;
        }

        .items-center {
            align-items: center;
        }

        .justify-between {
            justify-content: space-between;
        }

        .section-title {
            background-color: #f0f0f0;
            padding: 10px;
            margin: 20px 0 10px 0;
            border-left: 4px solid #3730a3;
            font-weight: 600;
            font-size: 1.125rem;
        }

        .stat-card {
            background-color: #f8f8f8;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 0.375rem;
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e40af;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #666;
            margin-top: 5px;
        }

        .page-break {
            page-break-before: always;
        }

        @media print {
            .page-break {
                page-break-before: always;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Encabezado -->
        <div class="text-center mb-8">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-blue-50 p-4 rounded">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 text-blue-800" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
            </div>
            <h1 class="text-3xl font-bold mb-2">DASHBOARD DE VENTAS</h1>
            <h2 class="text-xl font-semibold text-indigo-800 mb-3">
                {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}
            </h2>
            <p class="font-medium">{{ config('app.name') }}</p>
            <p class="text-sm mt-2">
                Tipo de Servicio: 
                @if($serviceType === 'all')
                    Todos los servicios
                @elseif($serviceType === 'mesa')
                    🍽️ Mesa
                @elseif($serviceType === 'delivery')
                    🚚 Delivery
                @elseif($serviceType === 'directa')
                    🥡 Venta Directa
                @else
                    {{ ucfirst($serviceType) }}
                @endif
            </p>
            <p class="text-sm">Documento generado el: {{ now()->format('d/m/Y H:i') }}</p>
        </div>

        <!-- Estadísticas Generales -->
        <div class="section-title">📊 ESTADÍSTICAS GENERALES</div>
        <div class="grid grid-cols-3 gap-6 mb-6">
            <div class="stat-card">
                <div class="stat-value">S/ {{ number_format($data['stats']['total_sales'], 2) }}</div>
                <div class="stat-label">Total Ventas</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">{{ number_format($data['stats']['total_orders']) }}</div>
                <div class="stat-label">Total Órdenes</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">S/ {{ number_format($data['stats']['average_ticket'], 2) }}</div>
                <div class="stat-label">Ticket Promedio</div>
            </div>
        </div>

        <!-- Ventas por Tipo de Servicio -->
        <div class="section-title">🏷️ VENTAS POR TIPO DE SERVICIO</div>
        <table class="mb-6">
            <thead>
                <tr>
                    <th>Tipo de Servicio</th>
                    <th class="text-right">Monto</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['stats']['sales_by_type'] as $type => $amount)
                    @php
                        $percentage = $data['stats']['total_sales'] > 0 ? ($amount / $data['stats']['total_sales']) * 100 : 0;
                        $typeLabel = match($type) {
                            'mesa' => '🍽️ Mesa',
                            'delivery' => '🚚 Delivery',
                            'apps' => '📱 Apps',
                            'directa' => '🥡 Venta Directa',
                            default => ucfirst($type),
                        };
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $typeLabel }}</td>
                        <td class="text-right">S/ {{ number_format($amount, 2) }}</td>
                        <td class="text-right">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Top Productos -->
        <div class="section-title">🏆 TOP 10 PRODUCTOS MÁS VENDIDOS</div>
        <table class="mb-6">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Ingresos</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['top_products']->take(10) as $index => $product)
                    <tr>
                        <td class="text-center font-semibold">{{ $index + 1 }}</td>
                        <td class="font-medium">{{ $product->product->name ?? 'Producto eliminado' }}</td>
                        <td class="text-right">{{ number_format($product->total_quantity) }}</td>
                        <td class="text-right">S/ {{ number_format($product->total_revenue, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Métodos de Pago -->
        <div class="section-title">💳 MÉTODOS DE PAGO</div>
        <table class="mb-6">
            <thead>
                <tr>
                    <th>Método de Pago</th>
                    <th class="text-right">Monto</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalPayments = $data['payment_methods']->sum();
                @endphp
                @foreach($data['payment_methods'] as $method => $amount)
                    @php
                        $percentage = $totalPayments > 0 ? ($amount / $totalPayments) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $method }}</td>
                        <td class="text-right">S/ {{ number_format($amount, 2) }}</td>
                        <td class="text-right">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Plataformas de Apps -->
        @if(isset($data['apps_platforms']) && !empty(array_filter($data['apps_platforms'])))
        <div class="section-title">📱 VENTAS POR PLATAFORMA DE APPS</div>
        <table class="mb-6">
            <thead>
                <tr>
                    <th>Plataforma</th>
                    <th class="text-right">Ventas</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $appsTotal = array_sum($data['apps_platforms']);
                @endphp
                @foreach($data['apps_platforms'] as $platform => $amount)
                    @if($amount > 0)
                    @php
                        $percentage = $appsTotal > 0 ? ($amount / $appsTotal) * 100 : 0;
                        $platformLabel = match($platform) {
                            'rappi' => '🛵 Rappi',
                            'bita_express' => '🚚 Bita Express',
                            'didi_food' => '🚗 Didi Food',
                            'pedidos_ya' => '🍕 Pedidos Ya',
                            default => ucfirst($platform),
                        };
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $platformLabel }}</td>
                        <td class="text-right">S/ {{ number_format($amount, 2) }}</td>
                        <td class="text-right">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                    @endif
                @endforeach
                @if($appsTotal > 0)
                <tr class="font-bold bg-gray-50">
                    <td><strong>TOTAL APPS</strong></td>
                    <td class="text-right"><strong>S/ {{ number_format($appsTotal, 2) }}</strong></td>
                    <td class="text-right"><strong>100%</strong></td>
                </tr>
                @endif
            </tbody>
        </table>
        @endif

        <!-- Ventas por Hora -->
        <div class="section-title">⏰ DISTRIBUCIÓN DE VENTAS POR HORA</div>
        <table class="mb-6">
            <thead>
                <tr>
                    <th>Hora</th>
                    <th class="text-right">Ventas</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data['sales_by_hour'] as $hour => $amount)
                    @php
                        $percentage = $data['stats']['total_sales'] > 0 ? ($amount / $data['stats']['total_sales']) * 100 : 0;
                    @endphp
                    <tr>
                        <td class="font-medium">{{ $hour }}</td>
                        <td class="text-right">S/ {{ number_format($amount, 2) }}</td>
                        <td class="text-right">{{ number_format($percentage, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Pie de página -->
        <div class="mt-8 text-center text-sm">
            <p class="font-medium">{{ config('app.name') }}</p>
            <p>Reporte generado automáticamente el {{ now()->format('d/m/Y H:i:s') }}</p>
            <p class="mt-2">Dashboard de Ventas - Período: {{ $startDate->format('d/m/Y') }} al {{ $endDate->format('d/m/Y') }}</p>
        </div>
    </div>
</body>
</html>