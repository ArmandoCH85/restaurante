<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $quotation->quotation_number }}</title>
    <style>
        @page {
            margin: 1cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            background-color: #fff;
        }
        .container {
            width: 100%;
            margin: 0 auto;
        }
        .header {
            position: relative;
            height: 100px;
            margin-bottom: 30px;
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 10px;
        }
        .logo {
            position: absolute;
            top: 0;
            left: 0;
            max-height: 80px;
        }
        .company-info {
            position: absolute;
            top: 0;
            right: 0;
            text-align: right;
        }
        .company-info h1 {
            font-size: 18px;
            margin: 0 0 5px 0;
            color: #3b82f6;
        }
        .quotation-box {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            border-radius: 5px;
        }
        .quotation-number {
            font-size: 16px;
            font-weight: bold;
            color: #3b82f6;
            margin-bottom: 5px;
        }
        .quotation-date {
            font-size: 12px;
            color: #6b7280;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            float: right;
        }
        .status-draft {
            background-color: #e5e7eb;
            color: #4b5563;
        }
        .status-sent {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-rejected {
            background-color: #fee2e2;
            color: #b91c1c;
        }
        .status-expired {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-converted {
            background-color: #ede9fe;
            color: #5b21b6;
        }
        .customer-info, .billing-info {
            margin-bottom: 20px;
        }
        h2 {
            font-size: 14px;
            margin: 0 0 10px 0;
            color: #3b82f6;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background-color: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            width: 300px;
            float: right;
            margin-top: 20px;
        }
        .totals table {
            width: 100%;
        }
        .totals th {
            text-align: right;
            width: 50%;
            background-color: transparent;
            border: none;
            padding: 5px;
        }
        .totals td {
            text-align: right;
            border: none;
            padding: 5px;
        }
        .total-row {
            font-size: 14px;
            font-weight: bold;
            color: #3b82f6;
        }
        .total-row th, .total-row td {
            border-top: 2px solid #e5e7eb;
            padding-top: 10px;
        }
        .notes, .terms {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e5e7eb;
            background-color: #f9fafb;
            border-radius: 5px;
        }
        .notes h3, .terms h3 {
            font-size: 14px;
            margin: 0 0 10px 0;
            color: #3b82f6;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
            text-align: center;
        }
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(203, 213, 225, 0.3);
            z-index: -1;
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
            <div class="company-info">
                <h1>RESTAURANTE EJEMPLO</h1>
                <p>Av. Principal 123, Lima, Perú</p>
                <p>Teléfono: (01) 123-4567</p>
                <p>Email: info@restauranteejemplo.com</p>
                <p>RUC: 20123456789</p>
            </div>
        </div>

        <div class="quotation-box">
            <div class="quotation-number">COTIZACIÓN N° {{ $quotation->quotation_number }}</div>
            <div class="quotation-date">
                Fecha de emisión: {{ $quotation->issue_date->format('d/m/Y') }} |
                Válido hasta: {{ $quotation->valid_until->format('d/m/Y') }}
            </div>
            <div class="status-badge status-{{ $quotation->status }}">
                @switch($quotation->status)
                    @case('draft')
                        Borrador
                        @break
                    @case('sent')
                        Enviada
                        @break
                    @case('approved')
                        Aprobada
                        @break
                    @case('rejected')
                        Rechazada
                        @break
                    @case('expired')
                        Vencida
                        @break
                    @case('converted')
                        Convertida a Pedido
                        @break
                    @default
                        {{ $quotation->status }}
                @endswitch
            </div>
        </div>

        <div class="customer-info">
            <h2>DATOS DEL CLIENTE</h2>
            <table>
                <tr>
                    <td width="20%"><strong>Cliente:</strong></td>
                    <td width="30%">{{ $customer->name }}</td>
                    <td width="20%"><strong>{{ $customer->document_type }}:</strong></td>
                    <td width="30%">{{ $customer->document_number }}</td>
                </tr>
                <tr>
                    <td><strong>Dirección:</strong></td>
                    <td>{{ $customer->address ?? 'No especificada' }}</td>
                    <td><strong>Teléfono:</strong></td>
                    <td>{{ $customer->phone ?? 'No especificado' }}</td>
                </tr>
                <tr>
                    <td><strong>Email:</strong></td>
                    <td>{{ $customer->email ?? 'No especificado' }}</td>
                    <td><strong>Atendido por:</strong></td>
                    <td>{{ $user->name }}</td>
                </tr>
            </table>
        </div>

        <h3 style="margin-top: 30px; margin-bottom: 10px; color: #3b82f6; font-size: 16px;">DETALLE DE PRODUCTOS</h3>
        <table>
            <thead>
                <tr>
                    <th class="text-center" width="5%">N°</th>
                    <th width="45%">Descripción</th>
                    <th class="text-center" width="10%">Cant.</th>
                    <th class="text-right" width="15%">P. Unit.</th>
                    <th class="text-right" width="15%">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($details as $index => $detail)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $detail->product->name }}</strong>
                            @if($detail->notes)
                                <br><span style="color: #6b7280; font-size: 11px;">{{ $detail->notes }}</span>
                            @endif
                        </td>
                        <td class="text-center">{{ $detail->quantity }}</td>
                        <td class="text-right">S/ {{ number_format($detail->unit_price, 2) }}</td>
                        <td class="text-right">S/ {{ number_format($detail->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <th>Subtotal:</th>
                    <td>S/ {{ number_format($quotation->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <th>IGV (18%):</th>
                    <td>S/ {{ number_format($quotation->tax, 2) }}</td>
                </tr>
                @if($quotation->discount > 0)
                    <tr>
                        <th>Descuento:</th>
                        <td>S/ {{ number_format($quotation->discount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <th>TOTAL:</th>
                    <td>S/ {{ number_format($quotation->total, 2) }}</td>
                </tr>
            </table>

            <div style="margin-top: 10px; font-size: 11px; color: #6b7280; text-align: right;">
                <p>Precios expresados en Soles (S/)</p>
            </div>
        </div>

        <div style="clear: both;"></div>

        @if($quotation->status === 'rejected')
            <div class="watermark">RECHAZADA</div>
        @elseif($quotation->status === 'expired')
            <div class="watermark">VENCIDA</div>
        @elseif($quotation->status === 'converted')
            <div class="watermark">CONVERTIDA</div>
        @endif

        <div style="margin-top: 30px;">
            @if($quotation->notes)
                <div class="notes">
                    <h3>NOTAS ADICIONALES</h3>
                    <p>{{ $quotation->notes }}</p>
                </div>
            @endif

            @if($quotation->terms_and_conditions)
                <div class="terms">
                    <h3>TÉRMINOS Y CONDICIONES</h3>
                    <p>{!! nl2br(e($quotation->terms_and_conditions)) !!}</p>
                </div>
            @endif
        </div>

        <div class="footer">
            <p>Esta cotización es válida hasta el {{ $quotation->valid_until->format('d/m/Y') }}. Después de esta fecha, los precios y condiciones pueden variar.</p>
            <p>Gracias por su preferencia. Para cualquier consulta, no dude en contactarnos.</p>
            <p>© {{ date('Y') }} Restaurante Ejemplo. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
