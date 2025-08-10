# Revisión integral del proyecto (Markdown, Modelos, Vistas y JavaScript)

Este documento resume el estado actual de la documentación Markdown, los modelos Eloquent, las vistas Blade y los archivos JavaScript del proyecto, e incluye recomendaciones para mejorar calidad, consistencia y mantenibilidad.

## Alcance

- Documentación Markdown: inventario y observaciones.
- Modelos (app/Models): estructura esperada y mejoras.
- Vistas (resources/views): organización y pautas.
- JavaScript (resources/js y public/js): estructura y buenas prácticas.

## Resumen ejecutivo

- Documentación activa y variada (guías, specs, optimizaciones), con oportunidad de unificar índice y estilo.
- 30+ modelos Eloquent cubriendo POS, ventas, inventarios, mesas, delivery y facturación electrónica.
- Amplio set de vistas Blade, incluidas integraciones con Filament y Livewire; conviene estandarizar layouts, extracción de componentes y manejo de scripts.
- JavaScript repartido entre resources/js (Vite) y public/js (scripts publicados y personalizados); recomendable centralizar en Vite cuando sea posible.

---

## Inventario de archivos

### Markdown principales

- README.md
- RESPONSIVE_16_OPTIMIZATIONS.md
- TAILADMIN_README.md
- CLAUDE.md
- diccionario-datos.md
- contexto/contexto.md
- memorias/Funcionamiento.md
- memorias/falta.md
- docs/IGV_CALCULATION_FIX.md
- docs/PDF_PREVIEW_OPTIMIZATION_SUMMARY.md
- docs/SUNAT_CONFIGURATION.md
- docs/THERMAL_PRINTING_CONSISTENCY.md
- docs/THERMAL_PRINTING_OPTIMIZATION.md
- docs/THERMAL_PRINTING_PAPER_OPTIMIZATION.md
- database/seeders/README_PRODUCT_SYSTEM.md
- .augment/rules/agentfilament.md
- .augment/rules/laravel-developer.md
- .kiro/specs/delivery-ui-enhancement/CHANGELOG.md
- .kiro/specs/delivery-ui-enhancement/design.md
- .kiro/specs/delivery-ui-enhancement/pos-color-standards.md
- .kiro/specs/delivery-ui-enhancement/requirements.md
- .kiro/specs/delivery-ui-enhancement/table-map-icons-enhancement.md
- .kiro/specs/delivery-ui-enhancement/tasks.md
- .kiro/specs/delivery-ui-enhancement/testing-checklist.md
- .kiro/specs/delivery-ui-enhancement/user-guide.md

Observación: hay mezcla de español/inglés y docs muy útiles que merecen un índice central.

### Modelos Eloquent detectados (app/Models)

- AppSetting
- CashMovement
- CashRegister
- CompanyConfig
- Customer
- DeliveryOrder
- DocumentSeries
- ElectronicBillingConfig
- Employee
- Floor
- Ingredient
- IngredientStock
- InventoryMovement
- Invoice
- InvoiceDetail
- Order
- OrderDetail
- Payment
- Product
- ProductCategory
- Purchase
- PurchaseDetail
- Quotation
- QuotationDetail
- Recipe
- RecipeDetail
- Reservation
- Supplier
- Table
- User
- Warehouse

Notas: Existe `Enums/InvoiceStatusEnum.php` que puede usarse en casts/atributos para mayor seguridad de tipos.

### Vistas Blade (carpetas principales)

