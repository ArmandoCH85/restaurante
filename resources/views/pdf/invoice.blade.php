<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura #{{ $invoice->series }}-{{ $invoice->number }}</title>
    <style>
        /* Estilos optimizados para papel térmico - MARGEN IZQUIERDO SEGURO */
        @page {
            size: 80mm auto;
            margin: 3mm 2mm 3mm 6mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 8px;
            color: #000 !important;
            line-height: 1.0;
            width: 70mm;
            max-width: 70mm;
            margin: 0;
            padding: 2mm;
            box-sizing: border-box;
        }
        
        /* FORZAR COLOR NEGRO Y ESTILOS TÉRMICOS */
        * {
            color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        .container {
            width: 100%;
            max-width: 66mm;
            margin: 0;
            padding: 0 0 0 2mm;
            text-align: left;
        }
        
        .header, .footer {
            text-align: center;
            margin: 0 auto;
            max-width: 64mm;
        }
        
        .header h1 {
            margin: 0 0 3px 0;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 1px 0;
            font-size: 8px;
        }
        
        .footer p {
            margin: 1px 0;
            font-size: 7px;
        }
        
        hr {
            border: 0;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
        
        .info-table, .items-table {
            width: 100%;
            max-width: 64mm;
            margin: 0;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 0.5px 0;
            font-size: 8px;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 30%;
            font-weight: bold;
            font-size: 7px;
        }
        
        .info-table td:last-child {
            font-size: 7px;
            word-break: break-word;
        }
        
        .items-table th {
            padding: 1px 0;
            font-size: 7px;
            font-weight: bold;
            border-bottom: 1px solid #000;
        }
        
        .items-table td {
            padding: 1px 0;
            font-size: 8px;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        .col-qty { 
            width: 15%; 
            text-align: center;
            font-weight: bold;
            font-size: 7px;
        }
        
        .col-desc { 
            width: 60%; 
            word-break: break-word;
            font-size: 7px;
            padding-right: 2px;
        }
        
        .col-total { 
            width: 25%; 
            text-align: right;
            font-weight: bold;
            font-size: 7px;
        }
        
        .totals {
            margin-top: 4px;
        }
        
        .totals .row {
            display: flex;
            justify-content: space-between;
            margin: 1px 0;
        }
        
        .totals .row .col-label {
            font-size: 8px;
            font-weight: bold;
        }
        
        .totals .row .col-value {
            font-size: 8px;
            font-weight: bold;
            text-align: right;
        }
        
        .totals .total-final .col-label, 
        .totals .total-final .col-value {
            font-size: 10px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 2px;
        }
        
        .payment-info {
            text-align: center;
            font-size: 7px;
            margin: 3px 0;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>FACTURA ELECTRÓNICA</h1>
            <p><strong>{{ \App\Models\CompanyConfig::getRazonSocial() }}</strong></p>
            <p>RUC: {{ \App\Models\CompanyConfig::getRuc() }}</p>
            <p>{{ \App\Models\CompanyConfig::getDireccion() }}</p>
            @if(\App\Models\CompanyConfig::getTelefono())
                <p>Tel: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
            @endif
            <p><strong>F{{ $invoice->series }}-{{ str_pad($invoice->number, 8, '0', STR_PAD_LEFT) }}</strong></p>
        </div>
        <hr>
        <table class="info-table">
            <tr>
                <td><strong>Fecha:</strong></td>
                <td>{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') : now()->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Hora:</strong></td>
                <td>{{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('H:i:s') : now()->format('H:i:s') }}</td>
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
            @if($invoice->employee)
            <tr>
                <td><strong>Atendido por:</strong></td>
                <td>{{ $invoice->employee->name }}</td>
            </tr>
            @endif
        </table>
        <hr>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">CANT</th>
                    <th class="col-desc">PRODUCTO</th>
                    <th class="col-total">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $detail)
                <tr>
                    <td class="col-qty">{{ $detail->quantity }}</td>
                    <td class="col-desc">{{ strtoupper($detail->description) }}</td>
                    <td class="col-total">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        <div class="totals">
            @if($invoice->total > 0)
                <div class="row" style="display: flex; justify-content: space-between;">
                    <span class="col-label">OP. GRAVADAS:</span>
                    <span class="col-value">S/ {{ number_format($invoice->correct_subtotal, 2) }}</span>
                </div>
                <div class="row" style="display: flex; justify-content: space-between;">
                    <span class="col-label">IGV (18%):</span>
                    <span class="col-value">S/ {{ number_format($invoice->correct_igv, 2) }}</span>
                </div>
            @endif
            <div class="row total-final" style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span class="col-label">TOTAL:</span>
                <span class="col-value">S/ {{ number_format($invoice->total, 2) }}</span>
            </div>
            @if($invoice->payment_method === 'cash' && $invoice->change_amount > 0)
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">RECIBIDO:</span>
                <span class="col-value">S/ {{ number_format($invoice->payment_amount, 2) }}</span>
            </div>
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">VUELTO:</span>
                <span class="col-value">S/ {{ number_format($invoice->change_amount, 2) }}</span>
            </div>
            @endif
        </div>
        <hr>
        <div class="payment-info" style="text-align: center;">
            <p><strong>FORMA DE PAGO:</strong>
                {{ ucfirst(match($invoice->payment_method ?? 'cash') {
                    'cash' => 'Efectivo',
                    'credit_card' => 'Tarjeta de Crédito',
                    'debit_card' => 'Tarjeta de Débito',
                    'yape' => 'Yape',
                    'plin' => 'Plin',
                    'pedidos_ya' => 'Pedidos Ya',
                    'didi_food' => 'Didi Food',
                    'transfer' => 'Transferencia',
                    'bank_transfer' => 'Transferencia Bancaria',
                    'digital_wallet' => 'Billetera Digital',
                    'multiple' => 'Pago Múltiple',
                    default => ucfirst(str_replace('_', ' ', $invoice->payment_method ?? 'Efectivo'))
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
            <p><strong>{{ \App\Models\CompanyConfig::getRazonSocial() }}</strong></p>
        </div>
    </div>
    <script>
        window.addEventListener('load', function() {
            // Imprimir automáticamente al cargar
            setTimeout(function() {
                window.print();
                
                // Notificar a la ventana padre que se ha completado la facturación
                if (window.opener) {
                    window.opener.postMessage('invoice-completed', '*');
                    console.log('Notificación enviada: invoice-completed');
                }
                
                // Después de imprimir, redirigir al mapa de mesas
                window.addEventListener('afterprint', function() {
                    setTimeout(function() {
                        // Redirigir a mapa de mesas
                        if (window.opener) {
                            window.opener.location.href = '/admin/mapa-mesas';
                            window.close();
                        } else {
                            window.location.href = '/admin/mapa-mesas';
                        }
                    }, 500);
                });
                
                // Redirección alternativa por si no funciona afterprint
                setTimeout(function() {
                    if (window.opener) {
                        window.opener.location.href = '/admin/mapa-mesas';
                        window.close();
                    } else {
                        window.location.href = '/admin/mapa-mesas';
                    }
                }, 5000);
            }, 500);
        });
    </script>
</body>
</html>
