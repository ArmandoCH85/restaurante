<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Boleta Electrónica #{{ $invoice->series }}-{{ $invoice->number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @media print {
            @page {
                size: 80mm 297mm;
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 5mm;
            }
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            max-width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }

        .header {
            text-align: center;
            margin-bottom: 10px;
        }

        .company h1 {
            font-size: 16px;
            font-weight: bold;
            margin: 5px 0;
        }

        .company p {
            margin: 2px 0;
            font-size: 11px;
        }

        .document-title {
            text-align: center;
            margin: 10px 0;
            padding: 5px;
            border-top: 1px dashed #000;
            border-bottom: 1px dashed #000;
        }

        .document-title h2 {
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        .document-number {
            font-size: 16px;
            font-weight: bold;
            margin-top: 5px;
        }

        .info {
            margin: 10px 0;
        }

        .info-row, .customer-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .label {
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }

        th, td {
            text-align: left;
            padding: 3px 2px;
        }

        th {
            border-bottom: 1px solid #000;
        }

        .quantity {
            text-align: center;
        }

        .price, .subtotal {
            text-align: right;
        }

        .totals {
            margin-top: 10px;
            text-align: right;
            border-top: 1px dashed #000;
            padding-top: 5px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }

        .grand-total {
            font-weight: bold;
            font-size: 14px;
            margin-top: 5px;
            border-top: 1px solid #000;
            padding-top: 5px;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 11px;
            border-top: 1px dashed #000;
            padding-top: 10px;
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

        .payment-info {
            margin: 10px 0;
            padding: 5px;
            background-color: #f0f0f0;
            border-radius: 5px;
        }

        .payment-note {
            font-style: italic;
            margin-bottom: 5px;
        }

        .next-payment-btn {
            padding: 5px 10px;
            background-color: #10b981;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 5px;
        }

        .qr-code {
            text-align: center;
            margin-top: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company">
            <h1>{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'RESTAURANTE EJEMPLO' }}</h1>
            <p>RUC: {{ \App\Models\CompanyConfig::getRuc() ?? '20123456789' }}</p>
            <p>{{ \App\Models\CompanyConfig::getDireccion() ?? 'Av. Ejemplo 123, Ciudad' }}</p>
            @if(\App\Models\CompanyConfig::getTelefono())
                <p>Tel: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
            @endif
            @if(\App\Models\CompanyConfig::getEmail())
                <p>Email: {{ \App\Models\CompanyConfig::getEmail() }}</p>
            @endif
        </div>
    </div>

    <div class="document-title">
        <h2>BOLETA ELECTRÓNICA</h2>
        <div class="document-number">{{ $invoice->series }}-{{ $invoice->number }}</div>
    </div>

    <div class="info">
        <div class="info-row">
            <span class="label">Fecha de emisión:</span>
            <span>{{ $invoice->issue_date->format('d/m/Y') }}</span>
        </div>
    </div>

    <div class="customer-info">
        <div class="customer-row">
            <span class="label">Cliente:</span>
            <span>{{ $invoice->customer->name }}</span>
        </div>
        <div class="customer-row">
            <span class="label">{{ $invoice->customer->document_type }}:</span>
            <span>{{ $invoice->customer->document_number }}</span>
        </div>
        @if($invoice->customer->address)
        <div class="customer-row">
            <span class="label">Dirección:</span>
            <span>{{ $invoice->customer->address }}</span>
        </div>
        @endif
    </div>

    @if(isset($split_payment) && $split_payment)
    <div class="payment-info">
        <p class="payment-note">Este es un comprobante por pago dividido ({{ $invoice->notes }})</p>
        @if(isset($next_invoice_url) && $next_invoice_url)
        <button class="next-payment-btn no-print" onclick="window.location.href='{{ $next_invoice_url }}'">
            Ver siguiente comprobante
        </button>
        @endif
    </div>
    @endif

    <div class="order-info">
        <div class="info-row">
            <span class="label">Fecha:</span>
            <span class="value">{{ $date }}</span>
        </div>
        <div class="info-row">
            <span class="label">Mesa:</span>
            <span class="value">{{ $invoice->order->table->name ?? 'Para llevar' }}</span>
        </div>
        <div class="info-row">
            <span class="label">Tipo de pago:</span>
            <span class="value">
                @switch($invoice->payment_method)
                    @case('cash')
                        Efectivo
                        @break
                    @case('card')
                        Tarjeta
                        @break
                    @case('transfer')
                        Transferencia
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
            </span>
        </div>
        @if($invoice->payment_method === 'cash' && isset($change_amount) && $change_amount > 0)
        <div class="info-row">
            <span class="label">Monto recibido:</span>
            <span class="value">S/ {{ number_format($invoice->payment_amount, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Vuelto:</span>
            <span class="value">S/ {{ number_format($change_amount, 2) }}</span>
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="10%">CANT</th>
                <th width="55%">DESCRIPCIÓN</th>
                <th width="15%">P.UNIT</th>
                <th width="20%">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->details as $detail)
                <tr>
                    <td class="quantity">{{ $detail->quantity }}</td>
                    <td>{{ $detail->description }}</td>
                    <td class="price">{{ number_format($detail->unit_price, 2) }}</td>
                    <td class="subtotal">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span class="label">Subtotal:</span>
            <span>S/ {{ number_format($invoice->taxable_amount, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="label">IGV (18%):</span>
            <span>S/ {{ number_format($invoice->tax, 2) }}</span>
        </div>
        <div class="grand-total">
            <span class="label">Total:</span>
            <span>S/ {{ number_format($invoice->total, 2) }}</span>
        </div>
    </div>

    <div class="qr-code">
        <!-- Aquí se coloca el código QR que generalmente tiene
        RUC|TIPO DOC|SERIE|NUMERO|MTO IGV|MTO TOTAL|FECHA EMISIÓN|TIPO DOC ADQUIRIENTE|NRO DOC ADQUIRIENTE -->
        <img src="data:image/png;base64,{{ $qr_code ?? 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=' }}" width="150" height="150">
    </div>

    <div class="notice">
        Representación impresa de la Boleta Electrónica
        Autorizado mediante Resolución de Superintendencia N° 000-2023/SUNAT
        Consulte su comprobante en: www.sunat.gob.pe
    </div>

    <div class="footer">
        Gracias por su preferencia
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

    @php
    // Función para convertir números a letras
    function num_to_letras($numero, $moneda = 'SOLES', $centimos = 'CENTIMOS') {
        $maximo = 999999999.99;

        $unidades = array('', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE');
        $decenas = array('', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA');
        $centenas = array('', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS');
        $especiales = array('DIEZ' => array('DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISEIS', 'DIECISIETE', 'DIECIOCHO', 'DIECINUEVE'),
                            'VEINTE' => array('VEINTE', 'VEINTIUN', 'VEINTIDOS', 'VEINTITRES', 'VEINTICUATRO', 'VEINTICINCO', 'VEINTISEIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'));

        if ($numero > $maximo) {
            return 'Número demasiado grande';
        }

        $numero_str = number_format($numero, 2, '.', '');
        $partes = explode('.', $numero_str);
        $entero = $partes[0];
        $decimal = $partes[1];

        $texto = '';

        if ($entero == 0) {
            $texto = 'CERO';
        } else if ($entero == 1) {
            $texto = 'UNO';
        } else {
            // Millones
            if ($entero >= 1000000) {
                $millon = floor($entero / 1000000);
                $texto .= num_to_letras($millon, '', '') . ($millon == 1 ? ' MILLON ' : ' MILLONES ');
                $entero %= 1000000;
            }

            // Miles
            if ($entero >= 1000) {
                $miles = floor($entero / 1000);
                $texto .= ($miles == 1) ? 'MIL ' : num_to_letras($miles, '', '') . ' MIL ';
                $entero %= 1000;
            }

            // Centenas
            if ($entero >= 100) {
                $cent = floor($entero / 100);
                $texto .= ($cent == 1 && $entero % 100 == 0) ? 'CIEN ' : $centenas[$cent] . ' ';
                $entero %= 100;
            }

            // Decenas y unidades
            if ($entero > 0) {
                if ($entero < 10) {
                    $texto .= $unidades[$entero] . ' ';
                } else if ($entero >= 10 && $entero < 20) {
                    $texto .= $especiales['DIEZ'][$entero - 10] . ' ';
                } else if ($entero >= 20 && $entero < 30) {
                    $texto .= $especiales['VEINTE'][$entero - 20] . ' ';
                } else {
                    $dec = floor($entero / 10);
                    $uni = $entero % 10;
                    $texto .= $decenas[$dec] . ($uni > 0 ? ' Y ' . $unidades[$uni] : '') . ' ';
                }
            }
        }

        $texto = trim($texto) . ' CON ' . $decimal . '/100';

        return $texto;
    }
    @endphp

    <!-- Script para notificar a la ventana principal -->
    <script>
        // Imprimir automáticamente cuando se carga la página
        window.onload = function() {
            setTimeout(function() {
                window.print();

                // Notificar a la ventana padre que se ha completado la facturación
                if (window.opener) {
                    window.opener.postMessage('invoice-completed', '*');
                    console.log('Notificación enviada: invoice-completed');
                }

                // Cerrar esta ventana después de imprimir
                setTimeout(function() {
                    // window.close();
                }, 2000);
            }, 1000);
        };

        // Función para imprimir manualmente
        function printInvoice() {
            window.print();

            // Notificar a la ventana padre que se ha completado la facturación
            if (window.opener) {
                window.opener.postMessage('invoice-completed', '*');
                console.log('Notificación enviada: invoice-completed');
            }
        }
    </script>
</body>
</html>
