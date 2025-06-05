# 🧮 Corrección del Cálculo de IGV - Normativa Peruana

## 📋 Problema Identificado

El sistema estaba calculando **INCORRECTAMENTE** el IGV agregando 18% adicional al precio, cuando los precios registrados **YA INCLUYEN IGV**.

### ❌ Cálculo Anterior (INCORRECTO):
```php
// INCORRECTO: Agregaba 18% adicional
$tax = $subtotal * 0.18;
$total = $subtotal + $tax;

// Ejemplo: Producto S/ 100.00
// IGV calculado: S/ 18.00 (INCORRECTO)
// Total: S/ 118.00
```

### ✅ Cálculo Actual (CORRECTO):
```php
// CORRECTO: Calcula IGV incluido en el precio
$includedIgv = $priceWithIgv / 1.18 * 0.18;
$subtotal = $priceWithIgv / 1.18;

// Ejemplo: Producto S/ 118.00 (ya incluye IGV)
// IGV incluido: S/ 18.00 (CORRECTO)
// Subtotal: S/ 100.00
// Total: S/ 118.00
```

## 🎯 Solución Implementada

### **1. Trait CalculatesIgv**
- **Archivo**: `app/Traits/CalculatesIgv.php`
- **Propósito**: Centralizar cálculos de IGV según normativa peruana
- **Métodos principales**:
  - `calculateIncludedIgv()`: Calcula IGV incluido en precio
  - `calculateSubtotalFromPriceWithIgv()`: Calcula subtotal sin IGV
  - `getIgvBreakdown()`: Información completa de desglose

### **2. Modelo Order Actualizado**
- **Archivo**: `app/Models/Order.php`
- **Cambios**:
  - Agregado `use CalculatesIgv`
  - Método `recalculateTotals()` corregido
  - Logs mejorados para depuración

### **3. Controlador POS Corregido**
- **Archivo**: `app/Http/Controllers/PosController.php`
- **Cambio**: Usar `recalculateTotals()` en lugar de cálculo manual

### **4. Modelo Invoice Actualizado**
- **Archivo**: `app/Models/Invoice.php`
- **Cambios**:
  - Agregado `use CalculatesIgv`
  - Métodos `getCorrectSubtotalAttribute()` y `getCorrectIgvAttribute()`
  - Método `getCorrectTaxBreakdown()` para desglose completo

### **5. Vistas Actualizadas**
- **Pre-cuenta**: `resources/views/pos/pre-bill-print.blade.php`
- **Facturas**: `resources/views/pos/invoice-print.blade.php`
- **Boletas**: `resources/views/pos/receipt-print.blade.php`
- **Notas de venta**: `resources/views/pos/sales-note-print.blade.php`
- **Cotizaciones**: `resources/views/reports/quotation.blade.php`
- **Agregado**: Nota "* Precios incluyen IGV" en todos los documentos

### **6. Comandos de Migración**
- **Órdenes**: `app/Console/Commands/RecalculateOrderTotals.php`
- **Facturas**: `app/Console/Commands/RecalculateInvoiceTotals.php`
- **Uso**: Recalcular documentos existentes con nueva lógica

## 🚀 Comandos para Aplicar los Cambios

### **1. Verificar qué órdenes se afectarían (DRY-RUN):**
```bash
php artisan orders:recalculate-totals --dry-run --all
```

### **2. Recalcular solo órdenes con IGV en 0:**
```bash
php artisan orders:recalculate-totals --zero-tax
```

### **3. Recalcular TODAS las órdenes:**
```bash
php artisan orders:recalculate-totals --all
```

### **4. Recalcular facturas (DRY-RUN):**
```bash
php artisan invoices:recalculate-totals --dry-run
```

### **5. Recalcular solo boletas:**
```bash
php artisan invoices:recalculate-totals --type=receipt
```

### **6. Recalcular todas las facturas:**
```bash
php artisan invoices:recalculate-totals
```

## 📊 Ejemplos de Cálculo

### **Ejemplo 1: Producto Simple**
```
Precio registrado: S/ 118.00 (incluye IGV)

✅ Cálculo correcto:
- Subtotal: S/ 118.00 ÷ 1.18 = S/ 100.00
- IGV: S/ 118.00 ÷ 1.18 × 0.18 = S/ 18.00
- Total: S/ 118.00
```

### **Ejemplo 2: Orden con Descuento**
```
Productos: S/ 236.00 (incluye IGV)
Descuento: S/ 20.00

✅ Cálculo correcto:
- Total con IGV después descuento: S/ 216.00
- Subtotal: S/ 216.00 ÷ 1.18 = S/ 183.05
- IGV: S/ 216.00 ÷ 1.18 × 0.18 = S/ 32.95
- Total: S/ 216.00
```

### **Ejemplo 3: Múltiples Productos**
```
Producto A: S/ 59.00 (incluye IGV)
Producto B: S/ 118.00 (incluye IGV)
Total productos: S/ 177.00

✅ Cálculo correcto:
- Subtotal: S/ 177.00 ÷ 1.18 = S/ 150.00
- IGV: S/ 177.00 ÷ 1.18 × 0.18 = S/ 27.00
- Total: S/ 177.00
```

## 🔍 Validación de Cambios

### **Verificar en Base de Datos:**
```sql
-- Verificar que subtotal + IGV = total
SELECT 
    id,
    subtotal,
    tax as igv,
    total,
    (subtotal + tax) as calculated_total,
    ABS(total - (subtotal + tax)) as difference
FROM orders 
WHERE ABS(total - (subtotal + tax)) > 0.01;
```

### **Verificar en Aplicación:**
```php
// En tinker
$order = Order::find(1);
$breakdown = $order->getIgvBreakdown($order->total);
dd($breakdown);
```

## 📝 Notas Importantes

### **Cumplimiento Fiscal:**
- ✅ Cumple con normativa peruana de IGV incluido
- ✅ Muestra desglose correcto en comprobantes
- ✅ Nota aclaratoria "* Precios incluyen IGV"

### **Compatibilidad:**
- ✅ No afecta funcionalidad existente
- ✅ Órdenes nuevas usan cálculo correcto automáticamente
- ✅ Órdenes existentes se pueden recalcular con comando

### **Principio KISS:**
- ✅ Solución simple y directa
- ✅ Un solo trait para todos los cálculos
- ✅ Métodos reutilizables en todo el sistema

## 🎯 Resultado Final

### **Antes:**
- IGV calculado incorrectamente (agregando 18%)
- Totales inflados
- No cumplía normativa peruana

### **Después:**
- ✅ IGV calculado correctamente (incluido en precio)
- ✅ Totales precisos
- ✅ Cumple normativa fiscal peruana
- ✅ Documentos con desglose correcto
- ✅ Nota aclaratoria en comprobantes

## 🔧 Mantenimiento

Para futuras modificaciones de precios, recordar:
1. Los precios en `products` deben incluir IGV
2. Usar métodos del trait `CalculatesIgv`
3. Ejecutar `recalculateTotals()` después de cambios
4. Validar con `getIgvBreakdown()` si es necesario
