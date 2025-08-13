<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden #{{ $order->id }} - Impresión</title>
    <style>
        @media print {
            @page {
                margin: 10mm;
                size: A4;
            }
            body {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
            }
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            font-size: 12px;
        }
        
        .info-section {
            margin-bottom: 20px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .info-label {
            font-weight: bold;
            width: 120px;
        }
        
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        
        .products-table th,
        .products-table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .products-table th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        
        .totals-section {
            border-top: 2px solid #000;
            padding-top: 15px;
            margin-top: 20px;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        
        .total-final {
            font-weight: bold;
            font-size: 18px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 10px;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            border-top: 1px dashed #000;
            padding-top: 15px;
        }
        
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #007cba;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
        }
        
        .print-button:hover {
            background: #005a87;
        }
    </style>
</head>
<body>
    <!-- Botón de Impresión -->
    <button onclick="window.print()" class="print-button no-print">🖨️ Imprimir</button>

    <!-- Header -->
    <div class="header">
        <h1>COMPROBANTE DE VENTA</h1>
        <p>Orden #{{ $order->id }}</p>
        <p>{{ $order->order_datetime->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Información General -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Cliente:</span>
            <span>{{ $order->customer?->name ?? 'CLIENTE GENERAL' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Mesa:</span>
            <span>{{ $order->table?->name ?? 'SIN MESA' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Mesero:</span>
            <span>{{ $order->user?->name ?? 'NO ASIGNADO' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tipo Servicio:</span>
            <span>
                {{ $order->service_type === 'dine_in' ? 'EN LOCAL' : 
                   ($order->service_type === 'delivery' ? 'DELIVERY' : 'PARA LLEVAR') }}
            </span>
        </div>
        @if($order->payment_method)
            <div class="info-row">
                <span class="info-label">Método Pago:</span>
                <span>{{ strtoupper($order->payment_method) }}</span>
            </div>
        @endif
        @if($order->invoices->isNotEmpty())
            @php $invoice = $order->invoices->first(); @endphp
            <div class="info-row">
                <span class="info-label">Comprobante:</span>
                <span>
                    {{ $invoice->invoice_type === 'sales_note' ? 'NOTA DE VENTA' : 
                       ($invoice->invoice_type === 'receipt' ? 'BOLETA' : 'FACTURA') }}
                    - {{ $invoice->series }}-{{ str_pad($invoice->number, 6, '0', STR_PAD_LEFT) }}
                </span>
            </div>
        @endif
    </div>

    <!-- Productos -->
    <table class="products-table">
        <thead>
            <tr>
                <th style="width: 50%;">PRODUCTO</th>
                <th style="width: 10%;">CANT.</th>
                <th style="width: 20%;">P. UNIT.</th>
                <th style="width: 20%;">SUBTOTAL</th>
            </tr>
        </thead>
        <tbody>
            @forelse($order->orderDetails as $detail)
                <tr>
                    <td>
                        {{ strtoupper($detail->product?->name ?? 'PRODUCTO NO ENCONTRADO') }}
                        @if($detail->notes)
                            <br><small style="font-style: italic;">{{ $detail->notes }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $detail->quantity }}</td>
                    <td class="text-right">S/ {{ number_format($detail->unit_price, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($detail->subtotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center">NO HAY PRODUCTOS</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totales -->
    <div class="totals-section">
        <div class="total-row">
            <span>SUBTOTAL:</span>
            <span>S/ {{ number_format($order->subtotal ?? 0, 2) }}</span>
        </div>
        
        @if($order->discount > 0)
            <div class="total-row">
                <span>DESCUENTO:</span>
                <span>-S/ {{ number_format($order->discount, 2) }}</span>
            </div>
        @endif
        
        @if($order->tax > 0)
            <div class="total-row">
                <span>IGV (18%):</span>
                <span>S/ {{ number_format($order->tax, 2) }}</span>
            </div>
        @endif
        
        <div class="total-row total-final">
            <span>TOTAL A PAGAR:</span>
            <span>S/ {{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <!-- Comentarios -->
    @if($order->comments)
        <div style="margin-top: 20px; padding: 10px; border: 1px solid #000;">
            <strong>COMENTARIOS:</strong><br>
            {{ $order->comments }}
        </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>¡Gracias por su preferencia!</p>
        <p>Sistema de Gestión - {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        window.addEventListener('load', function() {
            // Esperar un poco para que cargue todo el contenido
            setTimeout(function() {
                // Enfocar la ventana
                window.focus();
                // Opcionalmente auto-imprimir
                // window.print();
            }, 500);
        });
        
        // Cerrar ventana después de imprimir
        window.addEventListener('afterprint', function() {
            setTimeout(function() {
                window.close();
            }, 1000);
        });
    </script>
</body>
</html>