<div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Gestión de Pedidos Delivery</h2>
        <button
            wire:click="$refresh"
            class="p-2 bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-full hover:bg-blue-100 dark:hover:bg-blue-800/50 transition-colors"
            title="Actualizar"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-800">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-800 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Pendientes</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $pendingCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4 border border-yellow-100 dark:border-yellow-800">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 dark:bg-yellow-800 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">En Preparación</p>
                    <p class="text-2xl font-bold text-yellow-700 dark:text-yellow-300">{{ $preparingCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-lg p-4 border border-indigo-100 dark:border-indigo-800">
            <div class="flex items-center">
                <div class="p-3 bg-indigo-100 dark:bg-indigo-800 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-indigo-600 dark:text-indigo-400 font-medium">En Ruta</p>
                    <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">{{ $inTransitCount }}</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4 border border-green-100 dark:border-green-800">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 dark:bg-green-800 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-green-600 dark:text-green-400 font-medium">Entregados Hoy</p>
                    <p class="text-2xl font-bold text-green-700 dark:text-green-300">{{ $deliveredTodayCount }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg mb-6 flex flex-wrap gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Estado</label>
            <select
                wire:model.live="statusFilter"
                class="w-full sm:w-auto px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            >
                <option value="">Todos los estados</option>
                <option value="pending">Pendiente</option>
                <option value="preparing">En preparación</option>
                <option value="ready">Listo para entrega</option>
                <option value="in_transit">En ruta</option>
                <option value="delivered">Entregado</option>
                <option value="cancelled">Cancelado</option>
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Repartidor</label>
            <select
                wire:model.live="deliveryUserFilter"
                class="w-full sm:w-auto px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            >
                <option value="">Todos los repartidores</option>
                <option value="unassigned">Sin asignar</option>
                @foreach($deliveryUsers as $user)
                <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Fecha</label>
            <input
                type="date"
                wire:model.live="dateFilter"
                class="w-full sm:w-auto px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
            >
        </div>

        <div class="ml-auto self-end">
            <button
                wire:click="resetFilters"
                class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors"
            >
                Limpiar filtros
            </button>
        </div>
    </div>

    <!-- Tabla de pedidos -->
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Dirección</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Total</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Repartidor</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Estado</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Fecha</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($deliveryOrders as $order)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $order->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        <div class="font-medium">{{ $order->customer->name ?? $order->deliveryOrder->customer_name ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-400">{{ $order->customer->phone ?? $order->deliveryOrder->customer_phone ?? 'N/A' }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-300 max-w-xs truncate">
                        {{ $order->deliveryOrder->delivery_address ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        S/ {{ number_format($order->total, 2) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        @if($order->deliveryOrder && $order->deliveryOrder->delivery_user_id)
                            {{ $order->deliveryOrder->deliveryUser->name ?? 'N/A' }}
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Sin asignar
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($order->deliveryOrder)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $order->deliveryOrder->status == 'pending' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : '' }}
                                {{ $order->deliveryOrder->status == 'preparing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : '' }}
                                {{ $order->deliveryOrder->status == 'ready' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : '' }}
                                {{ $order->deliveryOrder->status == 'in_transit' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}
                                {{ $order->deliveryOrder->status == 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : '' }}
                                {{ $order->deliveryOrder->status == 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : '' }}
                            ">
                                {{ $this->getStatusName($order->deliveryOrder->status) }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                No disponible
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                        {{ $order->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end space-x-2">
                            <button
                                wire:click="viewOrderDetails({{ $order->id }})"
                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                title="Ver detalles"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </button>

                            @if($order->deliveryOrder && !$order->deliveryOrder->delivery_user_id)
                            <button
                                wire:click="openAssignModal({{ $order->id }})"
                                class="text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300"
                                title="Asignar repartidor"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                </svg>
                            </button>
                            @endif

                            <button
                                wire:click="openStatusModal({{ $order->id }})"
                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                title="Cambiar estado"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </button>

                            <button
                                wire:click="printOrder({{ $order->id }})"
                                class="text-purple-600 hover:text-purple-900 dark:text-purple-400 dark:hover:text-purple-300"
                                title="Imprimir"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                        <div class="flex flex-col items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <p class="text-base font-medium">No se encontraron pedidos de delivery</p>
                            <p class="text-sm mt-1">Intenta con otros filtros o crea un nuevo pedido de delivery.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    <div class="mt-4">
        {{ $deliveryOrders->links() }}
    </div>

    <!-- Modal para asignar repartidor -->
    @if($showAssignModal)
    <div class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Asignar Repartidor</h3>
                <button wire:click="$set('showAssignModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <div class="mb-4">
                    <label for="selectedDeliveryUserId" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Seleccionar Repartidor</label>
                    <select
                        wire:model="selectedDeliveryUserId"
                        id="selectedDeliveryUserId"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">Seleccionar repartidor...</option>
                        @foreach($deliveryUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('selectedDeliveryUserId') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                <button
                    wire:click="$set('showAssignModal', false)"
                    class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Cancelar
                </button>
                <button
                    wire:click="assignDeliveryUser"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ !$selectedDeliveryUserId ? 'disabled' : '' }}
                >
                    Asignar
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal para cambiar estado -->
    @if($showStatusModal)
    <div class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Cambiar Estado del Pedido</h3>
                <button wire:click="$set('showStatusModal', false)" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6">
                <div class="mb-4">
                    <label for="newStatus" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nuevo Estado</label>
                    <select
                        wire:model="newStatus"
                        id="newStatus"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white"
                    >
                        <option value="">Seleccionar estado...</option>
                        <option value="pending">Pendiente</option>
                        <option value="preparing">En preparación</option>
                        <option value="ready">Listo para entrega</option>
                        <option value="in_transit">En ruta</option>
                        <option value="delivered">Entregado</option>
                        <option value="cancelled">Cancelado</option>
                    </select>
                    @error('newStatus') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                <button
                    wire:click="$set('showStatusModal', false)"
                    class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Cancelar
                </button>
                <button
                    wire:click="updateOrderStatus"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                    {{ !$newStatus ? 'disabled' : '' }}
                >
                    Actualizar
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal para ver detalles del pedido -->
    @if($showOrderDetailsModal && $viewingOrder)
    <div class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-4xl w-full overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Detalles del Pedido #{{ $viewingOrder->id }}</h3>
                <button wire:click="closeOrderDetailsModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="p-6 overflow-y-auto max-h-[calc(100vh-200px)]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Información del cliente -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Información del Cliente
                        </h4>

                        <div class="space-y-2">
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Nombre:</span>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $viewingOrder->customer->name ?? $viewingOrder->deliveryOrder->customer_name ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Teléfono:</span>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $viewingOrder->customer->phone ?? $viewingOrder->deliveryOrder->customer_phone ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Documento:</span>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $viewingOrder->customer->document_type ?? $viewingOrder->deliveryOrder->customer_document_type ?? 'N/A' }}:
                                    {{ $viewingOrder->customer->document_number ?? $viewingOrder->deliveryOrder->customer_document ?? 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Información de entrega -->
                    <div class="bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                            Información de Entrega
                        </h4>

                        <div class="space-y-2">
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Dirección:</span>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $viewingOrder->deliveryOrder->delivery_address ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Referencias:</span>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    {{ $viewingOrder->deliveryOrder->delivery_references ?? 'N/A' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Repartidor:</span>
                                <p class="text-sm font-medium text-gray-800 dark:text-gray-200">
                                    @if($viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->delivery_user_id)
                                        {{ $viewingOrder->deliveryOrder->deliveryUser->name ?? 'N/A' }}
                                    @else
                                        <span class="text-yellow-600 dark:text-yellow-400">Sin asignar</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <span class="text-xs text-gray-500 dark:text-gray-400">Estado:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'pending' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300' : '' }}
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'preparing' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300' : '' }}
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'ready' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : '' }}
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'in_transit' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : '' }}
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300' : '' }}
                                ">
                                    {{ $viewingOrder->deliveryOrder ? $this->getStatusName($viewingOrder->deliveryOrder->status) : 'N/A' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Productos del pedido -->
                <div class="mt-6">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Productos
                    </h4>

                    <div class="bg-white dark:bg-gray-800 shadow overflow-hidden rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach($viewingOrder->orderDetails as $detail)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                        {{ $detail->product->name ?? 'Producto no disponible' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        {{ $detail->quantity }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        S/ {{ number_format($detail->unit_price, 2) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">
                                        S/ {{ number_format($detail->subtotal, 2) }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-white text-right">
                                        Total:
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                        S/ {{ number_format($viewingOrder->total, 2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 dark:bg-gray-700/30 border-t border-gray-200 dark:border-gray-700 flex justify-end space-x-3">
                <button
                    wire:click="closeOrderDetailsModal"
                    class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Cerrar
                </button>
                <button
                    wire:click="printOrder({{ $viewingOrder->id }})"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                    Imprimir
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
