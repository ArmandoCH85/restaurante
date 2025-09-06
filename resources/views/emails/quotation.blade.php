<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cotización {{ $quotation->quotation_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding: 20px 0;
            border-bottom: 2px solid #f0f0f0;
        }
        .header img {
            max-width: 200px;
            height: auto;
        }
        .content {
            padding: 20px 0;
        }
        .quotation-details {
            background-color: #f9f9f9;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            padding: 20px 0;
            font-size: 12px;
            color: #777;
            border-top: 1px solid #f0f0f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Cotización {{ $quotation->quotation_number }}</h1>
        </div>

        <div class="content">
            <p>Estimado(a) cliente,</p>

            <p>{{ $message }}</p>

            <div class="quotation-details">
                <p><strong>Número de Cotización:</strong> {{ $quotation->quotation_number }}</p>
                <p><strong>Fecha de Emisión:</strong> {{ $quotation->issue_date->format('d/m/Y') }}</p>
                <p><strong>Válido Hasta:</strong> {{ $quotation->valid_until->format('d/m/Y') }}</p>
                <p><strong>Total:</strong> S/ {{ number_format($quotation->total, 2) }}</p>
            </div>

            <p>Adjuntamos el documento PDF con todos los detalles de la cotización.</p>

            <p>Si tiene alguna pregunta o necesita más información, no dude en contactarnos.</p>

            <p>Atentamente,</p>
            <p><strong>{{ $quotation->user->name }}</strong><br>
            <span style="font-size: 16px;">{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'Restaurante Ejemplo' }}</span></p>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} <span style="font-size: 16px;">{{ \App\Models\CompanyConfig::getRazonSocial() ?? 'Restaurante Ejemplo' }}</span>. Todos los derechos reservados.</p>
            <p style="font-size: 14px;">{{ \App\Models\CompanyConfig::getDireccion() ?? 'Av. Principal 123, Lima, Perú' }}
            @if(\App\Models\CompanyConfig::getTelefono())
                | <span style="font-size: 14px;">Teléfono: {{ \App\Models\CompanyConfig::getTelefono() }}</span>
            @endif
            @if(\App\Models\CompanyConfig::getEmail())
                | Email: {{ \App\Models\CompanyConfig::getEmail() }}
            @endif
            </p>
        </div>
    </div>
</body>
</html>
