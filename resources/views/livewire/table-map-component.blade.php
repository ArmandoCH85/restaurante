<div>
<div class="space-y-4 relative" x-data="{}"
    x-init="
        $nextTick(() => {
            Alpine.store('filters', {
                expanded: false // Inicialmente los filtros estarán contraídos
            });
        })
    ">
    {{-- Estructura basada en proporción áurea optimizada (75% : 25%) para mayor visibilidad del mapa --}}
    <div class="grid grid-cols-12 gap-4">
        {{-- Panel izquierdo (filtros y controls) - más compacto para dar prioridad al mapa --}}
        <div class="col-span-12 lg:col-span-3 space-y-3">
            @include('livewire.table-map.filters')
            @include('livewire.table-map.floor-selector')
        </div>

        {{-- Panel central (visualización de mesas) - Ahora con 75% del espacio --}}
        <div class="col-span-12 lg:col-span-9 space-y-3">
            <div class="bg-white rounded-xl shadow-sm p-4 dark:bg-gray-800">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-medium">{{ $viewMode === 'grid' ? 'Vista de Cuadrícula' : 'Vista de Diseño' }}</h2>
                    <div class="flex space-x-2">
                        <x-filament::button
                            type="button"
                            color="{{ $viewMode === 'grid' ? 'primary' : 'gray' }}"
                            size="sm"
                            wire:click="toggleViewMode"
                            icon="heroicon-o-squares-2x2"
                        >
                            Cuadrícula
                        </x-filament::button>
                        
                        <x-filament::button
                            type="button"
                            color="{{ $viewMode === 'layout' ? 'primary' : 'gray' }}"
                            size="sm"
                            wire:click="toggleViewMode"
                            icon="heroicon-o-map"
                        >
                            Mapa
                        </x-filament::button>
                        
                        @if ($viewMode === 'layout')
                            <x-filament::button
                                type="button"
                                color="{{ $isEditingLayout ? 'warning' : 'gray' }}"
                                size="sm"
                                wire:click="toggleEditLayout"
                                icon="heroicon-o-pencil"
                            >
                                {{ $isEditingLayout ? 'Guardar' : 'Editar' }}
                            </x-filament::button>
                            
                            <x-filament::button
                                type="button"
                                color="danger"
                                size="sm"
                                wire:click="resetLayout"
                                icon="heroicon-o-arrow-path"
                            >
                                Reiniciar
                            </x-filament::button>
                        @endif
                    </div>
                </div>
                
                <div id="table-map-container" class="relative min-h-[400px] border border-gray-200 dark:border-gray-700 rounded-lg p-2">
                    @if ($viewMode === 'grid')
                        @include('livewire.table-map.grid-view')
                    @else
                        @include('livewire.table-map.layout-view')
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    {{-- Panel inferior (detalles de mesa seleccionada) --}}
    <div class="w-full">
        @if($selectedTable)
            @include('livewire.table-map.selected-table-details')
        @endif
    </div>
    
    {{-- Modals --}}
    @include('livewire.table-map.modals')
</div>

@script
<script>
    // Alpine.js para interacciones básicas del lado del cliente
    document.addEventListener('alpine:init', () => {
        Alpine.data('tableDraggable', () => ({
            init() {
                if (!this.isEditingLayout) return;
                
                // Aquí se podría inicializar una librería de arrastrar y soltar como interact.js
                // para permitir mover las mesas en el modo de diseño
            }
        }));
    });
</script>
@endscript
</div>
