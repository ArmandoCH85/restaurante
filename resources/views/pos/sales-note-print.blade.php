<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota de Venta #{{ $invoice->series }}-{{ $invoice->number }}</title>
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
                padding: 3mm;
                font-size: 11px;
                line-height: 1.2;
            }
        }

        /* Estilos para papel de 57mm */
        @media print and (max-width: 57mm) {
            @page {
                size: 57mm 297mm;
                margin: 0;
            }

            body {
                padding: 2mm;
                font-size: 10px;
            }

            .company h1 {
                font-size: 13px;
            }

            .company p {
                font-size: 9px;
            }

            .document-title h2 {
                font-size: 12px;
            }

            .document-number {
                font-size: 13px;
            }

            th, td {
                padding: 2px 1px;
                font-size: 8px;
            }

            .info-row, .customer-row, .total-row {
                font-size: 9px;
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
            font-size: 14px;
            font-weight: bold;
            margin: 3px 0;
            line-height: 1.1;
        }

        .company p {
            margin: 1px 0;
            font-size: 10px;
            line-height: 1.1;
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

        .customer-info {
            margin: 10px 0;
        }

        .customer-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .order-info {
            margin: 10px 0;
        }

        .info-row {
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
        <h2>NOTA DE VENTA</h2>
        <div class="document-number">{{ $invoice->series }}-{{ $invoice->number }}</div>
    </div>

    <div class="info">
        <div class="info-row">
            <span class="label">Fecha:</span>
            <span>{{ $invoice->issue_date->format('d/m/Y') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Cliente:</span>
            <span>{{ $invoice->customer->name }}</span>
        </div>
        @if($invoice->customer->document_type != 'GENERIC')
        <div class="info-row">
            <span class="label">{{ $invoice->customer->document_type }}:</span>
            <span>{{ $invoice->customer->document_number }}</span>
        </div>
        @endif

        @if($invoice->order->service_type === 'delivery')
            @php
                // Lógica inteligente para obtener dirección y referencias
                $deliveryAddress = null;
                $deliveryReferences = null;

                // Primero intentar obtener del DeliveryOrder
                if($invoice->order->deliveryOrder) {
                    $deliveryAddress = $invoice->order->deliveryOrder->delivery_address;
                    $deliveryReferences = $invoice->order->deliveryOrder->delivery_references;
                }

                // Si el DeliveryOrder tiene valores por defecto, usar información del cliente
                if($deliveryAddress === 'Dirección pendiente de completar' || empty($deliveryAddress)) {
                    $deliveryAddress = $invoice->customer->address ?? null;
                }

                if($deliveryReferences === 'Referencias pendientes' || empty($deliveryReferences)) {
                    $deliveryReferences = $invoice->customer->address_references ?? null;
                }
            @endphp

            <!-- INFORMACIÓN DE DELIVERY -->
            @if($deliveryAddress)
            <div class="info-row">
                <span class="label">Dirección:</span>
                <span>{{ $deliveryAddress }}</span>
            </div>
            @endif
            @if($deliveryReferences)
            <div class="info-row">
                <span class="label">Referencias:</span>
                <span>{{ $deliveryReferences }}</span>
            </div>
            @endif
            @if($invoice->customer->phone)
            <div class="info-row">
                <span class="label">Teléfono:</span>
                <span>{{ $invoice->customer->phone }}</span>
            </div>
            @endif
        @elseif($invoice->order->service_type === 'dine_in' && $invoice->order->table)
            <!-- INFORMACIÓN DE MESA (EN LOCAL) -->
            <div class="info-row">
                <span class="label">Mesa:</span>
                <span>Mesa #{{ $invoice->order->table->number }}@if($invoice->order->table->location) - {{ ucfirst($invoice->order->table->location) }}@endif</span>
            </div>
        @else
            <!-- PARA LLEVAR O DRIVE THRU -->
            <div class="info-row">
                <span class="label">Tipo:</span>
                <span>
                    @switch($invoice->order->service_type)
                        @case('takeout')
                            Para llevar
                            @break
                        @case('drive_thru')
                            Drive Thru
                            @break
                        @default
                            Para llevar
                    @endswitch
                </span>
            </div>
        @endif
    </div>

    @if(isset($split_payment) && $split_payment)
    <div class="payment-info">
        <p class="payment-note">Pago dividido ({{ $invoice->notes }})</p>
        @if(isset($next_invoice_url) && $next_invoice_url)
        <button class="next-payment-btn no-print" onclick="window.location.href='{{ $next_invoice_url }}'">
            Ver siguiente comprobante
        </button>
        @endif
    </div>
    @endif

    <!-- DETALLES DEL PEDIDO (PRIMERO) -->
    <div style="border-top: 1px dashed #000; margin: 8px 0; padding-top: 5px;">
        @foreach($invoice->details as $detail)
            <div style="margin-bottom: 4px; font-size: 11px;">
                <div style="display: flex; justify-content: space-between;">
                    <span style="font-weight: bold;">{{ $detail->quantity }} x {{ $detail->description }}</span>
                    <span>{{ number_format($detail->subtotal, 2) }}</span>
                </div>
                @if($detail->unit_price != $detail->subtotal / $detail->quantity)
                    <div style="font-size: 9px; color: #666; margin-left: 10px;">
                        @ S/ {{ number_format($detail->unit_price, 2) }} c/u
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <!-- INFORMACIÓN DE PAGO (DESPUÉS DE DETALLES) -->
    <div class="info" style="border-top: 1px dashed #000; margin: 8px 0; padding-top: 5px;">
        <div class="info-row">
            <span class="label">Pago:</span>
            <span>
                @switch($invoice->payment_method)
                    @case('cash')
                        Efectivo
                        @break
                    @case('card')
                        Tarjeta
                        @break
                    @case('credit_card')
                        Tarjeta de Crédito
                        @break
                    @case('debit_card')
                        Tarjeta de Débito
                        @break
                    @case('digital_wallet')
                        Billetera Digital
                        @break
                    @case('bank_transfer')
                    @case('transfer')
                        Transferencia
                        @break
                    @case('yape')
                        Yape
                        @break
                    @case('plin')
                        Plin
                        @break
                    @case('multiple')
                        Múltiple
                        @break
                    @default
                        {{ ucfirst(str_replace('_', ' ', $invoice->payment_method)) }}
                @endswitch
            </span>
        </div>
        @if($invoice->payment_method === 'cash' && isset($change_amount) && $change_amount > 0)
        <div class="info-row">
            <span class="label">Recibido:</span>
            <span>S/ {{ number_format($invoice->payment_amount, 2) }}</span>
        </div>
        <div class="info-row">
            <span class="label">Vuelto:</span>
            <span>S/ {{ number_format($change_amount, 2) }}</span>
        </div>
        @endif
    </div>

    <!-- TOTALES PARA NOTAS DE VENTA -->
    <div class="totals">
        @if($invoice->tax > 0)
            <!-- Si por alguna razón la nota de venta tiene IGV, mostrarlo -->
            <div class="total-row">
                <span class="label">Subtotal:</span>
                <span>S/ {{ number_format($invoice->taxable_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span class="label">IGV (18%):</span>
                <span>S/ {{ number_format($invoice->tax, 2) }}</span>
            </div>
        @endif
        <div class="grand-total">
            <span class="label">Total:</span>
            <span>S/ {{ number_format($invoice->total, 2) }}</span>
        </div>
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

                // SOLUCIÓN KISS: Si viene del flujo unificado, limpiar carrito y redirigir
                @if(session('clear_cart_after_print') && session('table_id'))
                setTimeout(function() {
                    console.log('🧹 Limpiando carrito después de completar venta...');
                    // Redirigir directamente al mapa de mesas con parámetro de actualización
                    window.location.href = '{{ url("/tables?refresh=") }}' + Date.now();
                }, 3000); // Dar tiempo para que se complete la impresión
                @else
                // Cerrar esta ventana después de imprimir (flujo normal)
                setTimeout(function() {
                    // window.close();
                }, 2000);
                @endif
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

        // Función para abrir automáticamente la pre-cuenta si está configurado
        @if(isset($prebill_url) && $prebill_url)
        function openPreBill() {
            console.log('Abriendo pre-cuenta automáticamente...');
            window.open('{{ $prebill_url }}', '_blank', 'width=800,height=600');
        }

        // Abrir la pre-cuenta automáticamente después de un breve delay
        setTimeout(function() {
            openPreBill();
        }, 1500); // Esperar 1.5 segundos para que se cargue completamente la nota de venta
        @endif
    </script>
</body>
</html>
