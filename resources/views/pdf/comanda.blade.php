<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Comanda #{{ $order->id }}</title>
    <style>
        /* ===== OPTIMIZACIÓN PARA PAPEL TÉRMICO 80MM ===== */
        @page {
            margin: 0;
            size: 80mm auto;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', 'Liberation Mono', monospace;
            font-size: 11px;
            line-height: 1.3;
            color: #000;
            width: 80mm;
            margin: 0;
            padding: 4mm;
            background: white;
        }

        /* ===== HEADER EMPRESA ===== */
        .company-header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 2px;
        }

        .company-address {
            font-size: 9px;
            margin-bottom: 1px;
        }

        .document-type {
            font-size: 14px;
            font-weight: bold;
            background: #000;
            color: white;
            padding: 4px 8px;
            margin: 6px 0;
            text-align: center;
            letter-spacing: 2px;
        }

        /* ===== INFORMACIÓN DE LA ORDEN ===== */
        .order-info {
            margin: 8px 0;
            border: 1px solid #000;
            padding: 6px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
            font-size: 10px;
        }

        .info-row:last-child {
            margin-bottom: 0;
        }

        .info-label {
            font-weight: bold;
            min-width: 35mm;
        }

        .info-value {
            text-align: right;
            flex: 1;
        }

        /* ===== MESA/CLIENTE DESTACADO ===== */
        .table-info {
            text-align: center;
            background: #f0f0f0;
            border: 2px solid #000;
            padding: 8px;
            margin: 8px 0;
            font-size: 14px;
            font-weight: bold;
        }

        .table-number {
            font-size: 20px;
            color: #000;
        }

        /* ===== SEPARADORES ===== */
        .separator {
            border: none;
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .separator-thick {
            border: none;
            border-top: 2px solid #000;
            margin: 10px 0;
        }

        /* ===== TABLA DE PRODUCTOS ===== */
        .products-section {
            margin: 10px 0;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            text-align: center;
            background: #000;
            color: white;
            padding: 4px;
            margin-bottom: 6px;
            letter-spacing: 1px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
        }

        .products-table th {
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 4px 2px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }

        .products-table td {
            border: 1px solid #000;
            padding: 6px 4px;
            vertical-align: top;
        }

        .qty-col {
            width: 15%;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }

        .product-col {
            width: 85%;
            font-size: 11px;
        }

        .product-name {
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        /* ===== NOTAS ===== */
        .notes-section {
            margin: 10px 0;
            border: 1px solid #000;
            padding: 6px;
            background: #f9f9f9;
        }

        .notes-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 4px;
            text-decoration: underline;
        }

        .notes-content {
            font-size: 10px;
            line-height: 1.4;
        }

        /* ===== FOOTER ===== */
        .footer {
            text-align: center;
            margin-top: 12px;
            padding-top: 8px;
            border-top: 2px solid #000;
            font-size: 9px;
        }

        .timestamp {
            font-weight: bold;
            margin-bottom: 4px;
        }

        .footer-message {
            font-style: italic;
            margin-top: 6px;
        }

        /* ===== EFECTOS DE IMPRESIÓN ===== */
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }
        }

        /* ===== ESPACIADO MEJORADO ===== */
        .spacer-sm { margin: 4px 0; }
        .spacer-md { margin: 8px 0; }
        .spacer-lg { margin: 12px 0; }
    </style>
</head>
<body>
    <!-- HEADER DE LA EMPRESA -->
    <div class="company-header">
        @php
            $nombreComercial = \App\Models\CompanyConfig::getNombreComercial();
            $razonSocial = \App\Models\CompanyConfig::getRazonSocial();
            $direccion = \App\Models\CompanyConfig::getDireccion();
        @endphp

        <div class="company-name">
            {{ $nombreComercial ?: $razonSocial ?: 'RESTAURANTE' }}
        </div>

        @if($direccion)
        <div class="company-address">{{ $direccion }}</div>
        @endif
    </div>

    <!-- TIPO DE DOCUMENTO -->
    <div class="document-type">COMANDA - COCINA</div>

    <!-- INFORMACIÓN DE MESA/CLIENTE -->
    <div class="table-info">
        @if($order->table)
            <div>MESA</div>
            <div class="table-number">{{ $order->table->number }}</div>
        @else
            <div>VENTA DIRECTA</div>
            @if(!empty($customerNameForComanda))
                <div style="font-size: 12px; margin-top: 4px;">
                    {{ strtoupper($customerNameForComanda) }}
                </div>
            @endif
        @endif
    </div>

    <!-- INFORMACIÓN DE LA ORDEN -->
    <div class="order-info">
        <div class="info-row">
            <span class="info-label">ORDEN:</span>
            <span class="info-value">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">MESERO:</span>
            <span class="info-value">{{ strtoupper($order->employee?->name ?? 'N/A') }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">FECHA:</span>
            <span class="info-value">{{ $order->created_at->format('d/m/Y') }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">HORA:</span>
            <span class="info-value">{{ $order->created_at->format('H:i:s') }}</span>
        </div>

        @if($order->customer && empty($customerNameForComanda))
        <div class="info-row">
            <span class="info-label">CLIENTE:</span>
            <span class="info-value">{{ strtoupper($order->customer->name) }}</span>
        </div>
        @endif
    </div>

    <!-- SEPARADOR -->
    <hr class="separator-thick">

    <!-- PRODUCTOS -->
    <div class="products-section">
        <div class="section-title">PRODUCTOS SOLICITADOS</div>

        <table class="products-table">
            <thead>
                <tr>
                    <th class="qty-col">CANT</th>
                    <th class="product-col">PRODUCTO</th>
                </tr>
            </thead>
            <tbody>
                @forelse($order->orderDetails ?? [] as $detail)
                <tr>
                    <td class="qty-col">{{ $detail->quantity }}</td>
                    <td class="product-col">
                        <div class="product-name">
                            {{ strtoupper($detail->product?->name ?? 'PRODUCTO NO DISPONIBLE') }}
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" style="text-align: center; font-style: italic; padding: 12px;">
                        No hay productos en esta orden
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- NOTAS (SI EXISTEN) -->
    @if($order->notes)
    <div class="notes-section">
        <div class="notes-title">NOTAS ESPECIALES:</div>
        <div class="notes-content">{{ strtoupper($order->notes) }}</div>
    </div>
    @endif

    <!-- SEPARADOR FINAL -->
    <hr class="separator-thick">

    <!-- FOOTER -->
    <div class="footer">
        <div class="timestamp">
            IMPRESO: {{ now()->format('d/m/Y H:i:s') }}
        </div>

        <div class="footer-message">
            ¡GRACIAS POR SU PREFERENCIA!
        </div>
    </div>
</body>
</html>
