# CONTEXTO SISTEMA POS - Sistema de Gestión de Restaurante

## Descripción General

El sistema POS (Point of Sale) es una funcionalidad dual que se implementa tanto en Filament (`/admin/pos-interface`) como en una interfaz independiente (`/pos`). Ambas permiten la gestión completa de ventas, facturación y operaciones del restaurante.

## Rutas y Acceso

### Ruta Principal Filament
- **URL**: `http://restaurante.test/admin/pos-interface`
- **Nombre**: `pos-interface`
- **Controlador**: `App\Filament\Pages\PosInterface`
- **Vista**: `filament.pages.pos-interface`

### Ruta POS Independiente  
- **URL**: `http://restaurante.test/pos`
- **Nombre**: `pos.index`
- **Controlador**: `App\Http\Controllers\PosController`
- **Vista**: `pos.index`

## Arquitectura del Sistema

### 1. Estructura de Archivos Principales

#### **Controladores**
- **PosController.php** (`app/Http/Controllers/PosController.php`)
  - **Ubicación**: Líneas 1-1352
  - **Responsabilidades**: Gestión de rutas POS, creación de órdenes, facturación, clientes, comandas
  - **Métodos clave**: 
    - `index()`: Vista principal POS
    - `createOrderFromJS()`: Creación de órdenes desde JavaScript  
    - `generateInvoice()`: Generación de comprobantes
    - `findCustomer()`: Búsqueda de clientes

#### **Páginas Filament**
- **PosInterface.php** (`app/Filament/Pages/PosInterface.php`)
  - **Ubicación**: Líneas 1-100+ (archivo muy extenso - 40781 tokens)
  - **Responsabilidades**: Interfaz POS dentro del panel de Filament
  - **Propiedades clave**:
    - `$selectedTableId`: Mesa seleccionada
    - `$cartItems`: Productos en carrito
    - `$total`, `$subtotal`, `$tax`: Cálculos monetarios
    - `$numberOfGuests`: Número de comensales

#### **Componentes Livewire**
- **PointOfSale.php** (`app/Livewire/Pos/PointOfSale.php`)
  - **Ubicación**: Líneas 1-100+ (archivo extenso)
  - **Responsabilidades**: Lógica interactiva del POS
  - **Propiedades clave**:
    - `$table`: Mesa actual
    - `$cart`: Carrito de compras
    - `$categories`: Categorías de productos
    - `$products`: Productos disponibles

### 2. Vistas y Interfaz

#### **Vista Principal Filament**
- **Archivo**: `resources/views/filament/pages/pos-interface.blade.php`
- **Características**: 
  - Sistema de diseño responsivo con CSS Grid
  - Paleta de colores profesional inspirada en Square POS
  - Variables CSS personalizadas para consistencia visual
  - Layout de 3 columnas: sidebar categorías, productos, carrito

#### **Vista Livewire**
- **Archivo**: `resources/views/livewire/pos/point-of-sale.blade.php`
- **Características**:
  - Integración con Leaflet para mapas
  - SweetAlert2 para notificaciones
  - CSS optimizado para POS con sistema de colores profesional

#### **Vista POS Independiente**
- **Archivo**: `resources/views/pos/index.blade.php`
- **Características**: Wrapper simple que carga el componente Livewire

### 3. Modelos de Datos

#### **Order.php** (`app/Models/Order.php`)
- **Estados disponibles**:
  - `STATUS_OPEN = 'open'`
  - `STATUS_IN_PREPARATION = 'in_preparation'`  
  - `STATUS_READY = 'ready'`
  - `STATUS_DELIVERED = 'delivered'`
  - `STATUS_COMPLETED = 'completed'`
  - `STATUS_CANCELLED = 'cancelled'`

- **Relaciones clave**:
  - `table()`: Mesa asociada
  - `customer()`: Cliente
  - `orderDetails()`: Detalles de la orden
  - `parent()/children()`: Órdenes divididas

#### **Product.php** (`app/Models/Product.php`)
- **Tipos de producto**:
  - `TYPE_INGREDIENT = 'ingredient'`
  - `TYPE_SALE_ITEM = 'sale_item'`
  - `TYPE_BOTH = 'both'`

