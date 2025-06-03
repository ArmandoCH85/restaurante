<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Vista Previa Térmica - Sistema de Restaurante</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .preview-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .thermal-preview {
            background: white;
            border: 2px solid #333;
            border-radius: 8px;
            padding: 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
        }

        .thermal-80mm {
            width: 80mm;
            min-height: 200mm;
        }

        .thermal-57mm {
            width: 57mm;
            min-height: 200mm;
        }

        .preview-label {
            position: absolute;
            top: -30px;
            left: 0;
            background: #333;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .thermal-content {
            padding: 3mm;
            font-size: 11px;
            line-height: 1.2;
            font-family: Arial, sans-serif;
        }

        .thermal-57mm .thermal-content {
            padding: 2mm;
            font-size: 10px;
        }

        /* Estilos térmicos */
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

        .thermal-57mm .thermal-company h1 {
            font-size: 13px;
        }

        .thermal-company p {
            margin: 1px 0;
            font-size: 10px;
            line-height: 1.1;
        }

        .thermal-57mm .thermal-company p {
            font-size: 9px;
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

        .thermal-57mm .thermal-document-title h2 {
            font-size: 11px;
        }

        .thermal-document-number {
            font-size: 14px;
            font-weight: bold;
            margin-top: 3px;
        }

        .thermal-57mm .thermal-document-number {
            font-size: 13px;
        }

        .thermal-info {
            margin: 6px 0;
        }

        .thermal-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2px;
            font-size: 10px;
        }

        .thermal-57mm .thermal-info-row {
            font-size: 9px;
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

        .thermal-57mm .thermal-table th {
            font-size: 8px;
            padding: 1px;
        }

        .thermal-table td {
            padding: 2px 1px;
            font-size: 9px;
            border-bottom: 1px dashed #ccc;
        }

        .thermal-57mm .thermal-table td {
            font-size: 8px;
            padding: 1px;
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

        .thermal-57mm .thermal-total-row {
            font-size: 9px;
        }

        .thermal-grand-total {
            font-weight: bold;
            font-size: 11px;
            margin-top: 3px;
            border-top: 1px solid #000;
            padding-top: 3px;
        }

        .thermal-footer {
            margin-top: 12px;
            text-align: center;
            font-size: 9px;
            border-top: 1px dashed #000;
            padding-top: 6px;
        }

        .thermal-57mm .thermal-footer {
            font-size: 8px;
        }

        .label {
            font-weight: bold;
        }

        .controls {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            padding: 10px 20px;
            margin: 0 5px;
            background: #3C50E0;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #2a3eb8;
        }

        .btn-secondary {
            background: #6c757d;
        }

        .btn-secondary:hover {
            background: #545b62;
        }
    </style>
</head>
<body>
    <div class="controls">
        <h1>Vista Previa de Optimización Térmica</h1>
        <p>Comparación visual de formatos térmicos 80mm vs 57mm</p>
        <a href="{{ url()->previous() }}" class="btn btn-secondary">← Volver</a>
        <button onclick="window.print()" class="btn">🖨️ Imprimir Vista Previa</button>
    </div>

    <div class="preview-container">
        <!-- Vista 80mm -->
        <div class="thermal-preview thermal-80mm">
            <div class="preview-label">Papel Térmico 80mm</div>
            <div class="thermal-content">
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
                    <h2>BOLETA ELECTRÓNICA</h2>
                    <div class="thermal-document-number">B001-000123</div>
                </div>

                <div class="thermal-info">
                    <div class="thermal-info-row">
                        <span class="label">Fecha de emisión:</span>
                        <span>{{ now()->format('d/m/Y') }}</span>
                    </div>
                    <div class="thermal-info-row">
                        <span class="label">Cliente:</span>
                        <span>Cliente Ejemplo</span>
                    </div>
                    <div class="thermal-info-row">
                        <span class="label">DNI:</span>
                        <span>12345678</span>
                    </div>
                    <div class="thermal-info-row">
                        <span class="label">Mesa:</span>
                        <span>Mesa #5</span>
                    </div>
                    <div class="thermal-info-row">
                        <span class="label">Tipo de pago:</span>
                        <span>Efectivo</span>
                    </div>
                </div>

                <div style="border-top: 1px dashed #000; margin: 6px 0; padding-top: 3px;">
                    <div style="margin-bottom: 3px; font-size: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: bold;">2 x Lomo Saltado</span>
                            <span>50.00</span>
                        </div>
                        <div style="font-size: 9px; color: #666; margin-left: 10px;">
                            @ S/ 25.00 c/u
                        </div>
                    </div>
                    <div style="margin-bottom: 3px; font-size: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: bold;">1 x Inca Kola 500ml</span>
                            <span>5.00</span>
                        </div>
                    </div>
                </div>

                <div class="thermal-totals">
                    <div class="thermal-total-row">
                        <span class="label">Subtotal:</span>
                        <span>S/ 46.61</span>
                    </div>
                    <div class="thermal-total-row">
                        <span class="label">IGV (18%):</span>
                        <span>S/ 8.39</span>
                    </div>
                    <div class="thermal-grand-total">
                        <span class="label">Total:</span>
                        <span>S/ 55.00</span>
                    </div>
                </div>

                <div class="thermal-footer">
                    Gracias por su preferencia
                </div>
            </div>
        </div>

        <!-- Vista 57mm -->
        <div class="thermal-preview thermal-57mm">
            <div class="preview-label">Papel Térmico 57mm</div>
            <div class="thermal-content">
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
                    <h2>BOLETA ELECTRÓNICA</h2>
                    <div class="thermal-document-number">B001-000123</div>
                </div>

                <div class="thermal-info">
                    <div class="thermal-info-row">
                        <span class="label">Fecha:</span>
                        <span>{{ now()->format('d/m/Y') }}</span>
                    </div>
                    <div class="thermal-info-row">
                        <span class="label">Cliente:</span>
                        <span>Cliente Ejemplo</span>
                    </div>
                    <div class="thermal-info-row">
                        <span class="label">Mesa:</span>
                        <span>Mesa #5</span>
                    </div>
                    <div class="thermal-info-row">
                        <span class="label">Pago:</span>
                        <span>Efectivo</span>
                    </div>
                </div>

                <div style="border-top: 1px dashed #000; margin: 6px 0; padding-top: 3px;">
                    <div style="margin-bottom: 3px; font-size: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: bold;">2 x Lomo Saltado</span>
                            <span>50.00</span>
                        </div>
                    </div>
                    <div style="margin-bottom: 3px; font-size: 10px;">
                        <div style="display: flex; justify-content: space-between;">
                            <span style="font-weight: bold;">1 x Inca Kola 500ml</span>
                            <span>5.00</span>
                        </div>
                    </div>
                </div>

                <div class="thermal-totals">
                    <div class="thermal-total-row">
                        <span class="label">Subtotal:</span>
                        <span>S/ 46.61</span>
                    </div>
                    <div class="thermal-total-row">
                        <span class="label">IGV:</span>
                        <span>S/ 8.39</span>
                    </div>
                    <div class="thermal-grand-total">
                        <span class="label">Total:</span>
                        <span>S/ 55.00</span>
                    </div>
                </div>

                <div class="thermal-footer">
                    Gracias por su preferencia
                </div>
            </div>
        </div>
    </div>
</body>
</html>
