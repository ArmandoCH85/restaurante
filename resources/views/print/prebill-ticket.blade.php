<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pre-Cuenta #{{ $order->id }}</title>
    <style>
        /* Estilos optimizados para impresora térmica con mejor visibilidad */
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 10px;
            width: 80mm;
            font-size: 14px;
            line-height: 1.4;
            font-weight: bold;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 20px;
            margin: 0 0 8px 0;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header p {
            margin: 3px 0;
            font-size: 12px;
        }
        .header .company-name {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 12px 0;
        }
        th, td {
            text-align: left;
            padding: 5px 2px;
            font-size: 13px;
        }
        th {
            font-weight: bold;
            border-bottom: 2px solid #000;
            font-size: 12px;
        }
        .qty { 
            width: 12%; 
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
            font-size: 13px;
        }
        .info-table td {
            font-size: 12px;
            padding: 3px 0;
        }
        .info-table .label {
            font-weight: bold;
        }
        .totals {
            margin: 15px 0;
            text-align: right;
            font-size: 14px;
        }
        .totals div {
            margin: 6px 0;
            font-weight: bold;
        }
        .total-final {
            font-size: 16px !important;
            margin-top: 8px !important;
            border-top: 2px solid #000;
            padding-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
        }
        .footer p {
            margin: 4px 0;
            font-weight: bold;
        }
        .footer .disclaimer {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        hr {
            border: none;
            border-top: 2px solid #000;
            margin: 12px 0;
        }
        .separator-light {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
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
            $razonSocial = \App\Models\CompanyConfig::getRazonSocial();
        @endphp
        <p class="company-name">{{ $nombreComercial ?: $razonSocial }}</p>
        @if($nombreComercial && $razonSocial && $nombreComercial !== $razonSocial)
            <p>{{ $razonSocial }}</p>
        @endif
        <p><strong>RUC:</strong> {{ \App\Models\CompanyConfig::getRuc() }}</p>
        <p>{{ \App\Models\CompanyConfig::getDireccion() }}</p>
        @if(\App\Models\CompanyConfig::getTelefono())
            <p><strong>Tel:</strong> {{ \App\Models\CompanyConfig::getTelefono() }}</p>
        @endif
    </div>

    <hr>

    <table class="info-table">
        <tr>
            <td class="label">Mesa:</td>
            <td style="text-align: right;"><strong>{{ $order->table?->number ?? 'VENTA DIRECTA' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Orden:</td>
            <td style="text-align: right;"><strong>#{{ $order->id }}</strong></td>
        </tr>
        <tr>
            <td class="label">Mesero:</td>
            <td style="text-align: right;">{{ $order->employee?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Fecha:</td>
            <td style="text-align: right;">{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
    </table>

    <hr class="separator-light">

    <table>
        <thead>
            <tr>
                <th class="qty">CANT</th>
                <th>DESCRIPCIÓN</th>
                <th class="price">P.U.</th>
                <th class="total">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderDetails as $detail)
                <tr>
                    <td class="qty">{{ $detail->quantity }}</td>
                    <td class="product-name">{{ strtoupper($detail->product->name) }}</td>
                    <td class="price">S/ {{ number_format($detail->unit_price, 2) }}</td>
                    <td class="total">S/ {{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="separator-light">

    <div class="totals">
        <div>
            SUBTOTAL: S/ {{ number_format($order->subtotal, 2) }}
        </div>
        <div>
            IGV (18%): S/ {{ number_format($order->tax, 2) }}
        </div>
        @if($order->discount > 0)
        <div>
            DESCUENTO: -S/ {{ number_format($order->discount, 2) }}
        </div>
        @endif
        <div class="total-final">
            TOTAL A PAGAR: S/ {{ number_format($order->total, 2) }}
        </div>
    </div>

    <hr>

    <div class="footer">
        <p class="disclaimer">ESTE DOCUMENTO NO ES UN COMPROBANTE DE PAGO</p>
        <p>¡Gracias por su visita!</p>
        <p>Impreso: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        // Imprimir automáticamente al cargar
        window.onload = function() {
            window.print();
            // Cerrar la ventana después de imprimir
            window.onafterprint = function() {
                window.close();
            };
        };
    </script>
</body>
</html>
