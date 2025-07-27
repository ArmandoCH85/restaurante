<?php

namespace App\Filament\Pages;

use App\Filament\Widgets;
use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationLabel = 'Escritorio';
    protected static ?string $title = 'Escritorio';
    protected static ?int $navigationSort = -1;

    public function getMaxContentWidth(): ?string
    {
        return 'full'; // Margen izquierdo reducido en dashboard
    }

    /**
     * 🎯 WIDGETS ESPECÍFICOS POR ROL
     * Cada rol ve información relevante para sus funciones
     * OPTIMIZADO: Máximo 4-6 widgets por rol para mejor visualización
     */
    public function getWidgets(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }

        // 👑 SUPER ADMIN - Dashboard completo pero organizado (6 widgets máximo)
        if ($user->hasRole('super_admin')) {
            return [
                // 📊 ROW 1: ESTADÍSTICAS PRINCIPALES 
                \App\Filament\Widgets\SalesStatsWidget::class,
                
                // 📈 ROW 2: ANÁLISIS DE TENDENCIAS
                \App\Filament\Widgets\SalesChartWidget::class,
                
                // 💳 ROW 3: PAGOS Y PRODUCTOS (lado a lado)
                \App\Filament\Widgets\PaymentMethodsWidget::class,
                \App\Filament\Widgets\TopProductsWidget::class,
                
                // 💰 ROW 4: CAJA Y CONFIGURACIÓN (lado a lado)
                \App\Filament\Widgets\CashRegisterStatsWidget::class,
                \App\Filament\Widgets\SunatConfigurationOverview::class,
            ];
        }

        // 🏢 ADMIN - Dashboard gerencial enfocado (5 widgets)
        if ($user->hasRole('admin')) {
            return [
                // 📊 ROW 1: ESTADÍSTICAS PRINCIPALES
                \App\Filament\Widgets\SalesStatsWidget::class,
                
                // 📈 ROW 2: TENDENCIAS DE VENTAS
                \App\Filament\Widgets\SalesChartWidget::class,
                
                // 💳 ROW 3: PAGOS Y PRODUCTOS (lado a lado)
                \App\Filament\Widgets\PaymentMethodsWidget::class,
                \App\Filament\Widgets\TopProductsWidget::class,
                
                // 💰 ROW 4: ESTADO DE CAJAS
                \App\Filament\Widgets\CashRegisterStatsWidget::class,
            ];
        }

        // 💰 CAJERO - Dashboard de caja enfocado (4 widgets)
        if ($user->hasRole('cashier')) {
            return [
                // 📊 ROW 1: ESTADÍSTICAS DE VENTAS
                \App\Filament\Widgets\SalesStatsWidget::class,
                
                // 💳 ROW 2: MÉTODOS DE PAGO Y HORAS (lado a lado)
                \App\Filament\Widgets\PaymentMethodsWidget::class,
                \App\Filament\Widgets\SalesHoursWidget::class,
                
                // 💰 ROW 3: ESTADO DE CAJA
                \App\Filament\Widgets\CashRegisterStatsWidget::class,
            ];
        }

        // 👨‍🍳 COCINA - Dashboard de cocina simple (3 widgets)
        if ($user->hasRole('kitchen')) {
            return [
                // 🏆 ROW 1: PRODUCTOS MÁS PEDIDOS
                \App\Filament\Widgets\TopProductsWidget::class,
                
                // ⏰ ROW 2: HORAS PICO Y RESERVAS (lado a lado)
                \App\Filament\Widgets\SalesHoursWidget::class,
                \App\Filament\Widgets\ReservationStats::class,
            ];
        }

        // 🚚 DELIVERY - Dashboard básico (1 widget)
        if ($user->hasRole('delivery')) {
            return [
                // 📅 INFO BÁSICA
                \App\Filament\Widgets\ReservationStats::class,
            ];
        }

        // 📊 DEFAULT - Para roles no definidos (2 widgets)
        return [
            \App\Filament\Widgets\SalesStatsWidget::class,
            \App\Filament\Widgets\ReservationStats::class,
        ];
    }

    /**
     * 🔐 CONTROL DE ACCESO
     * Waiters van directo al mapa de mesas
     */
    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = Auth::user();

        // El rol waiter no puede acceder al Dashboard - va directo a mesas
        if ($user && $user->hasRole('waiter')) {
            return false;
        }

        return true;
    }

    /**
     * 📐 CONFIGURACIÓN DE COLUMNAS RESPONSIVAS
     * Optimizado para mejor visualización de widgets
     */
    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,      // Móvil: 1 columna (stack vertical)
            'sm' => 1,           // Tablet pequeña: 1 columna
            'md' => 2,           // Tablet: 2 columnas (widgets lado a lado)
            'lg' => 2,           // Desktop: 2 columnas (más espacio)
            'xl' => 2,           // Desktop grande: 2 columnas
            '2xl' => 3,          // Desktop extra: máximo 3 columnas
        ];
    }

    /**
     * 🎨 TÍTULO DINÁMICO SEGÚN EL ROL
     */
    public function getTitle(): string
    {
        /** @var User|null $user */
        $user = Auth::user();
        
        if (!$user) {
            return 'Escritorio';
        }

        if ($user->hasRole('super_admin')) {
            return 'Dashboard Administrativo';
        }
        
        if ($user->hasRole('admin')) {
            return '🏢 Dashboard Gerencial';
        }
        
        if ($user->hasRole('cashier')) {
            return '💰 Dashboard de Caja';
        }
        
        if ($user->hasRole('kitchen')) {
            return '👨‍🍳 Dashboard de Cocina';
        }
        
        if ($user->hasRole('delivery')) {
            return '🚚 Dashboard de Delivery';
        }

        return 'Escritorio';
    }

    /**
     * 📝 SUBTÍTULO CON INFORMACIÓN CONTEXTUAL
     */
    public function getSubheading(): ?string
    {
        $user = Auth::user();
        $currentTime = now()->format('H:i');
        $currentDate = now()->format('d/m/Y');
        
        if (!$user) {
            return null;
        }

        $roleName = $user->roles->first()?->name ?? 'usuario';
        
        return "¡Bienvenido! 🌟 | Rol: {$roleName} | {$currentDate} - {$currentTime} | Actualización en tiempo real";
    }
}
//comentario