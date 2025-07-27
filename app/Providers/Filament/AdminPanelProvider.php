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
            ->sidebarWidth('18rem') // Ancho optimizado para mejor legibilidad
            ->collapsedSidebarWidth('4rem') // Ancho colapsado elegante
            ->homeUrl(function () {
                $user = Auth::user();
                if ($user && $user->hasRole('waiter')) {
                    return '/admin/mapa-mesas';
                }
                return '/admin';
            })
            ->maxContentWidth('7xl')
            ->sidebarFullyCollapsibleOnDesktop()
            ->brandName('')
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
            // Eliminar grupos personalizados para que funcione con los recursos automáticos
            // Usar navegación automática de Filament
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
                    /* 🎨 TAILADMIN DESIGN SYSTEM - FONDO BLANCO */
                    :root {
                        --tailadmin-sidebar-bg: #FFFFFF;
                        --tailadmin-sidebar-hover: #EEF2FF;
                        --tailadmin-accent: #3C50E0;
                        --tailadmin-accent-hover: #5570F1;
                        --tailadmin-text: #1F2937;
                        --tailadmin-text-muted: #6B7280;
                        --tailadmin-border: #E5E7EB;
                    }

                    /* 🏗️ SIDEBAR BASE - TAILADMIN FONDO BLANCO */
                    .fi-sidebar {
                        background: var(--tailadmin-sidebar-bg) !important;
                        border-right: 1px solid var(--tailadmin-border) !important;
                    }

                    /* 🎭 HEADER CON LOGO - ESTILO TAILADMIN */
                    header.fi-sidebar-header {
                        background: #FFFFFF !important;
                        border-bottom: 1px solid #E5E7EB !important;
                        padding: 1.5rem !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        min-height: 80px !important;
                        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
                        position: relative !important;
                    }

                    /* OCULTAR LOGO DE MODO OSCURO */
                    header.fi-sidebar-header .fi-logo.hidden.dark\\:flex {
                        display: none !important;
                    }

                    /* MOSTRAR SOLO LOGO DE MODO CLARO */
                    header.fi-sidebar-header .fi-logo.flex.dark\\:hidden {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        width: 100% !important;
                    }

                    /* LOGO STYLING - FORZAR VISIBILIDAD */
                    header.fi-sidebar-header .fi-logo,
                    header.fi-sidebar-header .fi-logo img,
                    header.fi-sidebar-header img,
                    header.fi-sidebar-header svg {
                        display: block !important;
                        max-height: 64px !important;
                        height: 64px !important;
                        width: auto !important;
                        object-fit: contain !important;
                        filter: brightness(1) !important;
                        opacity: 1 !important;
                        visibility: visible !important;
                    }

                    /* CONTENEDOR DEL LOGO */
                    header.fi-sidebar-header a {
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        text-decoration: none !important;
                    }

                    /* TEXTO DEL LOGO */
                    header.fi-sidebar-header .fi-logo-text,
                    header.fi-sidebar-header h1,
                    header.fi-sidebar-header .text-xl {
                        color: #1F2937 !important;
                        font-size: 1.5rem !important;
                        font-weight: 700 !important;
                        letter-spacing: -0.025em !important;
                        margin: 0 !important;
                    }

                    /* 🧭 NAVEGACIÓN PRINCIPAL - ESTILO TAILADMIN */
                    nav.fi-sidebar-nav {
                        background: #FFFFFF !important;
                        padding: 1.5rem !important;
                        border-radius: 0 !important;
                        box-shadow: none !important;
                        margin: 0 !important;
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 0.5rem !important;
                        height: 100% !important;
                        overflow-y: auto !important;
                        scrollbar-width: thin !important;
                        scrollbar-color: #E5E7EB #FFFFFF !important;
                    }

                    /* SCROLLBAR TAILADMIN STYLE */
                    nav.fi-sidebar-nav::-webkit-scrollbar {
                        width: 6px !important;
                    }

                    nav.fi-sidebar-nav::-webkit-scrollbar-track {
                        background: #F9FAFB !important;
                        border-radius: 3px !important;
                    }

                    nav.fi-sidebar-nav::-webkit-scrollbar-thumb {
                        background: #D1D5DB !important;
                        border-radius: 3px !important;
                    }

                    nav.fi-sidebar-nav::-webkit-scrollbar-thumb:hover {
                        background: #9CA3AF !important;
                    }

                    /* 📁 NAVIGATION GROUPS - TAILADMIN STYLE */
                    .fi-sidebar-group-label {
                        background: transparent !important;
                        color: var(--tailadmin-text-muted) !important;
                        font-size: 0.75rem !important;
                        font-weight: 500 !important;
                        text-transform: uppercase !important;
                        letter-spacing: 0.1em !important;
                        padding: 0.75rem 1.5rem 0.5rem 1.5rem !important;
                        margin-bottom: 0.5rem !important;
                        border: none !important;
                        border-left: 3px solid var(--tailadmin-accent) !important;
                        border-radius: 0 !important;
                    }

                    /* 🎯 NAVIGATION ITEMS - TAILADMIN STYLE */
                    .fi-sidebar-item {
                        margin: 0.125rem 1.5rem !important;
                        border-radius: 0.5rem !important;
                        transition: all 0.3s ease !important;
                    }

                    .fi-sidebar-item-button {
                        color: var(--tailadmin-text-muted) !important;
                        padding: 0.875rem 1rem !important;
                        font-size: 0.875rem !important;
                        font-weight: 500 !important;
                        transition: all 0.3s ease !important;
                        border-radius: 0.5rem !important;
                        width: 100% !important;
                        display: flex !important;
                        align-items: center !important;
                        gap: 0.75rem !important;
                    }

                    /* HOVER STATE */
                    .fi-sidebar-item:hover {
                        background: var(--tailadmin-sidebar-hover) !important;
                        background-color: #EEF2FF !important;
                    }

                    .fi-sidebar-item:hover .fi-sidebar-item-button {
                        color: var(--tailadmin-text) !important;
                    }

                    .fi-sidebar-item:hover .fi-sidebar-item-label {
                        color: var(--tailadmin-text) !important;
                    }

                    .fi-sidebar-item:hover .fi-sidebar-item-icon {
                        color: var(--tailadmin-text) !important;
                    }

                    /* ACTIVE STATE - FONDO AZUL CLARO CON TEXTO OSCURO */
                    .fi-sidebar-item.fi-active {
                        background: #EEF2FF !important;
                        background-color: #EEF2FF !important;
                        border-radius: 0.5rem !important;
                        border-left: 3px solid var(--tailadmin-accent) !important;
                    }

                    .fi-sidebar-item.fi-active > .fi-sidebar-item-button {
                        color: var(--tailadmin-accent) !important;
                        font-weight: 600 !important;
                        background: transparent !important;
                    }

                    .fi-sidebar-item.fi-active .fi-sidebar-item-button .fi-sidebar-item-label {
                        color: var(--tailadmin-accent) !important;
                        font-weight: 600 !important;
                    }

                    .fi-sidebar-item.fi-active .fi-sidebar-item-button .fi-sidebar-item-icon {
                        color: var(--tailadmin-accent) !important;
                    }

                    /* 🎨 ICONS */
                    .fi-sidebar-item-icon {
                        width: 1.25rem !important;
                        height: 1.25rem !important;
                        flex-shrink: 0 !important;
                        color: inherit !important;
                    }

                    /* 🏷️ LABELS */
                    .fi-sidebar-item-label {
                        color: inherit !important;
                        font-size: 0.875rem !important;
                        font-weight: inherit !important;
                    }

                    /* 🌙 DARK MODE - MANTENER FONDO BLANCO */
                    .dark .fi-sidebar {
                        --tailadmin-sidebar-bg: #FFFFFF;
                        --tailadmin-sidebar-hover: #EEF2FF;
                        --tailadmin-text: #1F2937;
                        --tailadmin-text-muted: #6B7280;
                        --tailadmin-border: #E5E7EB;
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
