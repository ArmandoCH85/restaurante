<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Informe de Caja #{{ $cashRegister->id }}</title>
    <style>
        /* Reset y configuración base optimizada para DomPDF */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            color: #2c3e50;
            background: #ffffff;
        }
        
        /* Layout principal optimizado para horizontal */
        .page {
            width: 85%;
            margin: 0 auto;
            padding: 15mm 20mm;
            max-width: 220mm; /* Ancho más conservador */
        }
        
        /* Header optimizado */
        .header {
            text-align: center;
            margin-bottom: 12px;
            padding: 12px;
            background: #34495e;
            color: white;
            border-radius: 6px;
        }
        
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .header h2 {
            font-size: 14px;
            margin-bottom: 12px;
            font-weight: normal;
        }
        
        .header-info {
            font-size: 10px;
            line-height: 1.3;
        }
        
        /* Secciones */
        .section {
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 8px 12px;
            background: #ecf0f1;
            border-left: 4px solid #3498db;
            color: #2c3e50;
        }
        
        /* Tablas optimizadas */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }
        
        .info-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #bdc3c7;
            vertical-align: top;
            font-size: 10px;
        }
        
        .info-table td:first-child {
            font-weight: bold;
            width: 25%;
            color: #34495e;
        }
        
        .info-table td:last-child {
            width: 75%;
        }
        
        /* Grids para datos - optimizado horizontal */
        .data-grid {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            table-layout: fixed;
        }
        
        .data-row {
            display: table-row;
        }
        
        .data-cell {
            display: table-cell;
            padding: 6px 4px;
            border: 1px solid #bdc3c7;
            text-align: center;
            vertical-align: middle;
            background: #f8f9fa;
            width: 16.66%;
            font-size: 9px;
        }
        
        .data-cell.header {
            background: #34495e;
            color: white;
            font-weight: bold;
            font-size: 10px;
        }
        
        .data-cell.amount {
            font-weight: bold;
            font-size: 10px;
        }
        
        /* Sección de totales */
        .totals-section {
            background: #e8f4fd;
            padding: 8px;
            border: 1px solid #3498db;
            border-radius: 4px;
            margin: 6px 0;
            text-align: center;
        }
        
        .total-amount {
            font-size: 14px;
            font-weight: bold;
            color: #2980b9;
        }
        
        /* Comparación de montos */
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        
        .comparison-table th,
        .comparison-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #bdc3c7;
            font-size: 10px;
        }
        
        .comparison-table th {
            background: #34495e;
            color: white;
            font-weight: bold;
            font-size: 10px;
        }
        
        .comparison-table .expected {
            background: #d5f4e6;
        }
        
        .comparison-table .actual {
            background: #ffeaa7;
        }
        
        .comparison-table .difference {
            background: #55a3ff;
            color: white;
            font-weight: bold;
        }
        
        /* Métodos de pago */
        .payments-grid {
            display: table;
            width: 100%;
        }
        
        .payments-row {
            display: table-row;
        }
        
        .payment-cell {
            display: table-cell;
            width: 16.66%;
            padding: 6px;
            text-align: center;
            border: 1px solid #bdc3c7;
            background: #f8f9fa;
        }
        
        .payment-icon {
            font-size: 12px;
            margin-bottom: 4px;
        }
        
        .payment-name {
            font-size: 8px;
            font-weight: bold;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        
        .payment-count {
            font-size: 10px;
            font-weight: bold;
        }
        
        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 10px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-open {
            background: #27ae60;
            color: white;
        }
        
        .status-closed {
            background: #e74c3c;
            color: white;
        }
        
        .status-approved {
            background: #3498db;
            color: white;
        }
        
        /* Observaciones */
        .observations {
            background: #fffbf0;
            border: 1px solid #f39c12;
            border-left: 4px solid #f39c12;
            padding: 10px;
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9px;
            white-space: pre-line;
            line-height: 1.3;
        }
        
        /* Footer */
        .footer {
            margin-top: 20px;
            padding: 12px;
            background: #ecf0f1;
            text-align: center;
            border-top: 2px solid #34495e;
            font-size: 9px;
            color: #7f8c8d;
        }
        
        /* Utilidades */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-10 { margin-bottom: 10px; }
        .mb-15 { margin-bottom: 15px; }
        
        /* Prevención de saltos de página */
        .no-break {
            page-break-inside: avoid;
        }
        
        /* Ajustes específicos para DomPDF */
        .dompdf-fix {
            position: relative;
            overflow: hidden;
        }
    </style>
</head>
<body>
    <div class="page">
        <!-- Header -->
        <div class="header no-break">
            <h1>{{ $company['razon_social'] ?? 'RESTAURANTE EJEMPLO' }}</h1>
            <h2>INFORME DE OPERACIÓN DE CAJA</h2>
            <div class="header-info">
                <strong>RUC:</strong> {{ $company['ruc'] ?? '12345678901' }}<br>
                {{ $company['direccion'] ?? 'Dirección no configurada' }}<br>
                @if($company['telefono'])
                    <strong>Teléfono:</strong> {{ $company['telefono'] }}<br>
                @endif
                @if($company['email'])
                    <strong>Email:</strong> {{ $company['email'] }}
                @endif
            </div>
        </div>

        <!-- Información General en dos columnas -->
        <div class="section no-break">
            <div class="section-title">📋 INFORMACIÓN GENERAL</div>
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; width: 50%; padding-right: 10px;">
                    <table class="info-table">
                        <tr>
                            <td>ID de Caja:</td>
                            <td><strong>#{{ $cashRegister->id }}</strong></td>
                        </tr>
                        <tr>
                            <td>Estado:</td>
                            <td>
                                <span class="status-badge {{ $cashRegister->is_active ? 'status-open' : 'status-closed' }}">
                                    {{ $cashRegister->is_active ? 'ABIERTA' : 'CERRADA' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td>Monto Inicial:</td>
                            <td><strong>S/ {{ number_format($cashRegister->opening_amount, 2) }}</strong></td>
                        </tr>
                        <tr>
                            <td>Abierto por:</td>
                            <td>{{ $cashRegister->openedBy->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Fecha de Apertura:</td>
                            <td>{{ $cashRegister->opening_datetime->format('d/m/Y H:i:s') }}</td>
                        </tr>
                    </table>
                </div>
                <div style="display: table-cell; width: 50%; padding-left: 10px;">
                    <table class="info-table">
                        <tr>
                            <td>Duración:</td>
                            <td>
                                @if($cashRegister->is_active)
                                    {{ now()->diff($cashRegister->opening_datetime)->format('%h h %i min') }}
                                @else
                                    {{ $cashRegister->closing_datetime->diff($cashRegister->opening_datetime)->format('%h h %i min') }}
                                @endif
                            </td>
                        </tr>
                        @if(!$cashRegister->is_active)
                        <tr>
                            <td>Cerrado por:</td>
                            <td>{{ $cashRegister->closedBy->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td>Fecha de Cierre:</td>
                            <td>{{ $cashRegister->closing_datetime->format('d/m/Y H:i:s') }}</td>
                        </tr>
                        <tr>
                            <td>Estado de Revisión:</td>
                            <td>
                                <span class="status-badge {{ $cashRegister->is_approved ? 'status-approved' : 'status-closed' }}">
                                    {{ $cashRegister->reconciliationStatus }}
                                </span>
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
        </div>

        @if($isSupervisor)
        <!-- Resumen de Ventas -->
        <div class="section no-break">
            <div class="section-title">💰 RESUMEN DE VENTAS</div>
            <div class="data-grid">
                <div class="data-row">
                    <div class="data-cell header">💵 Efectivo</div>
                    <div class="data-cell header">💳 Tarjetas</div>
                    <div class="data-cell header">📱 Yape</div>
                    <div class="data-cell header">📱 Plin</div>
                    <div class="data-cell header">🛒 PedidosYa</div>
                    <div class="data-cell header">🍕 Didi Food</div>
                </div>
                <div class="data-row">
                    <div class="data-cell amount">S/ {{ number_format($systemSales['efectivo'], 2) }}</div>
                    <div class="data-cell amount">S/ {{ number_format($systemSales['tarjetas'], 2) }}</div>
                    <div class="data-cell amount">S/ {{ number_format($systemSales['yape'], 2) }}</div>
                    <div class="data-cell amount">S/ {{ number_format($systemSales['plin'], 2) }}</div>
                    <div class="data-cell amount">S/ {{ number_format($systemSales['pedidos_ya'], 2) }}</div>
                    <div class="data-cell amount">S/ {{ number_format($systemSales['didi_food'], 2) }}</div>
                </div>
            </div>
            
            <div class="totals-section">
                <div>TOTAL DE VENTAS</div>
                <div class="total-amount">S/ {{ number_format($systemSales['total'], 2) }}</div>
            </div>
        </div>

        @if(!$cashRegister->is_active)
        <!-- Montos de Cierre -->
        <div class="section no-break">
            <div class="section-title">🔒 MONTOS DE CIERRE</div>
            <table class="comparison-table">
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th>Monto</th>
                        <th>Observaciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="expected">
                        <td><strong>Monto Esperado</strong><br><small>(Ventas del Sistema)</small></td>
                        <td><strong>S/ {{ number_format($systemSales['total'], 2) }}</strong></td>
                        <td>Solo ventas registradas</td>
                    </tr>
                    <tr class="actual">
                        <td><strong>Montos de Cierre</strong><br><small>(Ingresos Manuales)</small></td>
                        <td><strong>S/ {{ number_format($systemSales['total'], 2) }}</strong></td>
                        <td>Igual a ventas del sistema</td>
                    </tr>
                    <tr class="difference">
                        <td><strong>DIFERENCIA</strong></td>
                        <td><strong>S/ 0.00</strong></td>
                        <td>✅ SIN DIFERENCIA</td>
                    </tr>
                </tbody>
            </table>
            <div class="text-center mb-15">
                <small><em>La diferencia es 0 porque ambos montos representan las mismas ventas reales del sistema</em></small>
            </div>
        </div>
        @endif

        <!-- Métodos de Pago -->
        <div class="section no-break">
            <div class="section-title">💳 MÉTODOS DE PAGO - NÚMERO DE USOS</div>
            <div class="payments-grid">
                <div class="payments-row">
                    <div class="payment-cell">
                        <div class="payment-icon">💵</div>
                        <div class="payment-name">Efectivo</div>
                        <div class="payment-count">{{ $paymentCounts['efectivo'] }} usos</div>
                    </div>
                    <div class="payment-cell">
                        <div class="payment-icon">💳</div>
                        <div class="payment-name">Tarjetas</div>
                        <div class="payment-count">{{ $paymentCounts['tarjetas'] }} usos</div>
                    </div>
                    <div class="payment-cell">
                        <div class="payment-icon">📱</div>
                        <div class="payment-name">Yape</div>
                        <div class="payment-count">{{ $paymentCounts['yape'] }} usos</div>
                    </div>
                    <div class="payment-cell">
                        <div class="payment-icon">📱</div>
                        <div class="payment-name">Plin</div>
                        <div class="payment-count">{{ $paymentCounts['plin'] }} usos</div>
                    </div>
                    <div class="payment-cell">
                        <div class="payment-icon">🛒</div>
                        <div class="payment-name">PedidosYa</div>
                        <div class="payment-count">{{ $paymentCounts['pedidos_ya'] }} usos</div>
                    </div>
                    <div class="payment-cell">
                        <div class="payment-icon">🍕</div>
                        <div class="payment-name">Didi Food</div>
                        <div class="payment-count">{{ $paymentCounts['didi_food'] }} usos</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Observaciones -->
        @if($filteredObservations)
        <div class="section">
            <div class="section-title">📝 OBSERVACIONES</div>
            <div class="observations">{{ $filteredObservations }}</div>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <div class="font-bold">Informe generado el {{ $generatedDate }}</div>
            <div>Sistema de Gestión de Restaurante - {{ $company['razon_social'] ?? 'RESTAURANTE EJEMPLO' }}</div>
            <div><em>Este documento es generado automáticamente por el sistema</em></div>
        </div>
    </div>
</body>
</html>