<div class="space-y-4">
    <!-- Información General -->
    <div class="bg-gray-50 p-4 rounded-lg">
        <h4 class="font-semibold text-gray-900 mb-3">📋 Información General</h4>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="font-medium text-gray-600">Orden #:</span>
                <span class="text-gray-900">{{ $order->id }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Fecha:</span>
                <span class="text-gray-900">{{ $order->order_datetime->format('d/m/Y H:i') }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Cliente:</span>
                <span class="text-gray-900">{{ $order->customer?->name ?? 'Sin cliente' }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Mesa:</span>
                <span class="text-gray-900">{{ $order->table?->name ?? 'Sin mesa' }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Mesero:</span>
                <span class="text-gray-900">{{ $order->user?->name ?? 'Sin asignar' }}</span>
            </div>
            <div>
                <span class="font-medium text-gray-600">Tipo Servicio:</span>
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                    {{ $order->service_type === 'dine_in' ? 'bg-green-100 text-green-800' : 
                       ($order->service_type === 'delivery' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                    {{ $order->service_type === 'dine_in' ? 'En Local' : 
                       ($order->service_type === 'delivery' ? 'Delivery' : 'Para Llevar') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Detalles de Productos -->
    <div>
        <h4 class="font-semibold text-gray-900 mb-3">🍽️ Productos Pedidos</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Producto</th>
                        <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase">Cant.</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Precio Unit.</th>
                        <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($order->orderDetails as $detail)
                        <tr>
                            <td class="px-3 py-2 text-sm text-gray-900">
                                {{ $detail->product?->name ?? 'Producto no encontrado' }}
                                @if($detail->notes)
                                    <div class="text-xs text-gray-500 italic">{{ $detail->notes }}</div>
                                @endif
                            </td>
                            <td class="px-3 py-2 text-center text-sm text-gray-900">{{ $detail->quantity }}</td>
                            <td class="px-3 py-2 text-right text-sm text-gray-900">S/ {{ number_format($detail->unit_price, 2) }}</td>
                            <td class="px-3 py-2 text-right text-sm font-medium text-gray-900">S/ {{ number_format($detail->subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
                                No hay productos en esta orden
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Totales -->
    <div class="bg-blue-50 p-4 rounded-lg">
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600">Subtotal:</span>
                <span class="text-gray-900">S/ {{ number_format($order->subtotal ?? 0, 2) }}</span>
            </div>
            @if($order->discount > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">Descuento:</span>
                    <span class="text-red-600">-S/ {{ number_format($order->discount, 2) }}</span>
                </div>
            @endif
            @if($order->tax > 0)
                <div class="flex justify-between">
                    <span class="text-gray-600">IGV:</span>
                    <span class="text-gray-900">S/ {{ number_format($order->tax, 2) }}</span>
                </div>
            @endif
            <div class="flex justify-between font-bold text-lg border-t pt-2">
                <span class="text-gray-900">TOTAL:</span>
                <span class="text-green-600">S/ {{ number_format($order->total, 2) }}</span>
            </div>
        </div>
    </div>

    <!-- Estado y Comentarios -->
    @if($order->comments || $order->status)
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h4 class="font-semibold text-gray-900 mb-2">📝 Información Adicional</h4>
            @if($order->status)
                <div class="mb-2">
                    <span class="font-medium text-gray-600">Estado:</span>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $order->status }}
                    </span>
                </div>
            @endif
            @if($order->comments)
                <div>
                    <span class="font-medium text-gray-600">Comentarios:</span>
                    <p class="text-sm text-gray-900 mt-1">{{ $order->comments }}</p>
                </div>
            @endif
        </div>
    @endif
</div>