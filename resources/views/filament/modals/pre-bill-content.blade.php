<div class="space-y-6">
    {{-- HEADER DE LA PRE-CUENTA --}}
    <div class="text-center border-b border-gray-200 pb-4">
        <h2 class="text-xl font-bold text-gray-900">PRE-CUENTA</h2>
        <p class="text-sm text-gray-500 mt-1">Orden #{{ $order->id }}</p>
        @if($order->table)
            <p class="text-sm text-gray-600">Mesa #{{ $order->table->number }}</p>
        @endif
        <p class="text-xs text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>

    {{-- PRODUCTOS --}}
    <div class="space-y-3">
        <h3 class="font-semibold text-gray-900 border-b border-gray-100 pb-2">Productos</h3>
        
        @forelse($order->orderDetails as $detail)
            <div class="flex justify-between items-center py-2 border-b border-gray-50 hover:bg-gray-50 rounded px-2">
                <div class="flex-1">
                    <p class="font-medium text-gray-900">{{ $detail->product->name ?? 'Producto eliminado' }}</p>
                    <p class="text-sm text-gray-500">
                        {{ $detail->quantity }} x S/ {{ number_format($detail->unit_price, 2) }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="font-semibold text-gray-900">
                        S/ {{ number_format($detail->subtotal, 2) }}
                    </p>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-gray-500">
                <p>No hay productos en esta orden</p>
            </div>
        @endforelse
    </div>

    {{-- DEBUG TEMPORAL --}}
    <div class="bg-red-100 p-2 text-xs">
        DEBUG: subtotal={{ $subtotal ?? 'NULL' }}, tax={{ $tax ?? 'NULL' }}, total={{ $total ?? 'NULL' }}
    </div>

    {{-- TOTALES --}}
    <div class="border-t border-gray-200 pt-4 space-y-2">
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">Subtotal:</span>
            <span class="font-medium">S/ {{ number_format($subtotal, 2) }}</span>
        </div>
        
        <div class="flex justify-between text-sm">
            <span class="text-gray-600">IGV (18%):</span>
            <span class="font-medium">S/ {{ number_format($tax, 2) }}</span>
        </div>
        
        <div class="flex justify-between border-t border-gray-200 pt-2 text-lg font-bold">
            <span class="text-gray-900">Total:</span>
            <span class="text-green-600">S/ {{ number_format($total, 2) }}</span>
        </div>
    </div>

    {{-- INFORMACIÓN ADICIONAL --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Esta es una pre-cuenta. No es un comprobante válido para efectos fiscales.
                </p>
            </div>
        </div>
    </div>

    @if($order->customer && $order->customer->id !== 1)
        {{-- DATOS DEL CLIENTE --}}
        <div class="border-t border-gray-200 pt-4">
            <h3 class="font-semibold text-gray-900 mb-2">Cliente</h3>
            <div class="bg-gray-50 rounded-lg p-3 space-y-1">
                <p class="text-sm"><span class="font-medium">Nombre:</span> {{ $order->customer->name }}</p>
                @if($order->customer->document_number)
                    <p class="text-sm"><span class="font-medium">Documento:</span> {{ $order->customer->document_type }} {{ $order->customer->document_number }}</p>
                @endif
                @if($order->customer->phone)
                    <p class="text-sm"><span class="font-medium">Teléfono:</span> {{ $order->customer->phone }}</p>
                @endif
            </div>
        </div>
    @endif
</div>