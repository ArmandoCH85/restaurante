<x-filament-panels::page>
    {{-- ✨ MAPA DE MESAS - MEJORADO 100% FILAMENT NATIVO ✨ --}}

    {{-- 📊 Estadísticas con Widget Nativo de Filament --}}
    @livewire(\App\Filament\Widgets\TableStatsWidget::class)

    {{-- 🎛️ Panel de controles 100% nativo de Filament --}}
    <x-filament::section class="mb-6">
        <x-slot name="headerActions">
            {{-- Controles de vista nativos --}}
            <div class="flex gap-2">
                <x-filament::button wire:click="$set('viewMode', 'grid')" :color="$this->viewMode === 'grid' ? 'primary' : 'gray'" size="sm"
                    icon="heroicon-m-squares-2x2">
                    Cuadrícula
                </x-filament::button>

                <x-filament::button wire:click="$set('viewMode', 'map')" :color="$this->viewMode === 'map' ? 'primary' : 'gray'" size="sm"
                    icon="heroicon-m-map">
                    Mapa
                </x-filament::button>

                <x-filament::button x-on:click="$dispatch('open-modal', { id: 'filters-modal' })" color="gray"
                    size="sm" icon="heroicon-m-adjustments-horizontal" :badge="$statusFilter || $locationFilter || $searchQuery
                        ? collect([$statusFilter, $locationFilter, $searchQuery])
                            ->filter()
                            ->count()
                        : null" badge-color="primary">
                    Filtros
                </x-filament::button>
            </div>
        </x-slot>

        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4" />
            <span>Última actualización: {{ now()->format('H:i') }}</span>
        </div>
    </x-filament::section>

    {{-- Panel principal de mesas --}}
    <x-filament::section>
        <x-slot name="heading">
           Mapa de Mesas
        </x-slot>

        <x-slot name="description">
            Haz clic en una mesa para abrir la Venta Directa
        </x-slot>

        {{-- 🎯 Vista Grid - 100% Componentes Nativos de Filament --}}
        @if ($viewMode === 'grid')
            {{-- Grid responsivo optimizado para aprovechar mejor el espacio --}}
            {{-- TEMPORALMENTE DESHABILITADO: wire:poll.5s --}}
            <div
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 px-4 justify-items-center"
                style="grid-column-gap: 4px; grid-row-gap: 16px;">
                @forelse($tables as $table)
                    @php
                        $style = match($table->status) {
                            'available' => 'background-color: #137F37 !important; color: #ffffff !important; border-left-color: #137F37 !important;',
                            'occupied'  => 'background-color: #E7444C !important; color: #ffffff !important; border-left-color: #E7444C !important;',
                            'reserved'  => 'background-color: #78350f !important; color: #fde68a !important; border-left-color: #f59e0b !important;',
                            'prebill'   => 'background-color: #1E90FF !important; color: #ffffff !important; border-left-color: #1E90FF !important;',
                            'maintenance' => 'background-color: #1E90FF !important; color: #ffffff !important; border-left-color: #1E90FF !important;',
                            default     => 'background-color: #1f2937 !important; color: #f3f4f6 !important; border-left-color: #6b7280 !important;',
                        };
                    @endphp
                    <div wire:click="openPOS({{ $table->id }})" wire:key="table-{{ $table->id }}"
                        class="rounded-2xl p-6 hover:shadow-lg transition-all duration-200 cursor-pointer border-l-4 table-card-original max-w-sm mx-auto"
                        style="{{ $style }}">
                        {{-- Header original como en la imagen --}}
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-2">
                                {{-- Número de mesa - UX/UI mejorado --}}
                                <span class="text-3xl font-black tracking-tight table-number">
                                    #{{ $table->number ?? str_replace('Mesa ', '', $table->name) }}
                                </span>
                                <x-filament::icon :icon="match ($table->shape ?? 'square') {
                                    'round' => 'heroicon-o-stop',
                                    'oval' => 'heroicon-o-stop',
                                    default => 'heroicon-o-square-3-stack-3d',
                                }" class="w-4 h-4 opacity-60" />
                            </div>

                            {{-- Badge de estado como en el original --}}
                            <x-filament::badge
                                :color="match ($table->status) {
                                    'available' => 'success',
                                    'occupied' => 'danger',
                                    'reserved' => 'warning',
                                    'prebill' => 'info',
                                    'maintenance' => 'gray',
                                    default => 'gray',
                                }"
                                size="lg"
                                :icon="match ($table->status) {
                                    'available' => 'heroicon-m-check-circle',
                                    'occupied' => 'heroicon-m-users',
                                    'reserved' => 'heroicon-m-clock',
                                    'prebill' => 'heroicon-m-banknotes',
                                    'maintenance' => 'heroicon-m-wrench-screwdriver',
                                    default => 'heroicon-m-question-mark-circle',
                                }"
                                class="font-bold text-sm px-4 py-2 tracking-wide"
                            >
                                {{ match ($table->status) {
                                    'available' => 'Disponible',
                                    'occupied' => 'Ocupada',
                                    'reserved' => 'Reservada',
                                    'prebill' => 'Pre-Cuenta',
                                    'maintenance' => 'Mantenimiento',
                                    default => 'Sin estado',
                                } }}
                            </x-filament::badge>
                        </div>

                        {{-- 🎯 ÁREA DE ICONOS DINÁMICOS - FORMATO ORIGINAL --}}
                        <div class="flex items-center justify-center h-16 bg-white/10 rounded-lg border border-white/20 transition-all duration-300 hover:bg-white/15 mb-4">
                            @if ($table->status === 'available')
                                {{-- Mesa Disponible: Icono de mesa vacía --}}
                                <div class="flex flex-col items-center space-y-2 text-green-200">
                                    <x-filament::icon icon="heroicon-o-squares-plus" class="w-8 h-8 table-icon-available" />
                                    <span class="text-sm font-semibold tracking-wide">Lista para usar</span>
                                </div>
                            @elseif ($table->status === 'occupied')
                                {{-- Mesa Ocupada: Iconos de personas + timer --}}
                                <div class="flex flex-col items-center space-y-1 text-red-200">
                                    <div class="flex items-center space-x-1">
                                        <x-filament::icon icon="heroicon-s-users" class="w-6 h-6 table-icon-occupied" />
                                        <x-filament::icon icon="heroicon-s-clock" class="w-5 h-5" />
                                    </div>
                                    @php
                                        $occupationTime = $table->getOccupationTime();
                                    @endphp
                                    @if ($occupationTime)
                                        <span class="text-sm font-bold bg-red-500/40 px-3 py-1.5 rounded-md animate-pulse tracking-wide">{{ $occupationTime }}</span>
                                    @else
                                        <span class="text-sm font-semibold tracking-wide">En uso</span>
                                    @endif
                                </div>
                            @elseif ($table->status === 'reserved')
                                {{-- Mesa Reservada: Icono de calendario --}}
                                <div class="flex flex-col items-center space-y-2 text-amber-200">
                                    <x-filament::icon icon="heroicon-o-calendar-days" class="w-8 h-8 table-icon-reserved" />
                                    <span class="text-sm font-semibold tracking-wide">Reservada</span>
                                </div>
                            @elseif ($table->status === 'prebill')
                                {{-- Pre-Cuenta: Icono de factura --}}
                                <div class="flex flex-col items-center space-y-2 text-blue-200">
                                    <x-filament::icon icon="heroicon-o-document-text" class="w-8 h-8 table-icon-prebill" />
                                    <span class="text-sm font-semibold tracking-wide">Solicitó cuenta</span>
                                </div>
                            @elseif ($table->status === 'maintenance')
                                {{-- Mantenimiento: Icono de herramientas --}}
                                <div class="flex flex-col items-center space-y-2 text-gray-300">
                                    <x-filament::icon icon="heroicon-o-wrench-screwdriver" class="w-8 h-8 table-icon-maintenance" />
                                    <span class="text-sm font-semibold tracking-wide">Mantenimiento</span>
                                </div>
                            @else
                                {{-- Estado desconocido --}}
                                <div class="flex flex-col items-center space-y-2 text-gray-400">
                                    <x-filament::icon icon="heroicon-o-question-mark-circle" class="w-8 h-8" />
                                    <span class="text-sm font-semibold tracking-wide">Estado desconocido</span>
                                </div>
                            @endif
                        </div>

                        {{-- Información mejorada con UX/UI --}}
                        <div class="space-y-2 text-base font-medium table-info">
                            {{-- Capacidad --}}
                            <div class="flex items-center space-x-3">
                                <x-filament::icon icon="heroicon-o-users" class="w-5 h-5 opacity-80" />
                                <span class="tracking-wide">{{ $table->capacity }} personas</span>
                            </div>

                            {{-- Piso --}}
                            @if ($table->floor)
                                <div class="flex items-center space-x-3">
                                    <x-filament::icon icon="heroicon-o-building-storefront" class="w-5 h-5 opacity-80" />
                                    <span class="tracking-wide">{{ $table->floor->name }}</span>
                                </div>
                            @endif

                            {{-- Ubicación --}}
                            @if ($table->location)
                                <div class="flex items-center space-x-3">
                                    <x-filament::icon icon="heroicon-o-map-pin" class="w-5 h-5 opacity-80" />
                                    <span class="tracking-wide">
                                        {{ match ($table->location) {
                                            'interior' => 'Interior',
                                            'exterior' => 'Exterior',
                                            'terraza' => 'Terraza',
                                            'private' => 'Privado',
                                            'vip' => 'VIP',
                                            'bar' => 'Barra',
                                            default => ucfirst($table->location),
                                        } }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    {{-- Estado vacío con componentes nativos --}}
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <x-filament::icon icon="heroicon-o-table-cells"
                                class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                No hay mesas disponibles
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                @if ($this->statusFilter || $this->locationFilter || $this->searchQuery)
                                    No se encontraron mesas que coincidan con los filtros aplicados.
                                @else
                                    No hay mesas registradas en el sistema.
                                @endif
                            </p>
                            <x-filament::button wire:click="resetFilters" icon="heroicon-o-arrow-path" color="gray"
                                size="sm">
                                Limpiar filtros
                            </x-filament::button>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Footer informativo con contadores --}}
            @if (count($tables) > 0)
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if ($this->statusFilter || $this->floorFilter || $this->capacityFilter)
                                Mostrando {{ count($tables) }} mesas filtradas
                            @else
                                Mostrando todas las {{ count($tables) }} mesas
                            @endif
                        </div>

                        <div class="text-xs text-gray-400 dark:text-gray-500">
                            Actualizado: {{ now()->format('H:i:s') }}
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- 🗺️ Vista Map - Estilo mejorado --}}
        @if ($viewMode === 'map')
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-x-0 gap-y-6 p-6">
                @forelse($tables as $table)
                    @php
                        $mapStyles = match ($table->status) {
                            'available' => [
                                'gradient' => 'from-emerald-500 to-emerald-600 dark:from-emerald-600 dark:to-emerald-700',
                                'border' => 'border-emerald-700 dark:border-emerald-800',
                                'text' => 'text-emerald-900 dark:text-emerald-100',
                                'shadow' => 'hover:shadow-emerald-500/30',
                                'pulse' => 'group-hover:ring-4 group-hover:ring-emerald-500/20',
                            ],
                            'occupied' => [
                                'gradient' => 'from-red-500 to-red-600 dark:from-red-600 dark:to-red-700',
                                'border' => 'border-red-700 dark:border-red-800',
                                'text' => 'text-red-900 dark:text-red-100',
                                'shadow' => 'hover:shadow-red-500/30',
                                'pulse' => 'group-hover:ring-4 group-hover:ring-red-500/20',
                            ],
                            'reserved' => [
                                'gradient' => 'from-amber-400 to-amber-500 dark:from-amber-600 dark:to-amber-700',
                                'border' => 'border-amber-700 dark:border-amber-800',
                                'text' => 'text-amber-900 dark:text-amber-100',
                                'shadow' => 'hover:shadow-amber-500/30',
                                'pulse' => 'group-hover:ring-4 group-hover:ring-amber-500/20',
                            ],
                            'prebill' => [
                                'gradient' => 'from-blue-400 to-blue-500 dark:from-blue-600 dark:to-blue-700',
                                'border' => 'border-blue-700 dark:border-blue-800',
                                'text' => 'text-blue-900 dark:text-blue-100',
                                'shadow' => 'hover:shadow-blue-500/30',
                                'pulse' => 'group-hover:ring-4 group-hover:ring-blue-500/20',
                            ],
                            'maintenance' => [
                                'gradient' => 'from-sky-400 to-sky-500 dark:from-sky-600 dark:to-sky-700',
                                'border' => 'border-sky-700 dark:border-sky-800',
                                'text' => 'text-sky-900 dark:text-sky-100',
                                'shadow' => 'hover:shadow-sky-500/30',
                                'pulse' => 'group-hover:ring-4 group-hover:ring-sky-500/20',
                            ],
                            default => [
                                'gradient' => 'from-emerald-100 to-emerald-200 dark:from-emerald-900 dark:to-emerald-800',
                                'border' => 'border-emerald-300 dark:border-emerald-700',
                                'text' => 'text-emerald-900 dark:text-emerald-100',
                                'shadow' => 'hover:shadow-emerald-500/30',
                                'pulse' => 'group-hover:ring-4 group-hover:ring-emerald-500/20',
                            ],
                        };
                    @endphp

                    <div wire:click="openPOS({{ $table->id }})"
                        class="group cursor-pointer bg-gradient-to-br {{ $mapStyles['gradient'] }} border-2 {{ $mapStyles['border'] }} rounded-2xl p-6 aspect-square flex flex-col items-center justify-center transition-all duration-300 hover:scale-110 hover:shadow-2xl {{ $mapStyles['shadow'] }} {{ $mapStyles['pulse'] }} transform-gpu relative overflow-hidden">
                        {{-- Efecto de ondas en el fondo --}}
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700">
                        </div>

                        {{-- Número de mesa principal --}}
                        <div
                            class="relative z-10 {{ $mapStyles['text'] }} font-black text-3xl group-hover:scale-110 transition-transform duration-300">
                            {{ $table->number ?? str_replace('Mesa ', '', $table->name) }}
                        </div>

                        {{-- Capacidad con icono --}}
                        <div
                            class="relative z-10 flex items-center gap-1 {{ $mapStyles['text'] }} text-sm font-semibold mt-2 opacity-80 group-hover:opacity-100 transition-opacity duration-300">
                            <x-heroicon-m-user-group class="h-4 w-4" />
                            <span>{{ $table->capacity }}p</span>
                        </div>

                        {{-- Forma de la mesa con icono --}}
                        <div
                            class="relative z-10 flex items-center gap-1 {{ $mapStyles['text'] }} text-sm font-semibold mt-2 opacity-80 group-hover:opacity-100 transition-opacity duration-300">
                            <x-filament::icon :icon="match ($table->shape ?? 'square') {
                                'round' => 'heroicon-o-stop',
                                'oval' => 'heroicon-o-stop',
                                default => 'heroicon-o-square-3-stack-3d',
                            }" class="w-4 h-4" />
                        </div>

                        {{-- Indicador de estado en la esquina --}}
                        <div
                            class="absolute top-2 right-2 w-3 h-3 rounded-full {{ match ($table->status) {
                                'available' => 'bg-emerald-500',
                                'occupied' => 'bg-rose-500 animate-pulse',
                                'reserved' => 'bg-amber-500',
                                'prebill' => 'bg-blue-500',
                                'maintenance' => 'bg-sky-500',
                                default => 'bg-gray-500',
                            } }}">
                        </div>

                        {{-- Texto de estado en hover --}}
                        <div
                            class="absolute inset-x-0 bottom-0 bg-black/80 text-white text-xs py-1 px-2 rounded-b-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                            {{ match ($table->status) {
                                'available' => 'Disponible',
                                'occupied' => 'Ocupada',
                                'reserved' => 'Reservada',
                                'prebill' => 'Pre-Cuenta',
                                'maintenance' => 'Mantenimiento',
                                default => 'Sin estado',
                            } }}
                        </div>
                    </div>
                @empty
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <x-heroicon-o-building-storefront class="h-20 w-20 text-gray-300 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No se encontraron mesas
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">Ajusta los filtros o añade nuevas mesas</p>
                        </div>
                    </div>
                @endforelse
            </div>
        @endif
    </x-filament::section>

    {{-- 🎨 Modal de filtros nativo de Filament --}}
    <x-filament::modal id="filters-modal" heading="Filtros Avanzados"
        description="Personaliza la vista del mapa de mesas" width="2xl">
        <div class="space-y-6">
            <div class="space-y-4">
                {{-- Filtro de estado nativo --}}
                <div>
                    <x-filament::fieldset>
                        <x-slot name="label">Estado de Mesa</x-slot>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="statusFilter">
                                <option value="">Todos los estados</option>
                                <option value="available">Disponible</option>
                                <option value="occupied">Ocupada</option>
                                <option value="reserved">Reservada</option>
                                <option value="prebill">Pre-Cuenta</option>
                                <option value="maintenance">Mantenimiento</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </x-filament::fieldset>
                </div>

                {{-- Filtro de ubicación nativo --}}
                <div>
                    <x-filament::fieldset>
                        <x-slot name="label">Ubicación</x-slot>
                        <x-filament::input.wrapper>
                            <x-filament::input.select wire:model.live="locationFilter">
                                <option value="">Todas las ubicaciones</option>
                                <option value="interior">Interior</option>
                                <option value="exterior">Exterior</option>
                                <option value="terraza">Terraza</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </x-filament::fieldset>
                </div>

                {{-- Búsqueda nativa --}}
                <div>
                    <x-filament::fieldset>
                        <x-slot name="label">Búsqueda Rápida</x-slot>
                        <x-filament::input.wrapper>
                            <x-filament::input type="text" wire:model.live.debounce.500ms="searchQuery"
                                placeholder="Buscar mesas..." />
                        </x-filament::input.wrapper>
                    </x-filament::fieldset>
                </div>
            </div>

            {{-- Resumen de filtros activos --}}
            @php
                $activeFilters = collect([$statusFilter, $locationFilter, $searchQuery])
                    ->filter()
                    ->count();
            @endphp

            <div
                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-filament::icon icon="heroicon-m-funnel" class="h-4 w-4" />
                <span>
                    @if ($activeFilters > 0)
                        {{ $activeFilters }} filtro(s) activo(s)
                    @else
                        Sin filtros aplicados
                    @endif
                </span>
            </div>
        </div>

        <x-slot name="footerActions">
            <x-filament::button wire:click="resetFilters" color="gray" size="sm">
                <x-filament::icon icon="heroicon-m-arrow-path" class="h-4 w-4 mr-2" />
                Limpiar Filtros
            </x-filament::button>

            <x-filament::button x-on:click="$dispatch('close-modal', { id: 'filters-modal' })" size="sm">
                Aplicar Filtros
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>

