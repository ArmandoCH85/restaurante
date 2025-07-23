# Sistema de Gestión de Restaurante

## Descripción del Proyecto
Sistema integral de gestión de restaurante desarrollado en Laravel 12 con Filament 3. Incluye POS completo, gestión de mesas, delivery, facturación electrónica SUNAT, inventario y reportes avanzados.

## Tecnologías Principales
- **Backend**: Laravel 12 + PHP 8.2
- **Frontend**: Filament 3 + Livewire + TailwindCSS
- **Base de Datos**: MySQL/PostgreSQL
- **Facturación**: Integración SUNAT con Greenter
- **Testing**: Pest PHP

## Estructura de Módulos

### Core del Sistema
- **POS (Punto de Venta)**: `app/Livewire/Pos/PointOfSale.php`
- **Gestión de Mesas**: `app/Livewire/TableMap/` - Sistema completo de mapas de mesas
- **Delivery**: `app/Livewire/Delivery/` - Gestión y tracking de pedidos delivery
- **Facturación SUNAT**: `app/Services/SunatService.php` - Integración completa con SUNAT

### Modelos de Datos Principales
```
app/Models/
├── User.php                 # Usuarios del sistema
├── Employee.php             # Empleados
├── Customer.php             # Clientes
├── Table.php               # Mesas del restaurante
├── Floor.php               # Pisos/plantas
├── Product.php             # Productos del menú
├── ProductCategory.php     # Categorías de productos
├── Order.php               # Pedidos
├── OrderDetail.php         # Detalles de pedidos
├── Invoice.php             # Facturas/comprobantes
├── Payment.php             # Pagos
├── DeliveryOrder.php       # Pedidos delivery
├── Reservation.php         # Reservaciones
├── CashRegister.php        # Caja registradora
├── Quotation.php           # Cotizaciones
├── Purchase.php            # Compras
├── Supplier.php            # Proveedores
├── Recipe.php              # Recetas
├── Ingredient.php          # Ingredientes
└── Warehouse.php           # Almacenes
```

### Controladores Principales
```
app/Http/Controllers/
├── PosController.php                    # Punto de venta
├── TableController.php                  # Gestión de mesas
├── InvoiceController.php                # Facturación
├── PaymentController.php                # Pagos
├── UnifiedPaymentController.php         # Pagos unificados
├── DeliveryOrderDetailsController.php   # Detalles delivery
└── CashRegisterPrintController.php      # Impresión caja
```

### Componentes Livewire
```
app/Livewire/
├── Pos/PointOfSale.php                 # Interfaz POS principal
├── TableMap/EnhancedTableMap.php       # Mapa de mesas mejorado
├── TableMap/TableMapView.php           # Vista del mapa
├── Delivery/DeliveryManager.php        # Gestión delivery
├── Delivery/DeliveryDriver.php         # Panel repartidor
└── EnhancedProfitProjection.php        # Proyección ganancias
```

## Comandos de Desarrollo

### Comandos Principales
```bash
# Iniciar servidor de desarrollo
composer run dev

# Ejecutar migraciones
php artisan migrate

# Ejecutar seeders
php artisan db:seed

# Limpiar cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Generar assets
npm run build
npm run dev
```

### Comandos Específicos del Sistema
```bash
# Configuración SUNAT
php artisan sunat:setup-directories
php artisan sunat:check-config

# Gestión de pedidos
php artisan orders:cleanup-pending

# Recálculos
php artisan orders:recalculate-totals
php artisan invoices:recalculate-totals

# Testing SUNAT
php artisan sunat:test
php artisan sunat:test-safe

# Resetear mesas
php artisan tables:reset-status
```

## Estructura de Base de Datos

