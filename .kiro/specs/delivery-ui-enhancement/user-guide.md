# Guía de Usuario - Nueva Interfaz de Delivery

## 🎉 ¡Bienvenido a la Nueva Experiencia de Delivery!

La interfaz de gestión de pedidos de delivery ha sido completamente renovada con componentes modernos de Filament 3, ofreciendo una experiencia más intuitiva, eficiente y visualmente atractiva.

## 📊 Dashboard de Estadísticas

### Vista General
Al acceder a la página de delivery, verás un dashboard con 4 métricas principales:

1. **⏳ Pedidos Pendientes**: Pedidos esperando asignación de repartidor
2. **🚚 En Tránsito**: Pedidos siendo entregados actualmente
3. **✅ Entregados Hoy**: Pedidos completados en el día con tendencia vs ayer
4. **📋 Total Activos**: Suma de pedidos en proceso (pendientes + asignados + en tránsito)

### Características
- **Actualización automática**: Se actualiza cada 30 segundos
- **Gráficos de tendencia**: Muestra los últimos 7 días
- **Comparación temporal**: Porcentaje de cambio vs día anterior
- **Interactivo**: Hover effects para mejor experiencia

## 🗂️ Tabla de Pedidos Mejorada

### Nuevas Columnas
- **Pedido**: Badge con número de orden y color primario
- **Cliente**: Información organizada (nombre + teléfono)
- **Dirección**: Con icono de ubicación y tooltip
- **Repartidor**: Estado visual (asignado/sin asignar)
- **Estado**: Badges coloridos con iconos específicos
- **Total**: Monto en soles con formato monetario

### Estados Visuales
- **⏳ Pendiente**: Gris - Esperando asignación
- **👤 Asignado**: Azul - Repartidor asignado
- **🚚 En Tránsito**: Amarillo - En camino
- **✅ Entregado**: Verde - Completado
- **❌ Cancelado**: Rojo - Cancelado

### Funciones de Tabla
- **Búsqueda global**: Busca en todos los campos
- **Ordenamiento**: Click en columnas para ordenar
- **Columnas personalizables**: Ocultar/mostrar columnas
- **Responsive**: Se adapta a móvil y tablet

## 🔍 Filtros Avanzados

### Tipos de Filtros
1. **Estado del Pedido**: Múltiple selección con emojis
2. **Repartidor**: Buscar por repartidor específico
3. **Rango de Fechas**: Selector de fechas con calendario
4. **Filtros rápidos**: "Hoy" y "Esta Semana"
5. **Rango de Total**: Por monto del pedido

### Cómo Usar
- Los filtros se muestran arriba de la tabla
- Selecciona múltiples opciones en cada filtro
- Los filtros activos se muestran como badges
- Click en "X" para remover filtros específicos

## 📝 Formulario de Nuevo Pedido (Wizard)

### Paso 1: Cliente
1. **Buscar Cliente Existente**:
   - Escribe nombre o teléfono
   - Selecciona de la lista
   - Datos se cargan automáticamente

2. **Datos del Cliente**:
   - Teléfono (obligatorio)
   - Nombre completo (obligatorio)
   - Tipo y número de documento (opcional)

### Paso 2: Entrega
1. **Tipo de Servicio**:
   - 🏠 A Domicilio
   - 🏪 Por Recoger

2. **Dirección**:
   - Dirección completa (obligatorio)
   - Referencias adicionales (opcional)

### Paso 3: Repartidor
- Seleccionar repartidor disponible
- Opción de crear nuevo repartidor
- Se puede dejar vacío para asignar después

### Características
- **Navegación por pasos**: Progreso visual
- **Validación en tiempo real**: Errores inmediatos
- **Autocompletado**: Búsqueda inteligente
- **Notificaciones**: Feedback cuando encuentra cliente

## ⚡ Acciones Rápidas

### Acciones Individuales
Cada pedido tiene un menú de acciones:

1. **💰 Procesar Pago**: Abre POS en nueva pestaña
2. **👤 Asignar Repartidor**: Modal con lista de repartidores
3. **🚚 Marcar En Tránsito**: Confirmación requerida
4. **✅ Marcar Entregado**: Confirmación requerida
5. **✏️ Editar**: Modificar detalles del pedido
6. **❌ Cancelar**: Con motivo de cancelación

