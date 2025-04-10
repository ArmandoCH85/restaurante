# Diccionario de Datos - Sistema de Gestión de Restaurantes

Este documento proporciona una descripción detallada de todas las tablas y campos en el sistema de gestión de restaurantes, explicando su propósito y relaciones.

## Índice de Tablas
1. [Empleados (employees)](#1-empleados-employees)
2. [Clientes (customers)](#2-clientes-customers)
3. [Proveedores (suppliers)](#3-proveedores-suppliers)
4. [Categorías de Productos (product_categories)](#4-categorías-de-productos-product_categories)
5. [Productos (products)](#5-productos-products)
6. [Mesas (tables)](#6-mesas-tables)
7. [Compras (purchases)](#7-compras-purchases)
8. [Detalles de Compra (purchase_details)](#8-detalles-de-compra-purchase_details)
9. [Pedidos (orders)](#9-pedidos-orders)
10. [Detalles de Pedido (order_details)](#10-detalles-de-pedido-order_details)
11. [Recetas (recipes)](#11-recetas-recipes)
12. [Detalles de Receta (recipe_details)](#12-detalles-de-receta-recipe_details)
13. [Movimientos de Inventario (inventory_movements)](#13-movimientos-de-inventario-inventory_movements)
14. [Comprobantes (invoices)](#14-comprobantes-invoices)
15. [Detalles de Comprobante (invoice_details)](#15-detalles-de-comprobante-invoice_details)
16. [Reservas (reservations)](#16-reservas-reservations)
17. [Pagos (payments)](#17-pagos-payments)
18. [Pedidos de Entrega (delivery_orders)](#18-pedidos-de-entrega-delivery_orders)
19. [Cajas (cash_registers)](#19-cajas-cash_registers)
20. [Movimientos de Caja (cash_movements)](#20-movimientos-de-caja-cash_movements)

---

## 1. Empleados (employees)

Esta tabla almacena la información de los empleados del restaurante, como meseros, cocineros, cajeros, etc.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del empleado | Clave primaria, Auto-incrementable |
| Nombre | first_name | String | Nombre del empleado | Obligatorio |
| Apellido | last_name | String | Apellido del empleado | Obligatorio |
| Número de documento | document_number | String(15) | Número de identificación (DNI, CE, etc.) | Único, Obligatorio |
| Teléfono | phone | String(20) | Número de teléfono de contacto | Opcional |
| Dirección | address | String | Dirección de residencia | Opcional |
| Cargo | position | String(50) | Puesto o cargo del empleado | Obligatorio |
| Fecha de contratación | hire_date | Date | Fecha en que fue contratado | Obligatorio |
| Salario base | base_salary | Decimal(10,2) | Salario base del empleado | Por defecto 0 |
| ID de usuario | user_id | BigInteger | Vínculo con la cuenta de usuario del sistema | Clave foránea a users, Opcional |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |
| Fecha de eliminación | deleted_at | Timestamp | Fecha y hora de eliminación lógica | Opcional, Para borrado lógico |

## 2. Clientes (customers)

Esta tabla almacena la información de los clientes para facturación, reservas y delivery.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del cliente | Clave primaria, Auto-incrementable |
| Tipo de documento | document_type | String(10) | Tipo de documento (DNI, RUC, etc.) | Obligatorio |
| Número de documento | document_number | String(15) | Número de identificación | Único, Obligatorio |
| Nombre/Razón social | name | String | Nombre completo o razón social | Obligatorio, Indexado |
| Teléfono | phone | String(20) | Número de teléfono de contacto | Opcional |
| Correo electrónico | email | String | Dirección de correo electrónico | Opcional |
| Dirección | address | String | Dirección para entregas o facturación | Opcional |
| Referencias de dirección | address_references | String | Información adicional para ubicar la dirección | Opcional |
| Validado en SUNAT | tax_validated | Boolean | Indica si los datos han sido validados con SUNAT | Por defecto False |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |
| Fecha de eliminación | deleted_at | Timestamp | Fecha y hora de eliminación lógica | Opcional, Para borrado lógico |

## 3. Proveedores (suppliers)

Esta tabla almacena la información de los proveedores de insumos y productos para el restaurante.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del proveedor | Clave primaria, Auto-incrementable |
| Razón social | business_name | String | Nombre de la empresa proveedora | Obligatorio |
| RUC/Identificación fiscal | tax_id | String(15) | Número de identificación tributaria | Único, Obligatorio |
| Dirección | address | String | Dirección del proveedor | Opcional |
| Teléfono | phone | String(20) | Número de teléfono de contacto | Opcional |
| Correo electrónico | email | String | Dirección de correo electrónico | Opcional |
| Nombre de contacto | contact_name | String | Nombre de la persona de contacto | Opcional |
| Teléfono de contacto | contact_phone | String | Teléfono directo de la persona de contacto | Opcional |
| Activo | active | Boolean | Indica si el proveedor está activo | Por defecto True |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |
| Fecha de eliminación | deleted_at | Timestamp | Fecha y hora de eliminación lógica | Opcional, Para borrado lógico |

## 4. Categorías de Productos (product_categories)

Esta tabla almacena las categorías para clasificar los productos del restaurante (bebidas, entradas, platos principales, etc.).

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único de la categoría | Clave primaria, Auto-incrementable |
| Nombre | name | String(50) | Nombre de la categoría | Único, Obligatorio |
| Descripción | description | String | Descripción de la categoría | Opcional |
| ID de categoría padre | parent_category_id | BigInteger | Referencia a una categoría superior | Clave foránea a product_categories, Opcional |
| Visible en menú | visible_in_menu | Boolean | Indica si se muestra en el menú | Por defecto True |
| Orden de visualización | display_order | Integer | Orden para mostrar en listados | Por defecto 0 |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 5. Productos (products)

Esta tabla almacena todos los productos que maneja el restaurante, tanto los que se venden como los insumos para preparación.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del producto | Clave primaria, Auto-incrementable |
| Código | code | String(20) | Código único para identificar el producto | Único, Obligatorio |
| Nombre | name | String | Nombre del producto | Obligatorio |
| Descripción | description | Text | Descripción detallada del producto | Opcional |
| Precio de venta | sale_price | Decimal(10,2) | Precio al que se vende al cliente | Obligatorio |
| Costo actual | current_cost | Decimal(10,2) | Costo actual de adquisición o producción | Por defecto 0 |
| Tipo de producto | product_type | Enum | Categoriza si es 'ingredient' (insumo), 'sale_item' (venta) o 'both' (ambos) | Obligatorio |
| ID de categoría | category_id | BigInteger | Categoría a la que pertenece | Clave foránea a product_categories, Obligatorio |
| Activo | active | Boolean | Indica si el producto está activo | Por defecto True |
| Tiene receta | has_recipe | Boolean | Indica si el producto tiene una receta asociada | Por defecto False |
| Ruta de imagen | image_path | String | Ruta a la imagen del producto | Opcional |
| Disponible | available | Boolean | Indica si el producto está disponible para venta | Por defecto True |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |
| Fecha de eliminación | deleted_at | Timestamp | Fecha y hora de eliminación lógica | Opcional, Para borrado lógico |

## 6. Mesas (tables)

Esta tabla almacena la información de las mesas del restaurante para gestionar reservas y órdenes.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único de la mesa | Clave primaria, Auto-incrementable |
| Número | number | String(10) | Número o código de la mesa | Obligatorio |
| Capacidad | capacity | Integer | Cantidad de personas que pueden sentarse | Obligatorio |
| Ubicación | location | String(50) | Ubicación dentro del local (salón principal, terraza, etc.) | Opcional |
| Estado | status | Enum | Estado de la mesa: 'available' (disponible), 'occupied' (ocupada), 'reserved' (reservada), 'maintenance' (en mantenimiento) | Por defecto 'available' |
| Código QR | qr_code | String | Código QR para identificar la mesa | Opcional |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 7. Compras (purchases)

Esta tabla registra las compras de insumos y productos a proveedores.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único de la compra | Clave primaria, Auto-incrementable |
| ID de proveedor | supplier_id | BigInteger | Proveedor al que se realizó la compra | Clave foránea a suppliers, Obligatorio |
| Fecha de compra | purchase_date | Date | Fecha en que se realizó la compra | Obligatorio |
| Número de documento | document_number | String(50) | Número de factura o documento del proveedor | Obligatorio |
| Tipo de documento | document_type | String(20) | Tipo de documento (factura, boleta, etc.) | Obligatorio |
| Subtotal | subtotal | Decimal(12,2) | Monto subtotal de la compra antes de impuestos | Obligatorio |
| Impuesto | tax | Decimal(12,2) | Monto del impuesto (IGV/IVA) | Obligatorio |
| Total | total | Decimal(12,2) | Monto total de la compra | Obligatorio |
| Estado | status | Enum | Estado de la compra: 'pending' (pendiente), 'completed' (completada), 'cancelled' (cancelada) | Por defecto 'completed' |
| Creado por | created_by | BigInteger | Usuario que registró la compra | Clave foránea a users, Obligatorio |
| Notas | notes | Text | Notas o comentarios adicionales | Opcional |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 8. Detalles de Compra (purchase_details)

Esta tabla almacena el detalle de los productos adquiridos en cada compra.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del detalle | Clave primaria, Auto-incrementable |
| ID de compra | purchase_id | BigInteger | Compra a la que pertenece este detalle | Clave foránea a purchases con cascade, Obligatorio |
| ID de producto | product_id | BigInteger | Producto que se compró | Clave foránea a products, Obligatorio |
| Cantidad | quantity | Decimal(10,3) | Cantidad comprada | Obligatorio |
| Costo unitario | unit_cost | Decimal(10,2) | Precio unitario de compra | Obligatorio |
| Subtotal | subtotal | Decimal(12,2) | Subtotal de este ítem (cantidad × costo unitario) | Obligatorio |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 9. Pedidos (orders)

Esta tabla registra los pedidos de los clientes en el restaurante.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del pedido | Clave primaria, Auto-incrementable |
| Tipo de servicio | service_type | Enum | Tipo de pedido: 'dine_in' (en mesa), 'takeout' (para llevar), 'delivery' (a domicilio), 'drive_thru' (auto servicio) | Obligatorio |
| ID de mesa | table_id | BigInteger | Mesa asignada al pedido (si aplica) | Clave foránea a tables, Opcional |
| ID de cliente | customer_id | BigInteger | Cliente que realizó el pedido (si está identificado) | Clave foránea a customers, Opcional |
| ID de empleado | employee_id | BigInteger | Empleado que atendió el pedido | Clave foránea a employees, Obligatorio |
| Fecha y hora del pedido | order_datetime | DateTime | Fecha y hora en que se registró el pedido | Obligatorio |
| Estado | status | Enum | Estado del pedido: 'open' (abierto), 'in_preparation' (en preparación), 'ready' (listo), 'completed' (completado), 'cancelled' (cancelado) | Por defecto 'open' |
| Subtotal | subtotal | Decimal(12,2) | Monto subtotal antes de impuestos | Por defecto 0 |
| Impuesto | tax | Decimal(12,2) | Monto del impuesto (IGV/IVA) | Por defecto 0 |
| Descuento | discount | Decimal(12,2) | Monto de descuento aplicado | Por defecto 0 |
| Total | total | Decimal(12,2) | Monto total del pedido | Por defecto 0 |
| Notas | notes | Text | Notas o instrucciones especiales | Opcional |
| Facturado | billed | Boolean | Indica si el pedido ha sido facturado | Por defecto False |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 10. Detalles de Pedido (order_details)

Esta tabla almacena el detalle de los productos solicitados en cada pedido.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del detalle | Clave primaria, Auto-incrementable |
| ID de pedido | order_id | BigInteger | Pedido al que pertenece este detalle | Clave foránea a orders con cascade, Obligatorio |
| ID de producto | product_id | BigInteger | Producto que se pidió | Clave foránea a products, Obligatorio |
| Cantidad | quantity | Integer | Cantidad solicitada | Obligatorio |
| Precio unitario | unit_price | Decimal(10,2) | Precio unitario de venta | Obligatorio |
| Subtotal | subtotal | Decimal(12,2) | Subtotal de este ítem (cantidad × precio unitario) | Obligatorio |
| Notas | notes | Text | Instrucciones especiales para este ítem | Opcional |
| Estado | status | Enum | Estado del ítem: 'pending' (pendiente), 'in_preparation' (en preparación), 'ready' (listo), 'delivered' (entregado), 'cancelled' (cancelado) | Por defecto 'pending' |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 11. Recetas (recipes)

Esta tabla almacena las recetas de preparación para los productos elaborados en el restaurante.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único de la receta | Clave primaria, Auto-incrementable |
| ID de producto | product_id | BigInteger | Producto al que pertenece esta receta | Clave foránea a products, Obligatorio |
| Instrucciones de preparación | preparation_instructions | Text | Descripción del proceso de preparación | Opcional |
| Costo esperado | expected_cost | Decimal(10,2) | Costo estimado de preparación | Por defecto 0 |
| Tiempo de preparación | preparation_time | Decimal(8,2) | Tiempo estimado de preparación en minutos | Obligatorio |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 12. Detalles de Receta (recipe_details)

Esta tabla almacena los ingredientes necesarios para cada receta.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del detalle | Clave primaria, Auto-incrementable |
| ID de receta | recipe_id | BigInteger | Receta a la que pertenece este ingrediente | Clave foránea a recipes con cascade, Obligatorio |
| ID de ingrediente | ingredient_id | BigInteger | Producto usado como ingrediente | Clave foránea a products, Obligatorio |
| Cantidad | quantity | Decimal(10,3) | Cantidad requerida | Obligatorio |
| Unidad de medida | unit_of_measure | String(20) | Unidad de medida (kg, g, l, ml, etc.) | Obligatorio |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 13. Movimientos de Inventario (inventory_movements)

Esta tabla registra todos los movimientos de entrada y salida de productos del inventario.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del movimiento | Clave primaria, Auto-incrementable |
| ID de producto | product_id | BigInteger | Producto que se movió | Clave foránea a products, Obligatorio |
| Tipo de movimiento | movement_type | Enum | Tipo: 'purchase' (compra), 'sale' (venta), 'adjustment' (ajuste), 'waste' (merma) | Obligatorio |
| Cantidad | quantity | Decimal(10,3) | Cantidad movida (positiva para entradas, negativa para salidas) | Obligatorio |
| Costo unitario | unit_cost | Decimal(10,2) | Costo unitario del producto en este movimiento | Opcional |
| Documento de referencia | reference_document | String | Documento que originó el movimiento (factura, boleta, etc.) | Opcional |
| ID de referencia | reference_id | BigInteger | ID del registro que originó el movimiento | Opcional |
| Tipo de referencia | reference_type | String | Tipo de la entidad que originó el movimiento (compra, pedido, etc.) | Opcional |
| Creado por | created_by | BigInteger | Usuario que registró el movimiento | Clave foránea a users, Obligatorio |
| Notas | notes | Text | Notas o comentarios adicionales | Opcional |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 14. Comprobantes (invoices)

Esta tabla almacena los comprobantes fiscales emitidos (boletas, facturas, notas de crédito y débito).

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del comprobante | Clave primaria, Auto-incrementable |
| Tipo de comprobante | invoice_type | Enum | Tipo: 'receipt' (boleta), 'invoice' (factura), 'credit_note' (nota de crédito), 'debit_note' (nota de débito) | Obligatorio |
| Serie | series | String(10) | Serie del comprobante (B001, F001, etc.) | Obligatorio |
| Número | number | String(10) | Número correlativo del comprobante | Obligatorio |
| Fecha de emisión | issue_date | Date | Fecha de emisión del comprobante | Obligatorio |
| ID de cliente | customer_id | BigInteger | Cliente al que se emitió el comprobante | Clave foránea a customers, Obligatorio |
| Monto gravado | taxable_amount | Decimal(12,2) | Monto afecto a impuestos | Obligatorio |
| Impuesto | tax | Decimal(12,2) | Monto del impuesto (IGV/IVA) | Obligatorio |
| Total | total | Decimal(12,2) | Monto total del comprobante | Obligatorio |
| Estado SUNAT | tax_authority_status | Enum | Estado en SUNAT: 'pending' (pendiente), 'accepted' (aceptado), 'rejected' (rechazado) | Por defecto 'pending' |
| Hash | hash | String(100) | Hash de verificación del CPE | Opcional |
| Código QR | qr_code | String | Código QR para verificación | Opcional |
| ID de pedido | order_id | BigInteger | Pedido asociado al comprobante | Clave foránea a orders, Opcional |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 15. Detalles de Comprobante (invoice_details)

Esta tabla almacena el detalle de los productos incluidos en cada comprobante fiscal.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del detalle | Clave primaria, Auto-incrementable |
| ID de comprobante | invoice_id | BigInteger | Comprobante al que pertenece este detalle | Clave foránea a invoices con cascade, Obligatorio |
| ID de producto | product_id | BigInteger | Producto que se facturó | Clave foránea a products, Obligatorio |
| Descripción | description | String | Descripción del producto como aparece en el comprobante | Obligatorio |
| Cantidad | quantity | Decimal(10,3) | Cantidad facturada | Obligatorio |
| Precio unitario | unit_price | Decimal(10,2) | Precio unitario de venta | Obligatorio |
| Subtotal | subtotal | Decimal(12,2) | Subtotal de este ítem (cantidad × precio unitario) | Obligatorio |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 16. Reservas (reservations)

Esta tabla gestiona las reservas de mesas realizadas por los clientes.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único de la reserva | Clave primaria, Auto-incrementable |
| ID de cliente | customer_id | BigInteger | Cliente que realizó la reserva | Clave foránea a customers, Obligatorio |
| ID de mesa | table_id | BigInteger | Mesa reservada | Clave foránea a tables, Opcional |
| Fecha de reserva | reservation_date | Date | Fecha para la cual se realizó la reserva | Obligatorio |
| Hora de reserva | reservation_time | Time | Hora para la cual se realizó la reserva | Obligatorio |
| Cantidad de personas | guests_count | Integer | Número de personas que asistirán | Obligatorio |
| Estado | status | Enum | Estado: 'pending' (pendiente), 'confirmed' (confirmada), 'cancelled' (cancelada), 'completed' (completada) | Por defecto 'pending' |
| Solicitudes especiales | special_requests | Text | Peticiones o necesidades especiales | Opcional |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 17. Pagos (payments)

Esta tabla registra los pagos realizados por los clientes para los pedidos.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del pago | Clave primaria, Auto-incrementable |
| ID de pedido | order_id | BigInteger | Pedido que se está pagando | Clave foránea a orders, Obligatorio |
| Método de pago | payment_method | Enum | Método: 'cash' (efectivo), 'credit_card' (tarjeta de crédito), 'debit_card' (tarjeta de débito), 'bank_transfer' (transferencia), 'digital_wallet' (billetera digital) | Obligatorio |
| Monto | amount | Decimal(12,2) | Monto pagado | Obligatorio |
| Número de referencia | reference_number | String | Número de referencia del pago (voucher, transferencia, etc.) | Opcional |
| Fecha y hora del pago | payment_datetime | DateTime | Fecha y hora en que se realizó el pago | Obligatorio |
| Recibido por | received_by | BigInteger | Usuario que registró el pago | Clave foránea a users, Obligatorio |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 18. Pedidos de Entrega (delivery_orders)

Esta tabla gestiona la información específica de los pedidos a domicilio.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del pedido de entrega | Clave primaria, Auto-incrementable |
| ID de pedido | order_id | BigInteger | Pedido asociado a esta entrega | Clave foránea a orders con cascade, Obligatorio |
| Dirección de entrega | delivery_address | String | Dirección donde se entregará el pedido | Obligatorio |
| Referencias de entrega | delivery_references | String | Referencias adicionales para ubicar la dirección | Opcional |
| ID de repartidor | delivery_person_id | BigInteger | Empleado asignado para la entrega | Clave foránea a employees, Opcional |
| Estado | status | Enum | Estado de la entrega: 'pending' (pendiente), 'assigned' (asignado), 'in_transit' (en camino), 'delivered' (entregado), 'cancelled' (cancelado) | Por defecto 'pending' |
| Hora estimada de entrega | estimated_delivery_time | DateTime | Hora estimada en que se entregará el pedido | Opcional |
| Hora real de entrega | actual_delivery_time | DateTime | Hora real en que se entregó el pedido | Opcional |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 19. Cajas (cash_registers)

Esta tabla gestiona las aperturas y cierres de caja en el restaurante.

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único de la caja | Clave primaria, Auto-incrementable |
| Fecha y hora de apertura | opening_datetime | DateTime | Fecha y hora en que se abrió la caja | Obligatorio |
| Fecha y hora de cierre | closing_datetime | DateTime | Fecha y hora en que se cerró la caja | Opcional |
| Monto de apertura | opening_amount | Decimal(12,2) | Monto con el que inició la caja | Obligatorio |
| Monto esperado | expected_amount | Decimal(12,2) | Monto que debería haber según el sistema | Opcional |
| Monto real | actual_amount | Decimal(12,2) | Monto real contado al cierre | Opcional |
| Diferencia | difference | Decimal(12,2) | Diferencia entre lo esperado y lo real | Opcional |
| Abierto por | opened_by | BigInteger | Usuario que abrió la caja | Clave foránea a users, Obligatorio |
| Cerrado por | closed_by | BigInteger | Usuario que cerró la caja | Clave foránea a users, Opcional |
| Observaciones | observations | Text | Notas sobre el cierre o la apertura | Opcional |
| Está activa | is_active | Boolean | Indica si la caja está actualmente abierta | Por defecto True |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |

## 20. Movimientos de Caja (cash_movements)

Esta tabla registra los movimientos de efectivo en la caja (ingresos y egresos adicionales).

| Campo (Español) | Campo (Inglés) | Tipo de dato | Descripción | Restricciones |
|----------------|----------------|--------------|-------------|---------------|
| ID | id | BigInteger | Identificador único del movimiento | Clave primaria, Auto-incrementable |
| ID de caja | cash_register_id | BigInteger | Caja en la que se realizó el movimiento | Clave foránea a cash_registers con cascade, Obligatorio |
| Tipo de movimiento | movement_type | Enum | Tipo: 'income' (ingreso), 'expense' (egreso) | Obligatorio |
| Monto | amount | Decimal(12,2) | Cantidad del movimiento | Obligatorio |
| Motivo | reason | String | Motivo del movimiento | Obligatorio |
| Aprobado por | approved_by | BigInteger | Usuario que aprobó el movimiento | Clave foránea a users, Obligatorio |
| Fecha de creación | created_at | Timestamp | Fecha y hora de creación del registro | Automático |
| Fecha de actualización | updated_at | Timestamp | Fecha y hora de última actualización | Automático |
