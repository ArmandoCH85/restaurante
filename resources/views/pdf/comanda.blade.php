<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comanda #{{ $order->id }}</title>
    <style>
        /* Estilos optimizados para impresora térmica de 80mm */
        @page {
            margin: 5mm;
        }
        body {
            font-family: 'monospace', sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
            width: 70mm; /* Ancho aproximado para papel de 80mm */
        }
        .container {
            width: 100%;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header .info {
            font-size: 9pt;
        }
        hr {
            border: 0;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .item-table {
            width: 100%;
        }
        .item-table th, .item-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .item-table .col-qty { width: 15%; text-align: left; }
        .item-table .col-desc { width: 85%; }
        .notes {
            margin-top: 10px;
            font-size: 9pt;
        }
        .notes p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>COMANDA</h1>
            <hr>
            <div class="info">
                @if($order->table)
                    <strong>Mesa: {{ $order->table->number }}</strong><br>
                @else
                    {{-- ✅ Mostrar nombre del cliente solo para venta directa --}}
                    @if(isset($customerNameForComanda) && !empty($customerNameForComanda))
                        <strong>Cliente: {{ $customerNameForComanda }}</strong><br>
                    @endif
                    <strong>VENTA DIRECTA</strong><br>
                @endif
                <strong>Orden:</strong> #{{ $order->id }}<br>
                <strong>Mesero:</strong> {{ $order->employee->name }}<br>
                <strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i:s') }}
            </div>
            <hr>
        </div>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comanda #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'monospace', sans-serif;
            font-size: 10pt;
            margin: 0;
            padding: 0;
            width: 80mm;
        }
        .ticket-container {
            width: 100%;
            padding: 3mm;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 14pt;
            font-weight: bold;
        }
        .header p {
            margin: 3px 0;
            font-size: 9pt;
        }
        hr {
            border: 0;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .info-table {
            width: 100%;
            font-size: 9pt;
            margin-bottom: 10px;
        }
        .info-table td {
            padding: 2px 0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th {
            text-align: left;
            border-bottom: 1px solid #000;
            padding: 3px 0;
            font-size: 9pt;
        }
        .items-table td {
            padding: 4px 0;
            font-size: 10pt;
            vertical-align: top;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 9pt;
        }
        .type-title {
            font-weight: bold;
            font-size: 14pt;
            text-align: center;
            text-transform: uppercase;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            @php
                $nombreComercial = \App\Models\CompanyConfig::getNombreComercial();
                $razonSocial = \App\Models\CompanyConfig::getRazonSocial();
            @endphp
            <h1>{{ $nombreComercial ?: $razonSocial }}</h1>
            <p>{{ \App\Models\CompanyConfig::getDireccion() }}</p>
            <p>COMANDA - COCINA</p>
        </div>

        <hr>

        <table class="info-table">
            <tr>
                <td><strong>Mesa:</strong> {{ $order->table?->number ?? 'VENTA DIRECTA' }}</td>
                <td style="text-align: right;"><strong>Orden:</strong> #{{ $order->id }}</td>
            </tr>
            <tr>
                <td><strong>Atendido:</strong> {{ $order->employee?->name ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @if(!empty($customerNameForComanda) && $order->table_id === null)
            <tr>
                <td colspan="2"><strong>Cliente:</strong> {{ $customerNameForComanda }}</td>
            </tr>
            @elseif($order->customer)
            <tr>
                <td colspan="2"><strong>Cliente:</strong> {{ $order->customer->name }}</td>
            </tr>
            @elseif(isset($customer))
            <tr>
                <td colspan="2"><strong>Cliente:</strong> {{ $customer?->name ?? 'Cliente general' }}</td>
            </tr>
            @endif
        </table>

        <div class="type-title">COMANDA</div>

        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 15%">Cant</th>
                    <th style="width: 85%">Producto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetails ?? [] as $detail)
                <tr>
                    <td>{{ $detail->quantity }}</td>
                    <td><strong>{{ $detail->product?->name ?? 'Producto no disponible' }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>

        <div class="footer">
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
        <table class="item-table">
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-desc">Producto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetails as $detail)
                <tr>
                    <td class="col-qty">{{ $detail->quantity }}</td>
                    <td class="col-desc">{{ $detail->product->name }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($order->notes)
        <hr>
        <div class="notes">
            <p><strong>Notas:</strong></p>
            <p>{{ $order->notes }}</p>
        </div>
        @endif
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
