{{-- Modal para cambio de estado --}}
@if($showChangeStatusModal && $selectedTable)
    <x-filament::modal 
        class="filament-table-status-modal" 
        id="change-status-modal"
        width="md"
        wire:key="change-status-modal"
        visible
    >
        <x-slot name="heading">
            Cambiar estado de mesa
        </x-slot>
        
        <x-slot name="description">
            ¿Estás seguro de que deseas cambiar el estado de la Mesa {{ $selectedTable->number }} a 
            <span class="font-medium">
                {{ $statusOptions[$newStatus] ?? $newStatus }}
            </span>?
        </x-slot>
        
        <div class="space-y-3">
            <div class="text-sm text-gray-600 dark:text-gray-400">
                <p>Estado actual: <span class="font-medium">{{ $statusOptions[$selectedTable->status] ?? $selectedTable->status }}</span></p>
                <p>Nuevo estado: <span class="font-medium">{{ $statusOptions[$newStatus] ?? $newStatus }}</span></p>
            </div>
            
            @if($newStatus === 'maintenance')
                <div class="bg-amber-50 text-amber-800 p-2 rounded-md text-xs">
                    <p class="flex items-center">
                        <x-heroicon-o-exclamation-triangle class="w-4 h-4 mr-1" />
                        <span>
                            Al marcar esta mesa como "Mantenimiento", no estará disponible para reservas ni pedidos.
                        </span>
                    </p>
                </div>
            @endif
            
            @if($selectedTable->status === 'occupied' && $newStatus === 'available')
                <div class="bg-blue-50 text-blue-800 p-2 rounded-md text-xs">
                    <p class="flex items-center">
                        <x-heroicon-o-information-circle class="w-4 h-4 mr-1" />
                        <span>
                            Asegúrese de que la cuenta ha sido pagada antes de cambiar el estado.
                        </span>
                    </p>
                </div>
            @endif
        </div>
        
        <x-slot name="footerActions">
            <x-filament::button
                type="button"
                color="gray"
                wire:click="$set('showChangeStatusModal', false)"
            >
                Cancelar
            </x-filament::button>
            
            <x-filament::button
                type="button"
                color="primary"
                wire:click="changeTableStatus"
            >
                Confirmar cambio
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
@endif

{{-- Modal para asignar orden --}}
@if($showAssignOrderModal && $selectedTable)
    <x-filament::modal 
        class="filament-table-assign-order-modal" 
        id="assign-order-modal"
        width="md"
        wire:key="assign-order-modal"
        visible
    >
        <x-slot name="heading">
            Asignar orden a Mesa {{ $selectedTable->number }}
        </x-slot>
        
        <x-slot name="description">
            Seleccione la orden pendiente que desea asignar a esta mesa.
        </x-slot>
        
        <div class="space-y-3">
            <div class="mb-3">
                <label for="orderIdToAssign" class="text-sm font-medium leading-6 text-gray-950 dark:text-white mb-1 block">Orden</label>
                <select 
                    id="orderIdToAssign"
                    wire:model.live="orderIdToAssign"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 shadow-sm text-sm"
                >
                    <option value="">Seleccionar orden...</option>
                    @foreach($pendingOrders as $order)
                        <option value="{{ $order->id }}">
                            #{{ $order->id }} - {{ optional($order->customer)->name ?? 'Cliente' }} ({{ $order->created_at->format('H:i') }})
                        </option>
                    @endforeach
                </select>
            </div>
            
            @if($orderIdToAssign)
                <div class="bg-blue-50 text-blue-800 p-2 rounded-md text-xs">
                    <p>
                        Al asignar esta orden, la mesa pasará automáticamente a estado "Ocupada".
                    </p>
                </div>
            @endif
        </div>
        
        <x-slot name="footerActions">
            <x-filament::button
                type="button"
                color="gray"
                wire:click="$set('showAssignOrderModal', false)"
            >
                Cancelar
            </x-filament::button>
            
            <x-filament::button
                type="button"
                color="primary"
                wire:click="assignOrderToTable"
                :disabled="!$orderIdToAssign"
            >
                Asignar orden
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
@endif

{{-- Modal para información de reserva --}}
@if($showReservationInfoModal && $selectedTable && $selectedTable->activeReservations && $selectedTable->activeReservations->isNotEmpty())
    <x-filament::modal 
        class="filament-table-reservation-info-modal" 
        id="reservation-info-modal"
        width="md"
        wire:key="reservation-info-modal"
        visible
    >
        <x-slot name="heading">
            Reservas para Mesa {{ $selectedTable->number }}
        </x-slot>
        
        <x-slot name="description">
            Detalles de las reservaciones programadas para hoy.
        </x-slot>
        
        <div class="space-y-4">
            @foreach($selectedTable->activeReservations as $reservation)
                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                    <div class="flex justify-between items-start">
                        <h3 class="font-medium">{{ $reservation->customer_name }}</h3>
                        <span class="text-sm px-1.5 py-0.5 rounded-full 
                            {{ $reservation->status === 'confirmed' ? 'bg-emerald-100 text-emerald-800' : '' }}
                            {{ $reservation->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                            {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                        ">
                            {{ ucfirst($reservation->status) }}
                        </span>
                    </div>
                    
                    <dl class="mt-2 text-sm">
                        <div class="flex justify-between py-1 text-sm">
                            <dt class="text-gray-600 dark:text-gray-400">Hora</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                {{ \Carbon\Carbon::parse($reservation->reservation_time)->format('H:i') }}
                            </dd>
                        </div>
                        
                        <div class="flex justify-between py-1 text-sm">
                            <dt class="text-gray-600 dark:text-gray-400">Personas</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">{{ $reservation->guest_count }}</dd>
                        </div>
                        
                        <div class="flex justify-between py-1 text-sm">
                            <dt class="text-gray-600 dark:text-gray-400">Contacto</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                {{ $reservation->contact_phone ?? 'No disponible' }}
                            </dd>
                        </div>
                        
                        <div class="flex justify-between py-1 text-sm">
                            <dt class="text-gray-600 dark:text-gray-400">Notas</dt>
                            <dd class="font-medium text-gray-900 dark:text-white">
                                {{ $reservation->notes ?? 'Sin notas' }}
                            </dd>
                        </div>
                    </dl>
                    
                    <div class="mt-3 flex justify-end gap-2">
                        @if($reservation->status !== 'cancelled')
                            <x-filament::button
                                type="button"
                                color="danger"
                                size="sm"
                                :href="route('filament.admin.resources.reservations.edit', ['record' => $reservation->id])" 
                                tag="a"
                            >
                                Cancelar
                            </x-filament::button>
                        @endif
                        
                        <x-filament::button
                            type="button"
                            color="primary"
                            size="sm"
                            :href="route('filament.admin.resources.reservations.edit', ['record' => $reservation->id])" 
                            tag="a"
                        >
                            Detalles
                        </x-filament::button>
                    </div>
                </div>
            @endforeach
        </div>
        
        <x-slot name="footerActions">
            <x-filament::button
                type="button"
                color="gray"
                wire:click="$set('showReservationInfoModal', false)"
            >
                Cerrar
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
@endif
