<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pre-Cuenta #{{ $order->id }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @media print {
            @page {
                size: A5 portrait;
                margin: 10mm;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 15px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #000;
            padding-bottom: 10px;
        }
        .logo {
            font-size: 24px;
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
    <div class="header">
        <div class="logo">Restaurante Ejemplo</div>
        <div class="subtitle">Pre-Cuenta</div>
        <div class="address">Av. Ejemplo 123, Ciudad</div>
        <div class="contact">Tel: (01) 234-5678 | Email: info@restaurante.com</div>
    </div>

    <div class="info">
        <div class="info-row">
            <span class="label">Pre-Cuenta #:</span> {{ $order->id }}
        </div>
        <div class="info-row">
            <span class="label">Fecha:</span> {{ $date }}
        </div>
        <div class="info-row">
            <span class="label">Mesa:</span> {{ $table ? 'Mesa #'.$table->number.' - '.ucfirst($table->location) : 'Venta Rápida' }}
        </div>
        <div class="info-row">
            <span class="label">Atendido por:</span> {{ $order->employee->name ?? 'No asignado' }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">CANT.</th>
                <th width="50%">DESCRIPCIÓN</th>
                <th width="20%">PRECIO</th>
                <th width="20%">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderDetails as $detail)
                <tr>
                    <td class="quantity">{{ $detail->quantity }}</td>
                    <td>
                        <div class="product-name">{{ $detail->product->name }}</div>
                        @if($detail->notes)
                            <div class="notes">{{ $detail->notes }}</div>
                        @endif
                    </td>
                    <td class="price">S/ {{ number_format($detail->unit_price, 2) }}</td>
                    <td class="subtotal">S/ {{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="value">S/ {{ number_format($order->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">I.G.V. (18%):</td>
            <td class="value">S/ {{ number_format($order->tax, 2) }}</td>
        </tr>
        @if($order->discount > 0)
        <tr>
            <td class="label">Descuento:</td>
            <td class="value">S/ {{ number_format($order->discount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td class="label">TOTAL:</td>
            <td class="value">S/ {{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    @if($order->notes)
        <div class="info">
            <div class="label">Notas adicionales:</div>
            <div>{{ $order->notes }}</div>
        </div>
    @endif

    <div class="notice">
        Esta pre-cuenta no tiene valor fiscal. Solicite su boleta o factura al pagar.
    </div>

    <div class="footer">
        Gracias por su preferencia
        <br>
        Generado el {{ $date }}
    </div>

    <div class="action-buttons no-print">
        <button class="print-button" onclick="window.print()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            </svg>
            Imprimir
        </button>
        <button class="print-button" style="background-color: #10b981;" onclick="window.close()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
            </svg>
            Cerrar ventana
        </button>
    </div>
</body>
</html>