- **Accessors importantes**:
  - `getPriceAttribute()`: Redirige a `sale_price`
  - `getImageAttribute()`: URL completa de imagen

#### **Otros Modelos Relacionados**:
- **ProductCategory**: Categorías de productos
- **Table**: Mesas del restaurante  
- **Customer**: Clientes
- **Invoice**: Facturación
- **CashRegister**: Caja registradora
- **DeliveryOrder**: Órdenes delivery

## Flujo de Funcionamiento

### 1. Acceso y Autenticación

#### **Middleware de Acceso**
- **Archivo**: `app/Http/Middleware/CheckPosAccess.php`
- **Lógica**:
  - Verifica autenticación del usuario
  - Permite acceso a roles: `super_admin`, `admin`, `cashier`, `waiter`
  - Para otros roles verifica permiso `access_pos`

#### **Control de Acceso en PosInterface**
```php
public static function canAccess(): bool
{
    // Verificar si es waiter específicamente
    if ($user->hasRole('waiter')) {
        return true;
    }
    // Super admin, admin, cashier pueden acceder siempre
    if ($user->hasRole(['super_admin', 'admin', 'cashier'])) {
        return true;
    }
    // Para otros roles, verificar permisos
    return $user->can('access_pos');
}
```

### 2. Flujo de Venta

#### **Proceso Estándar**:
1. **Selección de mesa/servicio** (dine_in, takeout, delivery)
2. **Selección de productos** por categorías
3. **Gestión del carrito** (agregar, editar cantidades, eliminar)
4. **Creación de orden** 
5. **Generación de comanda** (para cocina)
6. **Proceso de facturación** (boleta, factura, nota de venta)
7. **Completado de orden** y liberación de mesa

#### **Tipos de Servicio Soportados**:
- **dine_in**: Servicio en mesa
- **takeout**: Para llevar  
- **delivery**: Entrega a domicilio

### 3. Sistema de Facturación

#### **Tipos de Comprobantes**:
- **sales_note**: Nota de venta (no electrónica)
- **receipt**: Boleta electrónica (SUNAT)
- **invoice**: Factura electrónica (SUNAT)

#### **Integración SUNAT**:
- Servicio: `App\Services\SunatService`
- Estados: `PENDIENTE`, `ENVIADO`, `ACEPTADO`, `RECHAZADO`
- Generación automática de códigos QR
- Envío automático a SUNAT para boletas y facturas

### 4. Gestión de Pagos

#### **Métodos de Pago Soportados**:
- `cash`: Efectivo
- `card`: Tarjeta
- `transfer`: Transferencia bancaria
- `yape`: Yape
- `plin`: Plin
- `multiple`: Pago dividido (múltiples métodos)

## Configuración y Dependencias

### 1. Middleware Registrado
```php
// En routes/web.php
Route::middleware(['auth', 'pos.access'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    // ... más rutas POS
});
```

### 2. Dependencias Frontend
- **Leaflet**: Para mapas en delivery (`leaflet@1.9.4`)
- **SweetAlert2**: Para notificaciones (`sweetalert2@11`) 
- **TailwindCSS**: Sistema de estilos
- **Alpine.js**: Interactividad (incluido con Livewire)

### 3. Variables CSS Personalizadas

#### **Paleta de Colores**:
```css
:root {
    --pos-primary: #6366f1;        /* Indigo vibrante */
    --pos-secondary: #8b5cf6;      /* Púrpura elegante */
    --pos-success: #10b981;        /* Verde esmeralda */
    --pos-warning: #f59e0b;        /* Ámbar cálido */
    --pos-danger: #ef4444;         /* Rojo coral */
}
```

#### **Layout Responsivo**:
```css
.pos-main-container {
    display: grid;
    grid-template-columns: var(--pos-sidebar-width) 1fr var(--pos-cart-width);
    grid-template-areas: "sidebar products cart";
}
```

## Rutas Completas del Sistema

