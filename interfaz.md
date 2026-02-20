# Interfaz del Dashboard Ejecutivo (`/admin`)

## Identificacion del dashboard

- URL local: `http://restaurante.test/admin`
- Panel Filament: `admin` con ruta `admin` en `app/Providers/Filament/AdminPanelProvider.php:37`
- Login del panel: `CodeLogin` (PIN) en `app/Providers/Filament/AdminPanelProvider.php:38`
- Pagina dashboard del panel: `app/Filament/Pages/Dashboard.php:16`
- Titulo por rol `super_admin`: **Panel Ejecutivo** en `app/Filament/Pages/Dashboard.php:167`

## Parte visual (UI/UX)

La interfaz visual del panel admin se construye con Filament + widgets, y se personaliza con hooks y estilos inyectados desde el provider del panel.

### Personalizacion visual global

- Tipografia: `Manrope` (`->font('Manrope')`) en `app/Providers/Filament/AdminPanelProvider.php:62`
- Colores del panel (primary/info/success/warning/danger/gray) en `app/Providers/Filament/AdminPanelProvider.php:54`
- Sidebar colapsable + ancho personalizado en `app/Providers/Filament/AdminPanelProvider.php:39`
- Hooks con CSS/JS custom (topbar, sidebar, login, estilos y scripts) en `app/Providers/Filament/AdminPanelProvider.php:114`
- Assets publicos usados por el panel:
  - `public/css/login-daisyui-compiled.css` (solo login)
  - `public/css/executive-dashboard.css`
  - estilos del sidebar via `resources/views/filament/styles/sidebar-premium.blade.php`

### Vista del dashboard ejecutivo

- No usa una Blade propia del dashboard admin.
- Renderiza con la vista base de Filament y los widgets registrados en la pagina `Dashboard`.
- Distribucion responsive de columnas en `app/Filament/Pages/Dashboard.php:143`.

## Controlador

Para `/admin` no hay controlador MVC clasico; la pagina Filament `Dashboard` actua como controlador UI (Livewire + Filament):

- `app/Filament/Pages/Dashboard.php`

> Nota: Existe un dashboard MVC separado para `/dashboard`:
>
>- Controlador: `app/Http/Controllers/DashboardController.php`
>- Vista: `resources/views/dashboard/index.blade.php`
>
>Ese no corresponde al dashboard ejecutivo de `/admin`.

## Modelo (datos usados por el dashboard ejecutivo)

### Modelos principales

- `App\Models\Order`
- `App\Models\CashRegister`
- `App\Models\Payment`
- `App\Models\OrderDetail`
- `App\Models\AppSetting`

### Widgets y modelo consultado

1. `SalesStatsWidget` (`app/Filament/Widgets/SalesStatsWidget.php`)
   - Modelos: `Order`, `CashRegister`
2. `SalesChartWidget` (`app/Filament/Widgets/SalesChartWidget.php`)
   - Modelos: `Order`, `CashRegister`
3. `PaymentMethodsWidget` (`app/Filament/Widgets/PaymentMethodsWidget.php`)
   - Modelo: `Payment` (join con `orders`)
4. `TopProductsWidget` (`app/Filament/Widgets/TopProductsWidget.php`)
   - Modelo: `OrderDetail` (join con `orders`, relacion con `product.category`)
5. `CashRegisterStatsWidget` (`app/Filament/Widgets/CashRegisterStatsWidget.php`)
   - Modelos: `CashRegister`, `Payment`
6. `SunatConfigurationOverview` (`app/Filament/Widgets/SunatConfigurationOverview.php`)
   - Modelo: `AppSetting`

## Librerias y versiones

> Fuente: `composer.lock` y `package-lock.json`.

### Backend (PHP)

- PHP (constraint del proyecto): `^8.2` en `composer.json:9`
- Laravel Framework: `v12.7.2` (`composer.lock:3058`)
- Filament: `v3.3.8` (`composer.lock:1677`)
- Livewire: `v3.6.2` (`composer.lock:4101`)
- Filament Shield: `3.3.5` (`composer.lock:208`)
- Tomato Filament Users: `2.0.19` (`composer.lock:9185`)
- Greenter Lite: `v5.1.1` (`composer.lock:2355`)

### Frontend

- Vite: `6.2.2` (`package-lock.json:2983`)
- Tailwind CSS: `4.0.15` (`package-lock.json:2892`)
- DaisyUI: `5.0.47` (`package-lock.json:1882`)
- Axios: `1.8.4` (`package-lock.json:1674`)
- laravel-vite-plugin: `1.2.0` (`package-lock.json:2272`)

## Dashboard ejecutivo por rol

Cuando el usuario tiene rol `super_admin`, la pagina muestra el dashboard ejecutivo con estos widgets:

1. `SalesStatsWidget`
2. `SalesChartWidget`
3. `PaymentMethodsWidget`
4. `TopProductsWidget`
5. `CashRegisterStatsWidget`
6. `SunatConfigurationOverview`

Referencia: `app/Filament/Pages/Dashboard.php:44`.
