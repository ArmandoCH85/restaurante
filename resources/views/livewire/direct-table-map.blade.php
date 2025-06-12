<div wire:key="mapa-tabla-directo-root">
    <!-- Estructura principal del mapa de mesas - Diseño de ancho completo -->
    <div class="relative">
        <!-- Header con estadísticas y controles -->
        <div class="fi-card rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 mb-4">
            <!-- Estadísticas horizontales -->
            <div class="flex flex-wrap gap-4 mb-4">
                <!-- Disponibles -->
                <div class="fi-ta-stats-card grow basis-auto rounded-lg border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/20 relative overflow-hidden" style="flex: 0 1 calc(25% - 12px); min-width: 140px;">
                    <div class="flex items-center gap-3 p-3">
                        <svg class="fi-ta-stats-card-icon h-6 w-6 text-success-500 dark:text-success-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block">Disponibles</span>
                            <span class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $availableCount }}</span>
                        </div>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-success-500/20"></div>
                </div>

                <!-- Ocupadas -->
                <div class="fi-ta-stats-card grow basis-auto rounded-lg border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/20 relative overflow-hidden" style="flex: 0 1 calc(25% - 12px); min-width: 140px;">
                    <div class="flex items-center gap-3 p-3">
                        <svg class="fi-ta-stats-card-icon h-6 w-6 text-danger-500 dark:text-danger-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block">Ocupadas</span>
                            <span class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $occupiedCount }}</span>
                        </div>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-danger-500/20"></div>
                </div>

                <!-- Reservadas -->
                <div class="fi-ta-stats-card grow basis-auto rounded-lg border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/20 relative overflow-hidden" style="flex: 0 1 calc(25% - 12px); min-width: 140px;">
                    <div class="flex items-center gap-3 p-3">
                        <svg class="fi-ta-stats-card-icon h-6 w-6 text-warning-500 dark:text-warning-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block">Reservadas</span>
                            <span class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $reservedCount }}</span>
                        </div>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-warning-500/20"></div>
                </div>

                <!-- Total -->
                <div class="fi-ta-stats-card grow basis-auto rounded-lg border border-gray-200 bg-white shadow-sm ring-1 ring-gray-950/5 dark:border-white/10 dark:bg-gray-900 dark:ring-white/20 relative overflow-hidden" style="flex: 0 1 calc(25% - 12px); min-width: 140px;">
                    <div class="flex items-center gap-3 p-3">
                        <svg class="fi-ta-stats-card-icon h-6 w-6 text-primary-500 dark:text-primary-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <div>
                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400 block">Total</span>
                            <span class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $tables->count() }}</span>
                        </div>
                    </div>
                    <div class="absolute inset-x-0 bottom-0 h-1 bg-primary-500/20"></div>
                </div>
            </div>

            <!-- Controles: Botones agrupados con mejor UX/UI -->
            <div class="flex justify-end items-center">
                <div class="flex items-center bg-gray-50 dark:bg-gray-800 rounded-lg p-1 gap-1">
                    <!-- Botón Cuadrícula -->
                    <button wire:click="$set('viewMode', 'grid')"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $viewMode === 'grid' ? 'bg-white text-primary-600 shadow-sm ring-1 ring-primary-200 dark:bg-gray-700 dark:text-primary-400' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700/50' }}">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span>Cuadrícula</span>
                    </button>

                    <!-- Botón Mapa -->
                    <button wire:click="$set('viewMode', 'map')"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $viewMode === 'map' ? 'bg-white text-primary-600 shadow-sm ring-1 ring-primary-200 dark:bg-gray-700 dark:text-primary-400' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700/50' }}">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                        </svg>
                        <span>Mapa</span>
                    </button>

                    <!-- Separador visual -->
                    <div class="w-px h-6 bg-gray-300 dark:bg-gray-600 mx-1"></div>

                    <!-- Botón Filtros -->
                    <button wire:click="toggleFilters"
                            class="flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md transition-all duration-200 {{ $filtersOpen ? 'bg-white text-primary-600 shadow-sm ring-1 ring-primary-200 dark:bg-gray-700 dark:text-primary-400' : 'text-gray-600 hover:text-gray-900 hover:bg-white/50 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-700/50' }}">
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.707A1 1 0 013 7V4z" />
                        </svg>
                        <span>Filtros</span>
                        @if($statusFilter || $floorFilter || $capacityFilter)
                            <span class="bg-primary-600 text-white text-xs px-1.5 py-0.5 rounded-full min-w-[1.25rem] h-5 flex items-center justify-center">
                                {{ collect([$statusFilter, $floorFilter, $capacityFilter])->filter()->count() }}
                            </span>
                        @endif
                    </button>
                </div>
            </div>
        </div>

        <!-- Panel principal de mesas -->
        <div class="fi-card rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                                    @if($viewMode === 'grid')
                <!-- Vista de cuadrícula simplificada -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4"
                     {{-- TEMPORALMENTE DESHABILITADO: wire:poll.5s="refreshData" --}}
                    @forelse($tables as $table)
                        @php
                            $config = match($table->status) {
                                'available' => [
                                    'bg' => 'bg-emerald-50 border-emerald-200 hover:bg-emerald-100',
                                    'text' => 'text-emerald-800',
                                    'status' => 'DISPONIBLE'
                                ],
                                'occupied' => [
                                    'bg' => 'bg-red-50 border-red-200 hover:bg-red-100',
                                    'text' => 'text-red-800',
                                    'status' => 'OCUPADA'
                                ],
                                'reserved' => [
                                    'bg' => 'bg-amber-50 border-amber-200 hover:bg-amber-100',
                                    'text' => 'text-amber-800',
                                    'status' => 'RESERVADA'
                                ],
                                default => [
                                    'bg' => 'bg-gray-50 border-gray-200 hover:bg-gray-100',
                                    'text' => 'text-gray-800',
                                    'status' => 'SIN ESTADO'
                                ]
                            };
                        @endphp

                        <div wire:click="goToPos({{ $table->id }})"
                             wire:key="table-{{ $table->id }}-{{ now()->timestamp }}"
                             class="cursor-pointer p-4 rounded-xl border-2 {{ $config['bg'] }} hover:scale-105 transition-all duration-200 shadow-sm hover:shadow-md">

                            <!-- Nombre de Mesa -->
                            <div class="text-center mb-3">
                                <h3 class="text-xl font-bold text-gray-900">{{ $table->name }}</h3>
                            </div>

                            <!-- Estado -->
                            <div class="text-center mb-3">
                                <span class="text-sm font-bold {{ $config['text'] }}">{{ $config['status'] }}</span>
                            </div>

                            <!-- Capacidad -->
                            <div class="text-center mb-2">
                                <span class="text-sm text-gray-600">{{ $table->capacity }} personas</span>
                            </div>

                            <!-- Ubicación -->
                            <div class="text-center">
                                <span class="text-xs text-gray-500">
                                    @if(str_contains(strtolower($table->location ?? ''), 'exterior'))
                                        Exterior
                                    @else
                                        Interior
                                    @endif
                                </span>
                            </div>
                        </div>
                            @empty
                                <div class="col-span-full">
                                    <div class="text-center py-8">
                                        <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 text-gray-500 mb-4">
                                            <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </div>
                                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-1">No se encontraron mesas</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Intenta ajustar los filtros o añade nuevas mesas desde el panel de administración.</p>
                                        <button
                                            wire:click="resetFilters"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest bg-primary-600 hover:bg-primary-700 active:bg-primary-700 focus:outline-none focus:border-primary-700 focus:ring focus:ring-primary-200 disabled:opacity-25 transition"
                                        >
                                            <svg class="-ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                            Limpiar filtros
                                        </button>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                        @elseif($viewMode === 'map')
                <!-- Vista de mapa - Solo tarjetas ordenadas -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-4 p-6">
                    @forelse($tables as $table)
                        @php
                            $mapConfig = match($table->status) {
                                'available' => [
                                    'bg' => 'bg-emerald-100 hover:bg-emerald-200 border-emerald-300',
                                    'text' => 'text-emerald-900'
                                ],
                                'occupied' => [
                                    'bg' => 'bg-red-100 hover:bg-red-200 border-red-300',
                                    'text' => 'text-red-900'
                                ],
                                'reserved' => [
                                    'bg' => 'bg-amber-100 hover:bg-amber-200 border-amber-300',
                                    'text' => 'text-amber-900'
                                ],
                                default => [
                                    'bg' => 'bg-gray-100 hover:bg-gray-200 border-gray-300',
                                    'text' => 'text-gray-900'
                                ]
                            };
                        @endphp

                        <div
                            wire:click="goToPos({{ $table->id }})"
                            class="cursor-pointer group rounded-lg border-2 {{ $mapConfig['bg'] }} shadow-md hover:shadow-lg transform hover:scale-105 transition-all duration-200 ease-out flex flex-col items-center justify-center p-4 aspect-square {{ $mapConfig['text'] }}"
                        >
                            <div class="font-black text-2xl">{{ $table->number ?? str_replace('Mesa ', '', $table->name) }}</div>
                            <div class="text-sm font-medium opacity-90 mt-1">{{ $table->capacity }}p</div>
                        </div>
                    @empty
                        <div class="col-span-full text-center py-8">
                            <p class="text-gray-500">No se encontraron mesas</p>
                        </div>
                    @endforelse
                </div>
            @endif
        </div>

        <!-- Panel de filtros deslizable desde la derecha -->
        @if($filtersOpen)
            <!-- Overlay -->
            <div class="fixed inset-0 bg-black bg-opacity-50 z-40" wire:click="toggleFilters"></div>

            <!-- Panel deslizable -->
            <div class="fixed right-0 top-0 h-full w-80 bg-white dark:bg-gray-800 shadow-xl z-50 transform transition-transform duration-300 ease-in-out">
                <!-- Header del panel -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Filtros</h3>
                    <button wire:click="toggleFilters" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Contenido del panel -->
                <div class="p-4 space-y-6">
                    <!-- Estado -->
                    <div class="fi-form-field">
                        <label class="fi-label inline-flex items-center gap-x-2 text-sm font-medium leading-6 text-gray-950 dark:text-white mb-2">
                            Estado de las mesas
                        </label>
                        <select wire:model.live="statusFilter" class="fi-select block w-full rounded-lg border-0 bg-white py-2 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-gray-900 dark:text-white dark:ring-gray-700 dark:focus:ring-primary-500">
                            <option value="">Todas las mesas</option>
                            <option value="available">Disponibles</option>
                            <option value="occupied">Ocupadas</option>
                            <option value="reserved">Reservadas</option>
                        </select>
                    </div>

                    <!-- Piso -->
                    <div class="fi-form-field">
                        <label class="fi-label inline-flex items-center gap-x-2 text-sm font-medium leading-6 text-gray-950 dark:text-white mb-2">
                            Piso
                        </label>
                        <select wire:model.live="floorFilter" class="fi-select block w-full rounded-lg border-0 bg-white py-2 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-gray-900 dark:text-white dark:ring-gray-700 dark:focus:ring-primary-500">
                            <option value="">Todos los pisos</option>
                            @foreach($floors as $floor)
                                <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Capacidad -->
                    <div class="fi-form-field">
                        <label class="fi-label inline-flex items-center gap-x-2 text-sm font-medium leading-6 text-gray-950 dark:text-white mb-2">
                            Capacidad mínima
                        </label>
                        <input wire:model.live="capacityFilter" type="number" min="1" placeholder="Ej: 4" class="fi-input block w-full rounded-lg border-0 bg-white py-2 px-3 text-sm text-gray-950 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-primary-600 dark:bg-gray-900 dark:text-white dark:ring-gray-700">
                    </div>

                    <!-- Botones de acción -->
                    <div class="space-y-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <!-- Limpiar filtros -->
                        <button wire:click="resetFilters" class="w-full fi-btn fi-btn-color-gray rounded-lg bg-gray-100 hover:bg-gray-200 px-4 py-2 text-sm font-medium text-gray-700 transition-colors dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                            <svg class="h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Limpiar filtros
                        </button>

                        <!-- Reiniciar todas las mesas -->
                        <button wire:click="resetAllTables"
                                wire:confirm="¿Estás seguro de que quieres liberar TODAS las mesas? Esta acción no se puede deshacer."
                                class="w-full fi-btn fi-btn-color-danger rounded-lg bg-red-600 hover:bg-red-700 px-4 py-2 text-sm font-medium text-white shadow-sm ring-1 ring-red-600 transition duration-75 hover:ring-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:bg-red-500 dark:hover:bg-red-600">
                            <svg class="h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Reiniciar Todas las Mesas
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
