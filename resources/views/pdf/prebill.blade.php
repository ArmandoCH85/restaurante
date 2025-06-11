<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pre-Cuenta #{{ $order->id }}</title>
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
        .header, .footer {
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
        }
        .header p, .footer p {
            margin: 2px 0;
            font-size: 9pt;
        }
        hr {
            border: 0;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .info-table, .items-table {
            width: 100%;
        }
        .info-table td {
            padding: 1px 0;
            font-size: 9pt;
        }
        .items-table th, .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .col-qty { width: 15%; text-align: left; }
        .col-desc { width: 55%; }
        .col-price, .col-total { width: 15%; text-align: right; }
        .totals {
            margin-top: 10px;
        }
        .totals .row .col-label, .totals .row .col-value {
            font-size: 10pt;
        }
        .totals .row .col-value {
            text-align: right;
        }
        .totals .total-final .col-label, .totals .total-final .col-value {
            font-size: 12pt;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>PRE-CUENTA</h1>
            <p>{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'RESTAURANTE EJEMPLO' }}</p>
            <p>RUC: {{ \App\Models\CompanyConfig::getRuc() ?? '20123456789' }}</p>
            <p>{{ \App\Models\CompanyConfig::getDireccion() ?? 'Av. Ejemplo 123, Ciudad' }}</p>
            @if(\App\Models\CompanyConfig::getTelefono())
                <p>Tel: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
            @endif
        </div>
        <hr>
        <table class="info-table">
            <tr>
                <td><strong>Mesa:</strong> {{ $order->table ? $order->table->number : 'N/A' }}</td>
                <td style="text-align: right;"><strong>Orden:</strong> #{{ $order->id }}</td>
            </tr>
            <tr>
                <td><strong>Mesero:</strong> {{ $order->employee->name }}</td>
                <td style="text-align: right;">{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
            </tr>
        </table>
        <hr>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-desc">Producto</th>
                    <th class="col-price">P.U.</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetails as $detail)
                <tr>
                    <td class="col-qty">{{ $detail->quantity }}</td>
                    <td class="col-desc">{{ $detail->product->name }}</td>
                    <td class="col-price">{{ number_format($detail->unit_price, 2) }}</td>
                    <td class="col-total">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        <div class="totals">
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">SUBTOTAL:</span>
                <span class="col-value">S/ {{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">IGV (18%):</span>
                <span class="col-value">S/ {{ number_format($order->tax, 2) }}</span>
            </div>
            <div class="row total-final" style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span class="col-label">TOTAL:</span>
                <span class="col-value">S/ {{ number_format($order->total, 2) }}</span>
            </div>
        </div>
        <hr>
        <div class="footer">
            <p>ESTE DOCUMENTO NO ES UN COMPROBANTE DE PAGO.</p>
            <p>Gracias por su visita.</p>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pre-Cuenta #{{ $order->id }}</title>
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
            font-size: 9pt;
            vertical-align: top;
        }
        .col-qty { width: 15%; text-align: left; }
        .col-desc { width: 55%; }
        .col-price, .col-total { width: 15%; text-align: right; }
        .totals {
            margin-top: 10px;
        }
        .totals .row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }
        .totals .row .col-label, .totals .row .col-value {
            font-size: 10pt;
        }
        .totals .row .col-value {
            text-align: right;
        }
        .totals .total-final .col-label, .totals .total-final .col-value {
            font-size: 12pt;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 9pt;
        }
    </style>
</head>
<body>
    <div class="ticket-container">
        <div class="header">
            <h1>PRE-CUENTA</h1>
            @php
                $nombreComercial = \App\Models\CompanyConfig::getNombreComercial();
                $razonSocial = \App\Models\CompanyConfig::getRazonSocial();
            @endphp
            <p><strong>{{ $nombreComercial ?: $razonSocial }}</strong></p>
            <p>{{ $razonSocial }}</p>
            <p>RUC: {{ \App\Models\CompanyConfig::getRuc() }}</p>
            <p>{{ \App\Models\CompanyConfig::getDireccion() }}</p>
            @if(\App\Models\CompanyConfig::getTelefono())
                <p>Teléfono: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
            @endif
        </div>

        <hr>

        <table class="info-table">
            <tr>
                <td><strong>Mesa:</strong> {{ $order->table?->number ?? 'VENTA DIRECTA' }}</td>
                <td style="text-align: right;"><strong>Orden:</strong> #{{ $order->id }}</td>
            </tr>
            <tr>
                <td><strong>Mesero:</strong> {{ $order->employee?->name ?? 'N/A' }}</td>
                <td style="text-align: right;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <hr>

        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-desc">Producto</th>
                    <th class="col-price">P.U.</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetails ?? [] as $detail)
                <tr>
                    <td class="col-qty">{{ $detail->quantity }}</td>
                    <td class="col-desc">{{ $detail->product?->name ?? 'Producto no disponible' }}</td>
                    <td class="col-price">{{ number_format($detail->unit_price, 2) }}</td>
                    <td class="col-total">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <hr>

        <div class="totals">
            <div class="row">
                <span class="col-label">SUBTOTAL:</span>
                <span class="col-value">S/ {{ number_format($order->subtotal ?? 0, 2) }}</span>
            </div>
            <div class="row">
                <span class="col-label">IGV (18%):</span>
                <span class="col-value">S/ {{ number_format($order->tax ?? 0, 2) }}</span>
            </div>
            <div class="row total-final" style="margin-top: 5px;">
                <span class="col-label">TOTAL:</span>
                <span class="col-value">S/ {{ number_format($order->total ?? 0, 2) }}</span>
            </div>
        </div>

        <hr>

        <div class="footer">
            <p>ESTE DOCUMENTO NO ES UN COMPROBANTE DE PAGO.</p>
            <p>Gracias por su visita.</p>
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>