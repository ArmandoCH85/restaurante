<div class="bg-white rounded-xl shadow-sm p-4 dark:bg-gray-800">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-lg font-medium">Estadísticas</h2>
        @if($statusFilter || $locationFilter || $floorFilter || $searchQuery)
        <button wire:click="clearFilters" class="text-sm text-blue-600 hover:text-blue-800 flex items-center transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Limpiar filtros
        </button>
        @endif
    </div>

    <div class="flex overflow-x-auto pb-2 space-x-3 mb-4">
        {{-- Estadísticas de mesas por estado --}}
        <div class="flex-shrink-0 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/50 dark:text-emerald-300 p-3 rounded-lg text-center flex flex-col items-center justify-center min-w-[85px]">
            <div class="w-3 h-3 rounded-full bg-emerald-500 mb-1"></div>
            <p class="font-bold text-3xl">{{ $tables->where('status', 'available')->count() }}</p>
            <p class="text-xs">Disponibles</p>
        </div>

        <div class="flex-shrink-0 bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 p-3 rounded-lg text-center flex flex-col items-center justify-center min-w-[85px]">
            <div class="w-3 h-3 rounded-full bg-blue-500 mb-1"></div>
            <p class="font-bold text-3xl">{{ $tables->where('status', 'reserved')->count() }}</p>
            <p class="text-xs">Reservadas</p>
        </div>

        <div class="flex-shrink-0 bg-amber-50 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300 p-3 rounded-lg text-center flex flex-col items-center justify-center min-w-[85px]">
            <div class="w-3 h-3 rounded-full bg-amber-500 mb-1"></div>
            <p class="font-bold text-3xl">{{ $tables->where('status', 'occupied')->count() }}</p>
            <p class="text-xs">Ocupadas</p>
        </div>

        <div class="flex-shrink-0 bg-gray-50 text-gray-700 dark:bg-gray-700 dark:text-gray-300 p-3 rounded-lg text-center flex flex-col items-center justify-center min-w-[85px]">
            <div class="w-3 h-3 rounded-full bg-gray-500 mb-1"></div>
            <p class="font-bold text-3xl">{{ $tables->where('status', 'maintenance')->count() }}</p>
            <p class="text-xs">Mantenimiento</p>
        </div>

        <div class="flex-shrink-0 bg-blue-50 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 p-3 rounded-lg text-center flex flex-col items-center justify-center min-w-[85px]">
            <div class="mb-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
            </div>
            <p class="font-bold text-3xl">{{ $tables->count() }}</p>
            <p class="text-xs">Total</p>
        </div>
    </div>

    {{-- Leyenda de estado de mesas --}}
    <div class="flex flex-wrap gap-3 items-center text-sm mb-4">
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-full bg-emerald-500"></span>
            <span>Disponible</span>
        </span>

        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-full bg-blue-500"></span>
            <span>Reservada</span>
        </span>

        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-full bg-amber-500"></span>
            <span>Ocupada</span>
        </span>

        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-full bg-gray-500"></span>
            <span>Mantenimiento</span>
        </span>
    </div>

    {{-- Enlaces rápidos --}}
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <h3 class="text-sm font-medium mb-2">Acciones rápidas</h3>
        <div class="flex flex-wrap gap-2">
            <x-filament::button
                type="button"
                size="sm"
                color="gray"
                :href="route('filament.admin.resources.tables.index')"
                icon="heroicon-o-list-bullet"
            >
                Listado de mesas
            </x-filament::button>

            <x-filament::button
                type="button"
                size="sm"
                color="gray"
                :href="route('filament.admin.resources.tables.create')"
                icon="heroicon-o-plus"
            >
                Nueva mesa
            </x-filament::button>
        </div>
    </div>
</div>
