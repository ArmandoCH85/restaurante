<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Caja #{{ $cashRegister->id }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 11px;
            color: #1a202c;
            line-height: 1.6;
            background: #ffffff;
            padding: 25px;
        }
        
        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border-radius: 8px;
            overflow: hidden;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }
        
        .header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ffd700, #ff6b6b, #4ecdc4, #45b7d1);
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .header h2 {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
            opacity: 0.9;
        }
        
        .header .company-info {
            font-size: 12px;
            opacity: 0.8;
            line-height: 1.4;
        }
        
        .content {
            padding: 30px;
        }
        
        .section {
            margin-bottom: 35px;
            background: #f8fafc;
            border-radius: 8px;
            padding: 25px;
            border-left: 4px solid #667eea;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            color: #2d3748;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title::before {
            content: '';
            width: 4px;
            height: 20px;
            background: #667eea;
            border-radius: 2px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .info-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        
        .info-label {
            font-weight: 500;
            color: #4a5568;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        
        .info-value {
            font-size: 14px;
            font-weight: 600;
            color: #1a202c;
        }
        
        .amount {
            font-weight: 700;
            font-size: 16px;
        }
        
        .amount.positive {
            color: #38a169;
        }
        
        .amount.negative {
            color: #e53e3e;
        }
        
        .amount.neutral {
            color: #2d3748;
        }
        
        .sales-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .sales-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }
        
        .sales-card:hover {
            transform: translateY(-2px);
        }
        
        .sales-card .icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .sales-card .method-name {
            font-size: 12px;
            font-weight: 500;
            color: #4a5568;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .sales-card .amount {
            font-size: 18px;
            font-weight: 700;
            color: #1a202c;
        }
        
        .totals-section {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 25px;
            border-radius: 8px;
            margin: 25px 0;
            text-align: center;
        }
        
        .total-amount {
            font-size: 32px;
            font-weight: 700;
            margin: 15px 0;
        }
        
        .comparison-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin: 25px 0;
        }
        
        .comparison-card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border: 2px solid #e2e8f0;
        }
        
        .comparison-card.expected {
            border-color: #48bb78;
            background: linear-gradient(135deg, #f0fff4, #e6fffa);
        }
        
        .comparison-card.actual {
            border-color: #4299e1;
            background: linear-gradient(135deg, #ebf8ff, #e6fffa);
        }
        
        .comparison-title {
            font-size: 14px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .comparison-amount {
            font-size: 28px;
            font-weight: 700;
            color: #1a202c;
        }
        
        .difference-card {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin: 20px 0;
        }
        
        .payments-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .payment-card {
            background: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
            border: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }
        
        .payment-icon {
            font-size: 20px;
        }
        
        .payment-name {
            font-size: 10px;
            font-weight: 500;
            color: #4a5568;
            text-transform: uppercase;
        }
        
        .payment-count {
            font-size: 16px;
            font-weight: 700;
            color: #1a202c;
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-open {
            background: #48bb78;
            color: white;
        }
        
        .status-closed {
            background: #e53e3e;
            color: white;
        }
        
        .status-approved {
            background: #4299e1;
            color: white;
        }
        
        .observations {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: 'Courier New', monospace;
            font-size: 10px;
            line-height: 1.4;
            white-space: pre-line;
        }
        
        .footer {
            margin-top: 50px;
            padding: 25px;
            background: #f7fafc;
            text-align: center;
            border-top: 3px solid #667eea;
            border-radius: 0 0 8px 8px;
        }
        
        .footer-info {
            font-size: 10px;
            color: #718096;
            line-height: 1.6;
        }
        
        .highlight-box {
            background: #edf2f7;
            border: 2px dashed #a0aec0;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>{{ $company['razon_social'] ?? 'RESTAURANTE EJEMPLO' }}</h1>
            <h2>INFORME DE OPERACIÓN DE CAJA</h2>
            <div class="company-info">
                <p><strong>RUC:</strong> {{ $company['ruc'] ?? '12345678901' }}</p>
                <p>{{ $company['direccion'] ?? 'Dirección no configurada' }}</p>
                @if($company['telefono'])
                    <p><strong>Teléfono:</strong> {{ $company['telefono'] }}</p>
                @endif
                @if($company['email'])
                    <p><strong>Email:</strong> {{ $company['email'] }}</p>
                @endif
            </div>
        </div>

        <div class="content">
            <!-- Información General -->
            <div class="section">
                <div class="section-title">📋 INFORMACIÓN GENERAL</div>
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">ID de Caja</div>
                        <div class="info-value">#{{ $cashRegister->id }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Estado</div>
                        <div class="status-badge {{ $cashRegister->is_active ? 'status-open' : 'status-closed' }}">
                            {{ $cashRegister->is_active ? 'ABIERTA' : 'CERRADA' }}
                        </div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Monto Inicial</div>
                        <div class="info-value amount neutral">S/ {{ number_format($cashRegister->opening_amount, 2) }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Abierto por</div>
                        <div class="info-value">{{ $cashRegister->openedBy->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Fecha de Apertura</div>
                        <div class="info-value">{{ $cashRegister->opening_datetime->format('d/m/Y H:i:s') }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Duración</div>
                        <div class="info-value">
                            @if($cashRegister->is_active)
                                {{ now()->diff($cashRegister->opening_datetime)->format('%h h %i min') }}
                            @else
                                {{ $cashRegister->closing_datetime->diff($cashRegister->opening_datetime)->format('%h h %i min') }}
                            @endif
                        </div>
                    </div>
                </div>

                @if(!$cashRegister->is_active)
                <div class="info-grid">
                    <div class="info-card">
                        <div class="info-label">Cerrado por</div>
                        <div class="info-value">{{ $cashRegister->closedBy->name ?? 'N/A' }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Fecha de Cierre</div>
                        <div class="info-value">{{ $cashRegister->closing_datetime->format('d/m/Y H:i:s') }}</div>
                    </div>
                    <div class="info-card">
                        <div class="info-label">Estado de Revisión</div>
                        <div class="status-badge {{ $cashRegister->is_approved ? 'status-approved' : 'status-closed' }}">
                            {{ $cashRegister->reconciliationStatus }}
                        </div>
                    </div>
                </div>
                @endif
            </div>

            @if($isSupervisor)
            <!-- Resumen de Ventas -->
            <div class="section">
                <div class="section-title">💰 RESUMEN DE VENTAS</div>
                <div class="sales-grid">
                    <div class="sales-card">
                        <div class="icon">💵</div>
                        <div class="method-name">Efectivo</div>
                        <div class="amount">S/ {{ number_format($systemSales['efectivo'], 2) }}</div>
                    </div>
                    <div class="sales-card">
                        <div class="icon">💳</div>
                        <div class="method-name">Tarjetas</div>
                        <div class="amount">S/ {{ number_format($systemSales['tarjetas'], 2) }}</div>
                    </div>
                    <div class="sales-card">
                        <div class="icon">📱</div>
                        <div class="method-name">Yape</div>
                        <div class="amount">S/ {{ number_format($systemSales['yape'], 2) }}</div>
                    </div>
                    <div class="sales-card">
                        <div class="icon">📱</div>
                        <div class="method-name">Plin</div>
                        <div class="amount">S/ {{ number_format($systemSales['plin'], 2) }}</div>
                    </div>
                    <div class="sales-card">
                        <div class="icon">🛒</div>
                        <div class="method-name">PedidosYa</div>
                        <div class="amount">S/ {{ number_format($systemSales['pedidos_ya'], 2) }}</div>
                    </div>
                    <div class="sales-card">
                        <div class="icon">🍕</div>
                        <div class="method-name">Didi Food</div>
                        <div class="amount">S/ {{ number_format($systemSales['didi_food'], 2) }}</div>
                    </div>
                    <div class="sales-card">
                        <div class="icon">🚚</div>
                        <div class="method-name">Bita Express</div>
                        <div class="amount">S/ {{ number_format($systemSales['bita_express'], 2) }}</div>
                    </div>
                    <div class="sales-card">
                        <div class="icon">🏦</div>
                        <div class="method-name">Transferencia</div>
                        <div class="amount">S/ {{ number_format($systemSales['bank_transfer'], 2) }}</div>
                    </div>
                    <div class="sales-card">
                        <div class="icon">📲</div>
                        <div class="method-name">Billetera</div>
                        <div class="amount">S/ {{ number_format($systemSales['other_digital_wallet'], 2) }}</div>
                    </div>
                </div>
                
                <div class="totals-section">
                    <h3>TOTAL DE VENTAS</h3>
                    <div class="total-amount">S/ {{ number_format($systemSales['total'], 2) }}</div>
                </div>
            </div>

            @if(!$cashRegister->is_active)
            <!-- Montos de Cierre -->
            <div class="section">
                <div class="section-title">🔒 MONTOS DE CIERRE</div>
                @php
                    $expectedAmount = (float) ($cashRegister->expected_amount ?? 0);
                    $actualAmount = (float) ($cashRegister->actual_amount ?? 0);
                    $differenceAmount = (float) ($cashRegister->difference ?? 0);
                    $differenceLabel = $differenceAmount < 0 ? 'FALTANTE' : ($differenceAmount > 0 ? 'SOBRANTE' : 'SIN DIFERENCIA');
                @endphp
                <div class="comparison-grid">
                    <div class="comparison-card expected">
                        <div class="comparison-title">Monto Esperado (Apertura + Ventas - Egresos)</div>
                        <div class="comparison-amount">S/ {{ number_format($expectedAmount, 2) }}</div>
                    </div>
                    <div class="comparison-card actual">
                        <div class="comparison-title">Monto Contado (Cierre manual)</div>
                        <div class="comparison-amount">S/ {{ number_format($actualAmount, 2) }}</div>
                    </div>
                </div>
                
                <div class="difference-card">
                    <h3>DIFERENCIA: S/ {{ number_format($differenceAmount, 2) }} ({{ $differenceLabel }})</h3>
                    <p><small>Comparación entre monto contado y monto esperado.</small></p>
                </div>
            </div>
            @endif

            <!-- Métodos de Pago -->
            <div class="section">
                <div class="section-title">💳 MÉTODOS DE PAGO - NÚMERO DE USOS</div>
                <div class="payments-grid">
                    <div class="payment-card">
                        <div class="payment-icon">💵</div>
                        <div class="payment-name">Efectivo</div>
                        <div class="payment-count">{{ $paymentCounts['efectivo'] }} usos</div>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">💳</div>
                        <div class="payment-name">Tarjetas</div>
                        <div class="payment-count">{{ $paymentCounts['tarjetas'] }} usos</div>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">📱</div>
                        <div class="payment-name">Yape</div>
                        <div class="payment-count">{{ $paymentCounts['yape'] }} usos</div>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">📱</div>
                        <div class="payment-name">Plin</div>
                        <div class="payment-count">{{ $paymentCounts['plin'] }} usos</div>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">🛒</div>
                        <div class="payment-name">PedidosYa</div>
                        <div class="payment-count">{{ $paymentCounts['pedidos_ya'] }} usos</div>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">🍕</div>
                        <div class="payment-name">Didi Food</div>
                        <div class="payment-count">{{ $paymentCounts['didi_food'] }} usos</div>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">🚚</div>
                        <div class="payment-name">Bita Express</div>
                        <div class="payment-count">{{ $paymentCounts['bita_express'] }} usos</div>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">🏦</div>
                        <div class="payment-name">Transferencia</div>
                        <div class="payment-count">{{ $paymentCounts['bank_transfer'] }} usos</div>
                    </div>
                    <div class="payment-card">
                        <div class="payment-icon">📲</div>
                        <div class="payment-name">Billetera</div>
                        <div class="payment-count">{{ $paymentCounts['digital_wallet'] }} usos</div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Observaciones -->
            @if($cashRegister->observations)
            <div class="section">
                <div class="section-title">📝 OBSERVACIONES</div>
                <div class="observations">
                    {{ $cashRegister->observations }}
                </div>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-info">
                <p><strong>Informe generado el {{ now()->format('d/m/Y H:i:s') }}</strong></p>
                <p>Sistema de Gestión de Restaurante - {{ $company['razon_social'] ?? 'RESTAURANTE EJEMPLO' }}</p>
                <p><em>Este documento es generado automáticamente por el sistema</em></p>
            </div>
        </div>
    </div>
</body>
</html>
