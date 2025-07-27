<div class="p-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Información del Cliente -->
        <div class="bg-gray-50 p-4 rounded-lg border">
            <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                <x-heroicon-m-user class="w-5 h-5 mr-2 text-blue-500" />
                Cliente
            </h3>
            <div class="space-y-2">
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Nombre:</span>
                    <span class="text-gray-900">{{ $record->order->customer->name ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Teléfono:</span>
                    <span class="text-gray-900">{{ $record->order->customer->phone ?? 'N/A' }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Email:</span>
                    <span class="text-gray-900">{{ $record->order->customer->email ?? 'N/A' }}</span>
                </div>
                @if($record->order->customer->document_number)
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Documento:</span>
                    <span class="text-gray-900">{{ strtoupper($record->order->customer->document_type ?? 'DNI') }}: {{ $record->order->customer->document_number }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Información de Entrega -->
        <div class="bg-gray-50 p-4 rounded-lg border">
            <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                <x-heroicon-m-truck class="w-5 h-5 mr-2 text-green-500" />
                Entrega
            </h3>
            <div class="space-y-2">
                <div class="text-sm">
                    <span class="font-medium text-gray-600">Dirección:</span>
                    <p class="text-gray-900 mt-1">{{ $record->delivery_address }}</p>
                </div>
                @if($record->delivery_references)
                <div class="text-sm">
                    <span class="font-medium text-gray-600">Referencias:</span>
                    <p class="text-gray-900 mt-1">{{ $record->delivery_references }}</p>
                </div>
                @endif
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-24">Repartidor:</span>
                    <span class="text-gray-900">{{ $record->deliveryPerson->full_name ?? 'Sin asignar' }}</span>
                </div>
                @if($record->deliveryPerson && $record->deliveryPerson->phone)
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-24">Tel. Repartidor:</span>
                    <span class="text-gray-900">{{ $record->deliveryPerson->phone }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Información del Pedido -->
        <div class="bg-gray-50 p-4 rounded-lg border">
            <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                <x-heroicon-m-shopping-bag class="w-5 h-5 mr-2 text-purple-500" />
                Pedido
            </h3>
            <div class="space-y-2">
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Total:</span>
                    <span class="text-green-600 font-bold text-lg">S/ {{ number_format($record->order->total ?? 0, 2) }}</span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Estado:</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium
                        @if($record->status === 'pending') bg-gray-100 text-gray-800
                        @elseif($record->status === 'assigned') bg-blue-100 text-blue-800
                        @elseif($record->status === 'in_transit') bg-yellow-100 text-yellow-800
                        @elseif($record->status === 'delivered') bg-green-100 text-green-800
                        @elseif($record->status === 'cancelled') bg-red-100 text-red-800
                        @endif">
                        {{ match($record->status) {
                            'pending' => '⏳ Pendiente',
                            'assigned' => '👤 Asignado',
                            'in_transit' => '🚚 En Tránsito',
                            'delivered' => '✅ Entregado',
                            'cancelled' => '❌ Cancelado',
                            default => $record->status
                        } }}
                    </span>
                </div>
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Creado:</span>
                    <span class="text-gray-900">{{ $record->created_at->format('d/m/Y H:i') }}</span>
                </div>
                @if($record->actual_delivery_time)
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Entregado:</span>
                    <span class="text-gray-900">{{ $record->actual_delivery_time->format('d/m/Y H:i') }}</span>
                </div>
                @endif
                <div class="flex items-center text-sm">
                    <span class="font-medium text-gray-600 w-20">Pagado:</span>
                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $record->order->billed ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $record->order->billed ? '✅ Sí' : '❌ No' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Detalles del Pedido -->
    @if($record->order && $record->order->orderDetails->count() > 0)
    <div class="mt-6 bg-gray-50 p-4 rounded-lg border">
        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <x-heroicon-m-list-bullet class="w-5 h-5 mr-2 text-orange-500" />
            Productos Pedidos
        </h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Unit.</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($record->order->orderDetails as $detail)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm text-gray-900 font-medium">{{ $detail->product->name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 text-center">{{ $detail->quantity }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">S/ {{ number_format($detail->price, 2) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 font-medium">S/ {{ number_format($detail->quantity * $detail->price, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100">
                    <tr>
                        <td colspan="3" class="px-4 py-2 text-sm font-medium text-gray-900 text-right">Total:</td>
                        <td class="px-4 py-2 text-sm font-bold text-green-600">S/ {{ number_format($record->order->total ?? 0, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    <!-- Timeline de Estados -->
    <div class="mt-6 bg-gray-50 p-4 rounded-lg border">
        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <x-heroicon-m-clock class="w-5 h-5 mr-2 text-indigo-500" />
            Historial del Pedido
        </h3>
        <div class="space-y-3">
            <div class="flex items-center">
                <div class="flex-shrink-0 w-3 h-3 bg-blue-400 rounded-full"></div>
                <div class="ml-3 text-sm">
                    <span class="font-medium text-gray-900">Pedido creado</span>
                    <span class="text-gray-500 ml-2">{{ $record->created_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            
            @if($record->status !== 'pending')
            <div class="flex items-center">
                <div class="flex-shrink-0 w-3 h-3 bg-green-400 rounded-full"></div>
                <div class="ml-3 text-sm">
                    <span class="font-medium text-gray-900">Repartidor asignado</span>
                    @if($record->deliveryPerson)
                        <span class="text-gray-700 ml-1">({{ $record->deliveryPerson->full_name }})</span>
                    @endif
                    <span class="text-gray-500 ml-2">{{ $record->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            @endif
            
            @if(in_array($record->status, ['in_transit', 'delivered']))
            <div class="flex items-center">
                <div class="flex-shrink-0 w-3 h-3 bg-yellow-400 rounded-full"></div>
                <div class="ml-3 text-sm">
                    <span class="font-medium text-gray-900">En tránsito</span>
                    <span class="text-gray-500 ml-2">{{ $record->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            @endif
            
            @if($record->status === 'delivered')
            <div class="flex items-center">
                <div class="flex-shrink-0 w-3 h-3 bg-green-500 rounded-full"></div>
                <div class="ml-3 text-sm">
                    <span class="font-medium text-gray-900">Entregado exitosamente</span>
                    <span class="text-gray-500 ml-2">{{ $record->actual_delivery_time?->format('d/m/Y H:i') ?? $record->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            @endif

            @if($record->status === 'cancelled')
            <div class="flex items-center">
                <div class="flex-shrink-0 w-3 h-3 bg-red-500 rounded-full"></div>
                <div class="ml-3 text-sm">
                    <span class="font-medium text-gray-900">Pedido cancelado</span>
                    <span class="text-gray-500 ml-2">{{ $record->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Información Adicional -->
    @if($record->order->notes || $record->delivery_references)
    <div class="mt-6 bg-yellow-50 p-4 rounded-lg border border-yellow-200">
        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
            <x-heroicon-m-information-circle class="w-5 h-5 mr-2 text-yellow-500" />
            Notas Adicionales
        </h3>
        @if($record->order->notes)
        <div class="mb-2">
            <span class="font-medium text-gray-600">Notas del pedido:</span>
            <p class="text-gray-900 mt-1">{{ $record->order->notes }}</p>
        </div>
        @endif
        @if($record->delivery_references)
        <div>
            <span class="font-medium text-gray-600">Referencias de entrega:</span>
            <p class="text-gray-900 mt-1">{{ $record->delivery_references }}</p>
        </div>
        @endif
    </div>
    @endif
</div>