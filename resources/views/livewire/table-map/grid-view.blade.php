<div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-2">
    @forelse ($tables as $table)
        <div 
            wire:key="table-{{ $table->id }}" 
            wire:click="selectTable({{ $table->id }})" 
            class="relative cursor-pointer border rounded-xl overflow-hidden shadow-sm transition-all transform hover:scale-105 {{ $selectedTableId == $table->id ? 'ring-2 ring-primary-500' : '' }}"
        >
            {{-- Identificador de estado usando colores --}}
            <div class="h-2 w-full 
                {{ $table->status === 'available' ? 'bg-emerald-500' : '' }}
                {{ $table->status === 'reserved' ? 'bg-blue-500' : '' }}
                {{ $table->status === 'occupied' ? 'bg-amber-500' : '' }}
                {{ $table->status === 'maintenance' ? 'bg-gray-500' : '' }}
            "></div>
            
            {{-- Información de la mesa --}}
            <div class="p-3">
                <div class="flex justify-between items-start mb-1">
                    <span class="font-semibold">Mesa {{ $table->number }}</span>
                    <span class="text-xs px-1.5 py-0.5 rounded-full 
                        {{ $table->status === 'available' ? 'bg-emerald-100 text-emerald-800' : '' }}
                        {{ $table->status === 'reserved' ? 'bg-blue-100 text-blue-800' : '' }}
                        {{ $table->status === 'occupied' ? 'bg-amber-100 text-amber-800' : '' }}
                        {{ $table->status === 'maintenance' ? 'bg-gray-100 text-gray-800' : '' }}
                    ">
                        {{ $statusOptions[$table->status] ?? $table->status }}
                    </span>
                </div>
                
                <div class="text-xs text-gray-600 dark:text-gray-400">
                    <p class="flex items-center gap-1">
                        <x-heroicon-o-users class="w-3 h-3" />
                        <span>{{ $table->capacity }} personas</span>
                    </p>
                    
                    <p class="flex items-center gap-1">
                        <x-heroicon-o-map-pin class="w-3 h-3" />
                        <span>{{ $locationOptions[$table->location] ?? $table->location }}</span>
                    </p>
                </div>
                
                {{-- Indicadores de reservas o pedidos activos --}}
                <div class="mt-2 flex gap-1">
                    @if($table->activeReservations && $table->activeReservations->count() > 0)
                        <span class="inline-flex items-center bg-blue-100 text-blue-800 text-xs px-1.5 py-0.5 rounded-full">
                            <x-heroicon-o-clock class="w-3 h-3 mr-1" />
                            {{ $table->activeReservations->count() }}
                        </span>
                    @endif
                    
                    @if($table->activeOrders && $table->activeOrders->count() > 0)
                        <span class="inline-flex items-center bg-purple-100 text-purple-800 text-xs px-1.5 py-0.5 rounded-full">
                            <x-heroicon-o-shopping-cart class="w-3 h-3 mr-1" />
                            {{ $table->activeOrders->count() }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-span-full py-8 text-center">
            <x-heroicon-o-face-frown class="mx-auto h-12 w-12 text-gray-400" />
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No hay mesas</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">No se encontraron mesas con los filtros actuales.</p>
        </div>
    @endforelse
</div>
