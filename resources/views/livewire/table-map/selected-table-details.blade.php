<div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
            Mesa {{ $selectedTable->number }}
        </h3>
        <div class="flex items-center space-x-2">
            <span @class([
                'px-2 py-1 text-xs font-medium rounded-full',
                'bg-success-100 text-success-800 dark:bg-success-900 dark:text-success-100' => $selectedTable->status === 'available',
                'bg-danger-100 text-danger-800 dark:bg-danger-900 dark:text-danger-100' => $selectedTable->status === 'occupied',
                'bg-warning-100 text-warning-800 dark:bg-warning-900 dark:text-warning-100' => $selectedTable->status === 'reserved',
                'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-100' => $selectedTable->status === 'maintenance',
            ])>
                {{ $this->getStatusOptions()[$selectedTable->status] }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <!-- Capacidad -->
        <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Capacidad</span>
            <p class="text-sm text-gray-900 dark:text-white">{{ $selectedTable->capacity }} personas</p>
        </div>

        <!-- Ubicación -->
        <div>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Ubicación</span>
            <p class="text-sm text-gray-900 dark:text-white">{{ $selectedTable->location }}</p>
        </div>

        @if($selectedTable->status === 'occupied')
            <!-- Comensales actuales -->
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Comensales actuales</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $selectedTable->current_diners }} personas</p>
            </div>

            <!-- Tiempo ocupada -->
            <div>
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Ocupada desde</span>
                <p class="text-sm text-gray-900 dark:text-white">{{ $selectedTable->occupied_at?->diffForHumans() }}</p>
            </div>
        @endif
    </div>

    <!-- Acciones -->
    <div class="space-y-2">
        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300">Acciones</h4>
        <div class="grid grid-cols-2 gap-2">
            @if($selectedTable->status !== 'available')
                <x-filament::button
                    type="button"
                    color="success"
                    size="sm"
                    wire:click="openChangeStatusModal('available')"
                    icon="heroicon-o-check"
                >
                    Marcar disponible
                </x-filament::button>
            @endif

            @if($selectedTable->status !== 'occupied')
                <x-filament::button
                    type="button"
                    color="danger"
                    size="sm"
                    wire:click="openChangeStatusModal('occupied')"
                    icon="heroicon-o-users"
                >
                    Marcar ocupada
                </x-filament::button>
            @endif

            @if($selectedTable->status !== 'reserved')
                <x-filament::button
                    type="button"
                    color="warning"
                    size="sm"
                    wire:click="openChangeStatusModal('reserved')"
                    icon="heroicon-o-clock"
                >
                    Marcar reservada
                </x-filament::button>
            @endif

            @if($selectedTable->status !== 'maintenance')
                <x-filament::button
                    type="button"
                    color="gray"
                    size="sm"
                    wire:click="openChangeStatusModal('maintenance')"
                    icon="heroicon-o-wrench"
                >
                    Mantenimiento
                </x-filament::button>
            @endif
        </div>

        <div class="mt-4">
            <x-filament::button
                type="button"
                color="primary"
                size="sm"
                wire:click="goToPos({{ $selectedTable->id }})"
                icon="heroicon-o-shopping-cart"
                class="w-full"
            >
                Ir al POS
            </x-filament::button>
        </div>
    </div>
</div>
