<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;

use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Solutionforest\FilamentLoginScreen\Filament\Pages\Auth\Themes\Theme1\LoginScreenPage as LoginScreenPage;
use Illuminate\Support\Facades\Auth;
use App\Helpers\PermissionHelper;
use App\Filament\Pages\TableMap;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(LoginScreenPage::class)
            ->sidebarCollapsibleOnDesktop()
            ->homeUrl(function () {
                $user = Auth::user();
                if ($user && $user->hasRole('waiter')) {
                    return '/admin/mapa-mesas';
                }
                return '/admin';
            })
            ->maxContentWidth('7xl')
            ->sidebarFullyCollapsibleOnDesktop()
            ->brandName('') // Ocultar el nombre de la aplicación
            ->brandLogo('/images/logoWayna.svg')
            ->brandLogoHeight('6rem')
            ->colors([
                'primary' => [
                    50 => '#eff6ff',
                    100 => '#dbeafe',
                    200 => '#bfdbfe',
                    300 => '#93c5fd',
                    400 => '#60a5fa',
                    500 => '#3C50E0',
                    600 => '#2563eb',
                    700 => '#1d4ed8',
                    800 => '#1e40af',
                    900 => '#1e3a8a',
                    950 => '#172554',
                ],
            ])
            ->darkMode()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            // Registrar páginas explícitamente en lugar de descubrirlas automáticamente
            ->pages([
                TableMap::class,
                \App\Filament\Pages\PosInterface::class,
                \App\Filament\Pages\Dashboard::class, // ✅ Dashboard personalizado por roles
                \App\Filament\Pages\ReservationCalendar::class,
                \App\Filament\Pages\ReportesPage::class,
            ])
            // COMENTADO: Auto-descubrimiento de widgets deshabilitado para control granular
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\ReservationStats::class,
                \App\Filament\Widgets\PaymentMethodsWidget::class,
                \App\Filament\Widgets\SalesStatsWidget::class,
                \App\Filament\Widgets\SalesChartWidget::class,
                \App\Filament\Widgets\TopProductsWidget::class,
                \App\Filament\Widgets\SalesHoursWidget::class,
                \App\Filament\Widgets\TableStatsWidget::class,
                \App\Filament\Widgets\SuppliersCountWidget::class,
                \App\Filament\Widgets\SunatConfigurationOverview::class,
                \App\Filament\Widgets\ProfitChartWidget::class,
                \App\Filament\Widgets\CashRegisterStatsWidget::class,
                \App\Filament\Widgets\PaymentMethodsChart::class,
                \App\Filament\Widgets\CashRegisterPerformanceChart::class,
            ])
            ->navigationGroups([
                // 🏪 OPERACIONES PRINCIPALES - DIARIAS (Más usadas - Siempre expandidas)
                NavigationGroup::make()
                    ->label('🏪 Operaciones Diarias')
                    ->icon('heroicon-o-building-storefront')
                    ->collapsed(false) // Siempre expandido - uso diario
                    ->collapsible(false), // No colapsable - crítico

                // 🍽️ GESTIÓN DE MENÚ - FRECUENTE (Expandido por defecto)
                NavigationGroup::make()
                    ->label('🍽️ Menú y Carta')
                    ->icon('heroicon-o-squares-2x2')
                    ->collapsed(false), // Expandido - uso frecuente

                // 👥 CLIENTES - FRECUENTE (Expandido por defecto)
                NavigationGroup::make()
                    ->label('👥 Clientes')
                    ->icon('heroicon-o-user-circle')
                    ->collapsed(false), // Expandido - uso frecuente

                // 📦 INVENTARIO Y COMPRAS - SEMANAL (Expandido por defecto)
                NavigationGroup::make()
                    ->label('📦 Inventario y Compras')
                    ->icon('heroicon-o-cube')
                    ->collapsed(false), // Expandido - importante

                // 📄 FACTURACIÓN Y VENTAS - DIARIO/SEMANAL (Expandido)
                NavigationGroup::make()
                    ->label('📄 Facturación y Ventas')
                    ->icon('heroicon-o-document-text')
                    ->collapsed(false), // Expandido - importante

                // 📅 RESERVAS Y EVENTOS - MODERADO (Colapsado por defecto)
                NavigationGroup::make()
                    ->label('📅 Reservas y Eventos')
                    ->icon('heroicon-o-calendar-days')
                    ->collapsed(true), // Colapsado - uso moderado

                // 👨‍💼 PERSONAL Y EMPLEADOS - ADMINISTRATIVO (Colapsado)
                NavigationGroup::make()
                    ->label('👨‍💼 Personal')
                    ->icon('heroicon-o-user-group')
                    ->collapsed(true), // Colapsado - administrativo

                // 📊 REPORTES Y ANÁLISIS - CONSULTA (Colapsado)
                NavigationGroup::make()
                    ->label('📊 Reportes y Análisis')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsed(true), // Colapsado - consulta ocasional

                // 🔐 SEGURIDAD Y PERMISOS - ADMINISTRATIVO (Colapsado)
                NavigationGroup::make()
                    ->label('🔐 Seguridad')
                    ->icon('heroicon-o-shield-check')
                    ->collapsed(true), // Colapsado - administrativo

                // ⚙️ CONFIGURACIÓN DEL SISTEMA - ESPORÁDICO (Colapsado)
                NavigationGroup::make()
                    ->label('⚙️ Configuración')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(true), // Colapsado - uso esporádico
            ])
            ->navigationItems([
                // 🏪 OPERACIONES PRINCIPALES
                NavigationItem::make('Venta Directa')
                    ->url('/admin/pos-interface')
                    ->icon('heroicon-o-shopping-cart')
                    ->group('🏪 Operaciones Diarias')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && !$user->hasRole('waiter');
                    }),
                NavigationItem::make('Mapa de Mesas')
                    ->url('/admin/mapa-mesas')
                    ->icon('heroicon-o-map')
                    ->group('🏪 Operaciones Diarias')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin', 'cashier', 'waiter']));
                    }),

                // 📦 MENÚ Y PRODUCTOS
                NavigationItem::make('Productos')
                    ->url('/admin/resources/products')
                    ->icon('heroicon-o-squares-2x2')
                    ->group('🍽️ Menú y Carta')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Categorías')
                    ->url('/admin/resources/product-categories')
                    ->icon('heroicon-o-tag')
                    ->group('🍽️ Menú y Carta')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Recetas')
                    ->url('/admin/resources/recipes')
                    ->icon('heroicon-o-beaker')
                    ->group('🍽️ Menú y Carta')
                    ->sort(3)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // 🛒 INVENTARIO Y COMPRAS
                NavigationItem::make('Ingredientes')
                    ->url('/admin/resources/ingredients')
                    ->icon('heroicon-o-cube')
                    ->group('📦 Inventario y Compras')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Almacenes')
                    ->url('/admin/resources/warehouses')
                    ->icon('heroicon-o-building-storefront')
                    ->group('📦 Inventario y Compras')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Compras')
                    ->url('/admin/resources/purchases')
                    ->icon('heroicon-o-shopping-bag')
                    ->group('📦 Inventario y Compras')
                    ->sort(3)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Proveedores')
                    ->url('/admin/resources/suppliers')
                    ->icon('heroicon-o-building-office')
                    ->group('📦 Inventario y Compras')
                    ->sort(4)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // 📄 FACTURACIÓN
                NavigationItem::make('Comprobantes')
                    ->url('/admin/resources/invoices')
                    ->icon('heroicon-o-document-text')
                    ->group('📄 Facturación y Ventas')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin', 'cashier']));
                    }),
                NavigationItem::make('Series de Comprobantes')
                    ->url('/admin/document-series')
                    ->icon('heroicon-o-hashtag')
                    ->group('📄 Facturación y Ventas')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // 📅 RESERVAS Y COTIZACIONES
                NavigationItem::make('Reservas')
                    ->url('/admin/resources/reservations')
                    ->icon('heroicon-o-calendar-days')
                    ->group('📅 Reservas y Eventos')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Calendario de Reservas')
                    ->url('/admin/reservation-calendar')
                    ->icon('heroicon-o-calendar')
                    ->group('📅 Reservas y Eventos')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Cotizaciones')
                    ->url('/admin/resources/quotations')
                    ->icon('heroicon-o-document-duplicate')
                    ->group('📅 Reservas y Eventos')
                    ->sort(3)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // 👥 CLIENTES
                NavigationItem::make('Clientes')
                    ->url('/admin/resources/customers')
                    ->icon('heroicon-o-user-circle')
                    ->group('👥 Clientes')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin', 'delivery']));
                    }),

                // 👨‍💼 GESTIÓN DE PERSONAL
                NavigationItem::make('Empleados')
                    ->url('/admin/resources/employees')
                    ->icon('heroicon-o-user-group')
                    ->group('👨‍💼 Personal')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // 📊 REPORTES Y ANÁLISIS
                NavigationItem::make('Reportes')
                    ->url('/admin/reportes')
                    ->icon('heroicon-o-chart-bar')
                    ->group('📊 Reportes y Análisis')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // ⚙️ CONFIGURACIÓN DEL SISTEMA
                NavigationItem::make('Datos de la Empresa')
                    ->url('/admin/resources/company-configs')
                    ->icon('heroicon-o-building-office')
                    ->group('⚙️ Configuración')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin']));
                    }),
                NavigationItem::make('Facturación Electrónica')
                    ->url('/admin/resources/electronic-billing-configs')
                    ->icon('heroicon-o-document-text')
                    ->group('⚙️ Configuración')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin']));
                    }),
                NavigationItem::make('Mesas')
                    ->url('/admin/resources/tables')
                    ->icon('heroicon-o-squares-plus')
                    ->group('⚙️ Configuración')
                    ->sort(3)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Pisos')
                    ->url('/admin/resources/floors')
                    ->icon('heroicon-o-building-storefront')
                    ->group('⚙️ Configuración')
                    ->sort(4)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                \TomatoPHP\FilamentUsers\FilamentUsersPlugin::make()
            ]);
    }
}
//comentario