### Acciones Masivas (Bulk Actions)
Selecciona múltiples pedidos para:

1. **Asignar Repartidor**: A todos los seleccionados
2. **Marcar En Tránsito**: Cambio masivo de estado
3. **Marcar Entregados**: Confirmación masiva
4. **Exportar**: Descargar en Excel/CSV/PDF
5. **Eliminar**: Con confirmación

## 📋 Detalles Expandibles

### Cómo Acceder
- Click en cualquier fila de la tabla
- Se expande mostrando información detallada

### Información Mostrada
1. **Cliente**: Nombre, teléfono, email
2. **Entrega**: Dirección, referencias, repartidor
3. **Pedido**: Total, estado, fecha de creación
4. **Productos**: Lista completa con cantidades y precios
5. **Timeline**: Historial de cambios de estado

## 📤 Exportación de Datos

### Exportar Todo
- Botón "Exportar Todo" en la parte superior
- Opciones: Excel, CSV, PDF
- Filtros por fecha
- Incluir/excluir detalles de productos

### Exportar Seleccionados
- Selecciona pedidos específicos
- Bulk action "Exportar Seleccionados"
- Mismas opciones de formato

## 🔔 Sistema de Notificaciones

### Tipos de Notificaciones
- **✅ Éxito**: Acciones completadas (verde)
- **ℹ️ Información**: Estados de procesamiento (azul)
- **⚠️ Advertencia**: Situaciones que requieren atención (amarillo)
- **❌ Error**: Problemas que necesitan solución (rojo)

### Características
- **Duración inteligente**: Más tiempo para mensajes importantes
- **Acciones integradas**: Botones Ver, Reintentar, Descargar
- **Emojis visuales**: Mejor comprensión rápida
- **Auto-dismiss**: Se ocultan automáticamente

## 📱 Diseño Responsive

### Móvil (320px+)
- Columnas esenciales visibles
- Formulario adaptado verticalmente
- Touch-friendly buttons
- Menús colapsables

### Tablet (768px+)
- Más columnas visibles
- Filtros organizados
- Wizard horizontal
- Mejor aprovechamiento del espacio

### Desktop (1024px+)
- Todas las funciones disponibles
- Múltiples columnas
- Hover effects
- Máxima productividad

## 🚀 Consejos de Productividad

### Atajos de Teclado
- **Tab**: Navegar entre campos
- **Enter**: Confirmar acciones
- **Escape**: Cerrar modales
- **Ctrl+Click**: Selección múltiple

### Flujo Recomendado
1. Revisar dashboard al iniciar
2. Usar filtros para enfocarse
3. Procesar pedidos pendientes primero
4. Usar bulk actions para eficiencia
5. Exportar reportes al final del día

### Mejores Prácticas
- Mantener información de clientes actualizada
- Asignar repartidores rápidamente
- Usar referencias detalladas en direcciones
- Confirmar entregas inmediatamente
- Revisar métricas regularmente

## 🆘 Solución de Problemas

### Problemas Comunes

**No se cargan los datos**
- Verificar conexión a internet
- Refrescar la página (F5)
- Contactar soporte si persiste

**Filtros no funcionan**
- Limpiar filtros y volver a aplicar
- Verificar formato de fechas
- Revisar permisos de usuario

**Exportación falla**
- Reducir rango de fechas
- Intentar formato diferente
- Verificar espacio en disco

**Notificaciones no aparecen**
- Verificar configuración del navegador
- Permitir notificaciones del sitio
- Actualizar navegador

### Contacto de Soporte
- **Email**: soporte@restaurante.com
- **Teléfono**: +51 XXX-XXXX
- **Chat**: Disponible en la plataforma
- **Horario**: Lunes a Domingo 8:00 - 22:00

## 📈 Próximas Mejoras

### En Desarrollo
- Tracking GPS en tiempo real
- Notificaciones push móviles
- Integración con WhatsApp
- Reportes avanzados con gráficos
- API para aplicaciones móviles

### Sugerencias
¿Tienes ideas para mejorar la plataforma? Envíanos tus sugerencias a mejoras@restaurante.com

---

**¡Gracias por usar nuestra plataforma mejorada de delivery!** 🚀

*Última actualización: Enero 2025*