<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura Electrónica #{{ $invoice->series }}-{{ $invoice->number }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Detectar si es impresión térmica basado en parámetro URL */
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

            /* Ocultar elementos no necesarios en formato térmico */
            .thermal-hide {
                display: none !important;
            }
        }

        /* Estilos para formato A4 normal cuando no es térmica */
        @media print and (min-width: 200mm) {
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

            .thermal-only {
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

        .thermal-document-number {
            font-size: 14px;
            font-weight: bold;
            margin-top: 3px;
        }

        /* Estilos A4 normales */
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
        /* Estilos térmicos para información */
        .thermal-info {
            margin: 6px 0;
        }

        .thermal-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 10px;
        }

        .thermal-customer-info {
            margin: 6px 0;
        }

        .thermal-customer-row {
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

        /* Estilos A4 normales */
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

        /* Estilos térmicos para footer y elementos adicionales */
        .thermal-footer {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }

        .thermal-qr-code {
            text-align: center;
            margin: 8px 0;
        }

        .thermal-notice {
            margin-top: 8px;
            font-style: italic;
            text-align: center;
            font-size: 8px;
            line-height: 1.2;
        }

        .thermal-text-amount {
            margin: 6px 0;
            padding: 3px;
            border: 1px solid #000;
            font-style: italic;
            font-size: 9px;
            text-align: center;
        }

        /* Clases de utilidad térmica */
        .thermal-quantity {
            text-align: center;
        }

        .thermal-price, .thermal-subtotal {
            text-align: right;
        }

        /* Estilos A4 normales */
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
                <h2>
                    @switch($invoice->invoice_type)
                        @case('invoice')
                            FACTURA ELECTRÓNICA
                            @break
                        @case('receipt')
                            BOLETA ELECTRÓNICA
                            @break
                        @case('sales_note')
                            NOTA DE VENTA
                            @break
                        @default
                            COMPROBANTE DE PAGO
                    @endswitch
                </h2>
                <div class="thermal-document-number">{{ $invoice->series }}-{{ $invoice->number }}</div>
            </div>

            <div class="thermal-info">
                <div class="thermal-info-row">
                    <span class="label">Fecha:</span>
                    <span>{{ $invoice->issue_date->format('d/m/Y') }}</span>
                </div>
                <div class="thermal-info-row">
                    <span class="label">
                        @if($invoice->invoice_type == 'invoice')
                            Razón Social:
                        @else
                            Cliente:
                        @endif
                    </span>
                    <span>{{ $invoice->client_name }}</span>
                </div>
                <div class="thermal-info-row">
                    <span class="label">
                        @if($invoice->invoice_type == 'invoice')
                            RUC:
                        @elseif($invoice->invoice_type == 'receipt')
                            DNI:
                        @else
                            Doc:
                        @endif
                    </span>
                    <span>{{ $invoice->client_document }}</span>
                </div>


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
                        <div class="thermal-info-row">
                            <span class="label">Dirección:</span>
                            <span>{{ $deliveryAddress }}</span>
                        </div>
                        @if($deliveryReferences)
                        <div class="thermal-info-row">
                            <span class="label">Referencia:</span>
                            <span>{{ $deliveryReferences }}</span>
                        </div>
                        @endif
                    @else
                        <div class="thermal-info-row">
                            <span class="label">Tipo:</span>
                            <span>Delivery</span>
                        </div>
                    @endif
                @elseif($invoice->order->table_id && $invoice->order->table)
                    <!-- INFORMACIÓN DE MESA -->
                    <div class="thermal-info-row">
                        <span class="label">Mesa:</span>
                        <span>Mesa #{{ $invoice->order->table->number }}@if($invoice->order->table->location) - {{ ucfirst($invoice->order->table->location) }}@endif</span>
                    </div>
                @else
                    <!-- INFORMACIÓN DE TIPO DE SERVICIO -->
                    @if($invoice->order->service_type === 'dine_in')
                        <!-- EN LOCAL: Mostrar mesa si existe, sino "En local" -->
                        <div class="thermal-info-row">
                            <span class="label">Mesa:</span>
                            <span>
                                @if($invoice->order->table_id && $invoice->order->table)
                                    Mesa #{{ $invoice->order->table->number }}@if($invoice->order->table->location) - {{ ucfirst($invoice->order->table->location) }}@endif
                                @else
                                    En local
                                @endif
                            </span>
                        </div>
                    @elseif($invoice->order->service_type === 'takeout')
                        <!-- PARA LLEVAR -->
                        <div class="thermal-info-row">
                            <span class="label">Tipo:</span>
                            <span>Para llevar</span>
                        </div>
                    @else
                        <!-- FALLBACK -->
                        <div class="thermal-info-row">
                            <span class="label">Tipo:</span>
                            <span>Para llevar</span>
                        </div>
                    @endif
                @endif
            </div>

            @if(isset($split_payment) && $split_payment)
            <div class="thermal-info">
                <p style="font-style: italic; font-size: 9px;">Este es un comprobante por pago dividido ({{ $invoice->notes }})</p>
            </div>
            @endif

            <!-- DETALLES DEL PEDIDO (PRIMERO) -->
            <div style="border-top: 1px dashed #000; margin: 6px 0; padding-top: 3px;">
                @foreach($invoice->details as $detail)
                    <div style="margin-bottom: 3px; font-size: 10px;">
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
            <div class="thermal-info" style="border-top: 1px dashed #000; margin: 6px 0; padding-top: 3px;">
                <div class="thermal-info-row">
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
                <div class="thermal-info-row">
                    <span class="label">Recibido:</span>
                    <span>S/ {{ number_format($invoice->payment_amount, 2) }}</span>
                </div>
                <div class="thermal-info-row">
                    <span class="label">Vuelto:</span>
                    <span>S/ {{ number_format($change_amount, 2) }}</span>
                </div>
                @endif
            </div>

            <div class="thermal-text-amount">
                SON: {{ ucfirst(num_to_letras($invoice->total)) }} SOLES
            </div>

            <!-- TOTALES CON IGV -->
            <div class="thermal-totals">
                <div class="thermal-total-row">
                    <span class="label">Subtotal:</span>
                    <span>S/ {{ number_format($invoice->taxable_amount, 2) }}</span>
                </div>
                <div class="thermal-total-row">
                    <span class="label">IGV (18%):</span>
                    <span>S/ {{ number_format($invoice->tax, 2) }}</span>
                </div>
                <div class="thermal-grand-total">
                    <span class="label">Total:</span>
                    <span>S/ {{ number_format($invoice->total, 2) }}</span>
                </div>
            </div>

            <div class="thermal-footer">
                Gracias por su preferencia
            </div>
        </div>

        <!-- Versión A4 normal (oculta en impresión térmica) -->
        <div class="thermal-hide">
            <div class="header">
                <div class="company-info">
                    <div>
                        <h1>{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'RESTAURANTE EJEMPLO S.A.C.' }}</h1>
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

            <div class="info">
                <div class="info-row">
                    <span class="label">FECHA:</span> {{ $invoice->issue_date->format('d/m/Y') }}
                </div>
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
                            <span class="label">DIRECCIÓN:</span> {{ $deliveryAddress }}
                        </div>
                        @if($deliveryReferences)
                        <div class="info-row">
                            <span class="label">REFERENCIA:</span> {{ $deliveryReferences }}
                        </div>
                        @endif
                    @else
                        <div class="info-row">
                            <span class="label">TIPO:</span> Delivery
                        </div>
                    @endif
                @elseif($invoice->order->table_id && $invoice->order->table)
                    <!-- INFORMACIÓN DE MESA -->
                    <div class="info-row">
                        <span class="label">MESA:</span> Mesa #{{ $invoice->order->table->number }}@if($invoice->order->table->location) - {{ ucfirst($invoice->order->table->location) }}@endif
                    </div>
                @else
                    <!-- INFORMACIÓN DE TIPO DE SERVICIO -->
                    @if($invoice->order->service_type === 'dine_in')
                        <!-- EN LOCAL: Mostrar mesa si existe, sino "En local" -->
                        <div class="info-row">
                            <span class="label">MESA:</span>
                            @if($invoice->order->table_id && $invoice->order->table)
                                Mesa #{{ $invoice->order->table->number }}@if($invoice->order->table->location) - {{ ucfirst($invoice->order->table->location) }}@endif
                            @else
                                En local
                            @endif
                        </div>
                    @elseif($invoice->order->service_type === 'takeout')
                        <!-- PARA LLEVAR -->
                        <div class="info-row">
                            <span class="label">TIPO:</span>
                            Para llevar
                        </div>
                    @else
                        <!-- FALLBACK -->
                        <div class="info-row">
                            <span class="label">TIPO:</span>
                            Para llevar
                        </div>
                    @endif
                @endif
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

            <!-- DETALLES DEL PEDIDO (PRIMERO) -->
            <div style="border-top: 1px solid #000; margin: 15px 0; padding-top: 10px;">
                @foreach($invoice->details as $detail)
                    <div style="margin-bottom: 8px; font-size: 12px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: bold;">{{ $detail->quantity }} x {{ $detail->description }}</span>
                            <span>S/ {{ number_format($detail->subtotal, 2) }}</span>
                        </div>
                        @if($detail->unit_price != $detail->subtotal / $detail->quantity)
                            <div style="font-size: 10px; color: #666; margin-left: 15px;">
                                @ S/ {{ number_format($detail->unit_price, 2) }} c/u
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- INFORMACIÓN DE PAGO (DESPUÉS DE DETALLES) -->
            <div class="info" style="border-top: 1px solid #000; margin: 15px 0; padding-top: 10px;">
                <div class="info-row">
                    <span class="label">FORMA DE PAGO:</span>
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
                </div>
                @if($invoice->payment_method === 'cash' && isset($change_amount) && $change_amount > 0)
                <div class="info-row">
                    <span class="label">RECIBIDO:</span> S/ {{ number_format($invoice->payment_amount, 2) }}
                </div>
                <div class="info-row">
                    <span class="label">VUELTO:</span> S/ {{ number_format($change_amount, 2) }}
                </div>
                @endif
            </div>

            <div class="text-amount">
                SON: {{ ucfirst(num_to_letras($invoice->total)) }} SOLES
            </div>

            <!-- TOTALES CON IGV -->
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

            <div class="footer">
                Gracias por su preferencia
            </div>
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

        // Función para abrir automáticamente la pre-cuenta si está configurado
        @if(isset($prebill_url) && $prebill_url)
        function openPreBill() {
            console.log('Abriendo pre-cuenta automáticamente...');
            window.open('{{ $prebill_url }}', '_blank', 'width=800,height=600');
        }

        // Abrir la pre-cuenta automáticamente después de un breve delay
        setTimeout(function() {
            openPreBill();
        }, 1500); // Esperar 1.5 segundos para que se cargue completamente la factura
        @endif
    </script>
</body>
</html>
