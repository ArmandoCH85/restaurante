<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;

class TailAdminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Inyectar estilos CSS personalizados de TailAdmin
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): string => $this->getTailAdminStyles()
        );

        // SIDEBAR_NAV_START: Logo y nombre de la aplicación
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_START,
            fn (): string => $this->getSidebarLogo()
        );

        // SIDEBAR_NAV_END: Enlaces externos o información de versión
        FilamentView::registerRenderHook(
            PanelsRenderHook::SIDEBAR_NAV_END,
            fn (): string => $this->getSidebarFooterLinks()
        );

        // TOPBAR_START: Título dinámico de vista
        FilamentView::registerRenderHook(
            PanelsRenderHook::TOPBAR_START,
            fn (): string => $this->getTopbarTitle()
        );

        // CONTENT_START: Breadcrumb + headline contextual
        FilamentView::registerRenderHook(
            PanelsRenderHook::CONTENT_START,
            fn (): string => $this->getContentStart()
        );

        // CONTENT_END: Action buttons globales
        FilamentView::registerRenderHook(
            PanelsRenderHook::CONTENT_END,
            fn (): string => $this->getContentEnd()
        );

        // FOOTER: Firma legal
        FilamentView::registerRenderHook(
            PanelsRenderHook::FOOTER,
            fn (): string => $this->getFooter()
        );

        // BODY_START: Scripts y estilos adicionales
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_START,
            fn (): string => $this->getBodyScripts()
        );
    }

    /**
     * Obtener estilos CSS personalizados de TailAdmin
     */
    private function getTailAdminStyles(): string
    {
        return new HtmlString('
            <style>
                /* FORZAR COLORES TAILADMIN - MÁXIMA PRIORIDAD */

                /* Fondo general - FORZADO */
                * {
                    box-sizing: border-box;
                }

                html,
                body,
                #app,
                .fi-app,
                .fi-body,
                .fi-layout,
                .fi-main-ctn,
                .fi-layout-base,
                .fi-main-content,
                .fi-page,
                .fi-simple-layout,
                .fi-simple-main,
                .fi-simple-main-ctn,
                .fi-simple-page,
                .fi-resource-page,
                .fi-dashboard-page,
                .fi-page-content,
                .fi-section-content-ctn,
                .fi-main {
                    background-color: #F2F7FF !important;
                    background: #F2F7FF !important;
                }

                /* Tipografía global */
                body,
                .fi-body {
                    font-family: "Inter", ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif !important;
                    font-size: 15px !important;
                    color: #1F2937 !important;
                }

                /* SIDEBAR - FORZADO CON MÁXIMA ESPECIFICIDAD */
                .fi-sidebar,
                .fi-sidebar *,
                .fi-sidebar-nav,
                .fi-sidebar-nav *,
                aside[class*="fi-sidebar"],
                nav[class*="fi-sidebar"] {
                    background-color: #1C2434 !important;
                    background: #1C2434 !important;
                }

                .fi-sidebar {
                    width: 256px !important;
                    border-right: 1px solid rgba(0,0,0,0.06) !important;
                    transition: transform 150ms ease-in-out !important;
                }

                .fi-sidebar-nav {
                    padding: 0 !important;
                    height: 100% !important;
                }

                /* Logo area - height 64px */
                .tailadmin-logo {
                    padding: 16px 24px !important;
                    border-bottom: 1px solid var(--tailadmin-border) !important;
                    display: flex !important;
                    align-items: center !important;
                    gap: 12px !important;
                    height: 64px !important;
                }

                .tailadmin-logo img {
                    width: 120px !important;
                    height: 32px !important;
                }

                /* Navigation items - padding y = 10px, gap 8px */
                .fi-sidebar-item-button {
                    padding: 10px 24px !important;
                    margin: 0 !important;
                    border-radius: 0 !important;
                    transition: all 150ms ease-in-out !important;
                    color: #8A99AF !important;
                    background: transparent !important;
                    border: none !important;
                    display: flex !important;
                    align-items: center !important;
                    gap: 8px !important;
                    width: 100% !important;
                    text-align: left !important;
                    font-size: 14px !important;
                    font-weight: 500 !important;
                }

                .fi-sidebar-item-button:hover {
                    background-color: var(--tailadmin-sidebar-hover) !important;
                    color: white !important;
                }

                .fi-sidebar-item-button[aria-current="page"] {
                    background-color: var(--tailadmin-primary) !important;
                    color: white !important;
                }

                /* Icons - 18px size */
                .fi-sidebar-item-icon {
                    width: 18px !important;
                    height: 18px !important;
                    color: inherit !important;
                    flex-shrink: 0 !important;
                }

                .fi-sidebar-item-label {
                    color: inherit !important;
                    font-size: 14px !important;
                    font-weight: 500 !important;
                }

                /* Header - 64px height, white bg */
                .fi-topbar {
                    background-color: white !important;
                    height: 64px !important;
                    box-shadow: 0 1px 2px rgba(0,0,0,0.01) !important;
                    border-bottom: 1px solid var(--tailadmin-border) !important;
                    padding: 0 24px !important;
                    display: flex !important;
                    align-items: center !important;
                    justify-content: space-between !important;
                }

                /* Content wrapper - padding 24px */
                .fi-main {
                    background-color: var(--tailadmin-background) !important;
                    padding: 24px !important;
                }

                /* Asegurar fondo en todos los contenedores */
                .fi-simple-layout,
                .fi-simple-main,
                .fi-simple-main-ctn,
                .fi-simple-page,
                .fi-resource-page,
                .fi-dashboard-page,
                .fi-page-content,
                .fi-section-content-ctn {
                    background-color: var(--tailadmin-background) !important;
                }

                /* Override cualquier fondo blanco - más específico */
                .fi-layout > *:not(.fi-sidebar):not(.fi-topbar),
                .fi-main-ctn > *:not(.fi-card):not(.fi-section):not(.fi-table),
                .fi-page > *:not(.fi-card):not(.fi-section):not(.fi-table) {
                    background-color: var(--tailadmin-background) !important;
                }

                /* Forzar fondo en el HTML y body */
                html {
                    background-color: var(--tailadmin-background) !important;
                }

                /* Contenedor principal de Filament */
                #app,
                .fi-app {
                    background-color: var(--tailadmin-background) !important;
                    min-height: 100vh !important;
                }

                /* Cards - bg white, border, radius 8px */
                .fi-section,
                .fi-card,
                .fi-table {
                    background-color: white !important;
                    border: 1px solid var(--tailadmin-border) !important;
                    border-radius: 8px !important;
                    box-shadow: var(--tailadmin-shadow-light) !important;
                    margin-bottom: 20px !important;
                }

                /* Typography */
                .fi-header-heading {
                    font-size: 24px !important;
                    font-weight: 700 !important;
                    color: var(--tailadmin-text-primary) !important;
                }

                .fi-section-header-heading {
                    font-size: 20px !important;
                    font-weight: 600 !important;
                    color: var(--tailadmin-text-primary) !important;
                }

                /* Responsive simple */
                @media (max-width: 1024px) {
                    .tailadmin-hamburger {
                        display: block !important;
                    }
                }

                @media (min-width: 1025px) {
                    .tailadmin-hamburger {
                        display: none !important;
                    }
                }

                /* Hide default Filament elements */
                .fi-sidebar-header {
                    display: none !important;
                }

                /* Groups */
                .fi-sidebar-group-label {
                    color: #6B7280 !important;
                    font-size: 11px !important;
                    font-weight: 600 !important;
                    text-transform: uppercase !important;
                    letter-spacing: 0.05em !important;
                    padding: 16px 24px 8px 24px !important;
                    margin: 0 !important;
                }

                /* Separators */
                .tailadmin-separator {
                    height: 1px !important;
                    background-color: var(--tailadmin-border) !important;
                    margin: 8px 0 !important;
                }

                /* OVERRIDE ABSOLUTO - FUERZA BRUTA */
                body {
                    background: #F2F7FF !important;
                    background-color: #F2F7FF !important;
                }

                /* Forzar sidebar con especificidad máxima */
                aside.fi-sidebar,
                aside.fi-sidebar > *,
                aside.fi-sidebar nav,
                aside.fi-sidebar nav > *,
                .fi-sidebar-nav > ul,
                .fi-sidebar-nav > ul > li,
                .fi-sidebar-group,
                .fi-sidebar-item {
                    background: #1C2434 !important;
                    background-color: #1C2434 !important;
                }

                /* Contenido principal con fondo claro */
                main,
                main > *,
                .fi-main,
                .fi-main > *,
                .fi-page-content,
                .fi-page-content > * {
                    background: #F2F7FF !important;
                    background-color: #F2F7FF !important;
                }

                /* Excepciones para mantener blanco en tarjetas */
                .fi-section,
                .fi-card,
                .fi-table,
                .fi-topbar {
                    background: white !important;
                    background-color: white !important;
                }
            </style>
        ');
    }

    /**
     * SIDEBAR_NAV_START: Logo + nombre (120×32 px)
     */
    private function getSidebarLogo(): string
    {
        return new HtmlString('
            <div class="tailadmin-logo">
                <img src="' . asset('images/tailadmin-logo.svg') . '" alt="TailAdmin" style="width: 120px; height: 32px;" />
            </div>
            <div class="tailadmin-separator"></div>
        ');
    }

    /**
     * SIDEBAR_NAV_END: Footer o enlaces externos
     */
    private function getSidebarFooterLinks(): string
    {
        return new HtmlString('
            <div class="tailadmin-separator"></div>
            <div style="padding: 16px 24px; color: #6B7280; font-size: 12px;">
                <div style="margin-bottom: 8px;">
                    <a href="#" style="color: #6B7280; text-decoration: none; display: flex; align-items: center; gap: 8px; padding: 4px 0;">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Ayuda & Soporte
                    </a>
                </div>
                <div style="color: #9CA3AF; font-size: 11px;">
                    Versión 1.0.0
                </div>
            </div>
        ');
    }

    /**
     * TOPBAR_START: Título dinámico de vista
     */
    private function getTopbarTitle(): string
    {
        $currentRoute = request()->route()?->getName() ?? '';
        $title = $this->getPageTitle($currentRoute);

        return new HtmlString('
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="tailadmin-hamburger" style="display: block; background: none; border: none; padding: 8px; cursor: pointer; border: 1px solid #ccc;" onclick="toggleSidebar()">
                    <svg style="width: 24px; height: 24px; color: #374151;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 style="font-size: 24px; font-weight: 700; color: var(--tailadmin-text-primary); margin: 0;">
                    ' . $title . '
                </h1>
            </div>
        ');
    }

    /**
     * CONTENT_START: Breadcrumb + headline contextual
     */
    private function getContentStart(): string
    {
        // Solo mostrar en dashboard
        if (request()->is('admin') || request()->is('admin/')) {
            return new HtmlString('
                <div style="margin-bottom: 24px;">
                    <div style="background: white; border: 1px solid var(--tailadmin-border); border-radius: 8px; padding: 24px; box-shadow: var(--tailadmin-shadow-light);">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #3C50E0 0%, #7CD4FD 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                <svg style="width: 24px; height: 24px; color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                            <div>
                                <h2 style="font-size: 20px; font-weight: 600; color: var(--tailadmin-text-primary); margin: 0 0 4px 0;">
                                    ¡Bienvenido al Dashboard!
                                </h2>
                                <p style="color: var(--tailadmin-text-secondary); margin: 0; font-size: 15px;">
                                    Gestiona tu restaurante de manera eficiente con nuestro sistema integral.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            ');
        }

        return '';
    }

    /**
     * CONTENT_END: Action buttons globales
     */
    private function getContentEnd(): string
    {
        return new HtmlString('');
    }

    /**
     * FOOTER: Firma legal
     */
    private function getFooter(): string
    {
        return new HtmlString('
            <footer style="background: white; border-top: 1px solid var(--tailadmin-border); padding: 16px 24px; margin-top: 24px; text-align: center;">
                <p style="color: var(--tailadmin-text-secondary); font-size: 12px; margin: 0;">
                    © ' . date('Y') . ' TailAdmin. Todos los derechos reservados.
                </p>
            </footer>
        ');
    }

    /**
     * BODY_START: Scripts y estilos adicionales
     */
    private function getBodyScripts(): string
    {
        return new HtmlString('
            <script>
                // FORZAR COLORES TAILADMIN INMEDIATAMENTE
                document.addEventListener("DOMContentLoaded", function() {
                    // Forzar fondo general
                    document.body.style.backgroundColor = "#F2F7FF";
                    document.body.style.background = "#F2F7FF";
                    document.documentElement.style.backgroundColor = "#F2F7FF";

                    // Forzar sidebar
                    const sidebar = document.querySelector(".fi-sidebar");
                    if (sidebar) {
                        sidebar.style.backgroundColor = "#1C2434";
                        sidebar.style.background = "#1C2434";

                        // Forzar todos los elementos del sidebar
                        const sidebarElements = sidebar.querySelectorAll("*");
                        sidebarElements.forEach(function(el) {
                            if (!el.classList.contains("fi-sidebar-item-icon") &&
                                !el.classList.contains("fi-sidebar-item-label")) {
                                el.style.backgroundColor = "#1C2434";
                                el.style.background = "#1C2434";
                            }
                        });
                    }

                    // Forzar contenido principal
                    const main = document.querySelector(".fi-main");
                    if (main) {
                        main.style.backgroundColor = "#F2F7FF";
                        main.style.background = "#F2F7FF";
                    }

                    // SIMPLE: Solo toggle del sidebar
                    window.toggleSidebar = function() {
                        const sidebar = document.querySelector(".fi-sidebar");
                        if (sidebar) {
                            const isHidden = sidebar.style.transform.includes("translateX(-100%)");
                            sidebar.style.transform = isHidden ? "translateX(0)" : "translateX(-100%)";
                        }
                    }

                    // Colapsar grupos de navegación inicialmente
                    setTimeout(function() {
                        const navigationGroups = document.querySelectorAll(".fi-sidebar-group");

                        navigationGroups.forEach(function(group) {
                            const collapseButton = group.querySelector(".fi-sidebar-group-collapse-button");
                            const groupItems = group.querySelector(".fi-sidebar-group-items");

                            if (collapseButton && groupItems) {
                                groupItems.style.display = "none";
                                collapseButton.classList.add("rotate-180");
                            }
                        });
                    }, 100);

                    // Aplicar colores una vez más después de que todo cargue
                    setTimeout(function() {
                        document.body.style.backgroundColor = "#F2F7FF";
                        const sidebar = document.querySelector(".fi-sidebar");
                        if (sidebar) {
                            sidebar.style.backgroundColor = "#1C2434";
                        }
                    }, 500);
                });
            </script>
        ');
    }

    /**
     * Obtener título de página basado en la ruta
     */
    private function getPageTitle(string $route): string
    {
        $titles = [
            'filament.admin.pages.dashboard' => 'Dashboard',
            'filament.admin.resources.users.index' => 'Usuarios',
            'filament.admin.resources.roles.index' => 'Roles',
            'filament.admin.resources.permissions.index' => 'Permisos',
        ];

        return $titles[$route] ?? 'Panel de Administración';
    }
}
