# Estándares de Colores POS - Sistema de Mesas y Delivery

## 🎨 Sistema de Colores Unificado

### Basado en:
- **Filament 3** Color System
- **Sistemas POS** profesionales (Square, Toast, Lightspeed)
- **Psicología del color** para entornos de trabajo
- **Accesibilidad** WCAG 2.1

## 📊 Estados de Mesas

| Estado | Color | Código | Uso | Justificación |
|--------|-------|--------|-----|---------------|
| **🟢 Disponible** | Verde | `success` / `#10b981` | Mesa libre para ocupar | Universal - Verde = Disponible |
| **🔴 Ocupada** | Rojo | `danger` / `#ef4444` | Mesa con clientes activos | Universal - Rojo = Ocupado/Atención |
| **🟡 Reservada** | Amarillo | `warning` / `#f59e0b` | Mesa reservada para horario específico | Universal - Amarillo = Precaución |
| **🔵 Pre-Cuenta** | Azul | `info` / `#06b6d4` | Mesa solicitó la cuenta | POS Standard - Azul = Proceso |
| **⚫ Mantenimiento** | Gris | `gray` / `#6b7280` | Mesa fuera de servicio | Universal - Gris = Inactivo |

## 🚚 Estados de Delivery

| Estado | Color | Código | Uso | Justificación |
|--------|-------|--------|-----|---------------|
| **🕐 Pendiente** | Gris | `gray` / `#6b7280` | Pedido creado, sin asignar | Neutral - Esperando acción |
| **👨‍💼 Asignado** | Azul | `info` / `#06b6d4` | Repartidor asignado | Azul = Información/Proceso |
| **🚛 En Ruta** | Amarillo | `warning` / `#f59e0b` | Pedido siendo entregado | Amarillo = En progreso |
| **✅ Entregado** | Verde | `success` / `#10b981` | Entrega completada | Verde = Éxito |
| **🚫 Cancelado** | Rojo | `danger` / `#ef4444` | Pedido cancelado | Rojo = Error/Cancelación |

## 🎯 Implementación Técnica

### CSS Variables (Filament 3 Compatible)
```css
:root {
    /* Estados Principales */
    --success-color: #10b981;      /* Verde - Disponible/Entregado */
    --danger-color: #ef4444;       /* Rojo - Ocupada/Cancelado */
    --warning-color: #f59e0b;      /* Amarillo - Reservada/En Ruta */
    --info-color: #06b6d4;         /* Azul - Pre-Cuenta/Asignado */
    --gray-color: #6b7280;         /* Gris - Mantenimiento/Pendiente */
    
    /* Variaciones de Intensidad */
    --success-light: rgba(16, 185, 129, 0.15);
    --danger-light: rgba(239, 68, 68, 0.15);
    --warning-light: rgba(245, 158, 11, 0.15);
    --info-light: rgba(6, 182, 212, 0.15);
    --gray-light: rgba(107, 114, 128, 0.15);
}
```

### Filament Badge Colors
```php
// Para tablas y componentes Filament
->colors([
    'gray' => 'pending',           // Gris para pendiente
    'info' => 'assigned',          // Azul para asignado/pre-cuenta
    'warning' => 'in_transit',     // Amarillo para en ruta/reservada
    'success' => 'delivered',      // Verde para entregado/disponible
    'danger' => 'cancelled',       // Rojo para cancelado/ocupada
])
```

## 🏆 Mejores Prácticas

### ✅ Hacer:
- **Consistencia**: Usar los mismos colores en todo el sistema
- **Contraste**: Asegurar legibilidad en fondos claros y oscuros
- **Iconos**: Acompañar colores con iconos para accesibilidad
- **Gradientes sutiles**: Usar gradientes ligeros para profundidad
- **Bordes**: Usar bordes coloreados para mejor definición

### ❌ Evitar:
- Colores muy saturados que cansen la vista
- Usar rojo y verde juntos (daltonismo)
- Más de 5 colores diferentes en una vista
- Colores que cambien significado entre contextos
- Fondos muy contrastantes que dificulten la lectura

## 📱 Responsive y Accesibilidad

### Contraste Mínimo (WCAG 2.1)
- **Texto normal**: 4.5:1
- **Texto grande**: 3:1
- **Elementos UI**: 3:1

### Modo Oscuro
```css
.dark {
    --success-color: #34d399;     /* Verde más claro */
    --danger-color: #f87171;      /* Rojo más claro */
    --warning-color: #fbbf24;     /* Amarillo más claro */
    --info-color: #22d3ee;        /* Azul más claro */
    --gray-color: #9ca3af;        /* Gris más claro */
}
```

## 🔄 Transiciones y Animaciones

### Estados Dinámicos
```css
.table-card {
    transition: all 0.2s ease;
}

.table-occupied .table-number {
    animation: pulse 2s infinite;
}

.status-badge {
    transition: all 0.2s ease;
}
```

## 📊 Métricas de Éxito

### KPIs de Color UX:
- **Tiempo de reconocimiento**: < 0.5 segundos
- **Tasa de error**: < 2% en identificación de estados
- **Satisfacción del usuario**: > 4.5/5
- **Accesibilidad**: 100% WCAG 2.1 AA

## 🎨 Paleta Extendida (Futuras Expansiones)

| Uso | Color | Código | Cuándo Usar |
|-----|-------|--------|-------------|
| **Prioridad Alta** | Naranja | `#f97316` | Pedidos urgentes |
| **VIP** | Púrpura | `#8b5cf6` | Clientes especiales |
| **Promoción** | Rosa | `#ec4899` | Ofertas especiales |
| **Temporal** | Índigo | `#6366f1` | Estados temporales |

---

## 📝 Notas de Implementación

1. **Todos los colores** están basados en la paleta de Tailwind CSS incluida en Filament 3
2. **Compatibilidad** garantizada con temas claro y oscuro
3. **Performance** optimizado con CSS variables
4. **Escalabilidad** preparado para nuevos estados
5. **Mantenimiento** centralizado en variables CSS

---

*Última actualización: Enero 2025*
*Versión: 1.0*
*Compatible con: Filament 3.x*