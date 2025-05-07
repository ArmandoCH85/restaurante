<div class="bg-white dark:bg-gray-900 min-h-screen">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Seguimiento de Pedidos Delivery</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gestiona y monitorea los pedidos de delivery en tiempo real</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('pos.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    POS
                </a>
                <a href="{{ route('tables.map') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-800 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                    Mapa de Mesas
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="statusFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                    <select id="statusFilter" wire:model.live="statusFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="assigned">Asignado</option>
                        <option value="in_transit">En tránsito</option>
                    </select>
                </div>
                @if(!$isDeliveryPerson)
                <div>
                    <label for="deliveryPersonFilter" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Repartidor</label>
                    <select id="deliveryPersonFilter" wire:model.live="deliveryPersonFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Todos los repartidores</option>
                        @foreach($deliveryPersons as $person)
                            <option value="{{ $person->id }}">{{ $person->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div>
                    <label for="searchQuery" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                    <div class="flex">
                        <input type="text" id="searchQuery" wire:model.defer="searchQuery" placeholder="Dirección o cliente..." class="flex-1 rounded-l-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <button wire:click="search" class="inline-flex items-center px-3 py-2 border border-l-0 border-gray-300 rounded-r-md bg-gray-50 text-gray-500 hover:bg-gray-100 dark:bg-gray-600 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-end">
                    <button wire:click="resetFilters" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Limpiar filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Delivery Orders Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($activeDeliveries as $delivery)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden border-l-4 relative"
                     style="border-left-color: {{ $delivery->status === 'pending' ? '#f59e0b' : ($delivery->status === 'assigned' ? '#3b82f6' : ($delivery->status === 'in_transit' ? '#6366f1' : '#10b981')) }}">

                    <!-- Header with Order ID and Status -->
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 flex justify-between items-center">
                        <div class="flex items-center">
                            <span class="text-lg font-semibold text-gray-900 dark:text-white">Orden #{{ $delivery->order_id }}</span>
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                  style="background-color: {{ $delivery->status === 'pending' ? '#fef3c7' : ($delivery->status === 'assigned' ? '#dbeafe' : ($delivery->status === 'in_transit' ? '#e0e7ff' : '#d1fae5')) }};
                                         color: {{ $delivery->status === 'pending' ? '#92400e' : ($delivery->status === 'assigned' ? '#1e40af' : ($delivery->status === 'in_transit' ? '#4338ca' : '#065f46')) }}">
                                {{ $getStatusName($delivery->status) }}
                            </span>
                        </div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">
                            {{ $delivery->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <!-- Customer and Address Info -->
                    <div class="p-4">
                        <div class="mb-3">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Cliente</h3>
                            <p class="text-base font-medium text-gray-900 dark:text-white">{{ $delivery->order->customer->name ?? 'Sin cliente' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ $delivery->order->customer->phone ?? 'Sin teléfono' }}</p>
                        </div>

                        <div class="mb-3">
                            <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección</h3>
                            <p class="text-base text-gray-900 dark:text-white">{{ $delivery->delivery_address }}</p>
                            @if($delivery->delivery_references)
                                <p class="text-sm text-gray-600 dark:text-gray-300">{{ $delivery->delivery_references }}</p>
                            @endif
                        </div>

                        @if($delivery->deliveryPerson)
                            <div class="mb-3">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Repartidor</h3>
                                <p class="text-base font-medium text-gray-900 dark:text-white">{{ $delivery->deliveryPerson->full_name }}</p>
                            </div>
                        @endif

                        @if($delivery->estimated_delivery_time)
                            <div class="mb-3">
                                <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">Tiempo estimado</h3>
                                <p class="text-base text-gray-900 dark:text-white">{{ $delivery->estimated_delivery_time->format('H:i') }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                        @if($delivery->status === 'pending')
                            <div x-data="{ open: false }">
                                <button @click="open = !open" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    Asignar Repartidor
                                </button>

                                <div x-show="open" @click.away="open = false" class="absolute inset-x-0 bottom-full mb-2 transform px-4" style="display: none;">
                                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-3">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Seleccionar repartidor</h4>
                                        <div class="space-y-2 max-h-40 overflow-y-auto">
                                            @foreach($deliveryPersons as $person)
                                                <button wire:click="assignDeliveryPerson({{ $delivery->id }}, {{ $person->id }})" @click="open = false" class="w-full text-left px-3 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
                                                    {{ $person->full_name }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @elseif($delivery->status === 'assigned')
                            <button wire:click="markAsInTransit({{ $delivery->id }})" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                                Marcar En Tránsito
                            </button>
                        @elseif($delivery->status === 'in_transit')
                            <button wire:click="markAsDelivered({{ $delivery->id }})" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Marcar como Entregado
                            </button>
                        @endif

                        @if(!in_array($delivery->status, ['delivered', 'cancelled']))
                            <div x-data="{ openCancel: false }">
                                <button @click="openCancel = !openCancel" class="mt-2 w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Cancelar Pedido
                                </button>

                                <div x-show="openCancel" @click.away="openCancel = false" class="absolute inset-x-0 bottom-full mb-2 transform px-4" style="display: none;">
                                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-3">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Motivo de cancelación</h4>
                                        <div x-data="{ reason: '' }">
                                            <textarea x-model="reason" class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white" rows="2" placeholder="Ingrese el motivo..."></textarea>
                                            <div class="mt-2 flex justify-end space-x-2">
                                                <button @click="openCancel = false" class="inline-flex items-center px-3 py-1.5 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 dark:bg-gray-700 dark:text-gray-200 dark:border-gray-600 dark:hover:bg-gray-600">
                                                    Cancelar
                                                </button>
                                                <button wire:click="cancelDelivery({{ $delivery->id }}, reason)" @click="openCancel = false" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Confirmar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-span-full bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 text-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                    <h3 class="mt-2 text-lg font-medium text-gray-900 dark:text-white">No hay pedidos activos</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se encontraron pedidos de delivery activos con los filtros seleccionados.</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Notification -->
    <div x-data="{ show: false, message: '', title: '', type: 'success' }"
         x-on:notification.window="
            show = true;
            title = $event.detail.title;
            message = $event.detail.message;
            type = $event.detail.type;
            setTimeout(() => { show = false }, 3000);
         "
         x-show="show"
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
         x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 flex items-end justify-center px-4 py-6 pointer-events-none sm:p-6 sm:items-start sm:justify-end z-50"
         style="display: none;">
        <div class="max-w-sm w-full bg-white dark:bg-gray-800 shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <template x-if="type === 'success'">
                            <svg class="h-6 w-6 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="type === 'error'">
                            <svg class="h-6 w-6 text-red-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </template>
                        <template x-if="type === 'warning'">
                            <svg class="h-6 w-6 text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </template>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900 dark:text-white" x-text="title"></p>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400" x-text="message"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="show = false" class="bg-white dark:bg-gray-800 rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <span class="sr-only">Cerrar</span>
                            <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
