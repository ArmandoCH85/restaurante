<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pre-Cuenta #{{ $order->id }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @media print {
            @page {
                size: 80mm 297mm;
                margin: 0;
            }

            body {
                margin: 0;
                padding: 3mm;
                font-family: Arial, sans-serif;
                font-size: 11px;
                line-height: 1.2;
            }

            .no-print {
                display: none !important;
            }

            /* PRINCIPIO KISS: Una sola versión optimizada para todos los formatos */
            .thermal-hide {
                display: none !important;
            }
        }

        /* Estilos para formato A5 cuando no es impresión térmica */
        @media screen, print and (min-width: 148mm) {
            @page {
                size: A5;
                margin: 10mm;
            }

            body {
                padding: 10mm;
                font-size: 12px;
            }

            .thermal-only {
                display: block !important;
            }
        }

        /* Estilos para formato A5 normal cuando no es térmica */
        @media print and (min-width: 140mm) {
            @page {
                size: A5 portrait;
                margin: 10mm;
            }

            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 12px;
                margin: 0;
                padding: 15px;
                max-width: 800px;
                margin: 0 auto;
                background-color: #f9fafb;
                color: #111827;
            }

            .thermal-only {
                display: none !important;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            max-width: 800px;
            margin: 0 auto;
            background-color: #f9fafb;
            color: #111827;
        }

        /* Estilos térmicos optimizados */
        .thermal-header {
            text-align: center;
            margin-bottom: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #000;
        }

        .thermal-company h1 {
            font-size: 14px;
            font-weight: bold;
            margin: 3px 0;
            line-height: 1.1;
        }

        .thermal-company p {
            margin: 1px 0;
            font-size: 10px;
            line-height: 1.1;
        }

        .thermal-document-title {
            text-align: center;
            margin: 8px 0;
            padding: 4px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .thermal-document-title h2 {
            font-size: 12px;
            margin: 0;
            padding: 0;
            font-weight: bold;
        }

        .thermal-info {
            margin: 6px 0;
        }

        .thermal-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 10px;
        }

        .thermal-table {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }

        .thermal-table th {
            text-align: left;
            padding: 2px 1px;
            border-bottom: 1px solid #000;
            font-size: 9px;
            font-weight: bold;
        }

        .thermal-table td {
            padding: 2px 1px;
            font-size: 9px;
            border-bottom: 1px dashed #ccc;
        }

        .thermal-totals {
            margin-top: 6px;
            text-align: right;
            border-top: 1px dashed #000;
            padding-top: 3px;
        }

        .thermal-total-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
            font-size: 10px;
        }

        .thermal-grand-total {
            font-weight: bold;
            font-size: 11px;
            margin-top: 3px;
            border-top: 1px solid #000;
            padding-top: 3px;
        }

        .thermal-footer {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }

        .thermal-notice {
            margin-top: 8px;
            font-style: italic;
            text-align: center;
            font-size: 8px;
            line-height: 1.2;
        }

        /* Estilos A5 normales */
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        .logo {
            text-align: center;
            margin-bottom: 5px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 16px;
            margin-bottom: 5px;
        }
        .address, .contact {
            font-size: 11px;
            margin-bottom: 3px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            text-transform: uppercase;
        }
        .info {
            margin-bottom: 15px;
        }
        .info-row {
            margin-bottom: 5px;
        }
        .label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            text-align: left;
            padding: 8px 5px;
            border-bottom: 1px solid #000;
            background-color: #f2f2f2;
            font-weight: bold;
        }
        td {
            padding: 8px 5px;
            border-bottom: 1px solid #ddd;
        }
        .product-name {
            font-weight: bold;
        }
        .notes {
            font-style: italic;
            font-size: 10px;
            color: #555;
            margin-top: 3px;
        }
        .quantity {
            text-align: center;
        }
        .price, .subtotal {
            text-align: right;
        }
        .totals {
            width: 100%;
            margin-top: 20px;
        }
        .totals td {
            border: none;
            padding: 5px;
        }
        .totals .label {
            text-align: right;
            width: 80%;
        }
        .totals .value {
            text-align: right;
            width: 20%;
            font-weight: bold;
        }
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            border-top: 1px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 10px;
            font-size: 11px;
            color: #555;
        }
        .notice {
            margin-top: 20px;
            font-style: italic;
            text-align: center;
            font-size: 11px;
        }
        .action-buttons {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
        }
        .print-button {
            padding: 10px 20px;
            background-color: #1a56db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .print-button:hover {
            background-color: #1e429f;
        }
    </style>
