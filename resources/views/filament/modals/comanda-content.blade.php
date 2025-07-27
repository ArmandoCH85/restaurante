<div class="space-y-6">
    {{-- HEADER DE LA COMANDA --}}
    <div class="text-center border-b border-orange-200 pb-4">
        <h2 class="text-xl font-bold text-orange-900">👨‍🍳 COMANDA</h2>
        <p class="text-sm text-orange-600 mt-1">Orden #{{ $order->id }}</p>
        
        @if($isDirectSale && !empty($customerNameForComanda))
            {{-- CLIENTE DESTACADO PARA VENTA DIRECTA --}}
            <div class="mt-3 bg-blue-50 border border-blue-200 rounded-lg p-3">
                <div class="flex items-center justify-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-2">
                        <p class="text-sm font-semibold text-blue-700">Cliente:</p>
                        <p class="text-lg font-bold text-blue-900">{{ $customerNameForComanda }}</p>
                    </div>
                </div>
            </div>
        @elseif($order->table)
            <p class="text-lg font-semibold text-orange-700 mt-2">Mesa #{{ $order->table->number }}</p>
        @endif
        
        <p class="text-xs text-gray-400 mt-1">{{ $order->created_at->format('d/m/Y H:i') }}</p>
    </div>

    {{-- PRODUCTOS PARA LA COCINA --}}
    <div class="space-y-3">
        <h3 class="font-semibold text-gray-900 border-b border-gray-100 pb-2 flex items-center">
            <svg class="h-5 w-5 text-orange-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H9a1 1 0 110-2H8.771l-.546-2.187A1 1 0 019.18 7h1.64a1 1 0 01.955.813L11.229 10H12a1 1 0 110 2h-.771l-.146.585A1 1 0 0110.18 13H9.82a1 1 0 01-.955-.813L8.771 12z" clip-rule="evenodd"></path>
            </svg>
            Preparar
        </h3>
        
        @forelse($order->orderDetails as $detail)
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 hover:bg-orange-100 transition-colors">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-lg font-bold text-gray-900">
                            {{ $detail->product->name ?? 'Producto eliminado' }}
                            @if(strpos($detail->notes, 'HELADA') !== false)
                                <span class="bg-blue-100 text-blue-800 text-xs font-bold px-2 py-0.5 rounded ml-1">HELADA</span>
                            @elseif(strpos($detail->notes, 'AL TIEMPO') !== false)
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-0.5 rounded ml-1">AL TIEMPO</span>
                            @elseif(strpos($detail->notes, 'FRESCA') !== false)
                                <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-0.5 rounded ml-1">FRESCA</span>
                            @elseif(strpos($detail->notes, 'ROJO') !== false)
                                <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-0.5 rounded ml-1">ROJO</span>
                            @elseif(strpos($detail->notes, 'JUGOSO') !== false)
                                <span class="bg-pink-100 text-pink-800 text-xs font-bold px-2 py-0.5 rounded ml-1">JUGOSO</span>
                            @elseif(strpos($detail->notes, 'TRES CUARTOS') !== false)
                                <span class="bg-orange-100 text-orange-800 text-xs font-bold px-2 py-0.5 rounded ml-1">TRES CUARTOS</span>
                            @elseif(strpos($detail->notes, 'BIEN COCIDO') !== false)
                                <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2 py-0.5 rounded ml-1">BIEN COCIDO</span>
                            @endif
                        </p>
                        
                        {{-- CANTIDAD DESTACADA PARA LA COCINA --}}
                        <div class="flex items-center mt-2">
                            <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full">
                                CANTIDAD: {{ $detail->quantity }}
                            </span>
                        </div>

                        {{-- NOTAS ESPECIALES --}}
                        @if($detail->notes)
                            @php
                                $notesText = $detail->notes;
                                // Eliminar las palabras de temperatura y punto de cocción de las notas para no mostrarlas dos veces
                                $notesText = str_replace(['HELADA', 'AL TIEMPO', 'FRESCA', 'ROJO', 'JUGOSO', 'TRES CUARTOS', 'BIEN COCIDO'], '', $notesText);
                                $notesText = trim($notesText);
                            @endphp
                            
                            @if($notesText)
                                <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded">
                                    <p class="text-sm text-yellow-800">
                                        <span class="font-semibold">📝 Nota:</span> {{ $notesText }}
                                    </p>
                                </div>
                            @endif
                        @endif
                    </div>
                    
                    {{-- PRECIO (OPCIONAL PARA LA COCINA) --}}
                    <div class="text-right ml-4">
                        <p class="text-sm text-gray-500">
                            S/ {{ number_format($detail->unit_price, 2) }}
                        </p>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-4 text-gray-500">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 mb-2">
                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <p>No hay productos en esta orden</p>
            </div>
        @endforelse
    </div>

    {{-- INFORMACIÓN ADICIONAL PARA LA COCINA --}}
    <div class="bg-green-50 border border-green-200 rounded-lg p-3">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-green-700">
                    <span class="font-semibold">Para la cocina:</span> Esta es la comanda de preparación. 
                    @if($isDirectSale && !empty($customerNameForComanda))
                        El pedido es para <strong>{{ $customerNameForComanda }}</strong> (venta directa).
                    @elseif($order->table)
                        El pedido es para la Mesa #{{ $order->table->number }}.
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- NÚMERO DE COMENSALES --}}
    @if($order->number_of_guests && $order->number_of_guests > 1)
        <div class="text-center bg-blue-50 border border-blue-200 rounded-lg p-2">
            <p class="text-sm text-blue-700">
                <span class="font-semibold">👥 Comensales:</span> {{ $order->number_of_guests }} personas
            </p>
        </div>
    @endif

    {{-- RESUMEN RÁPIDO --}}
    <div class="border-t border-gray-200 pt-4">
        <div class="flex justify-between items-center text-sm text-gray-600">
            <span>Total de productos:</span>
            <span class="font-semibold">{{ $order->orderDetails->sum('quantity') }} items</span>
        </div>
        <div class="flex justify-between items-center text-sm text-gray-600 mt-1">
            <span>Tipos diferentes:</span>
            <span class="font-semibold">{{ $order->orderDetails->count() }} productos</span>
        </div>
    </div>
</div> 