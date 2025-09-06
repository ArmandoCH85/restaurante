<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Boleta #{{ $invoice->series }}-{{ $invoice->number }}</title>
    <style>
        /* Estilos optimizados para papel térmico - MARGEN IZQUIERDO SEGURO */
        @page {
            size: 80mm auto;
            margin: 0mm 2mm 3mm 6mm;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            color: #000 !important;
            line-height: 1.3;
            width: 70mm;
            max-width: 70mm;
            margin: 0;
            padding: 0 2mm 2mm 2mm;
            box-sizing: border-box;
        }
        
        /* FORZAR COLOR NEGRO Y ESTILOS TÉRMICOS */
        * {
            color: #000 !important;
            -webkit-print-color-adjust: exact !important;
            color-adjust: exact !important;
        }
        
        .container {
            width: 100%;
            max-width: 66mm;
            margin: 0;
            padding: 0 0 0 2mm;
            text-align: left;
        }
        
        .header, .footer {
            text-align: center;
            margin: 0 auto;
            max-width: 64mm;
        }
        
        .header h1 {
            margin: 0 0 3px 0;
            font-size: 20px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .header p {
            margin: 1px 0;
            font-size: 20px;
            font-weight: bold;
        }
        
        .footer p {
            margin: 1px 0;
            font-size: 13px;
        }
        
        hr {
            border: 0;
            border-top: 1px dashed #000;
            margin: 4px 0;
        }
        
        .info-table, .items-table {
            width: 100%;
            max-width: 64mm;
            margin: 0;
            border-collapse: collapse;
        }
        
        .info-table td {
            padding: 1px 0;
            font-size: 14px;
            vertical-align: top;
        }
        
        .info-table td:first-child {
            width: 30%;
            font-weight: bold;
            font-size: 13px;
        }
        
        .info-table td:last-child {
            font-size: 13px;
            word-break: break-word;
        }
        
        .items-table th {
            padding: 3px 0;
            font-size: 13px;
            font-weight: bold;
            border-bottom: 1px solid #000;
            border-top: 1px solid #000;
        }
        
        .items-table td {
            padding: 2px 0;
            font-size: 12px;
            vertical-align: top;
            word-wrap: break-word;
            text-align: center;
        }
        
        .col-qty { 
            width: 10%; 
            text-align: center;
            font-weight: bold;
            font-size: 14px;
        }
        
        .col-desc { 
            width: 65%; 
            word-break: break-word;
            font-size: 14px;
            padding-right: 2px;
        }
        
        .col-total { 
            width: 25%; 
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        
        .totals {
            margin-top: 4px;
        }
        
        .totals .row {
            display: flex;
            justify-content: space-between;
            margin: 1px 0;
        }
        
        .totals .row .col-label {
            font-size: 14px;
            font-weight: bold;
        }
        
        .totals .row .col-value {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
        }
        
        .totals .total-final .col-label, 
        .totals .total-final .col-value {
            font-size: 16px;
            font-weight: bold;
            border-top: 1px solid #000;
            padding-top: 2px;
        }
        
        .payment-info {
            text-align: center;
            font-size: 13px;
            margin: 3px 0;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                font-weight: bold;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>BOLETA ELECTRÓNICA</h1>
            <p><strong style="font-size: 16px;">{{ \App\Models\CompanyConfig::getRazonSocial() }}</strong></p>
            <p style="font-size: 14px;">RUC: {{ \App\Models\CompanyConfig::getRuc() }}</p>
            <p style="font-size: 14px;">{{ \App\Models\CompanyConfig::getDireccion() }}</p>
            @if(\App\Models\CompanyConfig::getTelefono())
                <p style="font-size: 14px;">Tel: {{ \App\Models\CompanyConfig::getTelefono() }}</p>
            @endif
            <p><strong style="font-size: 15px;">B{{ $invoice->series }}-{{ str_pad($invoice->number, 8, '0', STR_PAD_LEFT) }}</strong></p>
        </div>
        <hr>
        <table class="info-table">
            <tr>
                <td><strong>Fecha:</strong></td>
                <td>{{ $invoice->issue_date ? \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') : now()->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Hora:</strong></td>
                <td>{{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('H:i:s') : now()->format('H:i:s') }}</td>
            </tr>
            {{-- Información del Cliente --}}
            <tr>
                <td><strong>Cliente:</strong></td>
                <td>{{ $invoice->client_name }}</td>
            </tr>
            @if($invoice->client_document)
            <tr>
                <td><strong>DNI:</strong></td>
                <td>{{ $invoice->client_document }}</td>
            </tr>
            @endif
            
            {{-- Para delivery: manejo inteligente de direcciones --}}
            @if($invoice->order && $invoice->order->service_type === 'delivery' && $invoice->order->deliveryOrder)
                @php
                    $deliveryOrder = $invoice->order->deliveryOrder;
                    $clientAddress = $invoice->client_address;
                    $deliveryAddress = $deliveryOrder->delivery_address;
                    $recipientAddress = $deliveryOrder->recipient_address;
                    
                    // Mostrar dirección del cliente solo si es diferente a la de entrega
                    $showClientAddress = $clientAddress && 
                                       $clientAddress !== $deliveryAddress && 
                                       $clientAddress !== 'Dirección pendiente de completar';
                @endphp
                
                @if($showClientAddress)
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td>{{ $clientAddress }}</td>
                </tr>
                @endif
                
                {{-- Separador visual para delivery --}}
                <tr><td colspan="2" style="text-align: center; font-weight: bold; padding: 4px 0; border-top: 1px dashed #000; border-bottom: 1px dashed #000;">🚚 INFORMACIÓN DE CONTACTO</td></tr>
                
                @if($deliveryAddress && $deliveryAddress !== 'Dirección pendiente de completar')
                <tr>
                    <td><strong>Dirección Entrega:</strong></td>
                    <td>{{ $deliveryAddress }}</td>
                </tr>
                @endif
                
                @if($deliveryOrder->delivery_references)
                <tr>
                    <td><strong>Referencias:</strong></td>
                    <td>{{ $deliveryOrder->delivery_references }}</td>
                </tr>
                @endif
                
                @if($deliveryOrder->recipient_name)
                <tr>
                    <td><strong>Recibe:</strong></td>
                    <td>{{ $deliveryOrder->recipient_name }}</td>
                </tr>
                @endif
                
                @if($deliveryOrder->recipient_phone)
                <tr>
                    <td><strong>Teléfono:</strong></td>
                    <td>{{ $deliveryOrder->recipient_phone }}</td>
                </tr>
                @endif
                
                {{-- Separador de cierre --}}
                <tr><td colspan="2" style="border-bottom: 1px dashed #000; padding: 2px 0;"></td></tr>
            @else
                {{-- Para no-delivery: mostrar dirección cliente normalmente --}}
                @if($invoice->client_address)
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td>{{ $invoice->client_address }}</td>
                </tr>
                @endif
            @endif
            @php
                $waiterName = null;
                
                // Prioridad 1: Usuario de la orden
                if ($invoice->order && $invoice->order->employee_id) {
                    $orderUser = \App\Models\User::find($invoice->order->employee_id);
                    if ($orderUser) {
                        $waiterName = $orderUser->name;
                    }
                }
                
                // Prioridad 2: Usuario directo de la factura
                if (!$waiterName && $invoice->employee_id) {
                    $invoiceUser = \App\Models\User::find($invoice->employee_id);
                    if ($invoiceUser) {
                        $waiterName = $invoiceUser->name;
                    }
                }
                
                // Prioridad 3: Empleado relacionado
                if (!$waiterName && $invoice->employee) {
                    $waiterName = $invoice->employee->full_name;
                }
                
                // Prioridad 4: Usuario actual como fallback
                if (!$waiterName && auth()->user()) {
                    $waiterName = auth()->user()->name . ' (Usuario actual)';
                }
                
                // Prioridad 5: Mensaje por defecto
                if (!$waiterName) {
                    $waiterName = 'Sin información del mesero';
                }
            @endphp
            
            <tr>
                <td><strong>Atendido por:</strong></td>
                <td>{{ $waiterName }}</td>
            </tr>
        </table>
        <hr>
        <table class="items-table">
            <thead>
                <tr>
                    <th class="col-qty">CANT</th>
                    <th class="col-desc">PRODUCTO</th>
                    <th class="col-total">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->details as $detail)
                <tr>
                    <td class="col-qty">{{ intval($detail->quantity) }}</td>
                    <td class="col-desc">{{ strtoupper($detail->description) }}</td>
                    <td class="col-total">{{ number_format($detail->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <hr>
        <div class="totals">
            @if($invoice->total > 0)
                <div class="row" style="display: flex; justify-content: space-between;">
                    <span class="col-label">OP. GRAVADAS:</span>
                    <span class="col-value">S/ {{ number_format($invoice->correct_subtotal, 2) }}</span>
                </div>
                <div class="row" style="display: flex; justify-content: space-between;">
                    <span class="col-label">IGV (18%):</span>
                    <span class="col-value">S/ {{ number_format($invoice->correct_igv, 2) }}</span>
                </div>
            @endif
            <div class="row total-final" style="display: flex; justify-content: space-between; margin-top: 5px;">
                <span class="col-label">TOTAL:</span>
                <span class="col-value">S/ {{ number_format($invoice->total, 2) }}</span>
            </div>
            @php
                // Calcular dinámicamente el vuelto en caso de que los datos de BD no estén correctos
                $totalPaid = 0;
                $changeAmount = 0;
                
                if ($invoice->order && $invoice->order->payments) {
                    $totalPaid = $invoice->order->payments->sum('amount');
                    $hasCashPayment = $invoice->order->payments->where('payment_method', 'cash')->isNotEmpty();
                    
                    if ($hasCashPayment && $totalPaid > $invoice->total) {
                        $changeAmount = $totalPaid - $invoice->total;
                    }
                }
                
                // Usar el vuelto de la BD si está correcto, sino usar el calculado
                $displayChange = ($invoice->change_amount > 0) ? $invoice->change_amount : $changeAmount;
                $displayPaid = ($invoice->payment_amount > 0) ? $invoice->payment_amount : $totalPaid;
            @endphp
            
            @if($displayChange > 0)
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">RECIBIDO:</span>
                <span class="col-value">S/ {{ number_format($displayPaid, 2) }}</span>
            </div>
            <div class="row" style="display: flex; justify-content: space-between;">
                <span class="col-label">🪙 VUELTO:</span>
                <span class="col-value">S/ {{ number_format($displayChange, 2) }}</span>
            </div>
            @endif
        </div>
        <hr>
        <div class="payment-info" style="text-align: center;">
            <p><strong>FORMA DE PAGO:</strong>
                {{ ucfirst(match($invoice->payment_method ?? 'cash') {
                    'cash' => 'Efectivo',
                    'credit_card' => 'Tarjeta de Crédito',
                    'debit_card' => 'Tarjeta de Débito',
                    'bank_transfer' => 'Transferencia Bancaria',
                    'digital_wallet' => 'Billetera Digital',
                    'yape' => 'Yape',
                    'plin' => 'Plin',
                    'pedidos_ya' => 'Pedidos Ya',
                    'didi_food' => 'Didi Food',
                    'mixto' => '💳 Pago Mixto',
                    'multiple' => '💳 Pago Múltiple',
                    default => ucfirst(str_replace('_', ' ', $invoice->payment_method ?? 'Efectivo'))
                }) }}
            </p>
            @if(($invoice->payment_method ?? 'cash') === 'mixto' && $invoice->order && $invoice->order->payments)
                <div style="font-size: 10px; margin-top: 5px; text-align: center;">
                    <strong>Detalle de pagos:</strong><br>
                    @foreach($invoice->order->payments as $payment)
                        {{ ucfirst(match($payment->payment_method) {
                            'cash' => 'Efectivo',
                            'credit_card' => 'Tarjeta Crédito',
                            'debit_card' => 'Tarjeta Débito',
                            'bank_transfer' => 'Transferencia',
                            'digital_wallet' => 'Billetera Digital',
                            'yape' => 'Yape',
                            'plin' => 'Plin',
                            'pedidos_ya' => 'Pedidos Ya',
                            'didi_food' => 'Didi Food',
                            'rappi' => 'Rappi',
                            'bita_express' => 'Bita Express',
                            default => ucfirst(str_replace('_', ' ', $payment->payment_method))
                        }) }}: S/ {{ number_format($payment->amount, 2) }}<br>
                    @endforeach
                </div>
            @endif

        </div>
        <hr>
        <div class="footer">
            <p>Representación impresa de la Boleta Electrónica</p>
            <p>Autorizado mediante Resolución de Superintendencia</p>
            <p>N° 203-2015/SUNAT</p>
            <p>Consulte su comprobante en www.sunat.gob.pe</p>
            <p>Gracias por su preferencia</p>
            <p><strong style="font-size: 16px;">{{ \App\Models\CompanyConfig::getRazonSocial() }}</strong></p>
        </div>
    </div>
    <script>
        window.addEventListener('load', function() {
            // Imprimir automáticamente al cargar
            setTimeout(function() {
                window.print();
                
                // Notificar a la ventana padre que se ha completado la facturación
                if (window.opener) {
                    window.opener.postMessage('invoice-completed', '*');
                    console.log('Notificación enviada: invoice-completed');
                }
                
                // Después de imprimir, redirigir al mapa de mesas
                window.addEventListener('afterprint', function() {
                    setTimeout(function() {
                        // Redirigir a mapa de mesas
                        if (window.opener) {
                            window.opener.location.href = '/admin/mapa-mesas';
                            window.close();
                        } else {
                            window.location.href = '/admin/mapa-mesas';
                        }
                    }, 500);
                });
                
                // Redirección alternativa por si no funciona afterprint
                setTimeout(function() {
                    if (window.opener) {
                        window.opener.location.href = '/admin/mapa-mesas';
                        window.close();
                    } else {
                        window.location.href = '/admin/mapa-mesas';
                    }
                }, 5000);
            }, 500);
        });
    </script>
</body>
</html>