</head>
<body>
    <!-- Versión térmica optimizada -->
    <div class="thermal-only">
        <div class="thermal-header">
            <div class="thermal-company">
                <h1>{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'RESTAURANTE EJEMPLO' }}</h1>
                <p>RUC: {{ \App\Models\CompanyConfig::getRuc() ?? '20123456789' }}</p>
                <p>{{ \App\Models\CompanyConfig::getDireccion() ?? 'Av. Ejemplo 123, Lima' }}</p>
                @if(\App\Models\CompanyConfig::getTelefono())
                    <p>Tel: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
                @endif
                @if(\App\Models\CompanyConfig::getEmail())
                    <p>Email: {{ \App\Models\CompanyConfig::getEmail() }}</p>
                @endif
            </div>
        </div>

        <div class="thermal-document-title">
            <h2>PRE-CUENTA</h2>
            <div style="font-size: 11px; margin-top: 2px;">#{{ $order->id }}</div>
        </div>

        <div class="thermal-info">
            <div class="thermal-info-row">
                <span class="label">Fecha:</span>
                <span>{{ $date }}</span>
            </div>
            <div class="thermal-info-row">
                <span class="label">Mesa:</span>
                <span>{{ $table ? 'Mesa #'.$table->number.' - '.ucfirst($table->location) : 'Venta Rápida' }}</span>
            </div>
            <div class="thermal-info-row">
                <span class="label">Atendido por:</span>
                <span>{{ $order->employee->name ?? 'No asignado' }}</span>
            </div>
        </div>

        <div style="border-top: 1px dashed #000; margin: 8px 0; padding-top: 5px;">
            @foreach($order->orderDetails as $detail)
                <div style="margin-bottom: 4px; font-size: 11px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="font-weight: bold;">{{ $detail->quantity }} x {{ $detail->product->name }}</span>
                        <span>{{ number_format($detail->subtotal, 2) }}</span>
                    </div>
                    @if($detail->notes)
                        <div style="font-style: italic; font-size: 9px; color: #666; margin-left: 10px;">{{ $detail->notes }}</div>
                    @endif
                    @if($detail->unit_price != $detail->subtotal / $detail->quantity)
                        <div style="font-size: 9px; color: #666; margin-left: 10px;">
                            @ S/ {{ number_format($detail->unit_price, 2) }} c/u
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="thermal-totals">
            <div class="thermal-total-row">
                <span class="label">Subtotal:</span>
                <span>S/ {{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="thermal-total-row">
                <span class="label">I.G.V. (18%):</span>
                <span>S/ {{ number_format($order->tax, 2) }}</span>
            </div>
            @if($order->discount > 0)
            <div class="thermal-total-row">
                <span class="label">Descuento:</span>
                <span>S/ {{ number_format($order->discount, 2) }}</span>
            </div>
            @endif
            <div class="thermal-grand-total">
                <span class="label">TOTAL:</span>
                <span>S/ {{ number_format($order->total, 2) }}</span>
            </div>
        </div>

        @if($order->notes)
        <div class="thermal-info">
            <div style="font-weight: bold; font-size: 9px;">Notas:</div>
            <div style="font-size: 9px; margin-top: 1px;">{{ $order->notes }}</div>
        </div>
        @endif

        <div class="thermal-footer">
            Gracias por su preferencia
        </div>
    </div>

    <!-- VERSIÓN A5 ELIMINADA - PRINCIPIO KISS: Solo una versión optimizada -->
    <!-- La versión térmica optimizada sirve para ambos formatos (80mm/57mm y A5) -->


</body>
</html>
