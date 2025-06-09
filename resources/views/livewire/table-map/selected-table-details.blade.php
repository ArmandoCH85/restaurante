<div class="bg-white rounded-xl shadow-sm p-4 dark:bg-gray-800">
    <div class="flex justify-between items-start mb-4">
        <div>
            <h2 class="text-lg font-medium">
                Mesa {{ $selectedTable->number }}
                <span class="text-sm px-2 py-0.5 rounded-full ml-1
                    {{ $selectedTable->status === 'available' ? 'bg-emerald-100 text-emerald-800' : '' }}
                    {{ $selectedTable->status === 'reserved' ? 'bg-blue-100 text-blue-800' : '' }}
                    {{ $selectedTable->status === 'occupied' ? 'bg-amber-100 text-amber-800' : '' }}
                    {{ $selectedTable->status === 'maintenance' ? 'bg-gray-100 text-gray-800' : '' }}
                ">
                    {{ $statusOptions[$selectedTable->status] ?? $selectedTable->status }}
                </span>
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                {{ $locationOptions[$selectedTable->location] ?? $selectedTable->location }} -
                Capacidad: {{ $selectedTable->capacity }} personas
            </p>
        </div>
        <button
            type="button"
            class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
            wire:click="unselectTable"
        >
            <x-heroicon-o-x-mark class="w-5 h-5" />
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
        {{-- Información detallada --}}
        <div class="space-y-3">
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Información</h3>
                <dl class="mt-2 divide-y divide-gray-200 dark:divide-gray-700">
                    <div class="flex justify-between py-1 text-sm">
                        <dt class="text-gray-600 dark:text-gray-400">ID</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">{{ $selectedTable->id }}</dd>
                    </div>

                    <div class="flex justify-between py-1 text-sm">
                        <dt class="text-gray-600 dark:text-gray-400">Piso</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ optional($selectedTable->floor)->name ?? 'No asignado' }}
                        </dd>
                    </div>

                    <div class="flex justify-between py-1 text-sm">
                        <dt class="text-gray-600 dark:text-gray-400">Última modificación</dt>
                        <dd class="font-medium text-gray-900 dark:text-white">
                            {{ $selectedTable->updated_at->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Acciones de mesa --}}
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300">Acciones</h3>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    {{-- Botones de cambio de estado --}}
                    @if($selectedTable->status !== 'available')
                        <x-filament::button
                            type="button"
                            color="success"
                            size="sm"
                            wire:click="openChangeStatusModal('available')"
                            icon="heroicon-o-check-circle"
                        >
                            Marcar disponible
                        </x-filament::button>
                    @endif

                    @if($selectedTable->status !== 'reserved')
                        <x-filament::button
                            type="button"
                            color="info"
                            size="sm"
                            wire:click="openChangeStatusModal('reserved')"
                            icon="heroicon-o-clock"
                        >
                            Marcar reservada
                        </x-filament::button>
                    @endif

                    @if($selectedTable->status !== 'occupied')
                        <x-filament::button
                            type="button"
                            color="warning"
                            size="sm"
                            wire:click="openChangeStatusModal('occupied')"
                            icon="heroicon-o-user"
                        >
                            Marcar ocupada
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
            </div>
        </div>

        {{-- Reservas y pedidos --}}
        <div class="space-y-4">
            {{-- Reservas activas para hoy --}}
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                    <x-heroicon-o-calendar class="w-4 h-4 mr-1" />
                    Reservas para hoy
                </h3>

                @if($selectedTable->activeReservations && $selectedTable->activeReservations->count() > 0)
                    <ul class="mt-2 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($selectedTable->activeReservations as $reservation)
                            <li class="py-2">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium">{{ $reservation->customer_name }}</span>
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 flex justify-between">
                                    <span>{{ $reservation->guest_count }} personas</span>
                                    <span>{{ $reservation->status }}</span>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400 italic mt-1">
                        No hay reservas para hoy
                    </p>
                @endif
            </div>

            {{-- Órdenes activas --}}
            <div>
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                    <x-heroicon-o-shopping-cart class="w-4 h-4 mr-1" />
                    Órdenes activas
                </h3>

                @if($selectedTable->activeOrders && $selectedTable->activeOrders->count() > 0)
                    <ul class="mt-2 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($selectedTable->activeOrders as $order)
                            <li class="py-2">
                                <div class="flex justify-between text-sm">
                                    <span class="font-medium">Orden #{{ $order->id }}</span>
                                    <span class="
                                        {{ $order->status === 'pending' ? 'text-amber-600 dark:text-amber-400' : '' }}
                                        {{ $order->status === 'processing' ? 'text-blue-600 dark:text-blue-400' : '' }}
                                        {{ $order->status === 'completed' ? 'text-emerald-600 dark:text-emerald-400' : '' }}
                                    ">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400">
                                    {{ \Carbon\Carbon::parse($order->created_at)->format('d/m/Y H:i') }}
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-600 dark:text-gray-400 italic mt-1">
                        No hay órdenes activas
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- Botones de acción --}}
    <div class="flex gap-2 justify-end mt-4 border-t border-gray-200 dark:border-gray-700 pt-4">
        <x-filament::button
            type="button"
                            color="gray"
            size="sm"
            :href="route('filament.admin.resources.tables.edit', ['record' => $selectedTable->id])"
            tag="a"
            icon="heroicon-o-pencil-square"
        >
            Editar mesa
        </x-filament::button>

        <x-filament::button
            type="button"
            color="primary"
            size="sm"
            :href="route('filament.admin.resources.reservations.create')"
            tag="a"
            icon="heroicon-o-calendar-days"
        >
            Nueva reserva
        </x-filament::button>
    </div>
</div>
