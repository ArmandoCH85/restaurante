<div class="space-y-4 p-4">
    {{-- Encabezado minimal --}}
    <div class="text-center border-b border-gray-200 pb-3">
        <h2 class="text-xl font-semibold text-gray-900">Comanda</h2>
        <p class="text-xs text-gray-500">Orden #{{ $order->id }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>

    {{-- Destino: Cliente o Mesa --}}
    @if($isDirectSale)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
            @if(empty($customerNameForComanda))
                <label class="block text-xs font-medium text-gray-700 mb-1">Nombre del cliente</label>
                <input
                    type="text"
                    wire:model.live.trim="customerNameForComanda"
                    placeholder="Ej: Juan Pérez o Mostrador"
                    class="w-full rounded-md border border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-3 py-2 text-gray-900 placeholder-gray-400"
                    autofocus
                    maxlength="100"
                />
                <p class="mt-1 text-[11px] text-gray-500">Requerido para imprimir en venta directa.</p>
            @else
                <div class="text-sm text-gray-700"><span class="font-semibold">Cliente:</span> {{ $customerNameForComanda }}</div>
            @endif
        </div>
    @elseif($order->table)
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-sm text-gray-700">
            <span class="font-semibold">Mesa:</span> #{{ $order->table->number }}
        </div>
    @endif

    {{-- Productos --}}
    <div class="space-y-3">
        <h3 class="text-sm font-semibold text-gray-800">Productos</h3>
        @forelse($order->orderDetails as $detail)
            <div class="bg-white border border-gray-200 rounded-md p-3">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-2">
                    <div class="flex-1 space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <h4 class="text-sm font-semibold text-gray-900 leading-tight">
                                {{ $detail->product->name ?? 'Producto eliminado' }}
                            </h4>
                        </div>

                        <div class="text-xs text-gray-600">
                            <span class="font-medium">Cantidad:</span> {{ $detail->quantity }}
                        </div>

                        @if($detail->notes)
                            @php
                                $notesText = $detail->notes;
                                $notesText = str_replace(['HELADA', 'AL TIEMPO', 'FRESCA', 'ROJO', 'JUGOSO', 'TRES CUARTOS', 'BIEN COCIDO'], '', $notesText);
                                $notesText = trim($notesText);
                            @endphp

                            @if($notesText)
                                <div class="bg-yellow-50 border border-yellow-200 p-2 rounded-md">
                                    <p class="text-xs font-semibold text-yellow-800 mb-1">Instrucciones:</p>
                                    <p class="text-xs text-yellow-700">{{ $notesText }}</p>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="flex-shrink-0 text-right text-xs text-gray-600">
                        <span class="font-medium">Precio Unit.:</span> S/ {{ number_format($detail->unit_price, 2) }}
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-6">
                <h3 class="text-sm font-semibold text-gray-600 mb-1">Sin productos</h3>
                <p class="text-xs text-gray-500">No hay productos en esta orden para preparar</p>
            </div>
        @endforelse
    </div>

    {{-- Información breve --}}
    <div class="bg-gray-50 border border-gray-200 rounded-md p-3 text-xs text-gray-700">
        <span class="font-semibold">Destino:</span>
        @if($isDirectSale && !empty($customerNameForComanda))
            {{ $customerNameForComanda }} (venta directa)
        @elseif($order->table)
            Mesa #{{ $order->table->number }}
        @else
            N/D
        @endif
    </div>

    {{-- Resumen --}}
    <div class="bg-white border border-gray-200 rounded-md p-3 text-xs">
        <div class="grid grid-cols-2 gap-2">
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Total ítems</span>
                <span class="font-semibold text-gray-800">{{ $order->orderDetails->sum('quantity') }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-600">Productos</span>
                <span class="font-semibold text-gray-800">{{ $order->orderDetails->count() }}</span>
            </div>
        </div>
    </div>
</div>
