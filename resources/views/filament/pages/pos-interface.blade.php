<x-filament-panels::page>
    <div class="h-screen flex flex-col bg-gray-50">
        {{-- HEADER SUPERIOR CON CATEGORÍAS (PROPORCIÓN ÁUREA) --}}
        <div class="bg-gradient-to-r from-gray-50 to-gray-100 shadow-sm border-b border-gray-200 px-8 py-6">
            {{-- CATEGORÍAS CON ESPACIADO ÁUREO --}}
            <div class="flex items-center space-x-8 overflow-x-auto pb-3 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100" style="gap: 1.618rem;">
                {{-- Botón Todos --}}
                <x-filament::button
                    wire:click="selectCategory(null)"
                    :color="$selectedCategoryId === null ? 'success' : 'gray'"
                    size="sm"
                    class="flex-shrink-0 px-10 py-3 text-sm font-medium whitespace-nowrap min-w-[100px] transition-all duration-200 hover:scale-105"
                >
                    Todos
                </x-filament::button>

                {{-- Botones de categorías --}}
                @foreach($this->categories as $category)
                    <x-filament::button
                        wire:click="selectCategory({{ $category->id }})"
                        :color="$selectedCategoryId === $category->id ? 'success' : 'gray'"
                        size="sm"
                        class="flex-shrink-0 px-10 py-3 text-sm font-medium whitespace-nowrap min-w-[100px] transition-all duration-200 hover:scale-105"
                    >
                        {{ $category->name }}
                    </x-filament::button>
                @endforeach
            </div>

            {{-- SUBCATEGORÍAS (SEGUNDA FILA) --}}
            @if($selectedCategoryId && $subcategories->isNotEmpty())
                <div class="flex items-center space-x-6 overflow-x-auto pt-4 pb-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100" style="gap: 1rem;">
                    {{-- Botón Todos de subcategorías --}}
                    <x-filament::button
                        wire:click="selectSubcategory(null)"
                        :color="$selectedSubcategoryId === null ? 'info' : 'gray'"
                        size="sm"
                        class="flex-shrink-0 px-8 py-2 text-xs font-medium whitespace-nowrap min-w-[90px] transition-all duration-200 hover:scale-105"
                    >
                        Todos
                    </x-filament::button>

                    {{-- Botones de subcategorías --}}
                    @foreach($subcategories as $subcat)
                        <x-filament::button
                            wire:click="selectSubcategory({{ $subcat->id }})"
                            :color="$selectedSubcategoryId === $subcat->id ? 'info' : 'gray'"
                            size="sm"
                            class="flex-shrink-0 px-8 py-2 text-xs font-medium whitespace-nowrap min-w-[90px] transition-all duration-200 hover:scale-105"
                        >
                            {{ $subcat->name }}
                        </x-filament::button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- CONTENIDO PRINCIPAL: PRODUCTOS + CARRITO (PROPORCIÓN ÁUREA) --}}
        <div class="flex-1 flex overflow-hidden">
            {{-- IZQUIERDA: PRODUCTOS (62% - PROPORCIÓN ÁUREA) --}}
            <div class="flex-1 p-6 overflow-y-auto" style="flex: 1.618;">
                {{-- BARRA DE BÚSQUEDA --}}
                <div class="mb-8">
                    <x-filament::input.wrapper class="mb-4">
                        <x-filament::input
                            type="text"
                            wire:model.debounce.300ms="search"
                            placeholder="Buscar productos..."
                            class="w-full text-base py-3"
                        />
                    </x-filament::input.wrapper>
                </div>

                {{-- GRID DE PRODUCTOS --}}
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 p-4">
                    @forelse ($products as $product)
                        <button
                            wire:click="addToCart({{ $product->id }})"
                            @class([
                                'relative p-4 bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200',
                                'cursor-not-allowed opacity-50' => !$canAddProducts,
                            ])
                            @if(!$canAddProducts)
                                disabled
                                title="No se pueden agregar productos. La orden está guardada."
                            @endif
                        >
                            <div class="text-center">
                                <div class="font-medium text-gray-900 truncate">
                                    {{ $product->name }}
                                </div>
                                <div class="text-sm text-gray-500">
                                    S/ {{ number_format($product->sale_price, 2) }}
                                </div>
                            </div>
                        </button>
                    @empty
                        <div class="col-span-full text-center py-8 text-gray-500">
                            No hay productos disponibles
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- DERECHA: CARRITO (38% - PROPORCIÓN ÁUREA) --}}
            <div class="bg-white border-l border-gray-200 flex flex-col shadow-lg" style="flex: 1; min-width: 380px; max-width: 420px;">
                {{-- HEADER DEL CARRITO --}}
                <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <x-filament::button
                                wire:click="clearCart"
                                color="danger"
                                size="sm"
                                outlined
                                class="h-8 w-8 flex items-center justify-center p-0"
                                title="Limpiar carrito"
                                :disabled="!$canClearCart"
                            >
                                <x-heroicon-m-trash class="h-4 w-4" />
                            </x-filament::button>

                            <div class="flex items-center bg-white rounded-lg border border-gray-300 h-8 overflow-hidden">
                                <div class="px-2 bg-gray-50 border-r border-gray-300 h-full flex items-center">
                                    <x-heroicon-o-users class="h-4 w-4 text-gray-500" />
                                </div>
                                <x-filament::input
                                    type="number"
                                    wire:model="current_diners"
                                    placeholder="# Com."
                                    class="border-0 h-full text-sm w-16 px-2 focus:ring-0"
                                    min="1"
                                />
                            </div>
                        </div>

                        <x-filament::badge color="success" size="lg">
                            {{ count($cartItems) }} items
                        </x-filament::badge>
                    </div>
                </div>

                {{-- ITEMS DEL CARRITO --}}
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    @forelse($cartItems as $index => $item)
                        <div class="bg-gray-50 rounded-lg border p-3 hover:bg-gray-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    {{-- NOMBRE Y PRECIO COMPACTO --}}
                                    <h4 class="text-sm font-semibold text-gray-900 truncate mb-1">{{ $item['name'] }}</h4>
                                    <p class="text-xs text-gray-600 mb-2">S/ {{ number_format($item['unit_price'], 2) }} c/u</p>

                                    {{-- CONTROLES DE CANTIDAD COMPACTOS E INSTANTÁNEOS --}}
                                    <div class="flex items-center justify-between" wire:loading.class="opacity-50" wire:target="updateQuantity({{ $index }})">
                                        <div class="flex items-center space-x-1 bg-white rounded border px-1">
                                            <x-filament::icon-button
                                                icon="heroicon-m-minus"
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                                size="sm"
                                                color="gray"
                                                class="h-6 w-6"
                                                tag="button"
                                                label="Restar uno"
                                            />
                                            <span class="text-sm font-semibold min-w-[1.5rem] text-center">{{ $item['quantity'] }}</span>
                                            <x-filament::icon-button
                                                icon="heroicon-m-plus"
                                                wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                                size="sm"
                                                color="success"
                                                class="h-6 w-6"
                                                tag="button"
                                                label="Añadir uno"
                                            />
                                        </div>

                                        {{-- SUBTOTAL COMPACTO --}}
                                        <span class="text-sm font-bold text-green-600">
                                            S/ {{ number_format($item['subtotal'], 2) }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <x-heroicon-o-shopping-cart class="h-16 w-16 text-gray-400 mx-auto mb-4" />
                            <p class="text-gray-500 text-base font-medium">Carrito vacío</p>
                            <p class="text-gray-400 text-sm mt-1">Selecciona productos para agregar</p>
                        </div>
                    @endforelse
                </div>

                {{-- FOOTER: TOTALES Y ACCIÓN --}}
                @if(count($cartItems) > 0)
                    <div class="border-t border-gray-200 p-4 bg-gray-50 space-y-4">
                        {{-- TOTALES --}}
                        <div class="space-y-2 bg-white rounded-lg p-3 border">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal:</span>
                                <span class="font-semibold">S/ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">IGV (18%):</span>
                                <span class="font-semibold">S/ {{ number_format($tax, 2) }}</span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex justify-between text-lg font-bold">
                                <span class="text-gray-900">Total:</span>
                                <span class="text-green-600">S/ {{ number_format($total, 2) }}</span>
                            </div>
                        </div>

                        {{-- BOTONES DE ACCIÓN CONDICIONALES --}}
                        <div class="space-y-3">
                            {{-- ✅ VENTA DIRECTA: IR DIRECTO A PAGAR SIN CREAR ORDEN PRIMERO --}}
                            @if($selectedTableId === null && !$order)
                                @if(auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                    <x-filament::button
                                        wire:click="mountAction('processBilling')"
                                        color="success"
                                        size="lg"
                                        class="w-full py-3 text-base font-bold"
                                        :disabled="!count($cartItems)"
                                    >
                                        <x-heroicon-m-credit-card class="h-5 w-5 mr-2" />
                                        Emitir Comprobante
                                    </x-filament::button>
                                @endif
                            @elseif(!$order || ($order && !$order->invoices()->exists()))
                                {{-- VENTA CON MESA: GUARDAR ORDEN PRIMERO --}}
                                <x-filament::button
                                    wire:click="processOrder"
                                    color="primary"
                                    size="lg"
                                    class="w-full py-3 text-base font-bold"
                                    :disabled="!count($cartItems)"
                                >
                                    <x-heroicon-m-check-circle class="h-5 w-5 mr-2" />
                                    Guardar Orden
                                </x-filament::button>
                            @endif

                            {{-- BOTÓN PARA PROCEDER AL PAGO DE LA ORDEN YA CREADA --}}
                            @if($order && !$order->invoices()->exists() && auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                <x-filament::button
                                    wire:click="mountAction('processBilling')"
                                    color="success"
                                    size="lg"
                                    class="w-full py-3 text-base font-bold"
                                >
                                    <x-heroicon-m-credit-card class="h-5 w-5 mr-2" />
                                    Emitir Comprobante
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL DE IMPRESIÓN --}}
    <div
                x-data="{
            open: false,
            type: '',
            url: '',
            title: '',
            printProcessing: false,
            init() {
                // Escuchar el evento de Livewire SOLO UNA VEZ
                $wire.on('open-print-modal', (event) => {
                    console.log('Evento recibido:', event);
                    this.type = event.type;
                    this.url = event.url;
                    this.title = event.title;
                    this.open = true;
                });

                // LISTENER ÚNICO para impresión automática
                if (!window.posInterfacePrintListenerAdded) {
                    window.posInterfacePrintListenerAdded = true;
                    $wire.on('open-print-window', (event) => {
                        if (this.printProcessing) return;
                        this.printProcessing = true;

                        console.log('🖨️ POS Interface - Imprimiendo comprobante...', event);

                        // Extraer ID del evento
                        let invoiceId = Array.isArray(event) ? (event[0]?.id || event[0]) : (event?.id || event);

                        if (!invoiceId) {
                            console.error('❌ Error: ID de comprobante no encontrado');
                            this.printProcessing = false;
                            return;
                        }

                        // Delay para DB + abrir ventana
                        setTimeout(() => {
                            const printUrl = `/print/invoice/${invoiceId}`;
                            console.log('🔗 Abriendo ventana de impresión:', printUrl);
                            window.open(printUrl, 'invoice_print_' + invoiceId, 'width=800,height=600,scrollbars=yes,resizable=yes');
                            this.printProcessing = false;
                        }, 800);
                    });
                }
            }
        }"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            {{-- OVERLAY con redirección al cerrar --}}
            <div
                @click="open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
                aria-hidden="true"></div>

            {{-- CONTENIDO DEL MODAL --}}
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div
                @click.outside="open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6"
            >
                {{-- ICONO Y TÍTULO --}}
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                        <x-heroicon-o-check-circle class="h-10 w-10 text-green-500 mx-auto"/>
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title" x-text="title">
                            <!-- El título se inyectará aquí -->
                        </h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                El comprobante se ha generado exitosamente. Puedes imprimirlo o descargarlo.
                            </p>
                        </div>
                    </div>
                </div>

                {{-- BOTONES DE ACCIÓN --}}
                <div class="mt-5 sm:mt-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <a :href="url" target="_blank" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm">
                        <x-heroicon-o-printer class="h-5 w-5 mr-2"/>
                        Imprimir / Descargar
                    </a>
                    <button
                        @click="open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/product-images.css') }}">
@endpush