### **Rutas POS Principales** (con middleware `pos.access`):
```php
/pos                                    # Interfaz principal
/pos/table/{table}                      # POS por mesa específica
/pos/command-pdf/{order}                # Generar comanda PDF
/pos/prebill-pdf/{order}                # Generar pre-cuenta PDF
/pos/create-order                       # Crear orden (AJAX)
/pos/unified/{order}                    # Formulario unificado pago/facturación
/pos/invoices                           # Lista de comprobantes
/pos/customers/find                     # Buscar cliente
/pos/customers/store                    # Crear cliente
```

### **Rutas de Facturación**:
```php
/pos/invoice/form/{order}               # Formulario de facturación
/pos/invoice/generate/{order}           # Generar comprobante
/pos/invoice/void/{invoice}             # Anular comprobante
```

### **Rutas de Vista Térmica**:
```php
/thermal-preview/command/{order}        # Vista previa comanda térmica
/thermal-preview/pre-bill/{order}       # Vista previa pre-cuenta térmica
```

## Estados y Transiciones

### **Estados de Orden**:
```
open → in_preparation → ready → delivered → completed
  ↓
cancelled (en cualquier momento antes de completed)
```

### **Estados de Mesa** (relacionados):
- `available`: Disponible
- `occupied`: Ocupada
- `reserved`: Reservada
- `maintenance`: Mantenimiento

### **Estados SUNAT**:
- `PENDIENTE`: Esperando envío
- `ENVIADO`: Enviado a SUNAT
- `ACEPTADO`: Aceptado por SUNAT
- `RECHAZADO`: Rechazado por SUNAT

## Características Técnicas Avanzadas

### 1. **Sistema de Caché y Optimizaciones**
- Relaciones eager loading en modelos (`protected $with`)
- Caché de productos por categoría
- Optimización de consultas N+1

### 2. **Sistema de Eventos**
- `DeliveryStatusChanged`: Cambios en estado delivery
- `PaymentRegistered/PaymentVoided`: Eventos de pagos

### 3. **Traits Utilizados**
- `CalculatesIgv`: Cálculos de IGV consistentes
- `SoftDeletes`: Eliminación suave en productos

### 4. **Validaciones y Seguridad**
- Form Requests para validación
- Políticas de autorización (Policies)
- Sanitización de datos de entrada
- Verificación de caja registradora abierta

## Integración con Otros Módulos

### **Gestión de Mesas**:
- Cambio automático de estado de mesa
- Liberación de mesa al completar orden
- Integración con mapa de mesas

### **Sistema de Delivery**:
- Creación automática de `DeliveryOrder`
- Tracking GPS de repartidores
- Notificaciones de estado

### **Caja Registradora**:
- Validación de caja abierta antes de crear órdenes
- Registro automático de movimientos
- Integración con sistema de cierre a ciegas

### **Inventario**:
- Control de stock de productos
- Verificación de disponibilidad
- Descontar stock automáticamente (si configurado)

## Logs y Debugging

### **Archivos de Log Importantes**:
- `storage/logs/laravel.log`: Log general
- `storage/logs/delivery_*.log`: Logs específicos de delivery
- Logs SUNAT en directorio configurado

### **Puntos de Log Clave** (confirmados en código):
- Creación de órdenes: `PosController::createOrderFromCartItems()`
- Facturación: `PosController::generateInvoice()`
- Liberación de mesas: Líneas 980-986, 1090-1096 en PosController
- Completado de órdenes: Líneas 993-998, 1104-1109 en PosController

## Consideraciones de Mantenimiento

### **Puntos Críticos a Monitorear**:
1. **Sincronización de mesas**: Estado entre diferentes sesiones
2. **Integridad de órdenes**: Validar totales calculados vs almacenados
3. **Estado SUNAT**: Monitorear comprobantes pendientes
4. **Cajas registradoras**: Verificar cierres diarios
5. **Stock de productos**: Consistencia con ventas

### **Actualizaciones Futuras**:
- Integración con impresoras térmicas (ya preparado)
- Mejoras en UI/UX basadas en feedback de usuarios
- Optimizaciones adicionales de performance
- Integración con sistemas de fidelización

---

**Fecha de análisis**: 19 de agosto de 2025  
**Versión del sistema**: Laravel 12 + Filament 3 + Livewire 3  
**Estado**: Sistema completamente funcional en producción