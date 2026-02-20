<?php

namespace App\Support;

final class CashRegisterClosingSummaryService
{
    /**
     * @param  array<string, mixed>  $input
     * @return array<string, mixed>
     */
    public function build(array $input): array
    {
        $difference = (float) ($input['difference'] ?? 0);

        return [
            'version' => 1,
            'generated_at' => now()->toIso8601String(),
            'kpis' => [
                'total_ingresos' => (float) ($input['total_ingresos'] ?? 0),
                'total_egresos' => (float) ($input['total_egresos'] ?? 0),
                'ganancia_real' => (float) ($input['ganancia_real'] ?? 0),
                'diferencia' => $difference,
                'difference_status' => $this->differenceStatus($difference),
            ],
            'conciliacion' => [
                'monto_inicial' => (float) ($input['monto_inicial'] ?? 0),
                'monto_esperado' => (float) ($input['monto_esperado'] ?? 0),
                'total_manual_ventas' => (float) ($input['total_manual_ventas'] ?? 0),
                'formula' => '(Manual + Inicial) - Esperado',
                'nota' => 'El Esperado ya considera egresos.',
            ],
            'efectivo' => [
                'total_contado' => (float) ($input['efectivo_total'] ?? 0),
                'billetes' => $this->normalizeDenominations($input['billetes'] ?? []),
                'monedas' => $this->normalizeDenominations($input['monedas'] ?? []),
            ],
            'otros_metodos' => $this->normalizeMethods($input['otros_metodos'] ?? []),
            'egresos' => [
                'total' => (float) ($input['total_egresos'] ?? 0),
                'url' => '/admin/egresos',
            ],
            'meta' => [
                'closed_by' => $input['closed_by'] ?? null,
                'closing_datetime' => $input['closing_datetime'] ?? null,
                'closing_observations' => $input['closing_observations'] ?? null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public function parseLegacy(string $text): ?array
    {
        if (! str_contains($text, 'CIERRE DE CAJA - RESUMEN COMPLETO')) {
            return null;
        }

        $difference = $this->extractMoney($text, '/DIFERENCIA:\s*S\/\s*([\d\.,-]+)/i');

        if ($difference === null) {
            return null;
        }

        $montoInicial = $this->extractMoney($text, '/Monto inicial:\s*S\/\s*([\d\.,-]+)/i') ?? 0.0;
        $summary = [
            'version' => 1,
            'generated_at' => null,
            'kpis' => [
                'total_ingresos' => $this->extractMoney($text, '/TOTAL INGRESOS:\s*S\/\s*([\d\.,-]+)/i') ?? 0.0,
                'total_egresos' => $this->extractMoney($text, '/TOTAL EGRESOS:\s*S\/\s*([\d\.,-]+)/i') ?? 0.0,
                'ganancia_real' => $this->extractMoney($text, '/GANANCIA REAL:\s*S\/\s*([\d\.,-]+)/i') ?? 0.0,
                'diferencia' => $difference,
                'difference_status' => $this->differenceStatus($difference),
            ],
            'conciliacion' => [
                'monto_inicial' => $montoInicial,
                'monto_esperado' => $this->extractMoney($text, '/MONTO ESPERADO:\s*S\/\s*([\d\.,-]+)/i') ?? 0.0,
                'total_manual_ventas' => $this->extractMoney($text, '/TOTAL MANUAL \(Ventas\):\s*S\/\s*([\d\.,-]+)/i') ?? 0.0,
                'formula' => '(Manual + Inicial) - Esperado',
                'nota' => 'El Esperado ya considera egresos.',
            ],
            'efectivo' => [
                'total_contado' => $this->extractMoney($text, '/EFECTIVO CONTADO:\s*S\/\s*([\d\.,-]+)/i') ?? 0.0,
                'billetes' => $this->parseDenominationLine($this->extractLine($text, 'Billetes:')),
                'monedas' => $this->parseDenominationLine($this->extractLine($text, 'Monedas:')),
            ],
            'otros_metodos' => $this->parseMethodsBlock($this->extractMethodsDetailBlock($text)),
            'egresos' => [
                'total' => $this->extractMoney($text, '/EGRESOS REGISTRADOS.+?Total:\s*S\/\s*([\d\.,-]+)/is') ?? 0.0,
                'url' => $this->extractUrl($text) ?? '/admin/egresos',
            ],
            'meta' => [
                'closed_by' => null,
                'closing_datetime' => null,
                'closing_observations' => null,
            ],
        ];

        return $summary;
    }

    /**
     * @param  array<string, mixed>  $summary
     */
    public function toLegacyText(array $summary): string
    {
        $kpis = $summary['kpis'] ?? [];
        $conciliacion = $summary['conciliacion'] ?? [];
        $efectivo = $summary['efectivo'] ?? [];
        $otros = $summary['otros_metodos'] ?? [];
        $egresos = $summary['egresos'] ?? [];

        $lines = [];
        $lines[] = '=== CIERRE DE CAJA - RESUMEN COMPLETO ===';
        $lines[] = 'REPORTE FINANCIERO PROFESIONAL';
        $lines[] = '';
        $lines[] = '[KPI FINANCIERO]';
        $lines[] = 'TOTAL INGRESOS: '.$this->money((float) ($kpis['total_ingresos'] ?? 0));
        $lines[] = 'TOTAL EGRESOS: '.$this->money((float) ($kpis['total_egresos'] ?? 0));
        $lines[] = 'GANANCIA REAL: '.$this->money((float) ($kpis['ganancia_real'] ?? 0));
        $lines[] = '(Ingresos - Egresos)';
        $lines[] = '';
        $lines[] = '[CONCILIACION]';
        $lines[] = 'MONTO ESPERADO: '.$this->money((float) ($conciliacion['monto_esperado'] ?? 0));
        $lines[] = '(Monto inicial: '
            .$this->money((float) ($conciliacion['monto_inicial'] ?? 0))
            .' + Ventas del dia: '
            .$this->money((float) ($kpis['total_ingresos'] ?? 0))
            .')';
        $lines[] = '';
        $lines[] = '[EFECTIVO CONTADO]';
        $lines[] = 'EFECTIVO CONTADO: '.$this->money((float) ($efectivo['total_contado'] ?? 0));
        $lines[] = 'Billetes: '.$this->formatDenominationInline($efectivo['billetes'] ?? []);
        $lines[] = 'Monedas: '.$this->formatDenominationInline($efectivo['monedas'] ?? []);
        $lines[] = '';

        $otherTotal = array_sum(array_map('floatval', $otros));
        if ($otherTotal > 0) {
            $lines[] = '[OTROS METODOS DE PAGO]';
            $lines[] = 'OTROS METODOS DE PAGO: '.$this->money($otherTotal);
            foreach ($otros as $name => $amount) {
                $value = (float) $amount;
                if ($value <= 0) {
                    continue;
                }
                $lines[] = '  - '.$this->formatMethodLabel((string) $name).': '.$this->money($value);
            }
            $lines[] = '';
        }

        if (((float) ($egresos['total'] ?? 0)) > 0) {
            $lines[] = '[EGRESOS REGISTRADOS]';
            $lines[] = 'EGRESOS REGISTRADOS (desde modulo de Egresos):';
            $lines[] = 'Total: '.$this->money((float) ($egresos['total'] ?? 0));
            $lines[] = 'Ver detalles en: '.($egresos['url'] ?? '/admin/egresos');
            $lines[] = '';
        }

        $difference = (float) ($kpis['diferencia'] ?? 0);
        $lines[] = '[CIERRE FINAL]';
        $lines[] = 'TOTAL MANUAL (Ventas): '.$this->money((float) ($conciliacion['total_manual_ventas'] ?? 0));
        $lines[] = 'DIFERENCIA: '.$this->money($difference).' '.$this->differenceLabel($difference);
        $lines[] = 'Formula: '.($conciliacion['formula'] ?? '(Manual + Inicial) - Esperado');
        $lines[] = 'Nota: '.($conciliacion['nota'] ?? 'El Esperado ya considera egresos.');

        $closingObservations = trim((string) ($summary['meta']['closing_observations'] ?? ''));
        if ($closingObservations !== '') {
            $lines[] = '';
            $lines[] = 'Observaciones: '.$closingObservations;
        }

        return implode("\n", $lines)."\n";
    }

    private function extractMoney(string $text, string $pattern): ?float
    {
        if (! preg_match($pattern, $text, $matches)) {
            return null;
        }

        return $this->normalizeMoney($matches[1]);
    }

    private function extractLine(string $text, string $prefix): ?string
    {
        if (! preg_match('/^'.preg_quote($prefix, '/').'\s*(.+)$/mi', $text, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function extractUrl(string $text): ?string
    {
        if (! preg_match('/Ver detalles en:\s*(\S+)/i', $text, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private function extractMethodsDetailBlock(string $text): ?string
    {
        if (! preg_match('/OTROS METODOS DE PAGO:\s*S\/\s*[\d\.,-]+\s*\n(.+?)(?:\n\s*\n|\[EGRESOS REGISTRADOS\]|EGRESOS REGISTRADOS|TOTAL MANUAL)/is', $text, $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    /**
     * @return array<string, float>
     */
    private function parseMethodsBlock(?string $block): array
    {
        if ($block === null) {
            return [];
        }

        $result = [];
        preg_match_all('/([A-Za-z\s_]+):\s*S\/\s*([\d\.,-]+)/u', $block, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $label = trim($match[1]);
            $value = trim($match[2]);
            $normalizedLabel = $this->normalizeMethodName($label);
            $amount = $this->normalizeMoney(str_replace('S/', '', $value));
            $result[$normalizedLabel] = $amount;
        }

        return $result;
    }

    /**
     * @return array<string, int>
     */
    private function parseDenominationLine(?string $line): array
    {
        if ($line === null) {
            return [];
        }

        $result = [];
        preg_match_all('/S\/\s*([\d\.]+)\s*(?:×|x)\s*(\d+)/u', $line, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $result[(string) $match[1]] = (int) $match[2];
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $denominations
     * @return array<string, int>
     */
    private function normalizeDenominations(array $denominations): array
    {
        $result = [];
        foreach ($denominations as $label => $quantity) {
            $result[(string) $label] = (int) $quantity;
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>  $methods
     * @return array<string, float>
     */
    private function normalizeMethods(array $methods): array
    {
        $result = [];
        foreach ($methods as $label => $amount) {
            $result[$this->normalizeMethodName((string) $label)] = (float) $amount;
        }

        return $result;
    }

    private function normalizeMethodName(string $label): string
    {
        return match (strtolower(trim($label))) {
            'pedidos ya', 'pedidosya', 'pedidos_ya' => 'pedidos_ya',
            'didi', 'didi food', 'didi_food' => 'didi',
            'card', 'tarjeta' => 'tarjeta',
            default => strtolower(str_replace(' ', '_', trim($label))),
        };
    }

    private function normalizeMoney(string $raw): float
    {
        $clean = str_replace([',', ' '], ['', ''], trim($raw));

        return (float) $clean;
    }

    /**
     * @param  array<string, int>  $values
     */
    private function formatDenominationInline(array $values): string
    {
        $parts = [];
        foreach ($values as $label => $quantity) {
            $parts[] = 'S/'.$label.'x'.$quantity;
        }

        return implode(' | ', $parts);
    }

    private function differenceStatus(float $difference): string
    {
        if ($difference === 0.0) {
            return 'sin_diferencia';
        }

        return $difference > 0 ? 'sobrante' : 'faltante';
    }

    private function differenceLabel(float $difference): string
    {
        if ($difference === 0.0) {
            return '(SIN DIFERENCIA)';
        }

        return $difference > 0 ? '(SOBRANTE)' : '(FALTANTE)';
    }

    private function money(float $value): string
    {
        return 'S/ '.number_format($value, 2);
    }

    private function formatMethodLabel(string $name): string
    {
        return match ($name) {
            'pedidos_ya' => 'Pedidos Ya',
            'bita_express' => 'Bita Express',
            default => ucfirst(str_replace('_', ' ', $name)),
        };
    }
}
