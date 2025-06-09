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
                padding: 3mm;
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.2;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Estilos para papel de 57mm */
        @media print and (max-width: 57mm) {
            @page {
                size: 57mm auto;
                margin: 0;
            }

            body {
                width: 57mm;
                padding: 2mm;
                font-size: 11px;
            }

            .title {
                font-size: 16px;
            }

            .subtitle {
                font-size: 13px;
            }

            th, td {
                padding: 3px 1px;
                font-size: 10px;
            }

            .info-row {
                font-size: 10px;
            }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 8px;
            max-width: 800px;
            margin: 0 auto;
            background-color: #f9fafb;
            color: #111827;
        }
        .header {
            text-align: center;
            margin-bottom: 8px;
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
        }
        .title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 3px;
            line-height: 1.1;
        }
        .subtitle {
            font-size: 14px;
            margin-bottom: 3px;
            line-height: 1.1;
        }
        .info {
            margin-bottom: 8px;
        }
        .info-row {
            margin-bottom: 2px;
            font-size: 11px;
        }
        .label {
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        th {
            text-align: left;
            padding: 3px 2px;
            border-bottom: 1px solid #000;
            font-weight: bold;
            font-size: 10px;
        }
        td {
            padding: 3px 2px;
            border-bottom: 1px dashed #ccc;
            font-size: 10px;
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
            font-size: 9px;
            margin-top: 1px;
        }
        .footer {
            text-align: center;
            margin-top: 8px;
            border-top: 1px dashed #000;
            padding-top: 6px;
            font-size: 9px;
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
        .item-separator {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }

        /* Estilos para el botón de imprimir */
        .command-container.no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-button {
            background-color: #4CAF50; /* Verde */
            border: none;
            color: white;
            padding: 15px 32px;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 8px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            transition: 0.3s;
        }

        .print-button:hover {
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
        }

        .print-button svg {
            margin-right: 8px;
        }
    </style>
</head>
<body>
    <div class="command-container no-print">
        <button onclick="window.print()" class="print-button">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir
        </button>
    </div>
    <div class="ticket-container">
    <div class="header">
        <div class="title">COMANDA</div>
        <div class="subtitle">{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'Restaurante Ejemplo' }}</div>
        @if(\App\Models\CompanyConfig::getRuc())
            <div style="font-size: 12px; margin-top: 3px;">RUC: {{ \App\Models\CompanyConfig::getRuc() }}</div>
        @endif
        @if(\App\Models\CompanyConfig::getDireccion())
            <div style="font-size: 11px; margin-top: 2px;">{{ \App\Models\CompanyConfig::getDireccion() }}</div>
        @endif
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

    <div style="border-top: 1px dashed #000; margin: 8px 0; padding-top: 5px;">
        @foreach($order->orderDetails as $detail)
            <div style="margin-bottom: 4px; font-size: 11px;">
                <div style="display: flex; align-items: flex-start; gap: 8px;">
                    <span style="font-weight: bold; min-width: 20px; text-align: center; background: #f0f0f0; padding: 2px 4px; border-radius: 3px;">{{ $detail->quantity }}</span>
                    <div style="flex: 1;">
                        <div style="font-weight: bold;">{{ $detail->product->name }}</div>
                        @if($detail->notes)
                            <div style="font-style: italic; font-size: 9px; color: #666; margin-top: 1px;">{{ $detail->notes }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

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
    </div>
</body>
</html>
