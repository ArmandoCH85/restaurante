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
            ->login()
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
            ->brandLogoHeight('8rem')
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
                // OPERACIONES PRINCIPALES - DIARIAS (Más usadas - Siempre expandidas)
                NavigationGroup::make()
                    ->label('Operaciones Diarias')
                    ->collapsed(false) // Siempre expandido - uso diario
                    ->collapsible(false), // No colapsable - crítico

                // GESTIÓN DE MENÚ - FRECUENTE (Expandido por defecto)
                NavigationGroup::make()
                    ->label('Menú y Carta')
                    ->collapsed(false), // Expandido - uso frecuente

                // CLIENTES - FRECUENTE (Expandido por defecto)
                NavigationGroup::make()
                    ->label('Clientes')
                    ->collapsed(false), // Expandido - uso frecuente

                // INVENTARIO Y COMPRAS - SEMANAL (Expandido por defecto)
                NavigationGroup::make()
                    ->label('Inventario y Compras')
                    ->collapsed(false), // Expandido - importante

                // FACTURACIÓN Y VENTAS - DIARIO/SEMANAL (Expandido)
                NavigationGroup::make()
                    ->label('Facturación y Ventas')
                    ->collapsed(false), // Expandido - importante

                // RESERVAS Y EVENTOS - MODERADO (Colapsado por defecto)
                NavigationGroup::make()
                    ->label('Reservas y Eventos')
                    ->collapsed(true), // Colapsado - uso moderado

                // PERSONAL Y EMPLEADOS - ADMINISTRATIVO (Colapsado)
                NavigationGroup::make()
                    ->label('Personal')
                    ->collapsed(true), // Colapsado - administrativo

                // REPORTES Y ANÁLISIS - CONSULTA (Colapsado)
                NavigationGroup::make()
                    ->label('Reportes y Análisis')
                    ->collapsed(true), // Colapsado - consulta ocasional

                // SEGURIDAD Y PERMISOS - ADMINISTRATIVO (Colapsado)
                NavigationGroup::make()
                    ->label('Seguridad')
                    ->collapsed(true), // Colapsado - administrativo

                // CONFIGURACIÓN DEL SISTEMA - ESPORÁDICO (Colapsado)
                NavigationGroup::make()
                    ->label('Configuración')
                    ->collapsed(true), // Colapsado - uso esporádico
            ])
            ->navigationItems([
                // MENÚ Y PRODUCTOS
                NavigationItem::make('Productos')
                    ->url('/admin/resources/products')
                    ->icon('heroicon-o-squares-2x2')
                    ->group('Menú y Carta')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Categorías')
                    ->url('/admin/resources/product-categories')
                    ->icon('heroicon-o-tag')
                    ->group('Menú y Carta')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Recetas')
                    ->url('/admin/resources/recipes')
                    ->icon('heroicon-o-beaker')
                    ->group('Menú y Carta')
                    ->sort(3)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // INVENTARIO Y COMPRAS
                NavigationItem::make('Ingredientes')
                    ->url('/admin/resources/ingredients')
                    ->icon('heroicon-o-cube')
                    ->group('Inventario y Compras')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Almacenes')
                    ->url('/admin/resources/warehouses')
                    ->icon('heroicon-o-building-storefront')
                    ->group('Inventario y Compras')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Compras')
                    ->url('/admin/resources/purchases')
                    ->icon('heroicon-o-shopping-bag')
                    ->group('Inventario y Compras')
                    ->sort(3)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Proveedores')
                    ->url('/admin/resources/suppliers')
                    ->icon('heroicon-o-building-office')
                    ->group('Inventario y Compras')
                    ->sort(4)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // FACTURACIÓN
                NavigationItem::make('Comprobantes')
                    ->url('/admin/resources/invoices')
                    ->icon('heroicon-o-document-text')
                    ->group('Facturación y Ventas')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin', 'cashier']));
                    }),
                NavigationItem::make('Series de Comprobantes')
                    ->url('/admin/document-series')
                    ->icon('heroicon-o-hashtag')
                    ->group('Facturación y Ventas')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // RESERVAS Y COTIZACIONES
                NavigationItem::make('Reservas')
                    ->url('/admin/resources/reservations')
                    ->icon('heroicon-o-calendar-days')
                    ->group('Reservas y Eventos')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Calendario de Reservas')
                    ->url('/admin/reservation-calendar')
                    ->icon('heroicon-o-calendar')
                    ->group('Reservas y Eventos')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Cotizaciones')
                    ->url('/admin/resources/quotations')
                    ->icon('heroicon-o-document-duplicate')
                    ->group('Reservas y Eventos')
                    ->sort(3)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // CLIENTES
                NavigationItem::make('Clientes')
                    ->url('/admin/resources/customers')
                    ->icon('heroicon-o-user-circle')
                    ->group('Clientes')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin', 'delivery']));
                    }),

                // GESTIÓN DE PERSONAL
                NavigationItem::make('Empleados')
                    ->url('/admin/resources/employees')
                    ->icon('heroicon-o-user-group')
                    ->group('Personal')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // REPORTES Y ANÁLISIS
                NavigationItem::make('Reportes')
                    ->url('/admin/reportes')
                    ->icon('heroicon-o-chart-bar')
                    ->group('Reportes y Análisis')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),

                // CONFIGURACIÓN DEL SISTEMA
                NavigationItem::make('Datos de la Empresa')
                    ->url('/admin/resources/company-configs')
                    ->icon('heroicon-o-building-office')
                    ->group('Configuración')
                    ->sort(1)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin']));
                    }),
                NavigationItem::make('Facturación Electrónica')
                    ->url('/admin/resources/electronic-billing-configs')
                    ->icon('heroicon-o-document-text')
                    ->group('Configuración')
                    ->sort(2)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin']));
                    }),
                NavigationItem::make('Mesas')
                    ->url('/admin/resources/tables')
                    ->icon('heroicon-o-squares-plus')
                    ->group('Configuración')
                    ->sort(3)
                    ->visible(function () {
                        $user = Auth::user();
                        return $user && ($user->hasRole(['super_admin', 'admin']));
                    }),
                NavigationItem::make('Pisos')
                    ->url('/admin/resources/floors')
                    ->icon('heroicon-o-building-storefront')
                    ->group('Configuración')
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
            ->renderHook(
                'panels::sidebar.nav.start',
                fn (): string => '<style>
                    :root {
                        --sidebar-bg-primary: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%);
                        --sidebar-bg-secondary: #0f172a;
                        --sidebar-accent: #3C50E0;
                        --sidebar-text: #f8fafc;
                        --sidebar-text-muted: #cbd5e1;
                    }
                    
                    /* Fondo corporativo del sidebar */
                    .fi-sidebar {
                        background: var(--sidebar-bg-primary) !important;
                        border-right: 2px solid var(--sidebar-accent) !important;
                        box-shadow: 4px 0 12px rgba(0, 0, 0, 0.15) !important;
                    }
                    
                    /* Logo con fondo destacado */
                    .fi-sidebar-header {
                        background: var(--sidebar-bg-secondary) !important;
                        border-bottom: 1px solid var(--sidebar-accent) !important;
                        padding: 1.5rem 1rem !important;
                    }
                    
                    /* Navegación con colores armonizados */
                    .fi-sidebar-nav {
                        background: transparent !important;
                    }
                    
                    /* Grupos de navegación - Headers principales */
                    .fi-sidebar-group-label {
                        background: linear-gradient(90deg, rgba(60, 80, 224, 0.15) 0%, rgba(60, 80, 224, 0.05) 100%) !important;
                        color: #60a5fa !important;
                        font-weight: 600 !important;
                        font-size: 0.75rem !important;
                        text-transform: uppercase !important;
                        letter-spacing: 0.05em !important;
                        text-shadow: 0 1px 3px rgba(0, 0, 0, 0.4) !important;
                        border: none !important;
                        border-left: 3px solid var(--sidebar-accent) !important;
                        border-radius: 0 6px 6px 0 !important;
                        padding: 0.75rem 1rem !important;
                        margin: 0.5rem 0 1rem 0 !important;
                        position: relative !important;
                        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
                    }
                    
                    .fi-sidebar-group-label::before {
                        content: "" !important;
                        position: absolute !important;
                        left: 0 !important;
                        top: 0 !important;
                        height: 100% !important;
                        width: 2px !important;
                        background: linear-gradient(180deg, var(--sidebar-accent) 0%, rgba(60, 80, 224, 0.3) 100%) !important;
                        border-radius: 0 2px 2px 0 !important;
                    }
                    
                    .fi-sidebar-group-label::after {
                        content: "" !important;
                        position: absolute !important;
                        right: 0.5rem !important;
                        top: 50% !important;
                        transform: translateY(-50%) !important;
                        width: 4px !important;
                        height: 4px !important;
                        background: var(--sidebar-accent) !important;
                        border-radius: 50% !important;
                        box-shadow: 0 0 6px rgba(60, 80, 224, 0.6) !important;
                    }
                    
                    /* Items de navegación */
                    .fi-sidebar-item-button {
                        color: #e2e8f0 !important;
                        border-radius: 8px !important;
                        transition: all 0.3s ease !important;
                        margin-bottom: 0.25rem !important;
                    }
                    
                    .fi-sidebar-item-button:hover {
                        background: rgba(60, 80, 224, 0.2) !important;
                        transform: translateX(4px) !important;
                        box-shadow: 0 2px 8px rgba(60, 80, 224, 0.3) !important;
                        color: #ffffff !important;
                    }
                    
                    .fi-sidebar-item.fi-active .fi-sidebar-item-button {
                        background: var(--sidebar-accent) !important;
                        color: #ffffff !important;
                        box-shadow: 0 4px 12px rgba(60, 80, 224, 0.4) !important;
                    }
                    
                    /* Textos específicos del sidebar */
                    .fi-sidebar-item-label {
                        color: #e2e8f0 !important;
                        font-weight: 500 !important;
                    }
                    
                    .fi-sidebar-item:hover .fi-sidebar-item-label {
                        color: #ffffff !important;
                    }
                    
                    .fi-sidebar-item.fi-active .fi-sidebar-item-label {
                        color: #ffffff !important;
                        font-weight: 600 !important;
                    }
                    
                    /* Iconos con efecto */
                    .fi-sidebar-item-icon {
                        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3)) !important;
                        color: #cbd5e1 !important;
                    }
                    
                    .fi-sidebar-item:hover .fi-sidebar-item-icon {
                        color: #ffffff !important;
                    }
                    
                    .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
                        color: #ffffff !important;
                    }
                    
                    /* Modo oscuro */
                    .dark .fi-sidebar {
                        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%) !important;
                    }
                    
                    .dark .fi-sidebar-header {
                        background: #020617 !important;
                    }
                    
                    .dark .fi-sidebar-item-button {
                        color: #f1f5f9 !important;
                    }
                    
                    .dark .fi-sidebar-item-label {
                        color: #f1f5f9 !important;
                    }
                    
                    .dark .fi-sidebar-item-icon {
                        color: #e2e8f0 !important;
                    }
                    
                    /* Headers en modo oscuro */
                    .dark .fi-sidebar-group-label {
                        background: linear-gradient(90deg, rgba(60, 80, 224, 0.25) 0%, rgba(60, 80, 224, 0.1) 100%) !important;
                        color: #93c5fd !important;
                        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3) !important;
                    }
                    
                    .dark .fi-sidebar-group-label::after {
                        box-shadow: 0 0 8px rgba(60, 80, 224, 0.8) !important;
                    }
                    
                    /* Scroll personalizado */
                    .fi-sidebar-nav::-webkit-scrollbar {
                        width: 6px;
                    }
                    
                    .fi-sidebar-nav::-webkit-scrollbar-track {
                        background: rgba(0, 0, 0, 0.1);
                        border-radius: 3px;
                    }
                    
                    .fi-sidebar-nav::-webkit-scrollbar-thumb {
                        background: var(--sidebar-accent);
                        border-radius: 3px;
                    }
                    
                    .fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
                        background: #2d42c7;
                    }
                </style>'
            )
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                \TomatoPHP\FilamentUsers\FilamentUsersPlugin::make()
            ]);
    }
}
//comentario
