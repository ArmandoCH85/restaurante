<?php

namespace App\Providers\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationItem;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->darkMode()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->resources([
                \App\Filament\Resources\DocumentSeriesResource::class,
                \App\Filament\Resources\InvoiceResource::class,
                \App\Filament\Resources\CashRegisterResource::class,
                \App\Filament\Resources\TableResource::class,
                \App\Filament\Resources\IngredientResource::class,
                \App\Filament\Resources\RecipeResource::class,
                \App\Filament\Resources\PurchaseResource::class,
                \App\Filament\Resources\ReservationResource::class,
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
                \App\Filament\Pages\ReservationCalendar::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\ReservationStats::class
            ])
            ->navigationItems([
                // Grupo: Ventas
                NavigationItem::make('Venta Directa')
                    ->url('/pos')
                    ->icon('heroicon-o-shopping-cart')
                    ->group('Ventas')
                    ->sort(1),
                NavigationItem::make('Mapa de Mesas')
                    ->url('/tables')
                    ->icon('heroicon-o-map')
                    ->group('Ventas')
                    ->sort(2),
                NavigationItem::make('Dashboard')
                    ->url('/dashboard')
                    ->icon('heroicon-o-chart-bar')
                    ->group('Facturación')
                    ->sort(1),
                NavigationItem::make('Apertura de Caja')
                    ->url('/admin/resources/cash-registers')
                    ->icon('heroicon-o-calculator')
                    ->group('Facturación')
                    ->sort(2),

                // Grupo: Inventario
                NavigationItem::make('Ingredientes')
                    ->url('/admin/resources/ingredients')
                    ->icon('heroicon-o-cube')
                    ->group('Inventario')
                    ->sort(1),
                NavigationItem::make('Recetas')
                    ->url('/admin/resources/recipes')
                    ->icon('heroicon-o-beaker')
                    ->group('Inventario')
                    ->sort(2),
                NavigationItem::make('Compras')
                    ->url('/admin/resources/purchases')
                    ->icon('heroicon-o-shopping-bag')
                    ->group('Inventario')
                    ->sort(3),
                NavigationItem::make('Almacén')
                    ->url('/admin/resources/warehouses')
                    ->icon('heroicon-o-building-storefront')
                    ->group('Inventario')
                    ->sort(4),


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
