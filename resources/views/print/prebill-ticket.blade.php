<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pre-Cuenta #{{ $order->id }}</title>
    <style>
        /* Estilos optimizados para impresora térmica */
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', monospace;
            margin: 0;
            padding: 8px;
            width: 80mm;
            font-size: 12px;
            line-height: 1.2;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0 0 5px 0;
        }
        .header p {
            margin: 2px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        th, td {
            text-align: left;
            padding: 3px 0;
        }
        .qty { width: 15%; text-align: center; }
        .price, .total { width: 25%; text-align: right; }
        .totals {
            margin: 10px 0;
            text-align: right;
        }
        .totals div {
            margin: 5px 0;
        }
        .footer {
            text-align: center;
            margin-top: 10px;
            font-size: 11px;
        }
        hr {
            border: none;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        @media print {
            body { margin: 0; }
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
        <p><strong>{{ $nombreComercial ?: $razonSocial }}</strong></p>
        <p>{{ $razonSocial }}</p>
        <p>RUC: {{ \App\Models\CompanyConfig::getRuc() }}</p>
        <p>{{ \App\Models\CompanyConfig::getDireccion() }}</p>
        @if(\App\Models\CompanyConfig::getTelefono())
            <p>Tel: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
        @endif
    </div>

    <hr>

    <table>
        <tr>
            <td><strong>Mesa:</strong> {{ $order->table?->number ?? 'N/A' }}</td>
            <td style="text-align: right;"><strong>Orden:</strong> #{{ $order->id }}</td>
        </tr>
        <tr>
            <td><strong>Mesero:</strong> {{ $order->employee?->name ?? 'N/A' }}</td>
            <td style="text-align: right;">{{ $order->created_at->format('d/m/Y H:i:s') }}</td>
        </tr>
    </table>

    <hr>

    <table>
        <thead>
            <tr>
                <th class="qty">Cant</th>
                <th>Descripción</th>
                <th class="price">P.U.</th>
                <th class="total">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderDetails as $detail)
                <tr>
                    <td class="qty">{{ $detail->quantity }}</td>
                    <td>{{ $detail->product->name }}</td>
                    <td class="price">{{ number_format($detail->unit_price, 2) }}</td>
                    <td class="total">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <hr>

    <div class="totals">
        <div>
            <strong>SUBTOTAL:</strong> S/ {{ number_format($order->subtotal, 2) }}
        </div>
        <div>
            <strong>IGV (18%):</strong> S/ {{ number_format($order->tax, 2) }}
        </div>
        @if($order->discount > 0)
        <div>
            <strong>DESCUENTO:</strong> -S/ {{ number_format($order->discount, 2) }}
        </div>
        @endif
        <div style="font-size: 14px;">
            <strong>TOTAL: S/ {{ number_format($order->total, 2) }}</strong>
        </div>
    </div>

    <hr>

    <div class="footer">
        <p><strong>ESTE DOCUMENTO NO ES UN COMPROBANTE DE PAGO</strong></p>
        <p>¡Gracias por su visita!</p>
        <p>{{ now()->format('d/m/Y H:i:s') }}</p>
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
