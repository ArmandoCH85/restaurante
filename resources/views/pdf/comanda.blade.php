<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comanda #{{ $order->id }}</title>
    <style>
        /* Estilos optimizados para impresora térmica de 80mm */
        @page {
            margin: 5mm;
        }
        body {
            font-family: 'monospace', sans-serif;
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
            width: 70mm; /* Ancho aproximado para papel de 80mm */
        }
        .container {
            width: 100%;
            padding: 0;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header .info {
            font-size: 9pt;
        }
        hr {
            border: 0;
            border-top: 1px dashed #000;
            margin: 10px 0;
        }
        .item-table {
            width: 100%;
        }
        .item-table th, .item-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .item-table .col-qty { width: 15%; text-align: left; }
        .item-table .col-desc { width: 85%; }
        .notes {
            margin-top: 10px;
            font-size: 9pt;
        }
        .notes p {
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>COMANDA</h1>
            <hr>
            <div class="info">
                @if($order->table)
                    <strong>Mesa: {{ $order->table->number }}</strong><br>
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
                    <td class="col-desc">{{ $detail->product->name }}</td>
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
    </div>
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
