<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comanda #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 10px;
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

    @if($order->notes)
        <div class="info">
            <div class="label">Notas para la cocina:</div>
            <div>{{ $order->notes }}</div>
        </div>
    @endif

    <div class="footer">
        Generado el {{ $date }}
    </div>
</body>
</html>
