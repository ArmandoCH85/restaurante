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

                // Si el usuario tiene rol delivery, redirigir directamente a /tables
                if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                    return '/tables';
                }

                // Para otros usuarios, redirigir al dashboard por defecto
                return '/admin';
            })
            ->sidebarFullyCollapsibleOnDesktop()
            ->brandName('') // Ocultar el nombre de la aplicación
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
                Pages\Dashboard::class,
                \App\Filament\Pages\ReservationCalendar::class,
                \App\Filament\Pages\ReportesPage::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\ReservationStats::class
            ])
            ->navigationGroups([
                // 🏪 OPERACIONES PRINCIPALES - Siempre visibles y expandidas
                NavigationGroup::make()
                    ->label('Operaciones')
                    ->icon('heroicon-o-building-storefront')
                    ->collapsed(false)
                    ->collapsible(false), // No colapsable - siempre visible

                // 📦 GESTIÓN DE PRODUCTOS - Expandido por defecto
                NavigationGroup::make()
                    ->label('Menú y Productos')
                    ->icon('heroicon-o-squares-2x2')
                    ->collapsed(false),

                // 🛒 COMPRAS E INVENTARIO - Expandido por defecto
                NavigationGroup::make()
                    ->label('Inventario')
                    ->icon('heroicon-o-cube')
                    ->collapsed(false),

                // 📄 FACTURACIÓN - Expandido por defecto
                NavigationGroup::make()
                    ->label('Facturación')
                    ->icon('heroicon-o-document-text')
                    ->collapsed(false),

                // 📅 RESERVAS Y COTIZACIONES - Expandido por defecto
                NavigationGroup::make()
                    ->label('Reservas')
                    ->icon('heroicon-o-calendar-days')
                    ->collapsed(false),

                // 👥 CLIENTES - Colapsado por defecto (menos usado)
                NavigationGroup::make()
                    ->label('Clientes')
                    ->icon('heroicon-o-user-circle')
                    ->collapsed(true),

                // 👨‍💼 PERSONAL Y USUARIOS - Colapsado por defecto (administrativo)
                NavigationGroup::make()
                    ->label('Personal')
                    ->icon('heroicon-o-user-group')
                    ->collapsed(true),

                // 📊 REPORTES - Colapsado por defecto (consulta ocasional)
                NavigationGroup::make()
                    ->label('Reportes')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsed(true),

                // ⚙️ CONFIGURACIÓN - Colapsado por defecto (uso esporádico)
                NavigationGroup::make()
                    ->label('Configuración')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(true),
            ])
            ->navigationItems([
                // 🏪 OPERACIONES PRINCIPALES
                NavigationItem::make('Venta Directa')
                    ->url('/pos')
                    ->icon('heroicon-o-shopping-cart')
                    ->group('Operaciones')
                    ->sort(1)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasCustomAccess('access_pos');
                    }),
                NavigationItem::make('Mapa de Mesas')
                    ->url('/tables')
                    ->icon('heroicon-o-map')
                    ->group('Operaciones')
                    ->sort(2)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasCustomAccess('access_tables');
                    }),
                NavigationItem::make('Gestión de Delivery')
                    ->url('/delivery/manage')
                    ->icon('heroicon-o-truck')
                    ->group('Operaciones')
                    ->sort(3)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasCustomAccess('access_delivery');
                    }),

                // 🚚 DELIVERY (solo para usuarios delivery)
                NavigationItem::make('Mis Pedidos')
                    ->url('/delivery/my-orders')
                    ->icon('heroicon-o-truck')
                    ->group('Operaciones')
                    ->sort(4)
                    ->visible(function() {
                        $user = Auth::user();
                        return $user && $user->roles->where('name', 'delivery')->count() > 0;
                    }),
                NavigationItem::make('Mapa de Pedidos')
                    ->url('/tables')
                    ->icon('heroicon-o-map')
                    ->group('Operaciones')
                    ->sort(5)
                    ->visible(function() {
                        $user = Auth::user();
                        return $user && $user->roles->where('name', 'delivery')->count() > 0;
                    }),



                // 📦 MENÚ Y PRODUCTOS
                NavigationItem::make('Productos')
                    ->url('/admin/resources/products')
                    ->icon('heroicon-o-squares-2x2')
                    ->group('Menú y Productos')
                    ->sort(1)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_product');
                    }),
                NavigationItem::make('Categorías')
                    ->url('/admin/resources/product-categories')
                    ->icon('heroicon-o-tag')
                    ->group('Menú y Productos')
                    ->sort(2)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_product::category');
                    }),
                NavigationItem::make('Recetas')
                    ->url('/admin/resources/recipes')
                    ->icon('heroicon-o-beaker')
                    ->group('Menú y Productos')
                    ->sort(3)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_recipe');
                    }),

                // 🛒 INVENTARIO Y COMPRAS
                NavigationItem::make('Ingredientes')
                    ->url('/admin/resources/ingredients')
                    ->icon('heroicon-o-cube')
                    ->group('Inventario')
                    ->sort(1)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_ingredient');
                    }),
                NavigationItem::make('Almacenes')
                    ->url('/admin/resources/warehouses')
                    ->icon('heroicon-o-building-storefront')
                    ->group('Inventario')
                    ->sort(2)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_warehouse');
                    }),
                NavigationItem::make('Compras')
                    ->url('/admin/resources/purchases')
                    ->icon('heroicon-o-shopping-bag')
                    ->group('Inventario')
                    ->sort(3)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_purchase');
                    }),
                NavigationItem::make('Proveedores')
                    ->url('/admin/resources/suppliers')
                    ->icon('heroicon-o-building-office')
                    ->group('Inventario')
                    ->sort(4)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_supplier');
                    }),

                // 📄 FACTURACIÓN
                NavigationItem::make('Comprobantes')
                    ->url('/admin/resources/invoices')
                    ->icon('heroicon-o-document-text')
                    ->group('Facturación')
                    ->sort(1)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_invoice');
                    }),
                NavigationItem::make('Series de Comprobantes')
                    ->url('/admin/resources/document-series')
                    ->icon('heroicon-o-hashtag')
                    ->group('Facturación')
                    ->sort(2)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_document::series');
                    }),
                NavigationItem::make('Caja')
                    ->url('/admin/resources/cash-registers')
                    ->icon('heroicon-o-banknotes')
                    ->group('Facturación')
                    ->sort(3)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_cash::register');
                    }),

                // 📅 RESERVAS Y COTIZACIONES
                NavigationItem::make('Reservas')
                    ->url('/admin/resources/reservations')
                    ->icon('heroicon-o-calendar-days')
                    ->group('Reservas')
                    ->sort(1)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_reservation');
                    }),
                NavigationItem::make('Calendario de Reservas')
                    ->url('/admin/reservation-calendar')
                    ->icon('heroicon-o-calendar')
                    ->group('Reservas')
                    ->sort(2)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_reservation');
                    }),
                NavigationItem::make('Cotizaciones')
                    ->url('/admin/resources/quotations')
                    ->icon('heroicon-o-document-duplicate')
                    ->group('Reservas')
                    ->sort(3)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_quotation');
                    }),

                // 👥 CLIENTES
                NavigationItem::make('Clientes')
                    ->url('/admin/resources/customers')
                    ->icon('heroicon-o-user-circle')
                    ->group('Clientes')
                    ->sort(1)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_customer');
                    }),

                // 👨‍💼 GESTIÓN DE PERSONAL
                NavigationItem::make('Empleados')
                    ->url('/admin/resources/employees')
                    ->icon('heroicon-o-user-group')
                    ->group('Personal')
                    ->sort(1)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_employee');
                    }),
                NavigationItem::make('Usuarios')
                    ->url('/admin/resources/users')
                    ->icon('heroicon-o-users')
                    ->group('Personal')
                    ->sort(2)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_user');
                    }),
                NavigationItem::make('Roles y Permisos')
                    ->url('/admin/resources/roles')
                    ->icon('heroicon-o-shield-check')
                    ->group('Personal')
                    ->sort(3)
                    ->visible(function() {
                        $user = Auth::user();
                        return $user && $user->hasRole('super_admin');
                    }),

                // 📊 REPORTES Y ANÁLISIS
                NavigationItem::make('Reportes')
                    ->url('/admin/reportes')
                    ->icon('heroicon-o-chart-bar')
                    ->group('Reportes')
                    ->sort(1)
                    ->visible(function() {
                        $user = Auth::user();
                        return $user && ($user->hasRole('super_admin') || $user->hasRole('admin'));
                    }),

                // ⚙️ CONFIGURACIÓN DEL SISTEMA
                NavigationItem::make('Datos de la Empresa')
                    ->url('/admin/resources/company-configs')
                    ->icon('heroicon-o-building-office')
                    ->group('Configuración')
                    ->sort(1)
                    ->visible(function() {
                        return PermissionHelper::hasPermission('view_any_company::config');
                    }),
                NavigationItem::make('Facturación Electrónica')
                    ->url('/admin/resources/electronic-billing-configs')
                    ->icon('heroicon-o-document-text')
                    ->group('Configuración')
                    ->sort(2)
                    ->visible(function() {
                        return PermissionHelper::hasPermission('view_any_electronic::billing::config');
                    }),
                NavigationItem::make('Mesas')
                    ->url('/admin/resources/tables')
                    ->icon('heroicon-o-squares-plus')
                    ->group('Configuración')
                    ->sort(3)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_table');
                    }),
                NavigationItem::make('Pisos')
                    ->url('/admin/resources/floors')
                    ->icon('heroicon-o-building-storefront')
                    ->group('Configuración')
                    ->sort(4)
                    ->visible(function() {
                        $user = Auth::user();
                        if ($user && $user->roles->where('name', 'delivery')->count() > 0) {
                            return false;
                        }
                        return PermissionHelper::hasPermission('view_any_floor');
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