@push('styles')
<style>
    /* =============================
       🎨 OPTIMIZACIÓN VISUAL MAPA DE MESAS
       (Sólo CSS – sin cambios HTML/JS)
       Principios: Proximidad, Similitud, Figura-Fondo, Jerarquía, Whitespace, Accesibilidad
       ============================= */

    /* ---- Variables de estado (permite ajustes centralizados) ---- */
    :root {
        --mesa-bg-available: #4CAF50;   /* Verde accesible */
        --mesa-bg-occupied:  #F44336;   /* Rojo vivo */
        --mesa-bg-reserved:  #FF9800;   /* Ámbar intenso */
        --mesa-bg-prebill:   #1976D2;   /* Azul contraste */
        --mesa-bg-maintenance:#757575;  /* Gris neutro */
        --mesa-fg-light: #FFFFFF;
        --mesa-shadow: 0 2px 6px rgba(0,0,0,0.10);
        --mesa-shadow-hover: 0 4px 12px rgba(0,0,0,0.15);
        --mesa-radius: 12px;
        --mesa-transition: .18s cubic-bezier(.4,.2,.2,1);
        --map-bg: #F5F5F5;
    }

    /* Fondo neutro del contenedor principal (Figura–Fondo) */
    body.dark .fi-main > *, body.dark .fi-section { /* evitar forzar en dark para no romper tema */ }
    .fi-main { background: var(--map-bg); }

    /* Grid de mesas: más respiración horizontal y consistente (Whitespace) */
    .fi-section .grid.grid-cols-1 { /* selector específico para no afectar otras grids */
        padding: 20px 8px 28px 8px; /* padding principal */
        gap: 16px; /* separación base entre mesas */
    }

    /* Tarjeta base (Similitud) */
    .table-card-original {
        width: 100%;
        max-width: 180px; /* tamaño consistente */
        min-height: 150px;
        border-radius: var(--mesa-radius) !important;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        box-shadow: var(--mesa-shadow);
        outline: 0 solid transparent;
        transition: transform var(--mesa-transition), box-shadow var(--mesa-transition), outline var(--mesa-transition), background-color var(--mesa-transition);
        isolation: isolate; /* asegura overlays correctos */
    }

    /* Hover Feedback (Feedback Visual) */
    .table-card-original:hover {
        transform: scale(1.05);
        box-shadow: var(--mesa-shadow-hover);
    }

    /* Estado activo / seleccionado (requiere que futuro HTML añada .is-active o data-selected) */
    .table-card-original.is-active,
    .table-card-original[data-selected="true"],
    .table-card-original:focus-visible {
        outline: 3px solid #2196F3;
        outline-offset: 2px;
    }

    /* Normaliza tipografía interna */
    .table-card-original span, .table-card-original p { line-height: 1.15; }

    /* Número de mesa (Jerarquía Visual) */
    .table-card-original .table-number {
        font-size: 14px !important;
        font-weight: 600 !important;
        padding: 2px 8px;
        border-radius: 20px;
        background: rgba(0,0,0,0.70);
        color: #fff !important;
        display: inline-block;
        letter-spacing: .5px;
    }

    /* Badge de estado: refuerzo tipográfico + icono (Accesibilidad) */
    .table-card-original .fi-badge { /* componente Filament */
        font-weight: 600;
        letter-spacing: .25px;
        backdrop-filter: saturate(140%) brightness(1.05);
    }

    /* Colores por estado (override de inline styles con !important) */
    /* Usamos subconjunto de tonalidades ya definidas asegurando contraste >= 4.5:1 */
    .table-card-original[style*='available'] { background: var(--mesa-bg-available) !important; color: var(--mesa-fg-light) !important; }
    .table-card-original[style*='occupied']  { background: var(--mesa-bg-occupied) !important; color: var(--mesa-fg-light) !important; }
    .table-card-original[style*='reserved']  { background: var(--mesa-bg-reserved) !important; color: var(--mesa-fg-light) !important; }
    .table-card-original[style*='prebill']   { background: var(--mesa-bg-prebill) !important; color: var(--mesa-fg-light) !important; }
    .table-card-original[style*='maintenance'] { background: var(--mesa-bg-maintenance) !important; color: var(--mesa-fg-light) !important; }

    /* Overlay sutil para reforzar figura sobre fondo claro (Figura–Fondo) */
    .table-card-original::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: inherit;
        background: linear-gradient(145deg, rgba(255,255,255,0.06), rgba(255,255,255,0.0));
        pointer-events: none;
        mix-blend-mode: overlay;
    }

    /* Zonas / Agrupaciones (Proximidad)
       NOTA: Sin clases de zona en HTML actual, se sugiere futura clase por ubicación.
       Por ahora sólo reforzamos separación vertical adicional cada 5 elementos para simular bloques. */
    .fi-section .grid.grid-cols-1 > *:nth-child(5n+1) { margin-top: 24px; }
    .fi-section .grid.grid-cols-1 > *:first-child { margin-top: 0; }

    /* Encabezados / Títulos (si se agregan zonas en futuro) */
    .table-zone-title { /* clase futura */
        font-weight: 600;
        color: #333;
        text-transform: uppercase;
        padding: 0 4px 8px 4px;
        border-bottom: 1px solid #E0E0E0;
        margin-bottom: 12px;
        letter-spacing: .5px;
        font-size: 12px;
    }

    /* Responsividad: pantallas pequeñas */
    @media (max-width: 640px) {
        .table-card-original {
            max-width: 140px;
            min-height: 120px;
            padding: 14px !important;
        }
        .table-card-original .table-number { font-size: 13px !important; }
        .fi-section .grid.grid-cols-1 { gap: 12px; padding: 16px 4px 24px; }
    }

    @media (max-width: 420px) {
        .table-card-original { max-width: 120px; min-height: 110px; }
        .table-card-original .table-number { font-size: 12px !important; }
    }

    /* Transiciones suaves para badges e iconos */
    .table-card-original .fi-badge, .table-card-original svg { transition: opacity var(--mesa-transition), transform var(--mesa-transition); }
    .table-card-original:hover .fi-badge { transform: translateY(-2px); }

    /* Eliminar bordes innecesarios heredados */
    .table-card-original { border: none !important; }
    .table-card-original [class*='border-'] { /* no afectar badges específicos */ }

    /* Soporte para usuarios con reducción de movimiento */
    @media (prefers-reduced-motion: reduce) {
        .table-card-original, .table-card-original:hover { transition: none; transform: none !important; }
    }

    /* Accesibilidad: focus visible claro */
    .table-card-original:focus-visible { box-shadow: 0 0 0 3px #2196F3, var(--mesa-shadow-hover); }

    /* Refuerzo semántico: iconos por estado (usando outline overlay) */
    .table-card-original[data-selected='true']::before { content: '✔'; position: absolute; top: 6px; right: 8px; font-size: 16px; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,.5); }
    /* Placeholder para potencial estado de limpieza futuro (si se añade clase .cleaning) */
    .table-card-original.cleaning { background: #FFC107 !important; color: #212121 !important; }
    .table-card-original.cleaning::before { content: '🧹'; position: absolute; top: 6px; right: 8px; }

    /* Ajuste de contraste en modo oscuro (si Filament aplica dark) */
    .dark .fi-main { background: #1e1e1e; }
    .dark .table-card-original[style*='available'] { background: #2E7D32 !important; }
    .dark .table-card-original[style*='occupied'] { background: #C62828 !important; }
    .dark .table-card-original[style*='reserved'] { background: #EF6C00 !important; }
    .dark .table-card-original[style*='prebill'] { background: #1565C0 !important; }
    .dark .table-card-original[style*='maintenance'] { background: #616161 !important; }
</style>
@endpush
  {{-- Resumen de filtros activos --}}
