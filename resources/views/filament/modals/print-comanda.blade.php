<div id="comanda-to-print" class="p-4">
    <style>
        /* Estilos específicos para la vista de impresión dentro del modal */
        #comanda-to-print {
            font-family: 'monospace', sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
            width: 100%; /* El ancho se controla por el contenedor del ticket */
        }
        .ticket-container {
            width: 300px; /* Ancho típico de ticket 80mm en pantalla */
            margin: 0 auto;
            padding: 5mm;
            background: #fff;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 { margin: 0; font-size: 14pt; font-weight: bold; text-transform: uppercase; }
        .header .info { font-size: 9pt; }
        #comanda-to-print hr { border: 0; border-top: 1px dashed #000; margin: 10px 0; }
        .item-table { width: 100%; }
        .item-table th, .item-table td { padding: 2px 0; vertical-align: top; }
        .item-table .col-qty { width: 15%; text-align: left; }
        .item-table .col-desc { width: 85%; }
        .notes { margin-top: 10px; font-size: 9pt; }
        .notes p { margin: 0; }

        /* Estilos para la impresión */
        @media print {
            body * {
                visibility: hidden;
            }
            #comanda-to-print, #comanda-to-print * {
                visibility: visible;
            }
            #comanda-to-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
            }
            .ticket-container {
                width: 70mm; /* Ancho fijo para impresión */
                margin: 0;
                box-shadow: none;
            }
        }
    </style>

    <div class="ticket-container">
        @if($order)
        <div class="header">
            <h1>COMANDA</h1>
            <hr>
            <div class="info">
                @if($order->table)
                    <strong>Mesa: {{ $order->table->number }}</strong><br>
                @else
                    {{-- ✅ Mostrar nombre del cliente solo para venta directa --}}
                    @if(!empty($customerNameForComanda))
                        <strong>Cliente: {{ $customerNameForComanda }}</strong><br>
                    @endif
                    <strong>VENTA DIRECTA</strong><br>
                @endif
                <strong>Orden:</strong> #{{ $order->id }}<br>
                <strong>Mesero:</strong> {{ $order->employee->name }}<br>
                <strong>Fecha:</strong> {{ $order->created_at->format('d/m/Y H:i:s') }}
            </div>
            <hr>
        </div>

        <table class="item-table">
            <thead>
                <tr>
                    <th class="col-qty">Cant</th>
                    <th class="col-desc">Producto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderDetails as $detail)
                <tr>
                    <td class="col-qty">{{ $detail->quantity }}</td>
                    <td class="col-desc">
                        {{ $detail->product->name }}
                        @if(strpos($detail->notes, 'HELADA') !== false)
                            <span style="font-size: 8pt; font-weight: bold;">(HELADA)</span>
                        @elseif(strpos($detail->notes, 'AL TIEMPO') !== false)
                            <span style="font-size: 8pt; font-weight: bold;">(AL TIEMPO)</span>
                        @elseif(strpos($detail->notes, 'FRESCA') !== false)
                            <span style="font-size: 8pt; font-weight: bold;">(FRESCA)</span>
                        @elseif(strpos($detail->notes, 'ROJO') !== false)
                            <span style="font-size: 8pt; font-weight: bold;">(ROJO)</span>
                        @elseif(strpos($detail->notes, 'JUGOSO') !== false)
                            <span style="font-size: 8pt; font-weight: bold;">(JUGOSO)</span>
                        @elseif(strpos($detail->notes, 'TRES CUARTOS') !== false)
                            <span style="font-size: 8pt; font-weight: bold;">(TRES CUARTOS)</span>
                        @elseif(strpos($detail->notes, 'BIEN COCIDO') !== false)
                            <span style="font-size: 8pt; font-weight: bold;">(BIEN COCIDO)</span>
                        @endif
                        
                        @if($detail->notes)
                            @php
                                $notesText = $detail->notes;
                                // Eliminar las palabras de temperatura y punto de cocción de las notas para no mostrarlas dos veces
                                $notesText = str_replace(['HELADA', 'AL TIEMPO', 'FRESCA', 'ROJO', 'JUGOSO', 'TRES CUARTOS', 'BIEN COCIDO'], '', $notesText);
                                $notesText = trim($notesText);
                            @endphp
                            
                            @if($notesText)
                                <div style="font-size: 8pt; font-style: italic; margin-top: 2px;">
                                    {{ $notesText }}
                                </div>
                            @endif
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($order->notes)
        <hr>
        <div class="notes">
            <p><strong>Notas:</strong></p>
            <p>{{ $order->notes }}</p>
        </div>
        @endif
        @else
        <div class="header">
            <h1>COMANDA</h1>
            <hr>
            <div class="info">
                <strong>Error:</strong> No se pudo crear la orden temporal.<br>
                <strong>Motivo:</strong> Carrito vacío o error en el sistema.
            </div>
            <hr>
        </div>
        @endif
    </div>
</div>
