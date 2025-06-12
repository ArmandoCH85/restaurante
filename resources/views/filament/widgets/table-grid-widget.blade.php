{{-- Solo mostrar cuando esté en modo grid --}}
@if($this->viewMode === 'grid')
    <x-filament-widgets::widget>
        <x-filament::section>
            <x-slot:heading>
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Mesas del Restaurante
                    </h3>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Total: {{ $this->getTables()->count() }} mesas
                    </div>
                </div>
            </x-slot:heading>

            {{-- Grid responsivo de mesas - ANCHO COMPLETO NATIVO --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 2xl:grid-cols-8 gap-4">
                @forelse($this->getTables() as $table)
                    <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-shadow duration-200">
                        {{-- Header de la mesa --}}
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center space-x-2">
                                {{-- Número de mesa --}}
                                <div class="flex items-center justify-center w-8 h-8 bg-gray-100 dark:bg-gray-800 rounded-full">
                                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        #{{ $table->number }}
                                    </span>
                                </div>

                                {{-- Forma de la mesa --}}
                                <x-filament::icon
                                    :icon="$this->getShapeIcon($table->shape)"
                                    class="w-4 h-4 text-gray-400"
                                />
                            </div>

                            {{-- Badge de estado --}}
                            <x-filament::badge
                                :color="$this->getStatusColor($table->status)"
                                size="sm"
                            >
                                {{ $this->getStatusLabel($table->status) }}
                            </x-filament::badge>
                        </div>

                        {{-- Información de la mesa --}}
                        <div class="space-y-2">
                            {{-- Capacidad --}}
                            <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                <x-filament::icon
                                    icon="heroicon-o-users"
                                    class="w-4 h-4"
                                />
                                <span>{{ $table->capacity }} personas</span>
                            </div>

                            {{-- Piso --}}
                            @if($table->floor)
                                <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-filament::icon
                                        icon="heroicon-o-building-storefront"
                                        class="w-4 h-4"
                                    />
                                    <span>{{ $table->floor->name }}</span>
                                </div>
                            @endif

                            {{-- Ubicación --}}
                            @if($table->location)
                                <div class="flex items-center space-x-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-filament::icon
                                        icon="heroicon-o-map-pin"
                                        class="w-4 h-4"
                                    />
                                    <span>
                                        {{ match($table->location) {
                                            'interior' => 'Interior',
                                            'exterior' => 'Exterior',
                                            'terraza' => 'Terraza',
                                            'private' => 'Privado',
                                            'vip' => 'VIP',
                                            'bar' => 'Barra',
                                            default => ucfirst($table->location)
                                        } }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        {{-- Acciones (para próximas implementaciones) --}}
                        <div class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-center">
                                <x-filament::icon
                                    :icon="$this->getStatusIcon($table->status)"
                                    :class="'w-5 h-5 ' . match($table->status) {
                                        'available' => 'text-green-500',
                                        'occupied' => 'text-red-500',
                                        'reserved' => 'text-yellow-500',
                                        'maintenance' => 'text-gray-500',
                                        default => 'text-gray-400'
                                    }"
                                />
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Estado vacío --}}
                    <div class="col-span-full">
                        <div class="text-center py-12">
                            <x-filament::icon
                                icon="heroicon-o-table-cells"
                                class="w-12 h-12 text-gray-400 mx-auto mb-4"
                            />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                No hay mesas disponibles
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400">
                                @if($this->statusFilter || $this->floorFilter || $this->capacityFilter)
                                    No se encontraron mesas que coincidan con los filtros aplicados.
                                @else
                                    No hay mesas registradas en el sistema.
                                @endif
                            </p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Footer con información adicional --}}
            @if($this->getTables()->count() > 0)
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            @if($this->statusFilter || $this->floorFilter || $this->capacityFilter)
                                Mostrando {{ $this->getTables()->count() }} mesas filtradas
                            @else
                                Mostrando todas las mesas
                            @endif
                        </div>

                        <div class="text-xs text-gray-400 dark:text-gray-500">
                            Actualizado: {{ now()->format('H:i:s') }}
                        </div>
                    </div>
                </div>
            @endif
        </x-filament::section>
    </x-filament-widgets::widget>
@endif
