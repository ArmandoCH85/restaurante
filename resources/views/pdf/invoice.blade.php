<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $invoice->series }}-{{ $invoice->number }}</title>
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
            <h1>FACTURA ELECTRÓNICA</h1>
            <p>{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'RESTAURANTE EJEMPLO' }}</p>
            <p>RUC: {{ \App\Models\CompanyConfig::getRuc() ?? '20123456789' }}</p>
            <p>{{ \App\Models\CompanyConfig::getDireccion() ?? 'Av. Ejemplo 123, Ciudad' }}</p>
            @if(\App\Models\CompanyConfig::getTelefono())
                <p>Tel: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
            @endif
            <p><strong>{{ $invoice->series }}-{{ $invoice->number }}</strong></p>
        </div>
        <hr>
        <table class="info-table">
            <tr>
                <td><strong>Fecha:</strong></td>
                <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Hora:</strong></td>
                <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('H:i:s') }}</td>
            </tr>
            <tr>
                <td><strong>Cliente:</strong></td>
                <td>{{ $invoice->client_name }}</td>
            </tr>
            <tr>
                <td><strong>RUC:</strong></td>
                <td>{{ $invoice->client_document }}</td>
            </tr>
            @if($invoice->client_address)
            <tr>
                <td><strong>Dirección:</strong></td>
                <td>{{ $invoice->client_address }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Atendido por:</strong></td>
                <td>{{ $invoice->employee->name }}</td>
            </tr>
        </table>
        <hr>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-desc">Descripción</th>
                    <th class="col-price">P.U.</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $detail)
                <tr>
                    <td class="col-qty">{{ $detail->quantity }}</td>
                    <td class="col-desc">{{ $detail->description }}</td>
                    <td class="col-price">{{ number_format($detail->unit_price, 2) }}</td>
                    <td class="col-total">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        <div class="totals">
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">OP. GRAVADAS:</span>
                <span class="col-value">S/ {{ number_format($invoice->taxable_amount, 2) }}</span>
            </div>
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">IGV (18%):</span>
                <span class="col-value">S/ {{ number_format($invoice->tax, 2) }}</span>
            </div>
            <div class="row total-final" style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span class="col-label">TOTAL:</span>
                <span class="col-value">S/ {{ number_format($invoice->total, 2) }}</span>
            </div>
        </div>
        <hr>
        <div class="payment-info">
            <p style="text-align: center;"><strong>FORMA DE PAGO:</strong>
                {{ ucfirst(match($invoice->payment_method) {
                    'cash' => 'Efectivo',
                    'card' => 'Tarjeta',
                    'yape' => 'Yape',
                    'plin' => 'Plin',
                    default => $invoice->payment_method
                }) }}
            </p>
        </div>
        <hr>
        <div class="footer">
            <p>Representación impresa de la Factura Electrónica</p>
            <p>Autorizado mediante Resolución de Superintendencia</p>
            <p>N° 203-2015/SUNAT</p>
            <p>Consulte su comprobante en www.sunat.gob.pe</p>
            <p>Gracias por su preferencia</p>
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
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura Electrónica #{{ $invoice->series }}-{{ $invoice->number }}</title>
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
        .title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin: 10px 0;
        }
        .document-number {
            font-size: 12pt;
            text-align: center;
            margin-bottom: 10px;
        }
        .qr-container {
            text-align: center;
            margin: 10px 0;
        }
        .qr-code {
            width: 100px;
            height: 100px;
            display: inline-block;
        }
        .legal-info {
            font-size: 8pt;
            text-align: center;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'RESTAURANTE EJEMPLO' }}</h1>
            <p>RUC: {{ \App\Models\CompanyConfig::getRuc() ?? '20123456789' }}</p>
            <p>{{ \App\Models\CompanyConfig::getDireccion() ?? 'Av. Ejemplo 123, Ciudad' }}</p>
            @if(\App\Models\CompanyConfig::getTelefono())
                <p>Tel: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
            @endif
        </div>
        <hr>
        <div class="title">FACTURA ELECTRÓNICA</div>
        <div class="document-number">{{ $invoice->series }}-{{ $invoice->number }}</div>
        <hr>
        <table class="info-table">
            <tr>
                <td colspan="2"><strong>Cliente:</strong> {{ $invoice->customer ? $invoice->customer->name : 'Cliente General' }}</td>
            </tr>
            @if($invoice->customer && $invoice->customer->document_number)
            <tr>
                <td colspan="2"><strong>RUC:</strong> {{ $invoice->customer->document_number }}</td>
            </tr>
            @endif
            @if($invoice->customer && $invoice->customer->address)
            <tr>
                <td colspan="2"><strong>Dirección:</strong> {{ $invoice->customer->address }}</td>
            </tr>
            @endif
            <tr>
                <td><strong>Fecha de Emisión:</strong></td>
                <td>{{ $invoice->issue_date->format('d/m/Y') }}</td>
            </tr>
            @if($invoice->order && $invoice->order->table)
            <tr>
                <td><strong>Mesa:</strong></td>
                <td>{{ $invoice->order->table->number }}</td>
            </tr>
            @endif
        </table>
        <hr>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-desc">Descripción</th>
                    <th class="col-price">P.U.</th>
                    <th class="col-total">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $detail)
                <tr>
                    <td class="col-qty">{{ $detail->quantity }}</td>
                    <td class="col-desc">{{ $detail->description }}</td>
                    <td class="col-price">{{ number_format($detail->unit_price, 2) }}</td>
                    <td class="col-total">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        <div class="totals">
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">OP. GRAVADA:</span>
                <span class="col-value">S/ {{ number_format($invoice->taxable_amount, 2) }}</span>
            </div>
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">IGV (18%):</span>
                <span class="col-value">S/ {{ number_format($invoice->tax, 2) }}</span>
            </div>
            <div class="row total-final" style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span class="col-label">TOTAL:</span>
                <span class="col-value">S/ {{ number_format($invoice->total, 2) }}</span>
            </div>
            @if($invoice->payment_method == 'cash' && isset($change_amount) && $change_amount > 0)
            <div class="row" style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span class="col-label">EFECTIVO:</span>
                <span class="col-value">S/ {{ number_format($invoice->payment_amount, 2) }}</span>
            </div>
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">VUELTO:</span>
                <span class="col-value">S/ {{ number_format($change_amount, 2) }}</span>
            </div>
            @endif
        </div>
        <hr>
        <div class="payment-info">
            <p><strong>Forma de Pago:</strong>
                @switch($invoice->payment_method)
                    @case('cash')
                        Efectivo
                        @break
                    @case('card')
                        Tarjeta
                        @break
                    @case('yape')
                        Yape
                        @break
                    @case('plin')
                        Plin
                        @break
                    @default
                        {{ $invoice->payment_method }}
                @endswitch
            </p>
        </div>
        <hr>
        @if($invoice->qr_code)
        <div class="qr-container">
            <img src="{{ $invoice->qr_code }}" alt="Código QR" class="qr-code">
        </div>
        @endif
        <div class="legal-info">
            <p>Representación impresa de la Factura Electrónica</p>
            <p>Puede consultar este documento en www.sunat.gob.pe</p>
            @if($invoice->hash_sign)
            <p>Resumen: {{ $invoice->hash_sign }}</p>
            @endif
        </div>
        <hr>
        <div class="footer">
            <p>Gracias por su preferencia.</p>
            <p>{{ now()->format('d/m/Y H:i:s') }}</p>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
