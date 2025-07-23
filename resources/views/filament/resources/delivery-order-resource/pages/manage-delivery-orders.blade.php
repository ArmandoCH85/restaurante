<x-filament-panels::page>
    <div class="h-screen flex overflow-hidden bg-gray-50">
        {{-- IZQUIERDA: TABLA FILAMENT DE DELIVERY CON SEMÁFOROS --}}
        <div class="flex-1 overflow-hidden">
            <div class="h-full p-6">
                <div class="bg-white rounded-lg shadow-sm h-full">
                    {{ $this->table }}
                </div>
            </div>
        </div>
        
        {{-- DERECHA: FORMULARIO NUEVO PEDIDO --}}
        <div class="w-80 bg-white border-l border-gray-200 flex flex-col shadow-lg">
            {{-- HEADER DEL FORMULARIO --}}
            <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                <h3 class="text-base font-semibold text-gray-800 flex items-center">
                    <x-heroicon-s-plus class="h-4 w-4 mr-2 text-green-600" />
                    Nuevo Pedido
                </h3>
            </div>
            
            {{-- FORMULARIO NUEVO DELIVERY --}}
            <div class="flex-1 overflow-y-auto p-4">
                <form wire:submit="createDeliveryOrder" class="space-y-4 h-full flex flex-col">
                    <div class="flex-1">
                        {{ $this->newDeliveryForm }}
                    </div>
                    
                    {{-- BOTONES DE ACCIÓN FIJOS AL FINAL --}}
                    <div class="mt-auto pt-3 border-t border-gray-200 bg-white sticky bottom-0">
                        <div class="flex gap-2">
                            <button 
                                type="button"
                                wire:click="resetForm"
                                class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded transition-colors duration-200"
                            >
                                🗑️ Limpiar
                            </button>
                            
                            <button 
                                type="submit"
                                class="flex-1 px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded transition-colors duration-200"
                            >
                                ✅ Continuar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-filament-panels::page>