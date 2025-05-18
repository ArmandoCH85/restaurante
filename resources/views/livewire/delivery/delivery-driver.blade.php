<div class="p-6 bg-white dark:bg-gray-800 shadow-md rounded-lg">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Mis Pedidos de Delivery</h2>
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
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 border border-blue-100 dark:border-blue-800">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 dark:bg-blue-800 rounded-full mr-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Por Recoger</p>
                    <p class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ $assignedCount }}</p>
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

    <!-- Lista de pedidos asignados -->
    <div class="space-y-4">
        <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">Pedidos Asignados</h3>

        @forelse($assignedOrders as $order)
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md overflow-hidden border border-gray-200 dark:border-gray-600">
            <div class="bg-gray-50 dark:bg-gray-600 px-4 py-3 border-b border-gray-200 dark:border-gray-500 flex justify-between items-center">
                <div class="flex items-center">
                    <span class="font-medium text-gray-700 dark:text-gray-200">Pedido #{{ $order->id }}</span>
                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $order->deliveryOrder->status == 'ready' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : '' }}
                        {{ $order->deliveryOrder->status == 'in_transit' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}
                    ">
                        {{ $this->getStatusName($order->deliveryOrder->status) }}
                    </span>
                </div>
                <div class="text-sm text-gray-500 dark:text-gray-300">
                    {{ $order->created_at->format('d/m/Y H:i') }}
                </div>
            </div>

            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Cliente</h4>
                        <div class="space-y-1">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                <span class="font-medium">Nombre:</span>
                                {{ $order->customer->name ?? $order->deliveryOrder->customer_name ?? 'N/A' }}
                            </p>
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                <span class="font-medium">Teléfono:</span>
                                {{ $order->customer->phone ?? $order->deliveryOrder->customer_phone ?? 'N/A' }}
                            </p>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Entrega</h4>
                        <div class="space-y-1">
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                <span class="font-medium">Dirección:</span>
                                {{ $order->deliveryOrder->delivery_address ?? 'N/A' }}
                            </p>
                            @if($order->deliveryOrder->delivery_references)
                            <p class="text-sm text-gray-600 dark:text-gray-300">
                                <span class="font-medium">Referencias:</span>
                                {{ $order->deliveryOrder->delivery_references }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total:</span>
                            <span class="text-lg font-bold text-gray-900 dark:text-white">S/ {{ number_format($order->total, 2) }}</span>
                        </div>

                        <div class="flex space-x-2">
                            <button
                                wire:click="viewOrderDetails({{ $order->id }})"
                                class="px-3 py-1.5 bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 rounded-md text-sm font-medium hover:bg-blue-200 dark:hover:bg-blue-800/50 transition-colors"
                            >
                                Ver Detalles
                            </button>

                            @if($order->deliveryOrder->status == 'ready')
                            <button
                                wire:click="updateStatus({{ $order->id }}, 'in_transit')"
                                class="px-3 py-1.5 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 transition-colors"
                            >
                                Iniciar Entrega
                            </button>
                            @endif

                            @if($order->deliveryOrder->status == 'in_transit' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('cashier')))
                            <button
                                wire:click="updateStatus({{ $order->id }}, 'delivered')"
                                class="px-3 py-1.5 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700 transition-colors"
                            >
                                Marcar como Entregado
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white dark:bg-gray-700 rounded-lg shadow-md p-6 text-center">
            <div class="flex flex-col items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
                <p class="text-base font-medium text-gray-700 dark:text-gray-300">No tienes pedidos asignados</p>
                <p class="text-sm mt-1 text-gray-500 dark:text-gray-400">Los pedidos que te sean asignados aparecerán aquí.</p>
            </div>
        </div>
        @endforelse
    </div>

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
                                <span class="text-xs text-gray-500 dark:text-gray-400">Estado:</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'ready' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/50 dark:text-purple-300' : '' }}
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'in_transit' ? 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/50 dark:text-indigo-300' : '' }}
                                    {{ $viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'delivered' ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300' : '' }}
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

                @if($viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'ready')
                <button
                    wire:click="updateStatus({{ $viewingOrder->id }}, 'in_transit')"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                    Iniciar Entrega
                </button>
                @endif

                @if($viewingOrder->deliveryOrder && $viewingOrder->deliveryOrder->status == 'in_transit' && (auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('cashier')))
                <button
                    wire:click="updateStatus({{ $viewingOrder->id }}, 'delivered')"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md shadow-sm text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    Marcar como Entregado
                </button>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
