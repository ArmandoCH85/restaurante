# Fix para "Total Notas de Venta" mostrando 0.00

## Problema Identificado

El reporte de ventas mostraba "Total Notas de Venta: 0.00" debido a una **inconsistencia en el almacenamiento de datos** entre diferentes partes del sistema.

## Causa Raíz

### Inconsistencia en el Almacenamiento de Notas de Venta

Las Notas de Venta se almacenaban de **dos formas diferentes** en la base de datos:

1. **Forma actual (PosController)**: 
   - `invoice_type = 'receipt'` 
   - `sunat_status = null`

2. **Forma legacy (Order model y Factory)**:
   - `invoice_type = 'sales_note'`
   - `sunat_status = 'PENDIENTE'` o `'NO_APLICA'`

### Problema en la Consulta

El método `getTotalByInvoiceType()` en `ReportViewerPage.php` solo buscaba la primera forma, por lo que no encontraba las Notas de Venta almacenadas en el formato legacy.

## Soluciones Implementadas

### 1. Actualización de la Consulta del Reporte (`ReportViewerPage.php`)

```php
case 'sales_note':
    // Notas de Venta: Buscar en ambas formas de almacenamiento
    // 1. Forma actual: invoice_type='receipt' + sunat_status=null
    $currentForm = $order->invoices->where('invoice_type', 'receipt')
        ->whereNull('sunat_status')->isNotEmpty();
    
    // 2. Forma legacy: invoice_type='sales_note' (cualquier sunat_status)
    $legacyForm = $order->invoices->where('invoice_type', 'sales_note')->isNotEmpty();
    
    return $currentForm || $legacyForm;
```

### 2. Corrección del Modelo Order (`Order.php`)

Se agregó lógica de mapeo consistente en el método `generateInvoice()`:

```php
// Mapear tipo de factura para almacenamiento en BD (sales_note se guarda como receipt)
$invoiceTypeForDb = $invoiceType === 'sales_note' ? 'receipt' : $invoiceType;

// Establecer estado SUNAT según el tipo de comprobante
$sunatStatus = in_array($invoiceType, ['invoice', 'receipt']) ? 'PENDIENTE' : null;
```

### 3. Actualización del Factory (`InvoiceFactory.php`)

Se aseguró que el factory genere datos consistentes:

```php
$invoiceType = $this->faker->randomElement(['invoice', 'receipt', 'sales_note']);

// Mapear sales_note a receipt para BD y establecer sunat_status correcto
$invoiceTypeForDb = $invoiceType === 'sales_note' ? 'receipt' : $invoiceType;
$sunatStatus = in_array($invoiceType, ['invoice', 'receipt']) ? 'PENDIENTE' : null;
```

### 4. Migración de Datos (`2025_01_16_000000_fix_sales_notes_consistency.php`)

Para limpiar datos existentes:

```php
// Convertir sales_note -> receipt + sunat_status=null
DB::table('invoices')
    ->where('invoice_type', 'sales_note')
    ->update([
        'invoice_type' => 'receipt',
        'sunat_status' => null
    ]);
```

### 5. Comandos de Diagnóstico y Corrección

- `php artisan debug:sales-notes-report` - Analiza el estado actual de los datos
- `php artisan fix:sales-notes-data --dry-run` - Simula correcciones sin aplicarlas
- `php artisan fix:sales-notes-data` - Aplica las correcciones

## Estado Final Esperado

### Almacenamiento Consistente de Comprobantes

| Tipo de Comprobante | invoice_type | sunat_status | Series Típicas |
|-------------------|--------------|--------------|---------------|
| **Nota de Venta** | `receipt` | `null` | NV001, NV002... |
| **Boleta Electrónica** | `receipt` | `PENDIENTE`/`ACEPTADO`/etc | B001, B002... |
| **Factura Electrónica** | `invoice` | `PENDIENTE`/`ACEPTADO`/etc | F001, F002... |

### Lógica de Identificación

- **Notas de Venta**: `invoice_type='receipt'` AND `sunat_status IS NULL`
- **Boletas**: `invoice_type='receipt'` AND `sunat_status IS NOT NULL` AND `sunat_status != 'NO_APLICA'`
- **Facturas**: `invoice_type='invoice'`

## Verificación del Fix

1. Ejecutar la migración: `php artisan migrate`
2. Corregir datos existentes: `php artisan fix:sales-notes-data`
3. Verificar resultados: `php artisan debug:sales-notes-report`
4. Probar el reporte de ventas en el panel administrativo

## Archivos Modificados

- `app/Filament/Pages/ReportViewerPage.php` - Lógica de consulta actualizada
- `app/Models/Order.php` - Método generateInvoice() corregido
- `database/factories/InvoiceFactory.php` - Factory consistente
- `database/migrations/2025_01_16_000000_fix_sales_notes_consistency.php` - Migración de datos
- `app/Console/Commands/DebugSalesNotesReport.php` - Comando de diagnóstico
- `app/Console/Commands/FixSalesNotesData.php` - Comando de corrección

## Resultado

Después de aplicar todas las correcciones, el reporte de ventas debe mostrar correctamente el total de Notas de Venta, combinando tanto los registros en formato actual como los legacy.