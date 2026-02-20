<?php

use App\Support\CashRegisterClosingSummaryService;

test('build genera estructura base del resumen de cierre', function () {
    $service = app(CashRegisterClosingSummaryService::class);

    $summary = $service->build([
        'total_ingresos' => 1435.20,
        'total_egresos' => 40.50,
        'ganancia_real' => 1394.70,
        'monto_inicial' => 300.00,
        'monto_esperado' => 1694.70,
        'efectivo_total' => 342.50,
        'total_manual_ventas' => 1468.90,
        'difference' => 74.20,
        'billetes' => ['200' => 0, '100' => 0],
        'monedas' => ['0.50' => 0, '0.20' => 0],
        'otros_metodos' => ['yape' => 741.60, 'tarjeta' => 212.90],
    ]);

    expect($summary)
        ->toHaveKey('kpis')
        ->and($summary)->toHaveKey('conciliacion')
        ->and($summary)->toHaveKey('efectivo')
        ->and($summary)->toHaveKey('otros_metodos')
        ->and(data_get($summary, 'kpis.difference_status'))->toBe('sobrante');
});

test('parseLegacy interpreta bloque textual del cierre completo', function () {
    $service = app(CashRegisterClosingSummaryService::class);

    $legacy = <<<'TXT'
=== CIERRE DE CAJA - RESUMEN COMPLETO ===

TOTAL INGRESOS: S/ 1,435.20
TOTAL EGRESOS: S/ 40.50
GANANCIA REAL: S/ 1,394.70

MONTO ESPERADO: S/ 1,694.70
(Monto inicial: S/ 300.00 + Ventas del dia: S/ 1,435.20)

EFECTIVO CONTADO: S/ 342.50
Billetes: S/200×0 | S/100×0 | S/50×0
Monedas: S/5×0 | S/0.50×0 | S/0.20×0

OTROS METODOS DE PAGO: S/ 1,126.40
Yape: S/ 741.60 | Tarjeta: S/ 212.90 | Didi: S/ 44.90 | Pedidos Ya: S/ 127.00 |

EGRESOS REGISTRADOS (desde modulo de Egresos):
Total: S/ 40.50
Ver detalles en: /admin/egresos

TOTAL MANUAL (Ventas): S/ 1,468.90
DIFERENCIA: S/ 74.20 (SOBRANTE)
Formula: (Manual + Inicial) - Esperado
Nota: El Esperado ya considera egresos.
TXT;

    $parsed = $service->parseLegacy($legacy);

    expect($parsed)
        ->not()->toBeNull()
        ->and(data_get($parsed, 'kpis.total_ingresos'))->toBe(1435.20)
        ->and(data_get($parsed, 'kpis.diferencia'))->toBe(74.20)
        ->and(data_get($parsed, 'kpis.difference_status'))->toBe('sobrante')
        ->and(data_get($parsed, 'egresos.url'))->toBe('/admin/egresos')
        ->and(data_get($parsed, 'otros_metodos.yape'))->toBe(741.60);
});
