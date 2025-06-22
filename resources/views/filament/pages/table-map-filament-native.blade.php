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
            {{-- Grid responsivo optimizado según las mejores prácticas de Filament --}}
            {{-- TEMPORALMENTE DESHABILITADO: wire:poll.5s --}}
            <div
                class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-8 gap-4">
                @forelse($tables as $table)
                    @php
                        $style = match($table->status) {
                            'available' => 'background-color: #d1fae5 !important; color: #065f46 !important; border: 1px solid #6ee7b7 !important;',
                            'occupied'  => 'background-color: #fee2e2 !important; color: #991b1b !important; border: 1px solid #fca5a5 !important;',
                            'reserved'  => 'background-color: #fef3c7 !important; color: #92400e !important; border: 1px solid #fcd34d !important;',
                            default     => 'background-color: #f3f4f6 !important; color: #1f2937 !important; border: 1px solid #d1d5db !important;',
                        };
                    @endphp
                    <div wire:click="openPOS({{ $table->id }})" wire:key="table-{{ $table->id }}"
                        class="rounded-lg p-4 hover:shadow-md transition-shadow duration-200 cursor-pointer"
                        style="{{ $style }}">
                        {{-- Header de la mesa con componentes nativos --}}
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                {{-- Número de mesa destacado --}}
                                <div
                                    class="flex items-center justify-center w-12 h-12 bg-black/10 rounded-lg">
                                    <span class="text-xl font-bold">
                                        #{{ $table->number ?? str_replace('Mesa ', '', $table->name) }}
                                    </span>
                                </div>

                                {{-- Forma de la mesa con icono nativo --}}
                                <x-filament::icon :icon="match ($table->shape ?? 'square') {
                                    'round' => 'heroicon-o-stop',
                                    'oval' => 'heroicon-o-stop',
                                    default => 'heroicon-o-square-3-stack-3d',
                                }" class="w-5 h-5 opacity-60" />
                            </div>

                            {{-- Badge de estado nativo (tamaño grande + icono) --}}
                            <x-filament::badge
                                :color="match ($table->status) {
                                    'available' => 'success',
                                    'occupied' => 'danger',
                                    'reserved' => 'warning',
                                    'maintenance' => 'gray',
                                    default => 'gray',
                                }"
                                :icon="match ($table->status) {
                                    'available' => 'heroicon-m-check-circle',
                                    'occupied' => 'heroicon-m-users',
                                    'reserved' => 'heroicon-m-clock',
                                    'maintenance' => 'heroicon-m-wrench-screwdriver',
                                    default => 'heroicon-m-question-mark-circle',
                                }"
                                size="lg"
                                class="font-bold tracking-wide px-3 py-1.5"
                            >
                                {{ match ($table->status) {
                                    'available' => 'Disponible',
                                    'occupied' => 'Ocupada',
                                    'reserved' => 'Reservada',
                                    'maintenance' => 'Mantenimiento',
                                    default => 'Sin estado',
                                } }}
                            </x-filament::badge>
                        </div>

                        {{-- Información de la mesa con iconos nativos --}}
                        <div class="space-y-2.5">
                            {{-- Capacidad --}}
                            <div class="flex items-center space-x-2 text-sm font-medium opacity-90">
                                <x-filament::icon icon="heroicon-o-users" class="w-5 h-5" />
                                <span>{{ $table->capacity }} personas</span>
                            </div>

                            {{-- Piso --}}
                            @if ($table->floor)
                                <div class="flex items-center space-x-2 text-sm font-medium opacity-90">
                                    <x-filament::icon icon="heroicon-o-building-storefront" class="w-5 h-5" />
                                    <span>{{ $table->floor->name }}</span>
                                </div>
                            @endif

                            {{-- Ubicación --}}
                            @if ($table->location)
                                <div class="flex items-center space-x-2 text-sm font-medium opacity-90">
                                    <x-filament::icon icon="heroicon-o-map-pin" class="w-5 h-5" />
                                    <span>
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
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-6 p-6">
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
  {{-- Resumen de filtros activos --}}
