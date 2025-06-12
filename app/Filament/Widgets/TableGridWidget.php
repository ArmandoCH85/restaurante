<?php

namespace App\Filament\Widgets;

use App\Models\Table;
use Filament\Widgets\Widget;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TableGridWidget extends Widget
{
    // DESACTIVANDO POLLING TEMPORALMENTE PARA TESTING
    protected static ?string $pollingInterval = null;

    // SOLUCIÓN REAL DE FILAMENT: ancho completo
    // protected int|string|array $columnSpan = 'full'; // COMENTADO PARA TESTING

    // Propiedades para filtros (recibidas desde la página)
    public ?string $statusFilter = null;
    public ?int $floorFilter = null;
    public ?int $capacityFilter = null;
    public string $viewMode = 'grid';

    // CACHÉ para evitar múltiples consultas
    protected $cachedTables = null;
    protected $cacheKey = null;

    // Listener para recibir filtros actualizados
    protected $listeners = [
        'filtersUpdated' => 'updateFilters',
        'viewModeChanged' => 'updateViewMode'
    ];

    // Vista del widget
    protected static string $view = 'filament.widgets.table-grid-widget';

    /**
     * CONTROL DE VISIBILIDAD DESDE PHP
     */
    public static function canView(): bool
    {
        Log::info('🔍 TableGridWidget::canView() ejecutado');

                // Principio de menor privilegio: solo admin y super_admin pueden ver las mesas del dashboard
        $user = Auth::user();
        return $user && ($user->hasRole(['super_admin', 'admin']));
    }

    /**
     * Actualizar filtros cuando la página los cambie
     */
    #[On('filtersUpdated')]
    public function updateFilters($filters): void
    {
        Log::info('📊 TableGridWidget - updateFilters ejecutado', [
            'filters_received' => $filters,
            'current_view_mode' => $this->viewMode,
        ]);

        $this->statusFilter = $filters['statusFilter'] ?? null;
        $this->floorFilter = $filters['floorFilter'] ?? null;
        $this->capacityFilter = $filters['capacityFilter'] ?? null;

        // LIMPIAR CACHÉ cuando cambien los filtros
        $this->cachedTables = null;
        $this->cacheKey = null;

        Log::info('✅ TableGridWidget - Filtros actualizados y caché limpiada');
    }

    /**
     * Actualizar modo de vista
     */
    #[On('viewModeChanged')]
    public function updateViewMode($data): void
    {
        Log::info('🎚️ TableGridWidget - updateViewMode ejecutado', [
            'data_received' => $data,
            'previous_mode' => $this->viewMode,
        ]);

        $this->viewMode = $data['mode'] ?? 'grid';

        // LIMPIAR CACHÉ cuando cambie el modo de vista
        $this->cachedTables = null;
        $this->cacheKey = null;

        Log::info('✅ TableGridWidget - Modo de vista actualizado y caché limpiada', [
            'new_mode' => $this->viewMode,
        ]);
    }

    /**
     * Obtener las mesas con filtros aplicados - CON CACHÉ
     */
    public function getTables()
    {
        // Crear clave de caché basada en filtros
        $currentCacheKey = md5(json_encode([
            'status' => $this->statusFilter,
            'floor' => $this->floorFilter,
            'capacity' => $this->capacityFilter,
            'view_mode' => $this->viewMode,
        ]));

        // Si ya tenemos datos en caché con la misma clave, devolverlos
        if ($this->cachedTables !== null && $this->cacheKey === $currentCacheKey) {
            Log::info('📦 TableGridWidget - Usando caché', [
                'tables_count' => $this->cachedTables->count(),
            ]);
            return $this->cachedTables;
        }

        Log::info('📋 TableGridWidget - getTables() ejecutado (nueva consulta)', [
            'filters' => [
                'status' => $this->statusFilter,
                'floor' => $this->floorFilter,
                'capacity' => $this->capacityFilter,
            ],
            'view_mode' => $this->viewMode,
        ]);

        $query = Table::query()->with(['floor']);

        // Aplicar filtros si están activos
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->floorFilter) {
            $query->where('floor_id', $this->floorFilter);
        }

        if ($this->capacityFilter) {
            $query->where('capacity', '>=', $this->capacityFilter);
        }

        $tables = $query->orderBy('number')->get();

        // Guardar en caché
        $this->cachedTables = $tables;
        $this->cacheKey = $currentCacheKey;

        Log::info('📊 TableGridWidget - Tablas obtenidas y guardadas en caché', [
            'tables_count' => $tables->count(),
            'cache_key' => $currentCacheKey,
        ]);

        return $tables;
    }

    /**
     * Obtener color según estado de la mesa
     */
    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'available' => 'success',
            'occupied' => 'danger',
            'reserved' => 'warning',
            'maintenance' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Obtener icono según estado de la mesa
     */
    public function getStatusIcon(string $status): string
    {
        return match ($status) {
            'available' => 'heroicon-o-check-circle',
            'occupied' => 'heroicon-o-users',
            'reserved' => 'heroicon-o-clock',
            'maintenance' => 'heroicon-o-wrench-screwdriver',
            default => 'heroicon-o-question-mark-circle',
        };
    }

    /**
     * Obtener texto en español según estado
     */
    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'available' => 'Disponible',
            'occupied' => 'Ocupada',
            'reserved' => 'Reservada',
            'maintenance' => 'Mantenimiento',
            default => 'Desconocido',
        };
    }

    /**
     * Obtener icono según forma de la mesa
     */
    public function getShapeIcon(string $shape): string
    {
        return match ($shape) {
            'round' => 'heroicon-o-stop',
            'square' => 'heroicon-o-stop',
            default => 'heroicon-o-stop',
        };
    }
}
