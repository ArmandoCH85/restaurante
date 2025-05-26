<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura Electrónica #{{ $invoice->series }}-{{ $invoice->number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @media print {
            @page {
                size: A4;
                margin: 10mm;
            }

            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                font-size: 12px;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #000;
        }
        .company-info {
            width: 50%;
        }
        .document-info {
            width: 40%;
            border: 1px solid #000;
            padding: 10px;
            text-align: center;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 5px;
        }
        .info {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }
        .client-info {
            width: 60%;
            border: 1px solid #000;
            padding: 10px;
        }
        .operation-info {
            width: 35%;
            border: 1px solid #000;
            padding: 10px;
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
            text-align: center;
            padding: 8px;
            border: 1px solid #000;
            background-color: #f2f2f2;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border: 1px solid #000;
        }
        .quantity {
            text-align: center;
        }
        .price, .subtotal {
            text-align: right;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
            font-size: 11px;
        }
        .totals {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .totals td {
            padding: 5px;
            border: 1px solid #000;
        }
        .totals .label {
            text-align: left;
            width: 60%;
        }
        .totals .value {
            text-align: right;
            width: 40%;
        }
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            background-color: #f2f2f2;
        }
        .notice {
            margin-top: 20px;
            font-style: italic;
            text-align: center;
            font-size: 11px;
        }
        .qr-code {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
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
        .text-amount {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #000;
            font-style: italic;
        }
        .voided-stamp {
            position: absolute;
            top: 30%;
            left: 20%;
            transform: rotate(-45deg);
            transform-origin: center;
            font-size: 70px;
            font-weight: bold;
            color: rgba(255, 0, 0, 0.3);
            border: 15px solid rgba(255, 0, 0, 0.3);
            padding: 10px 40px;
            text-align: center;
            z-index: 100;
            pointer-events: none;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($invoice->tax_authority_status === 'voided')
        <div class="voided-stamp">
            ANULADO<br>
            Fecha: {{ $invoice->voided_date->format('d/m/Y') }}
        </div>
        @endif

        <div class="header">
            <div class="company-info">
                <div class="logo">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" width="150">
                </div>
                <div>
                    <h1>RESTAURANTE EJEMPLO S.A.C.</h1>
                    <p>RUC: 20123456789</p>
                    <p>Av. Ejemplo 123, Lima</p>
                    <p>Tel: (01) 123-4567</p>
                </div>
            </div>
            <div class="document-info">
                <div class="title">
                    @switch($invoice->invoice_type)
                        @case('invoice')
                            FACTURA ELECTRÓNICA
                            @break
                        @case('receipt')
                            BOLETA DE VENTA ELECTRÓNICA
                            @break
                        @case('sales_note')
                            NOTA DE VENTA
                            @break
                        @default
                            COMPROBANTE DE PAGO
                    @endswitch
                </div>
                <div class="subtitle">{{ $invoice->series }}-{{ $invoice->number }}</div>
                <div>Fecha de emisión: {{ $invoice->issue_date->format('d/m/Y') }}</div>
            </div>
        </div>

        <div class="customer-info">
            <div class="customer-row">
                <span class="label">Cliente:</span>
                <span>{{ $invoice->customer->name }}</span>
            </div>
            <div class="customer-row">
                <span class="label">RUC:</span>
                <span>{{ $invoice->customer->document_number }}</span>
            </div>
            <div class="customer-row">
                <span class="label">Dirección:</span>
                <span>{{ $invoice->customer->address }}</span>
            </div>
        </div>

        <div class="info">
            <div class="client-info">
                <div class="info-row">
                    <span class="label">
                        @if($invoice->invoice_type == 'invoice')
                            RAZÓN SOCIAL:
                        @else
                            CLIENTE:
                        @endif
                    </span>
                    {{ $invoice->client_name }}
                </div>
                <div class="info-row">
                    <span class="label">
                        @if($invoice->invoice_type == 'invoice')
                            RUC:
                        @elseif($invoice->invoice_type == 'receipt')
                            DNI:
                        @else
                            DOC:
                        @endif
                    </span>
                    {{ $invoice->client_document }}
                </div>
                <div class="info-row">
                    <span class="label">DIRECCIÓN:</span> {{ $invoice->client_address }}
                </div>
            </div>
            <div class="operation-info">
                <div class="info-row">
                    <span class="label">FECHA:</span> {{ $date }}
                </div>
                <div class="info-row">
                    <span class="label">FORMA DE PAGO:</span>
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
                </div>
                @if($invoice->payment_method === 'cash' && isset($change_amount) && $change_amount > 0)
                <div class="info-row">
                    <span class="label">MONTO RECIBIDO:</span> S/ {{ number_format($invoice->payment_amount, 2) }}
                </div>
                <div class="info-row">
                    <span class="label">VUELTO:</span> S/ {{ number_format($change_amount, 2) }}
                </div>
                @endif
                <div class="info-row">
                    <span class="label">MESA:</span> {{ $invoice->order->table->name ?? 'Para llevar' }}
                </div>
            </div>
        </div>

        @if(isset($split_payment) && $split_payment)
        <div class="payment-note">
            <p>Este es un comprobante por pago dividido {{ $invoice->notes }}</p>
            @if(isset($next_invoice_url) && $next_invoice_url)
            <div class="no-print text-center my-3">
                <button class="print-button" style="background-color: #10b981;" onclick="window.location.href='{{ $next_invoice_url }}'">
                    Ver siguiente comprobante
                </button>
            </div>
            @endif
        </div>
        @endif

        <table>
            <thead>
                <tr>
                    <th width="10%">CANT</th>
                    <th width="10%">UNIDAD</th>
                    <th width="40%">DESCRIPCIÓN</th>
                    <th width="15%">V.UNIT</th>
                    <th width="10%">DESC</th>
                    <th width="15%">IMPORTE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $detail)
                    <tr>
                        <td class="quantity">{{ $detail->quantity }}</td>
                        <td class="quantity">UND</td>
                        <td>{{ $detail->description }}</td>
                        <td class="price">{{ number_format($detail->unit_price, 2) }}</td>
                        <td class="price">0.00</td>
                        <td class="subtotal">{{ number_format($detail->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-amount">
            SON: {{ ucfirst(num_to_letras($invoice->total)) }} SOLES
        </div>

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

        @if($invoice->invoice_type != 'sales_note')
        <div class="qr-code">
            <!-- QR code: RUC|TIPO DOC|SERIE|NUMERO|MTO IGV|MTO TOTAL|FECHA EMISIÓN|TIPO DOC ADQUIRIENTE|NRO DOC ADQUIRIENTE -->
            <img src="data:image/png;base64,{{ $qr_code ?? 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=' }}" width="150" height="150">
        </div>
        @endif

        <div class="notice">
            @switch($invoice->invoice_type)
                @case('invoice')
                    Representación impresa de la Factura Electrónica
                    @break
                @case('receipt')
                    Representación impresa de la Boleta de Venta Electrónica
                    @break
                @case('sales_note')
                    Nota de Venta - Documento Interno
                    @break
                @default
                    Representación impresa del Comprobante de Pago
            @endswitch
            @if($invoice->invoice_type != 'sales_note')
            <br>Autorizado mediante Resolución de Superintendencia N° 000-2023/SUNAT
            <br>Consulte su comprobante en: www.sunat.gob.pe
            @endif
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
            <button class="print-button" style="background-color: #10b981;" onclick="closeWindowAndNotify()">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                </svg>
                Cerrar ventana
            </button>
        </div>
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
                    window.close();
                }, 2000);
            }, 1000);
        };

        // Función para cerrar la ventana y notificar
        function closeWindowAndNotify() {
            // Notificar a la ventana padre que se ha completado la facturación
            if (window.opener) {
                window.opener.postMessage('invoice-completed', '*');
                console.log('Notificación enviada: invoice-completed');
            }

            // Cerrar la ventana
            window.close();
        }

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
