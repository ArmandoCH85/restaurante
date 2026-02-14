<?php

namespace App\Traits;

use App\Models\AppSetting;

/**
 * Trait para cálculos de IGV según normativa peruana
 * 
 * Los precios en el sistema YA INCLUYEN IGV
 * Este trait calcula cuánto IGV está incluido en el precio
 */
trait CalculatesIgv
{
    /**
     * Obtiene la tasa de IGV actual desde la configuración.
     * Retorna el valor porcentual (ej. 18.00 o 10.50).
     */
    public function getIgvRate(): float
    {
        // Intentar obtener del modelo si tiene la propiedad (para futuro soporte de tasa histórica)
        if (isset($this->igv_percent)) {
            return (float) $this->igv_percent;
        }
        
        // Obtener de la configuración global
        return (float) AppSetting::getSetting('FacturacionElectronica', 'igv_percent') ?: 18.00;
    }

    /**
     * Obtiene el factor de IGV (1 + tasa/100).
     * Ej. Si tasa es 18%, factor es 1.18.
     * Ej. Si tasa es 10.5%, factor es 1.105.
     */
    public function getIgvFactor(): float
    {
        return 1 + ($this->getIgvRate() / 100);
    }
    
    /**
     * Calcula el IGV incluido en un precio que ya contiene IGV
     * 
     * Fórmula: IGV = Precio_con_IGV / Factor * (Tasa / 100)
     * 
     * @param float $priceWithIgv Precio que ya incluye IGV
     * @return float IGV incluido en el precio
     */
    public function calculateIncludedIgv(float $priceWithIgv): float
    {
        $factor = $this->getIgvFactor();
        $rate = $this->getIgvRate() / 100;
        
        if ($factor == 0) return 0; // Evitar división por cero
        
        return round($priceWithIgv / $factor * $rate, 2);
    }

    /**
     * Calcula el subtotal (precio sin IGV) de un precio que incluye IGV
     * 
     * Fórmula: Subtotal = Precio_con_IGV / Factor
     * 
     * @param float $priceWithIgv Precio que ya incluye IGV
     * @return float Precio sin IGV (subtotal)
     */
    public function calculateSubtotalFromPriceWithIgv(float $priceWithIgv): float
    {
        $factor = $this->getIgvFactor();
        
        if ($factor == 0) return $priceWithIgv;
        
        return round($priceWithIgv / $factor, 2);
    }

    /**
     * Calcula el precio con IGV a partir de un subtotal
     * 
     * Fórmula: Precio_con_IGV = Subtotal * Factor
     * 
     * @param float $subtotal Precio sin IGV
     * @return float Precio con IGV incluido
     */
    public function calculatePriceWithIgv(float $subtotal): float
    {
        $factor = $this->getIgvFactor();
        return round($subtotal * $factor, 2);
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
        $rate = $this->getIgvRate();
        
        return [
            'subtotal' => $subtotal,
            'igv' => $igv,
            'total' => $priceWithIgv,
            'igv_rate' => $rate,
            'is_valid' => $this->validateIgvCalculations($priceWithIgv, $subtotal, $igv)
        ];
    }
}