### Tablas Principales
- `users` - Usuarios del sistema
- `employees` - Empleados
- `customers` - Clientes
- `tables` - Mesas del restaurante
- `floors` - Pisos/plantas
- `products` - Productos del menú
- `product_categories` - Categorías
- `orders` / `order_details` - Pedidos
- `invoices` / `invoice_details` - Facturación
- `payments` - Pagos
- `delivery_orders` - Delivery
- `reservations` - Reservaciones
- `cash_registers` - Caja registradora (incluye cierre a ciegas)
- `quotations` / `quotation_details` - Cotizaciones
- `purchases` / `purchase_details` - Compras
- `suppliers` - Proveedores
- `recipes` / `recipe_details` - Recetas
- `ingredients` / `ingredient_stock` - Inventario
- `warehouses` - Almacenes

## Rutas Principales

### Panel de Administración
- `/admin` - Panel Filament principal
- `/admin/mapa-mesas` - Mapa de mesas
- `/admin/reportes` - Reportes avanzados

### Sistema POS
- `/pos` - Interfaz principal POS
- `/pos/table/{id}` - POS por mesa específica

### Delivery
- `/delivery/manage` - Gestión delivery (admin)
- `/delivery/my-orders` - Panel repartidor

### Mesas
- `/tables` - Mapa de mesas público
- `/tables/maintenance` - Mantenimiento mesas

## Configuración Importante

### Variables de Entorno Clave
```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=restaurante
DB_USERNAME=
DB_PASSWORD=

# SUNAT (Facturación Electrónica)
SUNAT_RUC=
SUNAT_USUARIO_SOL=
SUNAT_CLAVE_SOL=
SUNAT_CERTIFICADO_PATH=
SUNAT_CERTIFICADO_PASSWORD=
SUNAT_PRODUCTION=false

# Email
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=
MAIL_USERNAME=
MAIL_PASSWORD=
```

## Testing

### Ejecutar Tests
```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos SUNAT
php artisan test --filter=Sunat

# Tests con coverage
php artisan test --coverage
```

## Deployment

### Preparar para Producción
```bash
# Optimizar autoloader
composer install --optimize-autoloader --no-dev

# Optimizar configuración
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generar assets
npm run build

# Ejecutar migraciones
php artisan migrate --force
```

## Funcionalidades Específicas

### Sistema de Caja Registradora
**Ubicación**: `app/Filament/Resources/CashRegisterResource.php`

#### Cierre a Ciegas
- **Implementación**: Líneas 235-240 en `CashRegisterResource.php`
- **Funcionalidad**: Los empleados regulares realizan conteo de efectivo sin ver los montos esperados
- **Supervisores**: Admin, super_admin y manager pueden ver las ventas reales
- **URL**: `/admin/operaciones-caja`

#### Funciones de Caja Disponibles:
1. **Apertura de caja** con monto inicial
2. **Conteo detallado** de billetes y monedas
3. **Cierre a ciegas** para empleados
4. **Aprobación/rechazo** de cierres
5. **Impresión** de reportes
6. **Reconciliación** automática

## Notas de Desarrollo

### Facturación SUNAT
- Configurar certificados en `storage/app/sunat/`
- Verificar configuración con `php artisan sunat:check-config`
- Testing en ambiente beta antes de producción

### Sistema POS
- Soporte para múltiples formas de pago
- Integración con impresoras térmicas
- Gestión de comandas y pre-cuentas

### Gestión de Mesas
- Mapas interactivos por piso
- Estados en tiempo real
- QR codes para pedidos directos

### Delivery
- Tracking GPS de repartidores
- Estados de entrega en tiempo real
- Notificaciones automáticas

## Troubleshooting

### Problemas Comunes
1. **Error SUNAT**: Verificar certificados y configuración
2. **Problemas POS**: Limpiar cache de navegador
3. **Mesas no actualizan**: Revisar Livewire y broadcasting
4. **PDFs no generan**: Verificar permisos storage y DomPDF

### Logs Importantes
- `storage/logs/laravel.log` - Log general
- `storage/logs/delivery_*.log` - Logs delivery específicos
- SUNAT logs en directorio configurado

## Contacto y Soporte

Para dudas sobre el código o implementación, revisar:
- Documentación Laravel: https://laravel.com/docs
- Documentación Filament: https://filamentphp.com/docs
- Documentación Greenter: https://greenter.dev/