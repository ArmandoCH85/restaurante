<div class="p-4 md:p-5 xl:p-6 overflow-y-auto grow text-[13px]" style="max-height:75vh;">
    <!-- Cabecera -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 border-b border-gray-200 pb-3 mb-4">
        <div>
            <h2 class="text-base md:text-lg font-semibold text-gray-900 tracking-tight">Comanda</h2>
            <p class="text-[10px] md:text-[11px] text-gray-500 font-medium">Orden #{{ $order->id }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
    <div class="flex items-center gap-3 text-[10px] md:text-[11px] text-gray-600">
            @if($order->table)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 border border-gray-200 font-medium text-[10px] md:text-[11px]">
                    <x-heroicon-o-table-cells class="w-3.5 h-3.5" /> Mesa #{{ $order->table->number }}
                </span>
            @endif
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 border border-gray-200 font-medium text-[10px] md:text-[11px]">
                <x-heroicon-o-clock class="w-3.5 h-3.5" /> {{ $order->created_at->diffForHumans(short:true) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5 relative">
        <!-- Columna principal productos -->
    <div class="xl:col-span-8 space-y-3">
            <!-- Destino / Cliente -->
            @if($isDirectSale)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 md:p-3.5">
                    <label class="block text-[10px] md:text-[11px] font-semibold text-gray-700 mb-1 uppercase tracking-wide">Nombre del cliente</label>
                    <input
                        type="text"
                        wire:model.debounce.400ms.trim="customerNameForComanda"
                        placeholder="Ej: Juan Pérez o Mostrador"
                        class="w-full rounded-md border border-gray-300 focus:border-indigo-500 focus:ring-indigo-200 px-3 py-2 text-[11px] md:text-[12px] text-gray-900 placeholder-gray-400"
                        autofocus
                        maxlength="100"
                    />
                    <p class="mt-1 text-[9px] md:text-[10px] text-gray-500 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        Requerido en venta directa para imprimir.
                    </p>
                </div>
            @elseif($order->table)
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 md:p-3.5 text-[11px] md:text-[12px] text-gray-700 flex items-center gap-2">
                    <span class="font-semibold">Mesa:</span> #{{ $order->table->number }}
                </div>
            @endif

            <!-- Lista de productos -->
            <div>
                <h3 class="text-[11px] md:text-xs font-semibold text-gray-800 mb-2 uppercase tracking-wide">Productos</h3>
                <div class="space-y-2 max-h-[46vh] overflow-y-auto pr-1 thin-scrollbar">
                    @forelse($order->orderDetails as $detail)
                        <div class="bg-white border border-gray-200 rounded-md p-2.5 group shadow-sm hover:shadow transition-shadow">
                            <div class="flex flex-col gap-2">
                                <div class="flex items-start justify-between gap-3">
                                    <h4 class="text-[11px] md:text-[12px] font-semibold text-gray-900 leading-snug">
                                        {{ $detail->product->name ?? 'Producto eliminado' }}
                                    </h4>
                                    <div class="text-[9px] md:text-[10px] text-gray-500 font-medium whitespace-nowrap">
                                        Cant: <span class="text-gray-800 font-semibold">{{ $detail->quantity }}</span>
                                    </div>
                                </div>

                                @php
                                    $notesText = $detail->notes ?? '';
                                    $notesText = str_replace(['HELADA', 'AL TIEMPO', 'FRESCA', 'ROJO', 'JUGOSO', 'TRES CUARTOS', 'BIEN COCIDO'], '', $notesText);
                                    $notesText = trim($notesText);
                                @endphp
                                @if($notesText)
                                    <div class="bg-yellow-50 border border-yellow-200/70 p-1.5 rounded-md">
                                        <p class="text-[9px] md:text-[10px] font-semibold text-yellow-800 mb-0.5 flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 20 20" fill="currentColor">
                                                <path d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l6.518 11.593c.75 1.336-.213 2.995-1.742 2.995H3.48c-1.53 0-2.493-1.659-1.743-2.995L8.257 3.1z" />
                                            </svg>
                                            Instrucciones
                                        </p>
                                        <p class="text-[9px] md:text-[10px] text-yellow-700 leading-snug">{{ $notesText }}</p>
                                    </div>
                                @endif

                                <div class="flex items-center justify-end text-[9px] md:text-[10px] text-gray-600 pt-1 border-t border-gray-100">
                                    <span class="font-medium text-gray-500 mr-1">Precio:</span>
                                    <span class="font-semibold text-gray-800">S/ {{ number_format($detail->unit_price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <h3 class="text-[12px] font-semibold text-gray-600 mb-1">Sin productos</h3>
                            <p class="text-[10px] text-gray-500">No hay productos en esta orden para preparar</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Resumen -->
    <div class="xl:col-span-4 space-y-3">
            <div class="bg-white border border-gray-200 rounded-lg p-3.5 shadow-sm">
                <h4 class="text-[11px] md:text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Destino</h4>
                <div class="text-[10px] md:text-[11px] text-gray-700 leading-relaxed">
                    @if($isDirectSale && !empty($customerNameForComanda))
                        <span class="font-semibold text-gray-900">{{ $customerNameForComanda }}</span>
                        <span class="block text-[10px] text-gray-500">Venta directa</span>
                    @elseif($order->table)
                        <span class="font-semibold text-gray-900">Mesa #{{ $order->table->number }}</span>
                    @else
                        <span class="text-gray-500">N/D</span>
                    @endif
                </div>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3.5">
                <h4 class="text-[11px] md:text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Resumen</h4>
                <ul class="space-y-2">
                    <li class="flex items-center justify-between text-[10px] md:text-[11px]">
                        <span class="text-gray-600">Total ítems</span>
                        <span class="font-semibold text-gray-900">{{ $order->orderDetails->sum('quantity') }}</span>
                    </li>
                    <li class="flex items-center justify-between text-[10px] md:text-[11px]">
                        <span class="text-gray-600">Productos diferentes</span>
                        <span class="font-semibold text-gray-900">{{ $order->orderDetails->count() }}</span>
                    </li>
                </ul>
            </div>

            <div class="hidden xl:block text-[9px] md:text-[10px] text-gray-400 text-right pt-1.5">
                Generado: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>

<style>
    /* Scrollbar sutil para lista productos */
    .thin-scrollbar::-webkit-scrollbar { width: 6px; }
    .thin-scrollbar::-webkit-scrollbar-track { background: transparent; }
    .thin-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.15); border-radius: 3px; }
    @media (max-width: 640px) {
        .thin-scrollbar::-webkit-scrollbar { width: 4px; }
    }
</style>
