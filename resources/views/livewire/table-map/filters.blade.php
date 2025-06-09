<div class="bg-white rounded-xl shadow-sm p-4 dark:bg-gray-800">
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-medium">Filtros</h2>
        <button
            type="button"
            class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 flex items-center"
            x-data="{}"
            x-on:click="$dispatch('toggle-filters')"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <span x-text="$store.filters.expanded ? 'Ocultar' : 'Mostrar'">Mostrar</span>
        </button>
    </div>
    
    <div class="space-y-3" x-data="{ expanded: true }" x-show="expanded" x-on:toggle-filters.window="expanded = !expanded" x-transition>
        <div class="flex flex-wrap items-center gap-2 mb-2">
            {{-- Filtro rápido por estado con chips de colores --}}
            <button 
                wire:click="$set('statusFilter', '')" 
                class="px-2 py-1 rounded-full text-xs flex items-center gap-1 transition-colors {{ !$statusFilter ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
            >
                <span class="inline-block w-2 h-2 rounded-full bg-blue-500"></span>
                Todos
            </button>
            <button 
                wire:click="$set('statusFilter', 'available')" 
                class="px-2 py-1 rounded-full text-xs flex items-center gap-1 transition-colors {{ $statusFilter === 'available' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
            >
                <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                Disponibles
            </button>
            <button 
                wire:click="$set('statusFilter', 'occupied')" 
                class="px-2 py-1 rounded-full text-xs flex items-center gap-1 transition-colors {{ $statusFilter === 'occupied' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
            >
                <span class="inline-block w-2 h-2 rounded-full bg-red-500"></span>
                Ocupadas
            </button>
            <button 
                wire:click="$set('statusFilter', 'reserved')" 
                class="px-2 py-1 rounded-full text-xs flex items-center gap-1 transition-colors {{ $statusFilter === 'reserved' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}"
            >
                <span class="inline-block w-2 h-2 rounded-full bg-yellow-500"></span>
                Reservadas
            </button>
        </div>

        <div class="flex flex-wrap gap-3">
            {{-- Campo de búsqueda inline (siempre visible) --}}
            <div class="relative flex-grow min-w-[200px]">
                <input 
                    type="search" 
                    wire:model.live.debounce.300ms="searchQuery" 
                    id="searchQuery"
                    placeholder="Buscar mesa..." 
                    class="w-full pl-8 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm"
                />
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 absolute left-2.5 top-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            
            {{-- Selectores compactos --}}
            <div class="flex gap-2 flex-wrap">
                <select 
                    wire:model.live="locationFilter" 
                    class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-xs py-1.5"
                    title="Ubicación"
                >
                    <option value="">Ubicación</option>
                    @foreach($locationOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                
                <select 
                    wire:model.live="floorFilter" 
                    class="rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-xs py-1.5"
                    title="Piso"
                >
                    <option value="">Piso</option>
                    @foreach($floors as $floor)
                        <option value="{{ $floor->id }}">{{ $floor->name }}</option>
                    @endforeach
                </select>
                
                @if($statusFilter || $locationFilter || $floorFilter || $searchQuery)
                    <button 
                        wire:click="clearFilters" 
                        class="px-2 py-1 text-xs flex items-center gap-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                        title="Limpiar todos los filtros"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Limpiar
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>

    {{-- Opciones adicionales --}}
    <div class="mt-4 flex flex-wrap gap-4 items-center">
        <label class="flex items-center gap-2">
            <x-filament::input 
                type="checkbox" 
                wire:model.live="showTodayReservations"
            />
            <span class="text-sm">Solo mesas con reservas hoy</span>
        </label>

        <x-filament::button 
            type="button" 
            color="gray" 
            size="sm" 
            wire:click="resetFilters" 
            icon="heroicon-o-x-mark"
        >
            Limpiar filtros
        </x-filament::button>
    </div>
</div>
