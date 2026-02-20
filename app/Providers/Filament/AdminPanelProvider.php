<?php

namespace App\Providers\Filament;

use App\Filament\Pages\InventarioPorAlmacen;
use App\Filament\Pages\TableMap;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
            ->sidebarFullyCollapsibleOnDesktop()
            ->sidebarWidth('16rem')
            ->collapsedSidebarWidth('4rem')
            ->homeUrl(function () {
                $user = Auth::user();

                if ($user && $user->hasRole('waiter')) {
                    return '/admin/mapa-mesas';
                }

                return '/admin';
            })
            ->maxContentWidth('full')
            ->brandName('')
            ->brandLogo(asset('images/logoWayna.svg'))
            ->brandLogoHeight('6rem')
            ->colors([
                'primary' => Color::Indigo,
                'info' => Color::Cyan,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
                'gray' => Color::Gray,
            ])
            ->font('Manrope')
            ->darkMode()
            ->globalSearch(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->pages([
                TableMap::class,
                \App\Filament\Pages\PosInterface::class,
                \App\Filament\Pages\Dashboard::class,
                \App\Filament\Pages\ReservationCalendar::class,
                \App\Filament\Pages\ReportesPage::class,
                \App\Filament\Pages\ReportViewerPage::class,
                \App\Filament\Pages\SalesByAreaReport::class,
                \App\Filament\Pages\DeliveryHeatmapPage::class,
                InventarioPorAlmacen::class,
            ])
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\ReservationStats::class,
                \App\Filament\Widgets\PaymentMethodsWidget::class,
                \App\Filament\Widgets\SalesStatsWidget::class,
                \App\Filament\Widgets\SalesChartWidget::class,
                \App\Filament\Widgets\SalesOverviewWidget::class,
                \App\Filament\Widgets\SalesByUserWidget::class,
                \App\Filament\Widgets\TopProductsWidget::class,
                \App\Filament\Widgets\SalesHoursWidget::class,
                \App\Filament\Widgets\TableStatsWidget::class,
                \App\Filament\Widgets\SuppliersCountWidget::class,
                \App\Filament\Widgets\SunatConfigurationOverview::class,
                \App\Filament\Widgets\ProfitChartWidget::class,
                \App\Filament\Widgets\CashRegisterStatsWidget::class,
                \App\Filament\Widgets\PaymentMethodsChart::class,
                \App\Filament\Widgets\CashRegisterPerformanceChart::class,
                \App\Filament\Widgets\PurchaseStatsWidget::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            ->authMiddleware([Authenticate::class])
            ->renderHook(PanelsRenderHook::HEAD_END, fn (): string => implode('', array_filter([
                '<link rel="preconnect" href="https://fonts.googleapis.com">',
                '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
                '<link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap" rel="stylesheet">',
                '<meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">',
                '<meta name="theme-color" content="#0f172a" media="(prefers-color-scheme: dark)">',
                '<link rel="stylesheet" href="' . asset('css/executive-dashboard.css') . '">',
                '<link rel="stylesheet" href="' . asset('css/cash-ops-polish.css') . '">',
                request()->routeIs('filament.admin.auth.login') ? '<link rel="stylesheet" href="' . asset('css/login-daisyui-compiled.css') . '">' : null,
                '<style id="admin-panel-typography-scale">.fi-body{font-size:16px;line-height:1.55;font-weight:400;font-family:"Manrope","Plus Jakarta Sans","DM Sans",system-ui,-apple-system,"Segoe UI",sans-serif}</style>',
                '<script>(function(){var p=window.location.pathname.replace(/\/+$/,"");document.addEventListener("DOMContentLoaded",function(){if(p==="/admin"){document.body.classList.add("exec-dashboard-page");}if(p==="/admin/operaciones-caja"||p.indexOf("/admin/operaciones-caja/")===0){document.body.classList.add("cash-ops-page");}});})();</script>',
            ])))
            ->renderHook(PanelsRenderHook::SIDEBAR_NAV_START, fn (): string => view('filament.styles.sidebar-premium')->render())
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, fn (): string => view('filament.auth.login-header')->render())
            ->renderHook(PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, fn (): string => '<div class="mt-4 flex flex-col items-center gap-2 text-sm text-gray-500"><span>Acceso por codigo PIN.</span><a href="' . url('/waiter/login') . '" class="text-primary-600 hover:underline">Ir al login de mesero</a></div>')
            ->renderHook(PanelsRenderHook::TOPBAR_START, fn (): string => view('filament.topbar.quick-links')->render())
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                \TomatoPHP\FilamentUsers\FilamentUsersPlugin::make(),
            ]);
    }
}
