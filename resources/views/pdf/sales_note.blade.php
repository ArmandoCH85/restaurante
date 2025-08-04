<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Venta #{{ $invoice->series }}-{{ $invoice->number }}</title>
    <style>
        /* Estilos optimizados para impresora térmica de 80mm */
        @page {
            margin: 5mm;
        }
        body {
            font-family: 'monospace', sans-serif;
            font-size: 10pt;
            color: #000 !important;
            line-height: 1.4;
            width: 70mm; /* Ancho aproximado para papel de 80mm */
        }
        
        /* FORZAR COLOR NEGRO EN TODOS LOS NAVEGADORES */
        * {
            color: #000 !important;
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
            <h1>NOTA DE VENTA</h1>
            <p><strong>{{ $company['razon_social'] ?? \App\Models\CompanyConfig::getRazonSocial() ?? 'RESTAURANTE EJEMPLO' }}</strong></p>
            <p>RUC: {{ $company['ruc'] ?? \App\Models\CompanyConfig::getRuc() ?? '20123456789' }}</p>
            <p>{{ $company['direccion'] ?? \App\Models\CompanyConfig::getDireccion() ?? 'Av. Ejemplo 123, Ciudad' }}</p>
            @if(($company['telefono'] ?? \App\Models\CompanyConfig::getTelefono()))
                <p>Tel: {{ $company['telefono'] ?? \App\Models\CompanyConfig::getTelefono() }}</p>
            @endif
            <p><strong>{{ $invoice->series }}-{{ str_pad($invoice->number, 8, '0', STR_PAD_LEFT) }}</strong></p>
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
                <td>{{ $invoice->client_name ?? ($customer ? $customer->name : 'Cliente General') }}</td>
            </tr>
            @if($invoice->client_document)
            <tr>
                <td><strong>Documento:</strong></td>
                <td>{{ $invoice->client_document }}</td>
            </tr>
            @endif
            @if($invoice->client_address)
            <tr>
                <td><strong>Dirección:</strong></td>
                <td>{{ $invoice->client_address }}</td>
            </tr>
            @endif
            @if($invoice->employee)
            <tr>
                <td><strong>Atendido por:</strong></td>
                <td>{{ $invoice->employee->name }}</td>
            </tr>
            @elseif($invoice->order && $invoice->order->employee)
            <tr>
                <td><strong>Atendido por:</strong></td>
                <td>{{ $invoice->order->employee->name }}</td>
            </tr>
            @elseif(auth()->user())
            <tr>
                <td><strong>Atendido por:</strong></td>
                <td>{{ auth()->user()->name }}</td>
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
                    <td class="col-desc">{{ $detail->description ?? ($detail->product ? $detail->product->name : 'Producto') }}</td>
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
                {{ ucfirst(match($invoice->payment_method ?? 'cash') {
                    'cash' => 'Efectivo',
                    'card' => 'Tarjeta',
                    'yape' => 'Yape',
                    'plin' => 'Plin',
                    'pedidos_ya' => 'Pedidos Ya',
                    'didi_food' => 'Didi Food',
                    default => $invoice->payment_method ?? 'Efectivo'
                }) }}
            </p>
        </div>

        @if(isset($change_amount) && $change_amount > 0)
        <hr>
        <div class="change-info">
            <p style="text-align: center;"><strong>CAMBIO:</strong> S/ {{ number_format($change_amount, 2) }}</p>
        </div>
        @endif

        <hr>
        <div class="footer">
            <p>ESTE DOCUMENTO NO TIENE VALIDEZ FISCAL</p>
            <p>NO ES COMPROBANTE DE PAGO AUTORIZADO POR SUNAT</p>
            <p>Gracias por su preferencia</p>
        </div>
    </div>
    <script>
        window.onload = function() {
            window.print();

            // Notificar a la ventana padre que se ha completado la facturación
            if (window.opener) {
                window.opener.postMessage('invoice-completed', '*');
                console.log('Notificación enviada: invoice-completed');
            }

            // Cerrar esta ventana después de imprimir
            setTimeout(function() {
                window.close();
            }, 2000);
        }
    </script>
</body>
</html>
