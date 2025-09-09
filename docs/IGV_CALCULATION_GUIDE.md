# Guía de Cálculo de IGV - Sistema de Restaurante

## Contexto Importante

**TODOS LOS PRECIOS EN EL SISTEMA YA INCLUYEN IGV DEL 18%**

Esto significa que cuando un producto tiene un precio de S/ 118.00, este precio ya incluye el IGV. El desglose correcto sería:
- Subtotal (sin IGV): S/ 100.00
- IGV (18%): S/ 18.00
- Total (con IGV): S/ 118.00

## Fórmulas Correctas

### Para calcular el subtotal SIN IGV a partir del precio CON IGV:
```php
$subtotalSinIgv = $precioConIgv / 1.18;
```

### Para calcular el IGV incluido en el precio:
```php
$igvIncluido = $precioConIgv - ($precioConIgv / 1.18);
// O de forma más directa:
$igvIncluido = $precioConIgv / 1.18 * 0.18;
```

## Implementación en el Sistema

### Trait CalculatesIgv

El sistema utiliza el trait `App\Traits\CalculatesIgv` que proporciona métodos estandarizados:

```php
// Calcular subtotal sin IGV
$subtotal = $model->calculateSubtotalFromPriceWithIgv($total);

// Calcular IGV incluido
$igv = $model->calculateIncludedIgv($total);

// Obtener desglose completo
$breakdown = $model->getIgvBreakdown($total);
```

### Modelos que usan CalculatesIgv

- `App\Models\Invoice`
- `App\Models\Order`

### Métodos de Acceso en Modelos

#### Invoice
```php
$invoice->correct_subtotal  // Subtotal sin IGV
$invoice->correct_igv       // IGV incluido
$invoice->getCorrectTaxBreakdown() // Desglose completo
```

#### Order
```php
$order->recalculateTotals() // Recalcula totales usando IGV incluido
```

## Errores Comunes Corregidos

### ❌ INCORRECTO (asume que el subtotal NO incluye IGV):
```php
$igv = $subtotal * 0.18;
$total = $subtotal + $igv;
```

### ✅ CORRECTO (reconoce que el total YA incluye IGV):
```php
$subtotal = $total / 1.18;
$igv = $total - $subtotal;
```

## Archivos Corregidos

1. **TableMap.php**: Cambiado de `$subtotal * 0.18` a usar `recalculateTotals()`
2. **unified-payment-form.blade.php**: Cambiado de `$order->subtotal * 0.18` a `$order->tax`
3. **invoice-form.blade.php**: Cambiado de `$order->subtotal * 0.18` a `$order->tax`
4. **PointOfSale.php**: Agregados métodos `getCartSubtotal()` y `getCartTax()` que calculan correctamente

## Verificación en Vistas

### ✅ Implementaciones Correctas:
```blade
<!-- Usando atributos del modelo -->
S/ {{ number_format($order->tax, 2) }}
S/ {{ number_format($invoice->correct_igv, 2) }}

<!-- Usando cálculo directo correcto -->
S/ {{ number_format($total / 1.18 * 0.18, 2) }}
```

### ❌ Evitar:
```blade
<!-- NO usar esto cuando los precios incluyen IGV -->
S/ {{ number_format($subtotal * 0.18, 2) }}
```

## Casos de Uso

### Punto de Venta (POS)
- Los precios de productos incluyen IGV
- El carrito suma totales con IGV incluido
- El desglose se calcula dividiendo entre 1.18

### Facturación
- Los totales de órdenes incluyen IGV
- Los comprobantes muestran el desglose correcto
- SUNAT requiere el desglose sin IGV + IGV = Total

### Reportes
- Todos los cálculos deben usar la lógica de IGV incluido
- Los subtotales mostrados son sin IGV
- Los totales mostrados incluyen IGV

## Notas para Desarrolladores

1. **Siempre usar el trait CalculatesIgv** cuando sea posible
2. **Verificar que los cálculos manuales** usen la fórmula correcta
3. **En las vistas**, preferir usar atributos del modelo (`$order->tax`) sobre cálculos manuales
4. **Al agregar nuevas funcionalidades**, recordar que los precios base incluyen IGV

## Contacto

Para dudas sobre el cálculo de IGV, consultar con el equipo de desarrollo o revisar el trait `CalculatesIgv`.