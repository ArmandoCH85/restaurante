<div class="space-y-6 p-1 sm:p-2">
    {{-- HEADER DE LA COMANDA --}}
    <div class="text-center border-b-2 border-orange-200 pb-6 bg-gradient-to-r from-orange-50 to-yellow-50 rounded-t-lg p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-4">
            <div class="bg-orange-100 p-3 rounded-full">
                <svg class="h-8 w-8 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H9a1 1 0 110-2H8.771l-.546-2.187A1 1 0 019.18 7h1.64a1 1 0 01.955.813L11.229 10H12a1 1 0 110 2h-.771l-.146.585A1 1 0 0110.18 13H9.82a1 1 0 01-.955-.813L8.771 12z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold text-orange-900">👨‍🍳 COMANDA</h2>
                <p class="text-sm sm:text-base text-orange-600 font-medium">Orden #{{ $order->id }}</p>
            </div>
        </div>

        @if($isDirectSale && !empty($customerNameForComanda))
            {{-- CLIENTE DESTACADO PARA VENTA DIRECTA --}}
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-4 sm:p-6 shadow-sm">
                <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                    <div class="bg-blue-100 p-3 rounded-full flex-shrink-0">
                        <svg class="h-6 w-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="text-center sm:text-left">
                        <p class="text-sm font-semibold text-blue-700 mb-1">Cliente:</p>
                        <p class="text-xl sm:text-2xl font-bold text-blue-900">{{ $customerNameForComanda }}</p>
                    </div>
                </div>
            </div>
        @elseif($order->table)
            <div class="bg-gradient-to-r from-orange-100 to-yellow-100 border-2 border-orange-300 rounded-xl p-4 shadow-sm">
                <p class="text-xl sm:text-2xl font-bold text-orange-800">Mesa #{{ $order->table->number }}</p>
            </div>
        @endif

        <div class="mt-4 flex flex-col sm:flex-row items-center justify-center gap-2 text-gray-500">
            <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"></path>
            </svg>
            <p class="text-sm font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>

    {{-- PRODUCTOS PARA LA COCINA --}}
    <div class="space-y-4">
        <div class="bg-gradient-to-r from-orange-100 to-red-100 rounded-xl p-4 border-l-4 border-orange-500">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 flex items-center gap-3">
                <div class="bg-orange-500 p-2 rounded-lg">
                    <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5zm5.771 7H9a1 1 0 110-2H8.771l-.546-2.187A1 1 0 019.18 7h1.64a1 1 0 01.955.813L11.229 10H12a1 1 0 110 2h-.771l-.146.585A1 1 0 0110.18 13H9.82a1 1 0 01-.955-.813L8.771 12z" clip-rule="evenodd"></path>
                    </svg>
                </div>
                <span>🍽️ Productos a Preparar</span>
            </h3>
            <p class="text-sm text-gray-600 mt-2">Lista detallada para el área de cocina</p>
        </div>
        
        @forelse($order->orderDetails as $detail)
            <div class="bg-gradient-to-r from-orange-50 to-yellow-50 border-2 border-orange-200 rounded-xl p-4 sm:p-6 hover:from-orange-100 hover:to-yellow-100 transition-all duration-300 shadow-sm hover:shadow-md">
                <div class="flex flex-col lg:flex-row lg:justify-between lg:items-start gap-4">
                    <div class="flex-1 space-y-3">
                        {{-- NOMBRE DEL PRODUCTO --}}
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2">
                            <h4 class="text-lg sm:text-xl font-bold text-gray-900 leading-tight">
                                {{ $detail->product->name ?? 'Producto eliminado' }}
                            </h4>

                            {{-- BADGES DE TEMPERATURA/COCCIÓN --}}
                            <div class="flex flex-wrap gap-1">
                                @if(strpos($detail->notes, 'HELADA') !== false)
                                    <span class="bg-blue-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">❄️ HELADA</span>
                                @elseif(strpos($detail->notes, 'AL TIEMPO') !== false)
                                    <span class="bg-yellow-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">🌡️ AL TIEMPO</span>
                                @elseif(strpos($detail->notes, 'FRESCA') !== false)
                                    <span class="bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">🌿 FRESCA</span>
                                @elseif(strpos($detail->notes, 'ROJO') !== false)
                                    <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">🥩 ROJO</span>
                                @elseif(strpos($detail->notes, 'JUGOSO') !== false)
                                    <span class="bg-pink-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">💧 JUGOSO</span>
                                @elseif(strpos($detail->notes, 'TRES CUARTOS') !== false)
                                    <span class="bg-orange-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">🔥 TRES CUARTOS</span>
                                @elseif(strpos($detail->notes, 'BIEN COCIDO') !== false)
                                    <span class="bg-gray-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">🍖 BIEN COCIDO</span>
                                @endif
                            </div>
                        </div>

                        {{-- CANTIDAD DESTACADA PARA LA COCINA --}}
                        <div class="flex items-center">
                            <div class="bg-gradient-to-r from-orange-500 to-red-500 text-white px-4 py-2 rounded-full shadow-lg">
                                <div class="flex items-center gap-2">
                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="text-sm font-bold">CANTIDAD: {{ $detail->quantity }}</span>
                                </div>
                            </div>
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
                                <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border-l-4 border-yellow-400 p-3 rounded-r-lg shadow-sm">
                                    <div class="flex items-start gap-2">
                                        <div class="bg-yellow-400 p-1 rounded-full flex-shrink-0 mt-0.5">
                                            <svg class="h-3 w-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-yellow-800 mb-1">📝 Instrucciones Especiales:</p>
                                            <p class="text-sm text-yellow-700 font-medium">{{ $notesText }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- PRECIO (OPCIONAL PARA LA COCINA) --}}
                    <div class="flex-shrink-0 text-center lg:text-right">
                        <div class="bg-gray-100 rounded-lg p-3 shadow-sm">
                            <p class="text-xs text-gray-500 font-medium mb-1">Precio Unit.</p>
                            <p class="text-lg font-bold text-gray-700">
                                S/ {{ number_format($detail->unit_price, 2) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-8 sm:py-12">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gradient-to-r from-gray-100 to-gray-200 mb-4 shadow-sm">
                    <svg class="h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-600 mb-2">Sin productos</h3>
                <p class="text-sm text-gray-500">No hay productos en esta orden para preparar</p>
            </div>
        @endforelse
    </div>

    {{-- INFORMACIÓN ADICIONAL PARA LA COCINA --}}
    <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-200 rounded-xl p-4 sm:p-6 shadow-sm">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
            <div class="bg-green-500 p-3 rounded-full flex-shrink-0">
                <svg class="h-6 w-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-lg font-bold text-green-800 mb-2">ℹ️ Información para Cocina</h4>
                <p class="text-sm sm:text-base text-green-700 leading-relaxed">
                    <span class="font-semibold">Esta es la comanda de preparación.</span>
                    @if($isDirectSale && !empty($customerNameForComanda))
                        El pedido es para <strong class="bg-green-200 px-2 py-1 rounded font-bold">{{ $customerNameForComanda }}</strong> (venta directa).
                    @elseif($order->table)
                        El pedido es para la <strong class="bg-green-200 px-2 py-1 rounded font-bold">Mesa #{{ $order->table->number }}</strong>.
                    @endif
                </p>
            </div>
        </div>
    </div>

    {{-- NÚMERO DE COMENSALES --}}
    @if($order->number_of_guests && $order->number_of_guests > 1)
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-center gap-3">
                <div class="bg-blue-500 p-2 rounded-full">
                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"></path>
                    </svg>
                </div>
                <p class="text-base sm:text-lg font-bold text-blue-800">
                    👥 Comensales: <span class="bg-blue-200 px-3 py-1 rounded-full">{{ $order->number_of_guests }} personas</span>
                </p>
            </div>
        </div>
    @endif

    {{-- RESUMEN RÁPIDO --}}
    <div class="bg-gradient-to-r from-gray-50 to-slate-50 border-t-4 border-gray-300 rounded-b-xl p-4 sm:p-6 shadow-inner">
        <h4 class="text-lg font-bold text-gray-800 mb-4 text-center">📊 Resumen de la Orden</h4>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="bg-orange-100 p-2 rounded-full">
                            <svg class="h-4 w-4 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-600">Total Items:</span>
                    </div>
                    <span class="text-xl font-bold text-orange-600">{{ $order->orderDetails->sum('quantity') }}</span>
                </div>
            </div>

            <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div class="bg-blue-100 p-2 rounded-full">
                            <svg class="h-4 w-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-gray-600">Productos:</span>
                    </div>
                    <span class="text-xl font-bold text-blue-600">{{ $order->orderDetails->count() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>