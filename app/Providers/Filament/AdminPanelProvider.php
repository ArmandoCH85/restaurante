<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Support\Colors\Color;

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
            ->brandLogo(asset('images/logoWayna.svg'))
            ->brandLogoHeight('4rem')
            ->colors([
                'primary' => Color::Blue,
                'gray' => Color::Slate,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Red,
            ])
            ->font('Inter')
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
                // OPERACIONES PRINCIPALES - DIARIAS (Único grupo con icono conservado)
                NavigationGroup::make()
                    ->label('🏪 Operaciones Diarias')
                    ->collapsed(false) // Siempre expandido - uso diario
                    ->collapsible(false), // No colapsable - crítico
            ])
            ->navigationItems([
                // Todos los navigation items se mantienen igual pero solo se mostrarán 
                // aquellos que pertenezcan al grupo "🏪 Operaciones Diarias" 
                // Los demás estarán disponibles pero sin grupo visible
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
            // Render Hooks para personalización del login POS
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => '<link rel="stylesheet" href="' . asset('css/login-daisyui-compiled.css') . '">'
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE,
                fn (): string => view('filament.auth.login-header')->render()
            )
            ->renderHook(
                PanelsRenderHook::AUTH_LOGIN_FORM_AFTER,
                fn (): string => '<a href="#" class="forgot-password-link">¿Olvidaste tu contraseña?</a>'
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
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
