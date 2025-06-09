{{-- SIEMPRE tiene un tag raíz para Livewire --}}
<div>
    @if($this->viewMode === 'layout')
        <x-filament-widgets::widget>
            <x-filament::section>
            <x-slot:heading>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Mapa del Restaurante
                    </h3>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Total: {{ $this->getTables()->count() }} mesas
                    </div>
                </div>
            </x-slot:heading>

            {{-- Área del mapa/layout del restaurante --}}
            <div class="relative bg-gray-50 dark:bg-gray-800 rounded-lg border-2 border-dashed border-gray-200 dark:border-gray-700 min-h-[500px] overflow-auto">
                {{-- Simulación de layout del restaurante --}}
                @forelse($this->getMapData() as $table)
                    <div
                        class="absolute cursor-pointer transform transition-all duration-200 hover:scale-110 hover:z-10"
                        style="left: {{ ($table['x'] ?? (($loop->index % 8) * 120 + 20)) }}px; top: {{ ($table['y'] ?? (floor($loop->index / 8) * 100 + 20)) }}px;"
                        title="Mesa {{ $table['number'] }} - {{ ucfirst($table['status']) }}"
                    >
                        {{-- Mesa visual --}}
                        <div class="flex flex-col items-center space-y-1">
                            {{-- Forma de la mesa --}}
                            <div class="
                                w-16 h-16 flex items-center justify-center text-white font-bold text-sm border-2
                                @if($table['shape'] === 'round') rounded-full @else rounded-lg @endif
                                @switch($table['status'])
                                    @case('available')
                                        bg-green-500 border-green-600
                                        @break
                                    @case('occupied')
                                        bg-red-500 border-red-600
                                        @break
                                    @case('reserved')
                                        bg-yellow-500 border-yellow-600
                                        @break
                                    @case('maintenance')
                                        bg-gray-500 border-gray-600
                                        @break
                                    @default
                                        bg-gray-400 border-gray-500
                                @endswitch
                                shadow-lg hover:shadow-xl
                            ">
                                {{ $table['number'] }}
                            </div>

                            {{-- Información de la mesa --}}
                            <div class="text-center">
                                <div class="text-xs font-medium text-gray-700 dark:text-gray-300">
                                    {{ $table['capacity'] }} pers.
                                </div>
                                @if($table['status'] === 'occupied' && $table['occupied_at'])
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($table['occupied_at'])->diffForHumans() }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Estado vacío --}}
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                No hay mesas que mostrar
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                Ajusta los filtros para ver las mesas del restaurante
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Leyenda del mapa --}}
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Leyenda</h4>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-green-500 rounded border border-green-600"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Disponible</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-red-500 rounded border border-red-600"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Ocupada</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-yellow-500 rounded border border-yellow-600"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Reservada</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-4 h-4 bg-gray-500 rounded border border-gray-600"></div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Mantenimiento</span>
                    </div>
                </div>

                {{-- Estadísticas en tiempo real --}}
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($this->getStats() as $key => $value)
                            <div class="text-center">
                                <div class="text-lg font-bold text-gray-900 dark:text-white">{{ $value }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $key) }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Footer con información de actualización --}}
            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 text-center">
                <div class="flex items-center justify-center space-x-2">
                    <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Actualización automática cada 5 segundos</span>
                </div>
            </div>
            </x-filament::section>
        </x-filament-widgets::widget>
    @endif
</div>
