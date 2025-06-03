# Optimización para Impresión Térmica - Sistema de Restaurante

## Descripción General

Se ha implementado una optimización completa para impresión en papel térmico de 80mm y 57mm en todos los comprobantes del sistema de restaurante. La optimización incluye:

- **Boletas de venta** - Optimizada para papel térmico
- **Facturas** - Versión térmica y A4 normal
- **Notas de venta** - Optimizada para papel térmico
- **Comandas de cocina** - Optimizada para papel térmico
- **Pre-cuentas** - Versión térmica y A5 normal

## Características Implementadas

### 1. Aprovechamiento Máximo del Espacio
- Reducción de márgenes y espaciado
- Optimización de tipografía (tamaños de fuente reducidos)
- Eliminación de espacios vacíos innecesarios
- Líneas de separación con bordes punteados para mejor legibilidad

### 2. Adaptación Automática a Tamaños de Papel
- **80mm**: Configuración principal optimizada
- **57mm**: Media queries específicas para papel más estrecho
- Detección automática del ancho de papel durante la impresión

### 3. Información Dinámica de Empresa
Todos los comprobantes muestran información completa desde la base de datos:
- Razón Social / Nombre Comercial
- RUC
- Dirección fiscal
- Teléfono (si está configurado)
- Email (si está configurado)

### 4. Principio KISS (Keep It Simple, Stupid)
- Diseño simple y funcional
- Solo información esencial
- Sin logos para documentos POS
- Formato profesional y limpio

## Archivos Modificados

### Comprobantes Optimizados:
1. `resources/views/pos/receipt-print.blade.php` - Boletas
2. `resources/views/pos/invoice-print.blade.php` - Facturas (dual: térmica + A4)
3. `resources/views/pos/sales-note-print.blade.php` - Notas de venta
4. `resources/views/pos/command-print.blade.php` - Comandas
5. `resources/views/pos/pre-bill-print.blade.php` - Pre-cuentas (dual: térmica + A5)

## Cómo Funciona

### Detección Automática
El sistema utiliza CSS Media Queries para detectar automáticamente el tipo de impresión:

```css
/* Para papel térmico de 80mm */
@media print {
    @page {
        size: 80mm 297mm;
        margin: 0;
    }
}

/* Para papel térmico de 57mm */
@media print and (max-width: 57mm) {
    @page {
        size: 57mm 297mm;
        margin: 0;
    }
}
```

### Versiones Duales (Facturas y Pre-cuentas)
- **Versión térmica**: Se muestra automáticamente al imprimir en papel térmico
- **Versión normal**: Se mantiene para impresión en papel A4/A5 estándar
- Cambio automático sin intervención del usuario

## Configuración de Empresa

La información de empresa se obtiene dinámicamente del modelo `CompanyConfig`:

```php
\App\Models\CompanyConfig::getRazonSocial()
\App\Models\CompanyConfig::getRuc()
\App\Models\CompanyConfig::getDireccion()
\App\Models\CompanyConfig::getTelefono()
\App\Models\CompanyConfig::getEmail()
```

## Compatibilidad

### Navegadores Soportados:
- Chrome/Chromium (recomendado)
- Firefox
- Edge
- Safari

### Impresoras Térmicas:
- Cualquier impresora térmica estándar de 80mm
- Impresoras térmicas de 57mm
- Compatible con drivers ESC/POS

## Uso en Producción

### Para Usuarios:
1. Abrir cualquier comprobante desde el sistema
2. Usar Ctrl+P o el botón "Imprimir"
3. Seleccionar la impresora térmica
4. El formato se optimiza automáticamente

### Para Desarrolladores:
- No se requiere configuración adicional
- La optimización está activa por defecto
- Preserva toda la funcionalidad existente

## Beneficios

1. **Ahorro de papel**: Mejor aprovechamiento del espacio
2. **Legibilidad mejorada**: Tipografía optimizada para papel térmico
3. **Profesionalismo**: Información completa de empresa
4. **Flexibilidad**: Funciona con ambos tamaños de papel
5. **Compatibilidad**: No rompe funcionalidad existente

## Mantenimiento

### Actualizar Información de Empresa:
1. Ir a Admin Panel → Configuración → Datos de la Empresa
2. Modificar los campos necesarios
3. Los cambios se reflejan inmediatamente en todos los comprobantes

### Personalización Adicional:
- Modificar archivos CSS en las vistas correspondientes
- Ajustar tamaños de fuente en las media queries
- Personalizar espaciado según necesidades específicas

## Notas Técnicas

- Utiliza CSS Grid y Flexbox para layouts responsivos
- Media queries específicas para cada tamaño de papel
- Preserva funcionalidad JavaScript existente
- Compatible con sistema de notificaciones actual
- No afecta la generación de PDFs o otros formatos