- resources/views/layouts: app.blade.php, pos.blade.php, tableview.blade.php
- resources/views/components: varios componentes reutilizables (imagen de producto, estados vacíos, scripts de caja, layouts)
- resources/views/pos: formularios e impresión (invoice, pre-bill, sales-note, history, modals)
- resources/views/pdf y print: plantillas PDF y tickets
- resources/views/reports: ventas, productos, caja, usuarios, servicios
- resources/views/tables: mantenimiento
- resources/views/livewire: table-map (múltiples vistas), delivery (manager, driver, tracking), POS, proyección de utilidades
- resources/views/filament: widgets, pages, tables/columns, resources/*/pages, modals, auth
- resources/views/vendor/filament y vendor/filamentloginscreen: overrides de plantillas de Filament y tema de login
- Otras: welcome.blade.php, thermal-preview.blade.php, tests visuales (image-test, test-images)

Nota: La organización es coherente con Laravel + Filament + Livewire.

### JavaScript

- resources/js
  - app.js
  - bootstrap.js
- public/js (publicados/terceros y personalizados)
  - table-shapes.js, pos-refresh.js, pos-modals.js
  - filament/* (notifications, forms, tables, widgets, support, echo, app)
  - ysfkaya/filament-phone-input/components/filament-phone-input.js

---

## Hallazgos y recomendaciones

### Documentación (Markdown)

- Crear un índice principal en docs/ (por ejemplo, `docs/INDEX.md`) enlazando todas las guías técnicas (SUNAT, IGV, thermal printing, responsive, etc.).
- Unificar idioma por documento o añadir una nota de idioma; usar títulos y estructura consistentes (H1 único, tabla de contenido opcional).
- Añadir secciones “Última actualización” y “Responsable” para trazabilidad.
- Mover notas operativas (`memorias/`) a una carpeta docs/operativo/ y enlazar desde README.

### Modelos

- Definir `$fillable`/`$guarded` en todos los modelos para evitar asignación masiva inadvertida.
- Añadir `$casts` tipados (incluyendo enums como `InvoiceStatusEnum`) y fechas (`datetime`/`immutable_datetime`).
- Documentar relaciones con PHPDoc (IDE-friendly) y considerar `->with()` por defecto en agregados frecuentes para evitar N+1.
- Crear scopes reutilizables (`scopePaid`, `scopePending`, `scopeBetweenDates`) en Payment, Invoice, Sales/Orders.
- Indexación a nivel DB: revisar migraciones para índices en claves foráneas y campos de búsqueda (ej. `orders.status`, `invoices.series/number`, `products.sku`).
- Soft deletes donde aplique (Products, Customers, etc.) y políticas de restauración.
- Factories/Seeders actualizados para pruebas (verificar `database/factories`).

### Vistas Blade

- Consolidar layouts y usar `@push('styles')`/`@push('scripts')` para mover scripts inline a assets versionados.
- Extraer componentes Blade para fragmentos repetidos (botones, tarjetas, tablas comunes, modales POS/delivery).
- En vistas PDF/print, aislar estilos en CSS dedicado para estabilidad de impresión; revisar tamaños de fuente y márgenes consistentes.
- Evitar lógica compleja en Blade; mover a ViewModels/Presenters o métodos de modelo (accessors) cuando aniden condicionales.
- Revisar overrides en `resources/views/vendor/filament/*` tras updates de Filament; documentar qué se sobreescribe y por qué.

### JavaScript

- Centralizar scripts personalizados (table-shapes, pos-*) en `resources/js` y compilarlos con Vite; publicar a `public/` solo compilados.
- Adoptar módulos ES, evitar variables globales; namespacing ligero si convive con scripts de terceros.
- Estándar de calidad: ESLint + Prettier (con reglas para Blade inline con `eslint-plugin-html` opcional).
- Para Livewire/Filament, usar eventos bien nombrados, throttling/debouncing en entradas y listeners con cleanup.
- Considerar extraer la lógica POS a módulos (payments, modals, refresh) y pruebas con Vitest/Jest donde sea crítico.

---

## Calidad y automatización sugerida

- Lint PHP: Laravel Pint o PHP-CS-Fixer; integrar en CI.
- Lint JS: ESLint + Prettier; estilos consistentes.
- Lint Blade: `blade-formatter` en pre-commit.
- Tests: ampliar Pest/Feature para flujos clave (venta, anulación, cierre de caja, delivery lifecycle).
- Build: asegurar pipeline Vite para producción; evitar commitear assets generados salvo necesidad específica.

## Tareas priorizadas (checklist)

- [ ] Crear `docs/INDEX.md` con enlaces a toda la documentación técnica y operativa.
- [ ] Revisar y completar `$fillable`/`$casts` en todos los modelos listados.
- [ ] Añadir scopes comunes (fechas/estado) y comentarios PHPDoc en relaciones.
- [ ] Extraer scripts inline de Blade a `resources/js` usando `@vite` y `@stack('scripts')`.
- [ ] Normalizar vistas PDF/print con CSS dedicado y pruebas de impresión.
- [ ] Configurar Pint, ESLint y Prettier; añadir hooks pre-commit.
- [ ] Auditar overrides de Filament y documentarlos.
- [ ] Reorganizar `public/js` (mantener solo compilados/vendor) y mover JS propio a Vite.

---

## Apéndice A: vistas detectadas (muestra)

- pos/: payment-history, payment-form, invoices-list, sales-note-print, void-*, unified-payment-form, pre-bill-pdf, invoice-form, index, command-pdf, cashier-modal
- pdf/: sales_note, receipt, prebill, invoice, comanda, cash_register_detail
- reports/: quotation, sales, profits, products, cash_register, service_types, sales_by_user
- livewire/table-map/: table-map-view-new, enhanced-table-map, delivery-only-view, selected-table-details, qr-table-map, modals, layout-view, grid-view, floor-selector, filters
- filament/: widgets (table-map-widget, table-grid-widget, report-export-widget), pages (pos-interface, reportes-page, reservation-calendar, table-map-filament-native), tables/columns (delivery-status-with-traffic-light), resources/*/pages/*, modals (print-comanda, pre-bill-content, delivery-order-details, comanda-content), auth/login-header
- vendor overrides: filament/components/{app,base}.blade.php, filamentloginscreen/themes/{base,theme1,theme2,theme3}
- generales: layouts/{app,pos,tableview}, dashboard/index, delivery/order-details, cash-registers/{print,detail-modal}, components/*, thermal-preview, welcome

## Apéndice B: JavaScript detectado

- resources/js: app.js, bootstrap.js
- public/js personalizados: table-shapes.js, pos-refresh.js, pos-modals.js
- public/js terceros/publicados: filament/*, ysfkaya/filament-phone-input/*

---

Última actualización: generada automáticamente a partir del árbol del repositorio. Para una auditoría más profunda por archivo (contenido, métricas, cobertura), ejecutar una revisión estática y tests dirigidos.
