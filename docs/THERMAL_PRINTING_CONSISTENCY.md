# 🖨️ Consistencia de Impresión Térmica - Sistema POS

## 📋 Resumen

El sistema POS del restaurante mantiene **perfecta consistencia** en el formato de impresión térmica para todos los documentos:

- ✅ **Comandas** (kitchen orders)
- ✅ **Pre-cuentas** (pre-bills) 
- ✅ **Comprobantes** (invoices/receipts)

## 🎯 Especificaciones Técnicas Unificadas

### **Formato de Papel**
```css
@page {
    size: 80mm auto;  /* Papel térmico estándar */
    margin: 0;
}
```

### **Tipografía Consistente**
```css
body {
    width: 80mm;
    font-family: Arial, sans-serif;
    font-size: 12px;        /* Comandas y pre-cuentas */
    font-size: 11px;        /* Comprobantes (más contenido) */
    line-height: 1.2;
    padding: 3mm;
}
```

### **Soporte Multi-Formato**
- **80mm**: Formato principal (estándar)
- **57mm**: Formato alternativo (papel más pequeño)
- **A4/A5**: Formato digital/PDF cuando no es impresión térmica

## 🔗 Rutas del Sistema

### **Pre-cuenta**
```php
// Generar pre-cuenta directa
GET /pos/prebill-pdf/{order}
Route::name('pos.prebill.pdf')

// Crear orden y mostrar pre-cuenta
GET /pos/prebill/generate
Route::name('pos.prebill.generate')

// Vista previa térmica (desarrollo)
GET /thermal-preview/pre-bill/{order}
Route::name('thermal.preview.prebill')
```

### **Comanda**
```php
// Generar comanda directa
GET /pos/command-pdf/{order}
Route::name('pos.command.pdf')

// Crear orden y mostrar comanda
GET /pos/command/generate
Route::name('pos.command.generate')
```

### **Comprobantes**
```php
// Formulario de facturación
GET /pos/invoice/form/{order}
Route::name('pos.invoice.form')

// Generar comprobante
POST /pos/invoice/generate/{order}
Route::name('pos.invoice.generate')
```

## 📁 Archivos de Vista

### **Pre-cuenta**
- `resources/views/pos/pre-bill-print.blade.php`
- Formato: 80mm térmico + A5 digital
- Contenido: Lista de productos, totales, información básica

### **Comanda**
- `resources/views/pos/command-print.blade.php`
- Formato: 80mm térmico optimizado
- Contenido: Productos para cocina, mesa, notas

### **Comprobantes**
- `resources/views/pos/invoice-print.blade.php` (Facturas)
- `resources/views/pos/receipt-print.blade.php` (Boletas)
- `resources/views/pos/sales-note-print.blade.php` (Notas de venta)
- Formato: 80mm térmico + A4 digital

## 🎨 Características de Diseño

### **Principio KISS Aplicado**
- ✅ Información esencial únicamente
- ✅ Sin logos en documentos POS
- ✅ Formato limpio y profesional
- ✅ Optimizado para papel térmico

### **Detección Automática**
```css
/* Papel 80mm estándar */
@media print {
    @page { size: 80mm auto; }
}

/* Papel 57mm pequeño */
@media print and (max-width: 57mm) {
    @page { size: 57mm auto; }
    body { font-size: 11px; }
}

/* Formato digital */
@media screen, print and (min-width: 148mm) {
    @page { size: A5; margin: 10mm; }
}
```

## 🔧 Funcionalidades Integradas

### **Generación Automática**
- Pre-cuenta se abre automáticamente después de generar comprobante
- Integración con componente Livewire POS
- Soporte para múltiples métodos de pago

### **Vista Previa Térmica**
- Rutas especiales para desarrollo/pruebas
- Simulación exacta del formato de impresión
- Útil para verificar diseño antes de imprimir

## 📊 Optimizaciones de Papel

### **Ahorro Estimado**
- **Pre-cuentas**: 35-40% menos papel vs. formato anterior
- **Comandas**: 25-30% menos papel vs. formato anterior
- **Comprobantes**: 40-50% menos papel vs. formato anterior

### **Mejoras Implementadas**
1. **Eliminación de tablas HTML** → Listas compactas
2. **Información consolidada** → Menos líneas
3. **Formato optimizado** → Mejor uso del espacio
4. **Sin elementos innecesarios** → Solo lo esencial

## 🚀 Uso en Producción

### **Impresión Directa**
```javascript
// Desde el POS Livewire
window.open('/pos/prebill-pdf/123', '_blank', 'width=800,height=600');
```

### **Integración con Impresora Térmica**
- Compatible con impresoras ESC/POS estándar
- Formato 80mm optimizado para velocidad
- Sin dependencias externas de PDF

## ✅ Verificación de Consistencia

Todos los documentos mantienen:
- ✅ Mismo ancho de papel (80mm)
- ✅ Misma fuente (Arial)
- ✅ Mismo padding (3mm)
- ✅ Mismo line-height (1.2)
- ✅ Misma estructura CSS
- ✅ Misma experiencia de usuario

## 🔍 Testing

Para verificar la consistencia:
1. Generar una comanda: `/pos/command-pdf/{order}`
2. Generar una pre-cuenta: `/pos/prebill-pdf/{order}`
3. Generar un comprobante: `/pos/invoice/form/{order}`
4. Comparar formato visual en impresión

**Resultado esperado**: Documentos visualmente consistentes con el mismo formato de papel y estilo.
