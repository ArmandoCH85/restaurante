<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comanda #{{ $order->id }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                width: 80mm;
                margin: 0;
                padding: 5mm;
                font-family: Arial, sans-serif;
                font-size: 14px;
            }

            .no-print {
                display: none !important;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 10px;
            max-width: 800px;
            margin: 0 auto;
            background-color: #f9fafb;
            color: #111827;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .subtitle {
            font-size: 16px;
            margin-bottom: 5px;
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
            margin-bottom: 15px;
        }
        th {
            text-align: left;
            padding: 5px;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }
        td {
            padding: 5px;
            border-bottom: 1px dashed #ccc;
        }
        .product-name {
            font-weight: bold;
        }
        .quantity {
            text-align: center;
            font-weight: bold;
        }
        .notes {
            font-style: italic;
            font-size: 12px;
            margin-top: 3px;
        }
        .footer {
            text-align: center;
            margin-top: 15px;
            border-top: 1px dashed #000;
            padding-top: 10px;
            font-size: 12px;
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
        <div class="title">COMANDA</div>
        <div class="subtitle">Restaurante Ejemplo</div>
    </div>

    <div class="info">
        <div class="info-row">
            <span class="label">Comanda #:</span> {{ $order->id }}
        </div>
        <div class="info-row">
            <span class="label">Fecha:</span> {{ $date }}
        </div>
        <div class="info-row">
            <span class="label">Mesa:</span> {{ $table ? 'Mesa #'.$table->number.' - '.$table->location : 'Venta Rápida' }}
        </div>
        <div class="info-row">
            <span class="label">Mesero:</span> {{ $order->employee->name ?? 'No asignado' }}
        </div>
        <div class="info-row">
            <span class="label">Tipo:</span> {{ $order->service_type === 'takeout' ? 'Para Llevar' : ($order->service_type === 'delivery' ? 'Delivery' : 'En Local') }}
        </div>
        @php
            // Extraer el nombre del cliente de las notas si existe
            $customerName = null;
            if ($order->notes && strpos($order->notes, 'Cliente:') === 0) {
                $customerName = trim(str_replace('Cliente:', '', $order->notes));
            }
        @endphp
        @if($customerName)
        <div class="info-row">
            <span class="label">Cliente:</span> {{ $customerName }}
        </div>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th width="15%">CANT.</th>
                <th width="85%">PRODUCTO</th>
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
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        // Extraer el nombre del cliente de las notas si existe
        $customerName = null;
        $kitchenNotes = $order->notes;

        if ($order->notes && strpos($order->notes, 'Cliente:') === 0) {
            // Si las notas empiezan con "Cliente:", extraer el nombre y quitar esa parte de las notas
            $customerName = trim(str_replace('Cliente:', '', $order->notes));
            $kitchenNotes = null; // No mostrar las notas de cliente como notas de cocina
        }
    @endphp

    @if($kitchenNotes)
        <div class="info">
            <div class="label">Notas para la cocina:</div>
            <div>{{ $kitchenNotes }}</div>
        </div>
    @endif

    <div class="footer">
        Generado el {{ $date }}
    </div>

    <div class="action-buttons no-print" style="display: flex; gap: 10px; margin-top: 20px; justify-content: center;">
        <button class="print-button" onclick="window.print()" style="background-color: #1a56db; padding: 8px 16px; border-radius: 6px; color: white; border: none; display: flex; align-items: center; gap: 5px; font-weight: 500; cursor: pointer; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M5 1a2 2 0 0 0-2 2v1h10V3a2 2 0 0 0-2-2H5zm6 8H5a1 1 0 0 0-1 1v3a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-3a1 1 0 0 0-1-1z"/>
                <path d="M0 7a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2h-1v-2a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v2H2a2 2 0 0 1-2-2V7zm2.5 1a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            </svg>
            Imprimir
        </button>
        <button class="print-button" style="background-color: #10b981; padding: 8px 16px; border-radius: 6px; color: white; border: none; display: flex; align-items: center; gap: 5px; font-weight: 500; cursor: pointer; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);" onclick="window.close()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                <path d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
            </svg>
            Cerrar
        </button>
    </div>
</body>
</html>
