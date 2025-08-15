<div class="p-4 md:p-5 xl:p-6 overflow-y-auto" style="max-height:75vh;">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 border-b border-gray-200 pb-3 mb-4">
        <div>
            <h2 class="text-base md:text-lg font-semibold text-gray-900 tracking-tight">Pre-Cuenta</h2>
            <p class="text-[10px] md:text-[11px] text-gray-500 font-medium">Orden #{{ $order->id }} · {{ $order->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap text-[10px] md:text-[11px] text-gray-600">
            @if($order->table)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 border border-gray-200 font-medium">
                    <x-heroicon-o-table-cells class="w-3.5 h-3.5" /> Mesa #{{ $order->table->number }}
                </span>
            @endif
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 border border-gray-200 font-medium">
                <x-heroicon-o-clock class="w-3.5 h-3.5" /> {{ $order->created_at->diffForHumans(short:true) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-5">
        <!-- Columna productos -->
        <div class="xl:col-span-8 space-y-3">
            <h3 class="text-[11px] md:text-xs font-semibold text-gray-800 mb-2 uppercase tracking-wide">Productos</h3>
            <div class="space-y-1.5 max-h-[46vh] overflow-y-auto thin-scrollbar pr-1">
                @forelse($order->orderDetails as $detail)
                    <div class="bg-white border border-gray-200 rounded-md p-2.5 flex items-start justify-between gap-3 shadow-sm hover:shadow transition-shadow">
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] md:text-[12px] font-semibold text-gray-900 truncate">{{ $detail->product->name ?? 'Producto eliminado' }}</p>
                            <p class="text-[10px] md:text-[11px] text-gray-500">{{ $detail->quantity }} x S/ {{ number_format($detail->unit_price, 2) }}</p>
                        </div>
                        <div class="text-right whitespace-nowrap">
                            <p class="text-[11px] md:text-[12px] font-semibold text-gray-900">S/ {{ number_format($detail->subtotal, 2) }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <h3 class="text-[12px] font-semibold text-gray-600 mb-1">Sin productos</h3>
                        <p class="text-[10px] text-gray-500">No hay productos en esta orden</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Sidebar resumen -->
        <div class="xl:col-span-4 space-y-3">
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3.5">
                <h4 class="text-[11px] md:text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Totales</h4>
                <ul class="space-y-1.5">
                    <li class="flex items-center justify-between text-[10px] md:text-[11px]">
                        <span class="text-gray-600">Subtotal</span>
                        <span class="font-semibold text-gray-900">S/ {{ number_format($subtotal, 2) }}</span>
                    </li>
                    <li class="flex items-center justify-between text-[10px] md:text-[11px]">
                        <span class="text-gray-600">IGV (18%)</span>
                        <span class="font-semibold text-gray-900">S/ {{ number_format($tax, 2) }}</span>
                    </li>
                    <li class="flex items-center justify-between pt-1 mt-1 border-t border-gray-200 text-[11px] md:text-[12px] font-semibold">
                        <span class="text-gray-800">Total</span>
                        <span class="text-green-600">S/ {{ number_format($total, 2) }}</span>
                    </li>
                </ul>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3.5">
                <p class="flex items-start gap-2 text-[9px] md:text-[10px] text-blue-700 leading-relaxed">
                    <svg class="w-4 h-4 text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                    Esta es una pre-cuenta. No es un comprobante válido para efectos fiscales.
                </p>
            </div>

            @if($order->customer && $order->customer->id !== 1)
                <div class="bg-white border border-gray-200 rounded-lg p-3.5 shadow-sm">
                    <h4 class="text-[11px] md:text-xs font-semibold text-gray-700 mb-2 uppercase tracking-wide">Cliente</h4>
                    <ul class="space-y-1.5 text-[10px] md:text-[11px] text-gray-700">
                        <li><span class="font-medium text-gray-600">Nombre:</span> <span class="font-semibold text-gray-900">{{ $order->customer->name }}</span></li>
                        @if($order->customer->document_number)
                            <li><span class="font-medium text-gray-600">Documento:</span> {{ $order->customer->document_type }} {{ $order->customer->document_number }}</li>
                        @endif
                        @if($order->customer->phone)
                            <li><span class="font-medium text-gray-600">Teléfono:</span> {{ $order->customer->phone }}</li>
                        @endif
                    </ul>
                </div>
            @endif

            <div class="hidden xl:block text-[9px] md:text-[10px] text-gray-400 text-right pt-1.5">
                Generado: {{ now()->format('d/m/Y H:i') }}
            </div>
        </div>
    </div>
</div>

<style>
    .thin-scrollbar::-webkit-scrollbar { width:6px; }
    .thin-scrollbar::-webkit-scrollbar-track { background:transparent; }
    .thin-scrollbar::-webkit-scrollbar-thumb { background:rgba(0,0,0,0.15); border-radius:3px; }
    @media (max-width:640px){ .thin-scrollbar::-webkit-scrollbar { width:4px; } }
</style>
