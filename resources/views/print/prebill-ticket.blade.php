<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pre-Cuenta #{{ $order->id }}</title>
    <style>
        /* Estilos optimizados para papel térmico */
        @page {
            size: 76mm auto;
            margin: 1mm;
        }
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            margin: 0;
            padding: 2mm;
            width: 72mm;
            font-size: 11px;
            line-height: 1.2;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 14px;
            margin: 0 0 4px 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 1px 0;
            font-size: 10px;
        }
        .header .company-name {
            font-size: 12px;
            font-weight: bold;
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }
        th, td {
            text-align: left;
            padding: 2px 1px;
            font-size: 10px;
        }
        th {
            font-weight: bold;
            border-bottom: 1px solid #000;
            font-size: 9px;
        }
        .qty { 
            width: 15%; 
            text-align: center; 
            font-weight: bold;
        }
        .price, .total { 
            width: 20%; 
            text-align: right; 
            font-weight: bold;
        }
        .product-name {
            font-weight: bold;
            font-size: 10px;
            word-break: break-word;
        }
        .info-table td {
            font-size: 10px;
            padding: 1px 0;
        }
        .info-table .label {
            font-weight: bold;
        }
        .totals {
            margin: 8px 0;
            text-align: right;
            font-size: 11px;
        }
        .totals div {
            margin: 2px 0;
            font-weight: bold;
        }
        .total-final {
            font-size: 12px !important;
            margin-top: 4px !important;
            border-top: 1px solid #000;
            padding-top: 2px;
        }
        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 9px;
        }
        .footer p {
            margin: 2px 0;
            font-weight: bold;
        }
        .footer .disclaimer {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        hr {
            border: none;
            border-top: 1px solid #000;
            margin: 4px 0;
        }
        .separator-light {
            border: none;
            border-top: 1px dashed #000;
            margin: 3px 0;
        }
        @media print {
            body { 
                margin: 0; 
                font-weight: bold;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            #print-btn { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>PRE-CUENTA</h1>
        @php
            $nombreComercial = \App\Models\CompanyConfig::getNombreComercial();
        @endphp
        <p class="company-name">{{ $nombreComercial ?: 'RESTAURANTE' }}</p>
        <p><strong>RUC:</strong> {{ \App\Models\CompanyConfig::getRuc() }}</p>
    </div>

    <hr>

    <table class="info-table">
        <tr>
            <td class="label">Mesa:</td>
            <td style="text-align: right;"><strong>{{ $order->table?->number ?? 'DIRECTA' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Orden:</td>
            <td style="text-align: right;"><strong>#{{ $order->id }}</strong></td>
        </tr>
        <tr>
            <td class="label">Fecha:</td>
            <td style="text-align: right;">{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <hr class="separator-light">

    <table>
        <thead>
            <tr>
                <th class="qty">CANT</th>
                <th>PRODUCTO</th>
                <th class="total">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderDetails as $detail)
                <tr>
                    <td class="qty">{{ $detail->quantity }}</td>
                    <td class="product-name">{{ strtoupper($detail->product->name) }}
                        @if($detail->notes)
                            <br><small style="font-size: 8px; font-weight: normal;">{{ $detail->notes }}</small>
                        @endif
                    </td>
                    <td class="total">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="separator-light">

    <div class="totals">
        @if($order->discount > 0)
        <div>DESCUENTO: -S/ {{ number_format($order->discount, 2) }}</div>
        @endif
        <div class="total-final">
            TOTAL: S/ {{ number_format($order->total, 2) }}
        </div>
    </div>

    <hr>

    <div class="footer">
        <p class="disclaimer">NO ES COMPROBANTE DE PAGO</p>
        <p>¡Gracias por su visita!</p>
        <p>{{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <script>
        window.addEventListener('load', function() {
            // Imprimir automáticamente al cargar
            setTimeout(function() {
                window.print();
                
                // Cerrar ventana después de imprimir
                window.addEventListener('afterprint', function() {
                    setTimeout(function() {
                        window.close();
                    }, 500);
                });
                
                // Cierre alternativo por si no funciona afterprint
                setTimeout(function() {
                    window.close();
                }, 3000);
            }, 500);
        });
    </script>
</body>
</html>
