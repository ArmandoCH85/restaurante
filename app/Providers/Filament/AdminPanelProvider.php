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
            ->login(\App\Filament\Pages\Auth\CodeLogin::class)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('16rem') // Ancho optimizado - balance perfecto
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
            ->globalSearch(false)
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
                \App\Filament\Widgets\SalesOverviewWidget::class, // ✅ Widget agregado para resolver error de componente
                \App\Filament\Widgets\SalesByUserWidget::class, // ✅ Widget agregado para resolver error de componente
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
                fn (): string => '<div class="flex flex-col items-center gap-2 mt-4 text-sm text-gray-500">'
                    . '<span>Acceso por código PIN.</span>'
                    . '<a href="' . url('/waiter/login') . '" class="text-primary-600 hover:underline">Ir al login de mesero</a>'
                    . '</div>'
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

                        /* 🎨 COLORES CORPORATIVOS PARA ICONOS */
                        --corporate-primary: #2563EB;
                        --corporate-secondary: #059669;
                        --corporate-accent: #DC2626;
                        --corporate-warning: #D97706;
                        --corporate-info: #0891B2;
                        --corporate-neutral: #6B7280;
                    }

                    /* 🏗️ SIDEBAR BASE - TAILADMIN FONDO BLANCO */
                    .fi-sidebar {
                        background: var(--tailadmin-sidebar-bg) !important;
                        border-right: 1px solid var(--tailadmin-border) !important;
                    }

                    /* 🎭 HEADER CON LOGO - ESTILO TAILADMIN */


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

                    /* 🧭 NAVEGACIÓN PRINCIPAL - OPTIMIZADA PARA 16REM */
                    nav.fi-sidebar-nav {
                        background: #FFFFFF !important;
                        padding: 1rem 1.25rem !important;
                        border-radius: 0 !important;
                        box-shadow: none !important;
                        margin: 0 !important;
                        display: flex !important;
                        flex-direction: column !important;
                        gap: 0.375rem !important;
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

                    /* 🎯 NAVIGATION ITEMS - OPTIMIZADO PARA 16REM */
                    .fi-sidebar-item {
                        margin: 0.125rem 1.25rem !important;
                        border-radius: 0.5rem !important;
                        transition: all 0.3s ease !important;
                    }

                    .fi-sidebar-item-button {
                        color: var(--tailadmin-text-muted) !important;
                        padding: 0.75rem 0.875rem !important;
                        font-size: 0.875rem !important; /* texto ligeramente mayor para acompañar el ícono */
                        font-weight: 500 !important;
                        transition: all 0.3s ease !important;
                        border-radius: 0.5rem !important;
                        width: 100% !important;
                        display: flex !important;
                        align-items: center !important;
                        gap: 0.75rem !important; /* más espacio con ícono grande */
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

                    /* 🎨 ICONS CON COLORES CORPORATIVOS */
                    .fi-sidebar-item-icon {
                        width: 2rem !important;      /* íconos aún más grandes */
                        height: 2rem !important;     /* íconos aún más grandes */
                        flex-shrink: 0 !important;
                        color: var(--corporate-neutral) !important;
                        transition: color 0.3s ease !important;
                    }

                    /* MÉTODO ALTERNATIVO: Aplicar colores por posición en el DOM */
                    .fi-sidebar-nav .fi-sidebar-item:nth-child(1) .fi-sidebar-item-icon {
                        color: var(--corporate-primary) !important; /* Dashboard */
                    }

                    .fi-sidebar-nav .fi-sidebar-item:nth-child(2) .fi-sidebar-item-icon {
                        color: var(--corporate-secondary) !important; /* POS */
                    }

                    .fi-sidebar-nav .fi-sidebar-item:nth-child(3) .fi-sidebar-item-icon {
                        color: var(--corporate-secondary) !important; /* Mapa Mesas */
                    }

                    /* ICONOS ESPECÍFICOS CON COLORES CORPORATIVOS - RUTAS REALES DE FILAMENT */

                    /* 🔵 AZUL CORPORATIVO - Dashboard y Admin */
                    .fi-sidebar-item[href*="/admin/dashboard"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="/admin"] .fi-sidebar-item-icon:first-child {
                        color: var(--corporate-primary) !important;
                    }

                    /* 🟢 VERDE CORPORATIVO - POS y Operaciones de Caja */
                    .fi-sidebar-item[href*="pos-interface"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="operaciones-caja"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="mapa-mesas"] .fi-sidebar-item-icon {
                        color: var(--corporate-secondary) !important;
                    }

                    /* 🟠 NARANJA CORPORATIVO - Reportes */
                    .fi-sidebar-item[href*="reportes"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="reports"] .fi-sidebar-item-icon {
                        color: var(--corporate-warning) !important;
                    }

                    /* 🔷 CIAN CORPORATIVO - Configuración y Usuarios */
                    .fi-sidebar-item[href*="configuracion"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="users"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="shield/roles"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="company-config"] .fi-sidebar-item-icon {
                        color: var(--corporate-info) !important;
                    }

                    /* 🔴 ROJO CORPORATIVO - Inventario y Productos */
                    .fi-sidebar-item[href*="products"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="product-categories"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="ingredients"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="inventario"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="warehouse"] .fi-sidebar-item-icon {
                        color: var(--corporate-accent) !important;
                    }

                    /* 🟣 PÚRPURA CORPORATIVO - Facturación y Ventas */
                    .fi-sidebar-item[href*="document-series"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="invoices"] .fi-sidebar-item-icon,
                    .fi-sidebar-item[href*="customers"] .fi-sidebar-item-icon {
                        color: #7C3AED !important;
                    }

                    /* 🏷️ LABELS OPTIMIZADOS */
                    .fi-sidebar-item-label {
                        color: inherit !important;
                        font-size: 0.875rem !important; /* acompaña el tamaño del ícono */
                        font-weight: inherit !important;
                        line-height: 1.25 !important;
                        overflow: hidden !important;
                        text-overflow: ellipsis !important;
                        white-space: nowrap !important;
                    }

                    /* 🌙 DARK MODE - MANTENER FONDO BLANCO */
                    .dark .fi-sidebar {
                        --tailadmin-sidebar-bg: #FFFFFF;
                        --tailadmin-sidebar-hover: #EEF2FF;
                        --tailadmin-text: #1F2937;
                        --tailadmin-text-muted: #6B7280;
                        --tailadmin-border: #E5E7EB;
                    }

                    /* =============================
                       TOOLTIP (Tippy) DEL SIDEBAR
                       Más grande y visual
                       ============================= */
                    .fi .tippy-box {
                        background-color: #374151 !important; /* gris oscuro elegante */
                        color: #FFFFFF !important;
                        border-radius: 0.5rem !important;
                        box-shadow: 0 10px 25px rgba(0,0,0,0.18), 0 6px 10px rgba(0,0,0,0.12) !important;
                        font-size: 0.95rem !important; /* texto más grande */
                        font-weight: 600 !important;
                        z-index: 10050 !important;
                    }

                    .fi .tippy-box .tippy-content {
                        padding: 0.5rem 0.75rem !important; /* más padding */
                    }

                    /* Flecha del tooltip acorde al fondo */
                    .fi .tippy-box[data-placement^="right"] > .tippy-arrow::before,
                    .fi .tippy-box[data-placement^="left"] > .tippy-arrow::before,
                    .fi .tippy-box[data-placement^="top"] > .tippy-arrow::before,
                    .fi .tippy-box[data-placement^="bottom"] > .tippy-arrow::before {
                        color: #374151 !important;
                    }

                    /* Tamaño de flecha ligeramente mayor */
                    .fi .tippy-box .tippy-arrow {
                        width: 12px !important;
                        height: 12px !important;
                    }
                </style>

                <script>
                    /* 📱 SIDEBAR COLAPSADO POR DEFECTO - SEGÚN DOCUMENTACIÓN FILAMENT */
                    document.addEventListener("DOMContentLoaded", function() {
                        // Verificar si Alpine.js está disponible
                        if (typeof Alpine !== "undefined" && Alpine.store) {
                            // Usar el store de Alpine.js para colapsar el sidebar
                            const sidebarStore = Alpine.store("sidebar");
                            if (sidebarStore && typeof sidebarStore.collapse === "function") {
                                // Colapsar sidebar al cargar la página
                                sidebarStore.collapse();
                            }
                        }

                        // Método alternativo usando clases CSS
                        setTimeout(() => {
                            const sidebar = document.querySelector(".fi-sidebar");
                            const body = document.body;

                            if (sidebar && body) {
                                // Agregar clase de sidebar colapsado
                                body.classList.add("fi-sidebar-collapsed");
                                sidebar.classList.add("fi-collapsed");

                                // Trigger evento para notificar el cambio
                                window.dispatchEvent(new CustomEvent("sidebar-collapsed", {
                                    detail: { collapsed: true }
                                }));
                            }

                            // 🎨 APLICAR COLORES CORPORATIVOS A ICONOS
                            applyIconColors();
                        }, 100);
                    });

                    /* 🎨 FUNCIÓN PARA APLICAR COLORES CORPORATIVOS */
                    function applyIconColors() {
                        const colorMap = {
                            "dashboard": "#2563EB",      // Azul corporativo
                            "pos-interface": "#059669",   // Verde corporativo
                            "mapa-mesas": "#059669",      // Verde corporativo
                            "operaciones-caja": "#059669", // Verde corporativo
                            "reportes": "#D97706",        // Naranja corporativo
                            "products": "#DC2626",        // Rojo corporativo
                            "product-categories": "#DC2626", // Rojo corporativo
                            "ingredients": "#DC2626",     // Rojo corporativo
                            "users": "#0891B2",          // Cian corporativo
                            "shield/roles": "#0891B2",   // Cian corporativo
                            "configuracion": "#0891B2",  // Cian corporativo
                            "document-series": "#7C3AED", // Púrpura corporativo
                            "customers": "#7C3AED"       // Púrpura corporativo
                        };

                        // Aplicar colores a los iconos del sidebar
                        document.querySelectorAll(".fi-sidebar-item").forEach(item => {
                            const href = item.getAttribute("href") || "";
                            const icon = item.querySelector(".fi-sidebar-item-icon");

                            if (icon) {
                                // Buscar coincidencia en el mapa de colores
                                for (const [route, color] of Object.entries(colorMap)) {
                                    if (href.includes(route)) {
                                        icon.style.color = color + " !important";
                                        break;
                                    }
                                }
                            }
                        });
                    }

                    /* 🔄 REAPLICA COLORES CUANDO CAMBIA LA NAVEGACIÓN */
                    document.addEventListener("livewire:navigated", applyIconColors);
                    document.addEventListener("turbo:load", applyIconColors);
                </script>'
            )

            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                \TomatoPHP\FilamentUsers\FilamentUsersPlugin::make()
            ]);
    }
}
//comentario
