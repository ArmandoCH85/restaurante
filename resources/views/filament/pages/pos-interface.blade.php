<x-filament-panels::page>
    <style>
        /* ========================================= */
        /* LAYOUT INTELIGENTE - RESPETA SIDEBAR DE FILAMENT */
        /* ========================================= */
        
        /* VARIABLES DINÁMICAS */
        :root {
            --cart-width: 420px;
        }
        
        /* CARRITO FIJO EN LADO DERECHO - SOLO ESTO ES FIJO */
        .pos-cart {
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            width: var(--cart-width) !important;
            height: 100vh !important;
            z-index: 1000 !important;
            background: white !important;
            border-left: 1px solid #e5e7eb !important;
            box-shadow: -4px 0 12px rgba(0, 0, 0, 0.1) !important;
            overflow-y: auto !important;
        }
        
        /* CONTENIDO PRINCIPAL - SE AJUSTA DINÁMICAMENTE */
        .pos-main-content {
            margin-right: calc(var(--cart-width) + 4px) !important; /* 4px gap mínimo */
            transition: margin-right 0.3s ease !important;
            height: 100vh !important; /* Misma altura que carrito */
            position: relative !important;
        }
        
        /* CUANDO SIDEBAR DE FILAMENT ESTÁ ABIERTO */
        .fi-sidebar ~ .fi-main .pos-main-content {
            margin-right: calc(var(--cart-width) + 4px) !important;
            /* Filament maneja el margen izquierdo automáticamente */
        }
        
        /* CUANDO SIDEBAR DE FILAMENT ESTÁ CERRADO */
        .fi-sidebar-close ~ .fi-main .pos-main-content,
        body:not(.fi-sidebar-open) .pos-main-content {
            margin-right: calc(var(--cart-width) + 4px) !important;
            /* Sin sidebar, el contenido se expande automáticamente */
        }
        
        /* AJUSTES ESPECÍFICOS PARA EL CONTENIDO DEL POS */
        .pos-categories {
            width: 200px !important;
            min-width: 200px !important;
            flex-shrink: 0 !important;
            height: 100vh !important; /* Misma altura que carrito */
        }
        
        .pos-products {
            flex: 1 !important;
            min-width: 0 !important; /* Permite que se encoja si es necesario */
            height: 100vh !important; /* Misma altura que carrito */
            overflow-y: auto !important;
        }
        
        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 1024px) {
            :root {
                --cart-width: 350px;
            }
            
            .pos-categories {
                width: 180px !important;
                min-width: 180px !important;
            }
            
            .pos-main-content {
                margin-right: calc(350px + 4px) !important;
            }
        }
        
        @media (max-width: 768px) {
            :root {
                --cart-width: 320px;
            }
            
            .pos-categories {
                width: 160px !important;
                min-width: 160px !important;
            }
            
            .pos-main-content {
                margin-right: calc(320px + 4px) !important;
            }
        }
        
        /* SCROLLBARS PERSONALIZADOS */
        .pos-cart::-webkit-scrollbar,
        .pos-products::-webkit-scrollbar {
            width: 6px;
        }
        
        .pos-cart::-webkit-scrollbar-track,
        .pos-products::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        
        .pos-cart::-webkit-scrollbar-thumb,
        .pos-products::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        
        .pos-cart::-webkit-scrollbar-thumb:hover,
        .pos-products::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* ASEGURAR QUE FILAMENT FUNCIONE NORMAL */
        .fi-main {
            transition: margin-left 0.3s ease !important;
        }
        
        /* NO TOCAR NADA MÁS DE FILAMENT */
        .pos-interface {
            position: relative !important;
            width: 100% !important;
            height: auto !important;
        }
    </style>
    <div class="pos-interface">
        <div class="pos-main-content flex overflow-hidden h-screen">
            {{-- SIDEBAR IZQUIERDO: CATEGORÍAS --}}
            <div class="pos-categories bg-gradient-to-b from-blue-100 to-blue-200 shadow-lg border-r border-blue-300 flex flex-col">
                {{-- HEADER DEL SIDEBAR --}}
                <div class="px-4 py-4 border-b border-blue-300">
                    <h3 class="text-sm font-bold text-blue-800 text-center">Categorías</h3>
                </div>
                
                {{-- CATEGORÍAS VERTICALES --}}
                <div class="flex-1 overflow-y-auto py-2 px-2 space-y-1 scrollbar-thin scrollbar-thumb-blue-400 scrollbar-track-blue-200">
                    @foreach($this->getCategoriesProperty() as $category)
                        <x-filament::button
                            wire:click="selectCategory({{ $category->id }})"
                            :color="$selectedCategoryId === $category->id ? 'primary' : 'gray'"
                            size="sm"
                            class="w-full justify-start px-4 py-3 text-sm font-medium text-left transition-all duration-200 hover:scale-[1.02] rounded-lg border border-gray-300 hover:border-gray-400 shadow-sm hover:shadow-md mb-1"
                        >
                            {{ $category->name }}
                        </x-filament::button>
                    @endforeach
                </div>
                
                {{-- SUBCATEGORÍAS (SI EXISTEN) --}}
                @if($selectedCategoryId && $subcategories->isNotEmpty())
                    <div class="border-t border-blue-300 px-2 py-3">
                        <h4 class="text-xs font-semibold text-blue-700 mb-2 px-2">Subcategorías</h4>
                        <div class="space-y-1">
                            {{-- Botón Todos de subcategorías --}}
                            <x-filament::button
                                wire:click="selectSubcategory(null)"
                                :color="$selectedSubcategoryId === null ? 'primary' : 'gray'"
                                size="sm"
                                class="w-full justify-start px-3 py-2 text-xs font-medium text-left transition-all duration-200 hover:scale-[1.02] rounded-md border border-gray-300 hover:border-gray-400 shadow-sm hover:shadow-md"
                            >
                                Todos
                            </x-filament::button>

                            {{-- Botones de subcategorías --}}
                            @foreach($subcategories as $subcat)
                                <x-filament::button
                                    wire:click="selectSubcategory({{ $subcat->id }})"
                                    :color="$selectedSubcategoryId === $subcat->id ? 'primary' : 'gray'"
                                    size="sm"
                                    class="w-full justify-start px-3 py-2 text-xs font-medium text-left transition-all duration-200 hover:scale-[1.02] rounded-md border border-gray-300 hover:border-gray-400 shadow-sm hover:shadow-md"
                                >
                                    {{ $subcat->name }}
                                </x-filament::button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            
            {{-- CENTRO: PRODUCTOS --}}
            <div class="pos-products flex-1 p-2 overflow-y-auto">
                {{-- BARRA DE BÚSQUEDA --}}
                <div class="mb-4">
                    <x-filament::input.wrapper class="mb-2">
                        <x-filament::input
                            type="text"
                            wire:model.debounce.300ms="search"
                            placeholder="Buscar productos..."
                            class="w-full text-base py-3"
                        />
                    </x-filament::input.wrapper>
                </div>

                {{-- GRID RESPONSIVO NATIVO DE FILAMENT/TAILWIND --}}
                <x-filament::section>
                    <div class="grid gap-3 grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7">
                    @forelse ($products as $product)
                            {{-- Card de producto usando componentes nativos --}}
                            <div class="bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden hover:shadow-lg transition-shadow duration-200">
                        <button
                            wire:click="addToCart({{ $product->id }})"
                            @class([
                                        'w-full p-4 text-center transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800',
                                'cursor-not-allowed opacity-50' => !$canAddProducts,
                            ])
                            @if(!$canAddProducts)
                                disabled
                                title="No se pueden agregar productos. La orden está guardada."
                            @endif
                        >
                                    {{-- Imagen del producto --}}
                                    <div class="product-image-container mx-auto mb-3">
                                    @if($product->image_path)
                                            <img 
                                                src="{{ $product->image }}" 
                                                alt="{{ $product->name }}" 
                                                class="w-16 h-16 object-cover rounded-lg mx-auto"
                                            />
                                    @else
                                            <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-lg flex items-center justify-center mx-auto">
                                                <span class="text-lg font-bold text-gray-500 dark:text-gray-400">
                                                {{ strtoupper(substr($product->name, 0, 2)) }}
                                            </span>
                                            </div>
                                        @endif
                                    </div>
                                    
                                    {{-- Nombre del producto --}}
                                    <h3 class="pos-responsive-text font-medium text-gray-800 dark:text-gray-200 text-center leading-tight mb-1 min-h-[2.5rem] flex items-center justify-center">
                                        {{ $product->name }}
                                    </h3>
                                    
                                    {{-- Precio del producto --}}
                                    <p class="pos-responsive-price text-green-600 dark:text-green-400">
                                        S/ {{ number_format($product->sale_price, 2) }}
                                    </p>

                                    {{-- Badge de categoría (opcional) --}}
                                    @if($product->category)
                                        <div class="mt-2">
                                            <x-filament::badge 
                                                color="gray" 
                                                size="sm"
                                            >
                                                {{ $product->category->name }}
                                            </x-filament::badge>
                                        </div>
                                    @endif
                                </button>
                            </div>
                        @empty
                            {{-- Estado vacío --}}
                            <div class="col-span-full">
                                <div class="text-center py-12">
                                    <x-filament::icon
                                        icon="heroicon-o-shopping-bag"
                                        class="w-12 h-12 text-gray-400 mx-auto mb-4"
                                    />
                                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                        No hay productos disponibles
                                    </h3>
                                    <p class="text-gray-500 dark:text-gray-400">
                                        @if($search || $selectedCategoryId)
                                            No se encontraron productos que coincidan con los filtros aplicados.
                                        @else
                                            No hay productos registrados en el sistema.
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Footer con información adicional --}}
                    @if($products && $products->count() > 0)
                        <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    @if($search || $selectedCategoryId)
                                        {{ $products->count() }} productos filtrados
                                    @else
                                        {{ $products->count() }} productos disponibles
                                    @endif
                                </div>
                                <div class="text-xs text-gray-400 dark:text-gray-500">
                                    Actualizado: {{ now()->format('H:i:s') }}
                                </div>
                            </div>
                        </div>
                    @endif
                </x-filament::section>
            </div>
        </div>

        {{-- DERECHA: CARRITO FIJO --}}
        <div class="pos-cart bg-white flex flex-col">
                {{-- HEADER DEL CARRITO --}}
                <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-green-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Carrito de Compra
                        </h3>
                        <x-filament::badge color="success" size="lg">
                            {{ count($cartItems) }} items
                        </x-filament::badge>
                    </div>

                    <!-- Controles del Carrito: Comensales y Limpiar -->
                    <div class="flex items-end justify-between gap-6">
                        <!-- Selector de Comensales -->
                        <div class="flex-grow">
                            <label for="numberOfGuests" class="block text-sm font-medium text-gray-700 mb-1.5">
                                Número de Comensales <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                    <x-heroicon-s-users class="h-5 w-5 text-gray-400" />
                                </div>
                                <input
                                    type="number"
                                    wire:model.live="numberOfGuests"
                                    min="1"
                                    class="pl-11 block w-full rounded-lg border-gray-300 text-center shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    placeholder="Ingrese el número de comensales"
                                    required
                                >
                            </div>
                        </div>

                        <!-- Botón Limpiar -->
                        <button
                            wire:click="clearCart"
                            class="h-10 w-10 flex items-center justify-center rounded-full bg-red-50 text-red-600 hover:bg-red-100 focus:outline-none"
                            title="Limpiar carrito"
                            {{ !$canClearCart ? 'disabled' : '' }}
                        >
                            <x-heroicon-s-trash class="h-5 w-5" />
                        </button>
                    </div>
                    
                    {{-- BARRA DE ACCIONES RÁPIDAS --}}
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg border border-gray-200">
                        <h4 class="text-xs font-semibold text-gray-600 mb-2 text-center">Acciones Rápidas</h4>
                        <div class="grid grid-cols-4 gap-1 justify-items-center" style="display: grid !important; grid-template-columns: repeat(4, 1fr) !important;">
                            {{-- FILA 1: Mapa, Comanda, Pre-Cuenta, Liberar --}}
                            
                            {{-- Mapa de Mesas --}}
                            <button 
                                wire:click="mountAction('backToTableMap')"
                                class="p-2 rounded-full hover:bg-blue-100 transition-all duration-200 hover:scale-110 group"
                                title="Ir a Mapa de Mesas"
                                @if(!($this->order && $this->order->table_id !== null)) disabled class="opacity-50 cursor-not-allowed" @endif
                            >
                                <x-heroicon-o-map class="h-5 w-5 text-gray-600 group-hover:text-blue-600" />
                            </button>
                            
                            {{-- Comanda --}}
                            <button 
                                wire:click="mountAction('printComanda')"
                                class="p-2 rounded-full hover:bg-blue-100 transition-all duration-200 hover:scale-110 group"
                                title="Imprimir Comanda"
                                @if(!($this->order || !empty($this->cartItems))) disabled class="opacity-50 cursor-not-allowed" @endif
                            >
                                <x-heroicon-o-document-text class="h-5 w-5 text-gray-600 group-hover:text-blue-600" />
                            </button>
                            
                            {{-- Pre-Cuenta --}}
                            <button 
                                wire:click="mountAction('printPreBillNew')"
                                class="p-2 rounded-full hover:bg-blue-100 transition-all duration-200 hover:scale-110 group"
                                title="Generar Pre-Cuenta"
                                @if(!($this->order || !empty($this->cartItems))) disabled class="opacity-50 cursor-not-allowed" @endif
                            >
                                <x-heroicon-o-document-duplicate class="h-5 w-5 text-gray-600 group-hover:text-blue-600" />
                            </button>
                            
                            {{-- Liberar Mesa --}}
                            @if(!auth()->user()->hasRole(['waiter', 'cashier']))
                                <button 
                                    wire:click="mountAction('releaseTable')"
                                    class="p-2 rounded-full hover:bg-blue-100 transition-all duration-200 hover:scale-110 group"
                                    title="Liberar Mesa"
                                    @if(!($this->order && $this->order->table_id && $this->order->status === 'open')) disabled class="opacity-50 cursor-not-allowed" @endif
                                >
                                    <x-heroicon-o-arrow-right-start-on-rectangle class="h-5 w-5 text-gray-600 group-hover:text-blue-600" />
                                </button>
                            @else
                                <div class="p-2 rounded-full bg-gray-200 opacity-50" title="Sin permisos">
                                    <x-heroicon-o-arrow-right-start-on-rectangle class="h-5 w-5 text-gray-400" />
                                </div>
                            @endif
                            
                            {{-- FILA 2: Reabrir, Transferir, Dividir, Cancelar --}}
                            
                            {{-- Reabrir Orden --}}
                            <button 
                                wire:click="mountAction('reopen_order_for_editing')"
                                class="p-2 rounded-full hover:bg-blue-100 transition-all duration-200 hover:scale-110 group"
                                title="Reabrir Orden"
                                @if(!($this->order instanceof \App\Models\Order && !$this->order->invoices()->exists())) disabled class="opacity-50 cursor-not-allowed" @endif
                            >
                                <x-heroicon-o-lock-open class="h-5 w-5 text-gray-600 group-hover:text-blue-600" />
                            </button>
                            
                            {{-- Transferir --}}
                            @if(!auth()->user()->hasRole(['waiter', 'cashier']))
                                <button 
                                    wire:click="mountAction('transferOrder')"
                                    class="p-2 rounded-full hover:bg-blue-100 transition-all duration-200 hover:scale-110 group"
                                title="Transferir Mesa"
                                @if(!($this->order && $this->order->table_id && $this->order->status === 'open')) disabled class="opacity-50 cursor-not-allowed" @endif
                            >
                                <x-heroicon-o-arrow-path-rounded-square class="h-5 w-5 text-gray-600 group-hover:text-blue-600" />
                            </button>
                            @else
                                <div class="p-2 rounded-full bg-gray-200 opacity-50" title="Sin permisos">
                                    <x-heroicon-o-arrow-path-rounded-square class="h-5 w-5 text-gray-400" />
                                </div>
                            @endif
                            
                            {{-- Dividir Cuenta --}}
                            <button 
                                wire:click="mountAction('split_items')"
                                class="p-2 rounded-full hover:bg-blue-100 transition-all duration-200 hover:scale-110 group"
                                title="Dividir Cuenta"
                                @if(!($this->order !== null && count($this->order->orderDetails ?? []) > 0)) disabled class="opacity-50 cursor-not-allowed" @endif
                            >
                                <x-heroicon-o-scissors class="h-5 w-5 text-gray-600 group-hover:text-blue-600" />
                            </button>
                            
                            {{-- Cancelar Orden --}}
                            @if(!auth()->user()->hasRole(['waiter', 'cashier']))
                                <button 
                                    wire:click="mountAction('cancelOrder')"
                                    class="p-2 rounded-full hover:bg-red-100 transition-all duration-200 hover:scale-110 group"
                                title="Cancelar Orden"
                                @if(!($this->order && $this->order->table_id && $this->order->status === 'open')) disabled class="opacity-50 cursor-not-allowed" @endif
                            >
                                <x-heroicon-o-x-circle class="h-5 w-5 text-gray-600 group-hover:text-red-600" />
                            </button>
                            @else
                                <div class="p-2 rounded-full bg-gray-200 opacity-50" title="Sin permisos">
                                    <x-heroicon-o-x-circle class="h-5 w-5 text-gray-400" />
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ITEMS DEL CARRITO --}}
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    @forelse($cartItems as $index => $item)
                        <div class="bg-gray-50 rounded-lg border p-3 hover:bg-gray-100 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex-1 min-w-0">
                                    {{-- NOMBRE Y PRECIO COMPACTO --}}
                                    <h4 class="pos-responsive-text font-semibold text-gray-900 truncate mb-1">{{ $item['name'] }}</h4>
                                    
                                    {{-- CHECKBOX PARA BEBIDAS HELADAS --}}
                                    @if($item['is_cold_drink'] ?? false)
                                        <div class="flex items-center space-x-4 mb-2">
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.temperature"
                                                    value="HELADA"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="cold-drink-{{ $index }}-cold"
                                                >
                                                <label for="cold-drink-{{ $index }}-cold" class="ml-2 text-sm text-gray-600">
                                                    Helada
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.temperature"
                                                    value="AL TIEMPO"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="cold-drink-{{ $index }}-room"
                                                >
                                                <label for="cold-drink-{{ $index }}-room" class="ml-2 text-sm text-gray-600">
                                                    Al tiempo
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- OPCIONES DE COCCIÓN PARA PARRILLAS --}}
                                    @if($item['is_grill_item'] ?? false)
                                        <div class="flex flex-wrap items-center gap-2 mb-2 bg-yellow-50 p-2 rounded-md border border-yellow-200">
                                            <span class="text-xs font-semibold text-yellow-800 w-full mb-1">Punto de cocción:</span>
                                            
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.cooking_point"
                                                    value="AZUL"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="grill-{{ $index }}-blue"
                                                >
                                                <label for="grill-{{ $index }}-blue" class="ml-1 text-xs text-gray-600">
                                                    Azul
                                                </label>
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.cooking_point"
                                                    value="ROJO"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="grill-{{ $index }}-red"
                                                >
                                                <label for="grill-{{ $index }}-red" class="ml-1 text-xs text-gray-600">
                                                    Rojo
                                                </label>
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.cooking_point"
                                                    value="MEDIO"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="grill-{{ $index }}-medium"
                                                >
                                                <label for="grill-{{ $index }}-medium" class="ml-1 text-xs text-gray-600">
                                                    Medio
                                                </label>
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.cooking_point"
                                                    value="TRES CUARTOS"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="grill-{{ $index }}-three-quarters"
                                                >
                                                <label for="grill-{{ $index }}-three-quarters" class="ml-1 text-xs text-gray-600">
                                                    Tres Cuartos
                                                </label>
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.cooking_point"
                                                    value="BIEN COCIDO"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="grill-{{ $index }}-well-done"
                                                >
                                                <label for="grill-{{ $index }}-well-done" class="ml-1 text-xs text-gray-600">
                                                    Bien Cocido
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    {{-- OPCIONES DE TIPO DE PRESA PARA POLLOS --}}
                                    @if($item['is_chicken_cut'] ?? false)
                                        <div class="flex flex-wrap items-center gap-2 mb-2 bg-orange-50 p-2 rounded-md border border-orange-200">
                                            <span class="text-xs font-semibold text-orange-800 w-full mb-1">Tipo de presa:</span>
                                            
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.chicken_cut_type"
                                                    value="PECHO"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="chicken-{{ $index }}-breast"
                                                >
                                                <label for="chicken-{{ $index }}-breast" class="ml-1 text-xs text-gray-600">
                                                    Pecho
                                                </label>
                                            </div>
                                            
                                            <div class="flex items-center">
                                                <input 
                                                    type="radio" 
                                                    wire:model.live="cartItems.{{ $index }}.chicken_cut_type"
                                                    value="PIERNA"
                                                    class="form-radio h-4 w-4 text-primary-600 border-gray-300 focus:ring-primary-500"
                                                    id="chicken-{{ $index }}-leg"
                                                >
                                                <label for="chicken-{{ $index }}-leg" class="ml-1 text-xs text-gray-600">
                                                    Pierna
                                                </label>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <p class="pos-responsive-text text-gray-600 mb-2 font-medium">S/ {{ number_format($item['unit_price'], 2) }} c/u</p>

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
                                                :disabled="!$canClearCart"
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
                                                :disabled="!$canClearCart"
                                            />
                                        </div>

                                        {{-- SUBTOTAL COMPACTO --}}
                                        <span class="pos-responsive-price text-green-600">
                                            S/ {{ number_format($item['quantity'] * $item['unit_price'], 2) }}
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
                            <div class="flex justify-between">
                                <span class="pos-responsive-text text-gray-600">Subtotal:</span>
                                <span class="pos-responsive-text font-semibold">S/ {{ number_format($subtotal, 2) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="pos-responsive-text text-gray-600">IGV (18%):</span>
                                <span class="pos-responsive-text font-semibold">S/ {{ number_format($tax, 2) }}</span>
                            </div>
                            <hr class="border-gray-200">
                            <div class="flex justify-between font-bold">
                                <span class="pos-responsive-price text-gray-900">Total:</span>
                                <span class="pos-responsive-price text-green-600">S/ {{ number_format($total, 2) }}</span>
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

                            {{-- MENSAJE CUANDO LA ORDEN YA ESTÁ FACTURADA --}}
                            @if($order && $order->invoices()->exists())
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
                                    <div class="flex items-center justify-center mb-2">
                                        <x-heroicon-o-check-circle class="h-6 w-6 text-green-600 mr-2" />
                                        <span class="text-green-800 font-semibold">Orden Facturada</span>
                                    </div>
                                    <p class="text-green-700 text-sm mb-3">
                                        Esta orden ya tiene comprobante(s) emitido(s).
                                    </p>
                                    @if(auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                        <x-filament::button
                                            wire:click="reimprimirComprobante"
                                            color="success"
                                            size="lg"
                                            class="w-full py-2 text-base font-bold"
                                        >
                                            <x-heroicon-m-printer class="h-5 w-5 mr-2" />
                                            Reimprimir Comprobante
                                        </x-filament::button>
                                    @endif
                                </div>
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

                // FUNCIÓN GLOBAL para mostrar modal de comanda (DEPRECATED - ahora usa modal de Filament)
                // window.showCommandModal = function(url) {
                //     console.log('🖨️ Mostrando modal de comanda:', url);
                //     setTimeout(() => {
                //         window.open(url, 'command_print_window', 'width=800,height=600,scrollbars=yes,resizable=yes');
                //     }, 500);
                // };
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
                                El comprobante se ha procesado exitosamente. ¿Desea imprimirlo?
                            </p>
                        </div>
                    </div>
                </div>

                {{-- BOTONES DE ACCIÓN --}}
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                    <button
                        @click="window.open(url, '_blank'); open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        Imprimir
                    </button>
                    <button
                        @click="open = false; window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        Saltar
                    </button>
                </div>
        </div>
    </div>
</x-filament-panels::page>

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/product-images.css') }}">
@endpush

<script>
// Listener para redirección automática al mapa de mesas después de imprimir comprobantes
window.addEventListener('message', function(event) {
    console.log('🖨️ POS Interface - Evento recibido:', event.data);

    // Manejar tanto string directo como objeto
    if (event.data === 'invoice-completed' ||
        (event.data && event.data.type === 'invoice-completed')) {

        console.log('✅ Comprobante impreso - Redirigiendo al mapa de mesas');

        // Mostrar mensaje de confirmación antes de redirigir
        setTimeout(function() {
            console.log('🔄 Redirigiendo al mapa de mesas...');
            window.location.href = '{{ \App\Filament\Pages\TableMap::getUrl() }}';
        }, 1500); // Dar tiempo para que se complete la impresión
    }
});

console.log('🎯 POS Interface - Listener de redirección activado');
</script>
