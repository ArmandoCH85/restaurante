<?php

namespace App\Traits;

/**
 * Trait para cálculos de IGV según normativa peruana
 * 
 * Los precios en el sistema YA INCLUYEN IGV (18%)
 * Este trait calcula cuánto IGV está incluido en el precio
 */
trait CalculatesIgv
{
    /**
     * Tasa de IGV en Perú (18%)
     */
    public const IGV_RATE = 0.18;

    /**
     * Factor para calcular IGV incluido: 1 + tasa_igv
     */
    public const IGV_FACTOR = 1.18;

    /**
     * Calcula el IGV incluido en un precio que ya contiene IGV
     * 
     * Fórmula: IGV = Precio_con_IGV / 1.18 * 0.18
     * 
     * @param float $priceWithIgv Precio que ya incluye IGV
     * @return float IGV incluido en el precio
     */
    public function calculateIncludedIgv(float $priceWithIgv): float
    {
        return round($priceWithIgv / self::IGV_FACTOR * self::IGV_RATE, 2);
    }

    /**
     * Calcula el subtotal (precio sin IGV) de un precio que incluye IGV
     * 
     * Fórmula: Subtotal = Precio_con_IGV / 1.18
     * 
     * @param float $priceWithIgv Precio que ya incluye IGV
     * @return float Precio sin IGV (subtotal)
     */
    public function calculateSubtotalFromPriceWithIgv(float $priceWithIgv): float
    {
        return round($priceWithIgv / self::IGV_FACTOR, 2);
    }

    /**
     * Calcula el precio con IGV a partir de un subtotal
     * 
     * Fórmula: Precio_con_IGV = Subtotal * 1.18
     * 
     * @param float $subtotal Precio sin IGV
     * @return float Precio con IGV incluido
     */
    public function calculatePriceWithIgv(float $subtotal): float
    {
        return round($subtotal * self::IGV_FACTOR, 2);
    }

    /**
     * Valida que los cálculos de IGV sean correctos
     * 
     * @param float $priceWithIgv Precio con IGV
     * @param float $subtotal Subtotal calculado
     * @param float $igv IGV calculado
     * @return bool True si los cálculos son correctos
     */
    public function validateIgvCalculations(float $priceWithIgv, float $subtotal, float $igv): bool
    {
        $calculatedTotal = $subtotal + $igv;
        $difference = abs($priceWithIgv - $calculatedTotal);
        
        // Permitir diferencia de hasta 0.01 por redondeo
        return $difference <= 0.01;
    }

    /**
     * Obtiene información detallada de los cálculos de IGV
     * 
     * @param float $priceWithIgv Precio que incluye IGV
     * @return array Array con subtotal, IGV y total
     */
    public function getIgvBreakdown(float $priceWithIgv): array
    {
        $subtotal = $this->calculateSubtotalFromPriceWithIgv($priceWithIgv);
        $igv = $this->calculateIncludedIgv($priceWithIgv);
        
        return [
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $priceWithIgv,
            'igv_rate' => self::IGV_RATE * 100, // 18%
            'is_valid' => $this->validateIgvCalculations($priceWithIgv, $subtotal, $igv)
        ];
    }
}
