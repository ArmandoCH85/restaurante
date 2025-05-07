<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg overflow-hidden">
    <!-- Modal para cancelar pedido -->
    <div id="cancelDeliveryModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Cancelar Pedido</h3>
            <p class="text-gray-700 dark:text-gray-300 mb-4">¿Estás seguro de que deseas cancelar este pedido? Por favor, indica el motivo:</p>

            <form id="cancelDeliveryForm" class="mb-4">
                <input type="hidden" id="cancelDeliveryId">
                <textarea id="cancelReason" class="w-full px-3 py-2 text-gray-700 dark:text-gray-300 border rounded-lg focus:outline-none focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600" rows="3" placeholder="Motivo de cancelación"></textarea>
            </form>

            <div class="flex justify-end space-x-3">
                <button onclick="closeCancelModal()" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors duration-200">Cancelar</button>
                <button onclick="submitCancelDelivery()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200">Confirmar</button>
            </div>
        </div>
    </div>

    <script>
        // Funciones para el modal de cancelación
        function openCancelModal(deliveryId) {
            document.getElementById('cancelDeliveryId').value = deliveryId;
            document.getElementById('cancelDeliveryModal').classList.remove('hidden');
        }

        function closeCancelModal() {
            document.getElementById('cancelDeliveryModal').classList.add('hidden');
            document.getElementById('cancelReason').value = '';
        }

        function submitCancelDelivery() {
            const deliveryId = document.getElementById('cancelDeliveryId').value;
            const reason = document.getElementById('cancelReason').value;

            // Llamar al método de Livewire
            @this.cancelDelivery(deliveryId, reason);

            // Cerrar el modal
            closeCancelModal();
        }

        // Escuchar eventos de Livewire
        document.addEventListener('livewire:initialized', function() {
            Livewire.on('openCancelDeliveryModal', function(deliveryId) {
                openCancelModal(deliveryId);
            });
        });
    </script>

    <div class="p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800 dark:text-white flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Detalles del Pedido #{{ $order->id }}
            </h2>
            <a href="{{ route('tables.map') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Volver
            </a>
        </div>

        <!-- Estado del pedido -->
        <div class="mb-6">
            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-500 dark:text-gray-400 mr-2">Estado:</span>
                <span class="px-3 py-1 rounded-full text-sm font-medium"
                    style="background-color: {{ $deliveryOrder->getStatusInfo()['bg'] }}; color: {{ $deliveryOrder->getStatusInfo()['color'] }}">
                    {{ $deliveryOrder->getStatusInfo()['text'] }}
                </span>
            </div>
        </div>

        <!-- Información del cliente -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Información del Cliente
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Nombre:</p>
                    <p class="text-base font-medium text-gray-800 dark:text-white">{{ $order->customer->name ?? 'No especificado' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Teléfono:</p>
                    <p class="text-base font-medium text-gray-800 dark:text-white">{{ $order->customer->phone ?? 'No especificado' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Dirección de entrega:</p>
                    <p class="text-base font-medium text-gray-800 dark:text-white">{{ $deliveryOrder->delivery_address }}</p>
                </div>
            </div>
        </div>

        <!-- Detalles del pedido -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
                Productos
            </h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Precio</th>
                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($order->orderDetails as $detail)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $detail->product->name }}</div>
                                    @if($detail->notes)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $detail->notes }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500 dark:text-gray-400">
                                    {{ $detail->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500 dark:text-gray-400">
                                    S/ {{ number_format($detail->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium text-gray-900 dark:text-white">
                                    S/ {{ number_format($detail->subtotal, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-500 dark:text-gray-300">Subtotal:</td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">S/ {{ number_format($order->subtotal, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-medium text-gray-500 dark:text-gray-300">IGV (18%):</td>
                            <td class="px-6 py-4 text-right text-sm font-medium text-gray-900 dark:text-white">S/ {{ number_format($order->tax, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm font-bold text-gray-700 dark:text-gray-200">TOTAL:</td>
                            <td class="px-6 py-4 text-right text-sm font-bold text-blue-600 dark:text-blue-400">S/ {{ number_format($order->total, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Información de entrega -->
        <div class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-3 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
                Información de Entrega
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Repartidor:</p>
                    <p class="text-base font-medium text-gray-800 dark:text-white">{{ $deliveryOrder->deliveryPerson->full_name ?? 'No asignado' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tiempo estimado:</p>
                    <p class="text-base font-medium text-gray-800 dark:text-white">
                        @if($deliveryOrder->estimated_delivery_time)
                            {{ $deliveryOrder->estimated_delivery_time->format('H:i') }}
                        @else
                            No especificado
                        @endif
                    </p>
                </div>
                @if($deliveryOrder->delivery_notes)
                    <div class="md:col-span-2">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Notas de entrega:</p>
                        <p class="text-base font-medium text-gray-800 dark:text-white">{{ $deliveryOrder->delivery_notes }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex flex-col sm:flex-row gap-3 mt-8">
            @php
                // Verificar si el usuario actual es el repartidor asignado a este pedido
                $user = \Illuminate\Support\Facades\Auth::user();
                $employee = \App\Models\Employee::where('user_id', $user->id)->first();
                $isAssignedDeliveryPerson = $employee && $deliveryOrder->delivery_person_id === $employee->id;
            @endphp

            @if($deliveryOrder->status === 'assigned' && $isAssignedDeliveryPerson)
                <button wire:click="updateDeliveryStatus({{ $deliveryOrder->id }}, 'in_transit')" class="w-full sm:w-auto px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    Iniciar Entrega
                </button>
            @elseif($deliveryOrder->status === 'in_transit' && $isAssignedDeliveryPerson)
                <button wire:click="updateDeliveryStatus({{ $deliveryOrder->id }}, 'delivered')" class="w-full sm:w-auto px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Marcar Entregado
                </button>
            @endif

            @if(!in_array($deliveryOrder->status, ['delivered', 'cancelled']) && $isAssignedDeliveryPerson)
                <button wire:click="openCancelModal({{ $deliveryOrder->id }})" class="w-full sm:w-auto px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors duration-200 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Cancelar Pedido
                </button>
            @endif
        </div>
    </div>
</div>
