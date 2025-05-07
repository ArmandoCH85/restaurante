<div class="flex flex-col h-screen overflow-hidden bg-gray-100 dark:bg-gray-900 font-sans">
    <style>
        /* Estilos para las representaciones visuales de las mesas */
        .table-visual {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid;
            transition: all 0.3s ease;
        }

        .table-square {
            border-radius: 4px;
        }

        .table-round {
            border-radius: 50%;
        }
    </style>
    <!-- Barra superior (Simplificada para enfoque POS) -->
    {{-- <header class="bg-white dark:bg-gray-800 shadow-sm flex-shrink-0">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-xl font-bold text-blue-600 dark:text-blue-400">POS Restaurante</span>
                </div>
                <div class="flex items-center space-x-4">
                     <span class="px-3 py-1.5 rounded-md text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                        @if($table)
                            Mesa: {{ $table->number }} | {{ ucfirst($table->location ?? 'General') }}
                        @else
                            Venta Rápida
                        @endif
                    </span>
                     <a href="{{ route('pos.invoices.list') }}" title="Ver Comprobantes" class="p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                    </a>
                     <a href="{{ route('tables.map') }}" title="Mapa de Mesas" class="p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors duration-200">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </a>
                    <button type="button" title="Configuración" class="p-2 rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-gray-200 transition-colors duration-200">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </button>
                </div>
            </div>
        </div>
    </header> --}}

     <!-- Header simplificado -->
     <header class="bg-white dark:bg-gray-800 shadow-sm z-10 flex-shrink-0">
        <div class="max-w-full mx-auto px-2 sm:px-4 lg:px-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-2 sm:h-14">
                <div class="flex items-center mb-2 sm:mb-0">
                    <span class="text-xl font-semibold text-gray-800 dark:text-gray-200">Sistema POS</span>
                    <span class="mx-2 text-gray-300 dark:text-gray-600">|</span>
                    <span class="px-3 py-1 rounded-md text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 ring-1 ring-inset ring-blue-200 dark:ring-blue-700">
                        @if($table)
                            Mesa: {{ $table->number }} <span class="hidden sm:inline">| {{ ucfirst($table->location ?? 'General') }}</span>
                        @else
                            Venta Rápida
                        @endif
                    </span>
                </div>
                <div class="flex items-center gap-2 overflow-x-auto py-1 px-1 -mx-1 pb-2 sm:pb-0 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                    <a href="{{ url('/admin') }}" title="Volver al Escritorio" class="flex-shrink-0 p-2 rounded-md text-white bg-green-600 hover:bg-green-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5 animate-pulse border-2 border-green-400 relative z-10" style="box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            <span class="sm:inline">Escritorio</span>
                        </span>
                    </a>
                    <a href="{{ url('/dashboard') }}" title="Dashboard" class="flex-shrink-0 p-2 rounded-md text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                            <span class="hidden sm:inline">Dashboard</span>
                        </span>
                    </a>
                    <a href="{{ route('tables.map') }}" title="Mapa de Mesas" class="flex-shrink-0 p-2 rounded-md text-white bg-red-600 hover:bg-red-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5 animate-pulse border-2 border-red-400 relative z-10" style="box-shadow: 0 4px 6px rgba(220, 38, 38, 0.25);">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span class="sm:inline">Mesas</span>
                        </span>
                    </a>
                    <a href="{{ url('admin/facturacion/comprobantes') }}" title="Ver Comprobantes" class="flex-shrink-0 p-2 rounded-md text-white bg-amber-600 hover:bg-amber-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <span class="hidden sm:inline">Comprobantes</span>
                        </span>
                    </a>
                    <div class="border-l border-gray-300 h-8 mx-1 flex-shrink-0 hidden sm:block"></div>
                    <a href="{{ url('/admin/inventory/movements') }}" title="Movimientos de Inventario" class="flex-shrink-0 p-2 rounded-md text-white bg-teal-600 hover:bg-teal-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                            <span class="hidden sm:inline">Inventario</span>
                        </span>
                    </a>
                    <a href="{{ url('/admin/purchases') }}" title="Compras" class="flex-shrink-0 p-2 rounded-md text-white bg-purple-600 hover:bg-purple-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" /></svg>
                            <span class="hidden sm:inline">Compras</span>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content - Three Panel Layout -->
    <div class="flex flex-1 overflow-hidden">
        <!-- Panel Izquierdo - Categorías -->
        <div class="w-52 flex-shrink-0 h-full overflow-y-auto bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 shadow-sm">
            <div class="p-3">
                <h2 class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 mb-3 px-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h7" />
                    </svg>
                    Categorías
                </h2>
                <nav class="space-y-1">
                    @foreach ($categories as $category)
                        <button
                            wire:click="loadProductsByCategory('{{ $category->id }}')"
                            class="w-full py-2 px-3 text-left rounded-md transition-all duration-200 text-sm flex items-center justify-between group
                                {{ $selectedCategoryId == $category->id
                                    ? 'bg-blue-50 text-blue-700 font-medium dark:bg-blue-900/50 dark:text-blue-300 shadow-sm'
                                    : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-300 dark:hover:bg-gray-700 dark:hover:text-white hover:shadow-sm' }}"
                        >
                            <span class="truncate font-medium">{{ $category->name }}</span>
                             <span class="text-xs font-normal ml-2 px-1.5 py-0.5 rounded-full
                                {{ $selectedCategoryId == $category->id
                                    ? 'bg-blue-100 text-blue-600 dark:bg-blue-600 dark:text-white'
                                    : 'text-gray-400 bg-gray-100 group-hover:bg-gray-200 dark:text-gray-500 dark:bg-gray-700 dark:group-hover:bg-gray-600' }}">
                                {{ $category->products_count }} {{-- Asumiendo que tienes un withCount('products') --}}
                            </span>
                        </button>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Panel Central - Productos -->
        <div class="flex-1 h-full overflow-y-auto bg-gray-50 dark:bg-gray-900/50">
            <div class="p-4">
                 <!-- Barra de búsqueda y título -->
                <div class="flex items-center justify-between mb-4 sticky top-0 bg-gray-50 dark:bg-gray-900/50 py-3 z-10 -mx-4 px-4 border-b border-gray-200 dark:border-gray-700/50">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-200">
                        {{ $categories->find($selectedCategoryId)?->name ?? 'Productos' }}
                            </h2>
                    <div class="relative w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </span>
                                <input
                            type="search"
                                    wire:model.live.debounce.300ms="searchQuery"
                                    placeholder="Buscar productos..."
                            class="w-full pl-10 pr-4 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400 transition"
                        />
                    </div>
                </div>

                <!-- Grid de Productos -->
                 <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                    @forelse ($products as $product)
                        <div
                            wire:key="product-{{ $product->id }}"
                            wire:click="addToCart({{ $product->id }})"
                            class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden group transition-all duration-300 hover:shadow-xl hover:-translate-y-1 cursor-pointer border border-gray-200 dark:border-gray-700 flex flex-col relative {{ !$product->available ? 'opacity-75' : '' }}"
                        >
                            <div class="h-36 relative overflow-hidden">
                                @if ($product->image_path)
                                    <img
                                        src="{{ asset('storage/' . $product->image_path) }}"
                                        alt="{{ $product->name }}"
                                        class="h-full w-full object-cover"
                                    >
                                @else
                                    <div class="flex items-center justify-center h-full w-full bg-gray-100 dark:bg-gray-700">
                                        <span class="text-sm text-gray-500 dark:text-gray-400">Sin imagen</span>
                                    </div>
                                @endif
                                @if (!$product->available)
                                    <div class="absolute inset-0 bg-black bg-opacity-30 flex items-center justify-center">
                                        <span class="bg-red-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-md transform rotate-12">
                                            Agotado
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="p-3 flex-grow flex flex-col justify-between">
                                <div>
                                    <h3 class="font-medium text-gray-800 dark:text-white text-sm truncate">{{ $product->name }}</h3>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $product->category->name ?? 'Sin categoría' }}</p>
                                </div>
                                <div class="mt-2 flex items-center justify-between">
                                    <span class="font-semibold text-blue-600 dark:text-blue-400 text-sm">
                                        S/ {{ number_format($product->sale_price, 2) }}
                                    </span>
                                    <button class="bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/30 dark:hover:bg-blue-800/50 text-blue-600 dark:text-blue-400 p-1 rounded-full transition-colors duration-150">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                    </button>
                                </div>
                            </div>

                        </div>
                    @empty
                        <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-500 dark:text-gray-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-3 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"> <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 10l4 4m0-4l-4 4" /> </svg>
                            <p class="text-base font-medium">No se encontraron productos</p>
                            <p class="text-sm mt-1">Intenta con otra categoría o búsqueda.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Panel Derecho - Pedido -->
        <div class="w-80 flex-shrink-0 h-full flex flex-col bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 shadow-lg">
            <!-- Encabezado del pedido -->
            <div class="p-4 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold text-gray-700 dark:text-gray-200 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Pedido Actual
                    </h2>
                    <button
                        wire:click="clearCart"
                        type="button"
                        class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:-translate-y-0.5"
                         {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                        Vaciar Carrito
                    </button>
                </div>

                <!-- Botones principales -->
                <div class="grid grid-cols-3 gap-2 mb-1">
                    <!-- Botón Comanda -->
                    <button
                        onclick="abrirComanda()"
                        type="button"
                        class="px-2 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs space-y-1 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md hover:-translate-y-0.5"
                        {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span>COMANDA</span>
                    </button>

                    <!-- Botón Pre-Cuenta -->
                    <button
                        onclick="abrirPreCuenta()"
                        type="button"
                        class="px-2 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs space-y-1 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md hover:-translate-y-0.5"
                        {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /> </svg>
                        <span>PRE-CUENTA</span>
                    </button>

                    <!-- Botón Confirmar Venta -->
                    <button
                        onclick="abrirFactura()"
                        type="button"
                        class="px-2 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs space-y-1 disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md hover:-translate-y-0.5"
                        {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /> </svg>
                        <span>FACTURAR</span>
                    </button>
                </div>

                <!-- Botón Transferir Mesa (siempre visible) -->
                <div class="mt-3 mb-2">
                    <button
                        type="button"
                        onclick="abrirModalTransferencia()"
                        class="w-full px-3 py-3 bg-red-500 hover:bg-red-600 animate-pulse text-white rounded-lg font-medium transition-all duration-200 flex items-center justify-center text-sm gap-2 shadow-md hover:shadow-lg"
                        id="btn-transferir-mesa"
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /> </svg>
                        <span class="text-base font-bold">TRANSFERIR MESA</span>
                    </button>
                </div>

                <!-- Botón Cancelar Pedido con estilo mejorado -->
                <div class="mt-3 mb-2 relative z-10">
                    <a
                        href="javascript:void(0);"
                        @if(count($cart) > 0)
                        onclick="if(confirm('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')) { @this.cancelOrder(); }"
                        @else
                        onclick="alert('No hay productos en el carrito para cancelar.')"
                        @endif
                        class="w-full px-3 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-all duration-200 flex items-center justify-center text-sm gap-2 shadow-md hover:shadow-lg cursor-pointer border-2 border-red-400 {{ count($cart) === 0 ? 'opacity-50' : 'animate-pulse' }}"
                        style="box-shadow: 0 4px 6px rgba(220, 38, 38, 0.25);"
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /> </svg>
                        <span class="text-base font-bold">CANCELAR PEDIDO</span>
                    </a>
                </div>
            </div>

            <!-- Lista de productos en el pedido -->
             <div class="flex-1 overflow-y-auto p-4 space-y-3 bg-gray-50/50 dark:bg-gray-800/30">
                @forelse ($cart as $item)
                    <div wire:key="cart-item-{{ $item['id'] }}" class="flex items-center space-x-3 bg-white dark:bg-gray-700/50 p-3 rounded-lg border border-gray-200 dark:border-gray-600/50 shadow-sm hover:shadow-md transition-all duration-200 hover:-translate-y-0.5 group">
                                <div class="flex-1 min-w-0">
                            <p class="font-medium text-sm text-gray-800 dark:text-gray-100 truncate">{{ $item['name'] }}</p>
                             <div class="flex items-center text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                 <span>S/ {{ number_format($item['price'], 2) }}</span>
                                 @can('admin')
                                 <button type="button" wire:click="openEditPriceModal('{{ $item['id'] }}')" class="ml-1 text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 bg-blue-50 dark:bg-blue-900/20 p-0.5 rounded-full" title="Editar precio">
                                     <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /> </svg>
                                        </button>
                                 @endcan
                                    </div>
                            <!-- Futura nota para el producto -->
                                </div>
                        <div class="flex items-center space-x-2 flex-shrink-0">
                                        <button
                                            wire:click="updateCartItemQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                            type="button"
                                class="w-6 h-6 flex items-center justify-center bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-full hover:bg-gray-200 dark:hover:bg-gray-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 shadow-sm hover:shadow"
                            > &minus; </button>
                            <span class="text-sm font-medium text-gray-800 dark:text-gray-200 w-5 text-center">{{ $item['quantity'] }}</span>
                                        <button
                                            wire:click="updateCartItemQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                            type="button"
                                class="w-6 h-6 flex items-center justify-center bg-blue-100 dark:bg-blue-600 text-blue-700 dark:text-blue-200 rounded-full hover:bg-blue-200 dark:hover:bg-blue-500 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 shadow-sm hover:shadow"
                            > + </button>
                                    </div>
                         <div class="flex flex-col items-end w-16 text-right flex-shrink-0">
                                        <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">S/ {{ number_format($item['subtotal'], 2) }}</span>
                                        <button
                                            wire:click="removeFromCart('{{ $item['id'] }}')"
                                            type="button"
                                class="mt-1 text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 transition-all duration-200 opacity-70 group-hover:opacity-100 p-1 rounded-full hover:bg-red-50 dark:hover:bg-red-900/30"
                                title="Eliminar del carrito"
                                        >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /> </svg>
                                        </button>
                                    </div>
                    </div>
                 @empty
                    <div class="h-full flex flex-col items-center justify-center text-center text-gray-500 dark:text-gray-400 px-4 pt-10">
                        <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-full mb-3 shadow-inner">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"> <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /> </svg>
                        </div>
                        <p class="text-base font-medium">El carrito está vacío</p>
                        <p class="text-sm mt-1 text-gray-400 dark:text-gray-500">Agrega productos para comenzar tu pedido</p>
                        <p class="text-xs mt-1">Selecciona productos del panel central para agregarlos al pedido.</p>
                    </div>
                @endforelse
            </div>

            <!-- Resumen y Notas del pedido -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex-shrink-0">
                 <div class="mb-3">
                    <label for="customerNote" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Nota para el pedido (opcional)</label>
                    <textarea
                        id="customerNote"
                        wire:model="customerNote"
                        placeholder="Ej: Sin ají, bien cocido..."
                        class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                        rows="2"
                    ></textarea>
                </div>

                <div class="space-y-1.5 text-sm">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">S/ {{ number_format($cartTotal, 2) }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">IGV (18%):</span>
                        <span class="font-medium text-gray-700 dark:text-gray-200">S/ {{ number_format($cartTotal * 0.18, 2) }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-gray-800 dark:text-gray-100">Total:</span>
                            <span class="text-base font-bold text-blue-600 dark:text-blue-400">S/ {{ number_format($cartTotal * 1.18, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Botones inferiores redundantes eliminados para simplificar --}}
                {{-- <div class="mt-4 grid grid-cols-3 gap-2"> ... </div> --}}
            </div>
        </div>
    </div>

    <!-- Modal de edición de precios (solo administradores) -->
     @if($showEditPriceModal)
     <div class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4"
          x-data="{ open: @entangle('showEditPriceModal') }"
          x-show="open"
          x-transition:enter="transition ease-out duration-300"
          x-transition:enter-start="opacity-0"
          x-transition:enter-end="opacity-100"
          x-transition:leave="transition ease-in duration-200"
          x-transition:leave-start="opacity-100"
          x-transition:leave-end="opacity-0"
          {{-- style="display: none;" --}}
    >
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full overflow-hidden" @click.away="open = false">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center mb-4">
                     <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Editar Precio</h3>
                     <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                     </button>
                 </div>

                <div class="mb-4 text-sm">
                    <label class="block font-medium text-gray-700 dark:text-gray-300 mb-1">Producto</label>
                     <p class="text-gray-900 dark:text-gray-100 font-semibold">{{ $cart[$editingProductId]['name'] ?? 'N/A' }}</p>
                </div>

                <div class="mb-5">
                    <label for="newPrice" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nuevo precio</label>
                    <div class="relative mt-1 rounded-md shadow-sm">
                         <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                             <span class="text-gray-500 dark:text-gray-400 sm:text-sm">S/</span>
                         </div>
                         <input
                            type="number"
                            id="newPrice"
                            wire:model="newPrice"
                            step="0.01"
                            min="0.01"
                             class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white pl-8 pr-4 py-2 focus:border-blue-500 focus:ring-blue-500 sm:text-sm transition"
                            placeholder="0.00"
                        >
                    </div>
                    {{-- Añadir validación de error si es necesario --}}
                 </div>
            </div>
             <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 flex justify-end gap-3">
                <button
                    type="button"
                    @click="open = false"
                     class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-500 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition"
                > Cancelar </button>
                <button
                    type="button"
                    wire:click="saveNewPrice"
                     class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
                    wire:loading.attr="disabled" wire:target="saveNewPrice"
                >
                    <span wire:loading.remove wire:target="saveNewPrice">Guardar Precio</span>
                    <span wire:loading wire:target="saveNewPrice">Guardando...</span>
                    </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Modal para Comanda -->
    <div class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
         x-data="{ url: @entangle('commandUrl') }"
         x-show="$wire.showCommandModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
    >
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full overflow-hidden" @click.away="$wire.showCommandModal = false">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Comanda
                </h3>
                <button @click="$wire.showCommandModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full p-1 transition-all duration-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 h-[70vh]">
                <iframe id="commandFrame" x-bind:src="url" class="w-full h-full border-0 rounded-md bg-gray-50 dark:bg-gray-700/30"></iframe>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    @click="document.getElementById('commandFrame').contentWindow.print()"
                    class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir
                </button>
                <button
                    type="button"
                    @click="$wire.showCommandModal = false"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-500 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Pre-Cuenta -->
    <div class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
         x-data="{ url: @entangle('preBillUrl') }"
         x-show="$wire.showPreBillModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
    >
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full overflow-hidden" @click.away="$wire.showPreBillModal = false">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Pre-Cuenta
                </h3>
                <button @click="$wire.showPreBillModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full p-1 transition-all duration-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4 h-[70vh]">
                <iframe id="preBillFrame" x-bind:src="url" class="w-full h-full border-0 rounded-md bg-gray-50 dark:bg-gray-700/30"></iframe>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    @click="document.getElementById('preBillFrame').contentWindow.print()"
                    class="inline-flex justify-center items-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-yellow-500 hover:bg-yellow-600 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-400"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Imprimir
                </button>
                <button
                    type="button"
                    @click="$wire.showPreBillModal = false"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-500 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal para Facturación -->
    <div class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
         x-data="{ url: @entangle('invoiceUrl') }"
         x-show="$wire.showInvoiceModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
    >
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-5xl w-full overflow-hidden" @click.away="$wire.showInvoiceModal = false">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Formulario de Facturación
                </h3>
                <button @click="$wire.showInvoiceModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full p-1 transition-all duration-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    </button>
                </div>
            <div class="px-6 py-4 h-[80vh]">
                <iframe id="invoiceFormFrame" x-bind:src="url" class="w-full h-full border-0 rounded-md bg-gray-50 dark:bg-gray-700/30"></iframe>
            </div>
        </div>
    </div>

    <!-- Modal para Transferir Mesa -->
    <div class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
         x-data="{showModal: @entangle('showTransferModal')}"
         x-show="showModal"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         style="display: none;"
         id="modal-transferir-mesa"
         x-init="
            $watch('showModal', value => {
                console.log('Estado del modal de transferencia:', value);
                if (value) {
                    console.log('Modal de transferencia abierto');
                } else {
                    console.log('Modal de transferencia cerrado');
                }
            });
         "
    >
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full overflow-hidden" @click.away="cerrarModalTransferencia()">
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transferir Mesa
                </h3>
                <button onclick="cerrarModalTransferencia()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full p-1 transition-all duration-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="mb-4">
                    <p class="text-gray-700 dark:text-gray-300 mb-2">
                        Selecciona la mesa a la que deseas transferir los productos de la mesa <span class="font-semibold">{{ $table ? $table->number : '' }}</span>.
                    </p>
                    <div class="bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700 dark:text-yellow-200">
                                    La mesa origen quedará disponible y todos los productos se transferirán a la mesa seleccionada.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 max-h-[50vh] overflow-y-auto p-2">
                    @foreach($availableTables as $availableTable)
                        <button
                            onclick="transferirMesa({{ $availableTable->id }})"
                            class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200 flex flex-col items-center"
                        >
                            <div class="table-visual {{ $availableTable->shape === 'square' ? 'table-square' : 'table-round' }} bg-green-100 dark:bg-green-900 border-green-500 mb-2">
                                <span class="text-lg font-bold">{{ $availableTable->number }}</span>
                            </div>
                            <div class="text-center">
                                <p class="font-medium text-gray-800 dark:text-gray-200">Mesa {{ $availableTable->number }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($availableTable->location) }} - {{ $availableTable->capacity }} personas</p>
                            </div>
                        </button>
                    @endforeach

                    @if(count($availableTables) === 0)
                        <div class="col-span-full flex flex-col items-center justify-center py-8 text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400">No hay mesas disponibles para transferir.</p>
                        </div>
                    @endif
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    onclick="cerrarModalTransferencia()"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-500 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Script para comunicación entre iframes -->
    <script>
        // Script de depuración para verificar los botones
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Verificando botones de acción...');

            // Verificar botón de transferencia
            const transferButton = document.querySelector('#btn-transferir-mesa');
            console.log('Botón de transferencia:', transferButton);
            if (transferButton) {
                console.log('Botón de transferencia encontrado');
                console.log('Disabled:', transferButton.disabled);
                console.log('Clase:', transferButton.className);

                // Agregar un listener para verificar que el evento se dispare
                transferButton.addEventListener('click', function() {
                    console.log('Botón de transferencia clickeado');
                });
            } else {
                console.error('Botón de transferencia NO encontrado');
            }

            // Verificar botón de cancelar pedido
            const cancelButton = document.querySelector('a[onclick*="cancelOrder"]');
            console.log('Botón de cancelar pedido:', cancelButton);
            if (cancelButton) {
                console.log('Botón de cancelar pedido encontrado');
                console.log('Clase:', cancelButton.className);
            } else {
                console.error('Botón de cancelar pedido NO encontrado');
            }
        });

        // Escuchar el evento personalizado de factura completada
        window.addEventListener('invoice-completed', function() {
            // Vaciar el carrito
            vaciarCarrito();

            // Mostrar mensaje de éxito
            alert('Factura generada correctamente');
        });

        // Mantener compatibilidad con el evento de mensaje
        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'invoice-completed') {
                // Vaciar el carrito
                vaciarCarrito();

                // Cerrar cualquier modal activo
                if (typeof closeModal === 'function') {
                    closeModal();
                }

                // Mostrar mensaje de éxito
                alert('Factura generada correctamente');
            }
        });
    </script>

    <!-- Scripts para abrir ventanas -->
    <script>
        function procesarRespuesta(response) {
            if (!response.ok) {
                return response.json().then(data => {
                    throw new Error(data.message || 'Error en la petición: ' + response.status);
                });
            }
            return response.json().then(data => {
                if (data.success && data.orderId) {
                    return data.orderId;
                } else {
                    throw new Error(data.message || 'Error al procesar la orden');
                }
            });
        }

        // Función para vaciar el carrito después de generar un comprobante
        // Esta función SOLO debe llamarse cuando se emite un comprobante final (factura/boleta)
        function vaciarCarrito() {
            // Obtener el botón de vaciar carrito y hacer clic en él
            const botonVaciar = document.querySelector('button[wire\\:click="clearCart"]');
            if (botonVaciar) {
                botonVaciar.click();
            } else {
                console.error('No se encontró el botón para vaciar el carrito');
                // Intentar refrescar la página como plan B
                window.location.reload();
            }
        }

        // Escuchar cuando se cierra la ventana del comprobante
        window.addEventListener('message', function(event) {
            if (event.data === 'invoice-completed') {
                console.log('Comprobante generado - Vaciando carrito');
                vaciarCarrito();
            }
        });

        // Funciones para mostrar modales
        function showCommandModal(url) {
            // Establecer la URL en el componente Livewire
            Livewire.dispatch('setCommandUrl', { url: url });

            // Mostrar el modal
            Livewire.dispatch('openCommandModal');
        }

        function showPreBillModal(url) {
            // Establecer la URL en el componente Livewire
            Livewire.dispatch('setPreBillUrl', { url: url });

            // Mostrar el modal
            Livewire.dispatch('openPreBillModal');
        }

        function abrirComanda() {
            // Obtener datos directamente del carrito visible
            const productos = [];
            document.querySelectorAll('[wire\\:key^="cart-item-"]').forEach(item => {
                const id = item.getAttribute('wire:key').replace('cart-item-', '');
                const name = item.querySelector('.font-medium').textContent;
                const price = parseFloat(item.querySelector('.text-gray-500').textContent.replace('S/ ', ''));
                const quantity = parseInt(item.querySelector('.text-center').textContent);
                const subtotal = parseFloat(item.querySelector('.font-semibold').textContent.replace('S/ ', ''));

                productos.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                });
            });

            if (productos.length === 0) {
                alert('No hay productos en el carrito');
                return;
            }

            // Si hay una mesa seleccionada, asegurarse de que esté marcada como ocupada
            @if($table)
                // Verificar si la mesa está disponible y cambiarla a ocupada si es necesario
                if ('{{ $table->status }}' === 'available') {
                    Livewire.dispatch('changeTableStatus', { tableId: {{ $table->id }}, status: 'occupied' });
                }
            @endif

            // Crear la orden con los productos capturados directamente
            fetch('{{ route("pos.create-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    has_products: true,
                    cart_items: productos
                })
            })
            .then(procesarRespuesta)
            .then(orderId => {
                // Mostrar la comanda en un modal
                showCommandModal('{{ url("pos/command-pdf") }}/' + orderId);
                // NO vaciar el carrito después de generar la comanda
            })
            .catch(error => {
                alert('Error: ' + error.message);
                console.error('Error completo:', error);
            });
        }

        function abrirPreCuenta() {
            // Obtener datos directamente del carrito visible
            const productos = [];
            document.querySelectorAll('[wire\\:key^="cart-item-"]').forEach(item => {
                const id = item.getAttribute('wire:key').replace('cart-item-', '');
                const name = item.querySelector('.font-medium').textContent;
                const price = parseFloat(item.querySelector('.text-gray-500').textContent.replace('S/ ', ''));
                const quantity = parseInt(item.querySelector('.text-center').textContent);
                const subtotal = parseFloat(item.querySelector('.font-semibold').textContent.replace('S/ ', ''));

                productos.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                });
            });

            if (productos.length === 0) {
                alert('No hay productos en el carrito');
                return;
            }

            // Si hay una mesa seleccionada, asegurarse de que esté marcada como ocupada
            @if($table)
                // Verificar si la mesa está disponible y cambiarla a ocupada si es necesario
                if ('{{ $table->status }}' === 'available') {
                    Livewire.dispatch('changeTableStatus', { tableId: {{ $table->id }}, status: 'occupied' });
                }
            @endif

            // Crear la orden con los productos capturados directamente
            fetch('{{ route("pos.create-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    has_products: true,
                    cart_items: productos
                })
            })
            .then(procesarRespuesta)
            .then(orderId => {
                // Mostrar la pre-cuenta en un modal
                showPreBillModal('{{ url("pos/prebill-pdf") }}/' + orderId);
                // NO vaciar el carrito después de generar la pre-cuenta
            })
            .catch(error => {
                alert('Error: ' + error.message);
                console.error('Error completo:', error);
            });
        }

        function abrirModalTransferencia() {
            console.log('Abriendo modal de transferencia...');
            console.log('Mesa actual:', {{ $table ? $table->id : 'null' }});
            console.log('Productos en carrito:', {{ count($cart) }});

            // Verificar si hay productos en el carrito
            if ({{ count($cart) }} === 0) {
                alert('Transferir mesa: Esta función permite mover todos los productos de esta mesa a otra mesa disponible. Primero debes añadir productos al carrito.');
                return;
            }

            try {
                // Forzar la carga de mesas disponibles y abrir el modal directamente
                @this.openTransferModal();
                console.log('Modal abierto:', @this.showTransferModal);
            } catch (error) {
                console.error('Error al abrir modal de transferencia:', error);
                alert('Error al abrir el modal de transferencia: ' + (error.message || 'Error desconocido'));
            }
        }

        function cerrarModalTransferencia() {
            console.log('Cerrando modal de transferencia...');
            @this.showTransferModal = false;
            console.log('Modal cerrado');
        }

        function transferirMesa(destinationTableId) {
            console.log('Transfiriendo a la mesa ID:', destinationTableId);

            try {
                // Mostrar mensaje de carga
                const mensaje = 'Transfiriendo productos a la mesa ' + destinationTableId + '...';
                console.log(mensaje);

                // Llamar al método del componente Livewire
                @this.transferTable(destinationTableId);

                // Cerrar el modal después de un breve retraso
                setTimeout(() => {
                    cerrarModalTransferencia();
                }, 500);
            } catch (error) {
                console.error('Error al transferir mesa:', error);
                alert('Error al transferir mesa: ' + error.message);
            }
        }

        function abrirFactura() {
            // Obtener datos directamente del carrito visible
            const productos = [];
            document.querySelectorAll('[wire\\:key^="cart-item-"]').forEach(item => {
                const id = item.getAttribute('wire:key').replace('cart-item-', '');
                const name = item.querySelector('.font-medium').textContent;
                const price = parseFloat(item.querySelector('.text-gray-500').textContent.replace('S/ ', ''));
                const quantity = parseInt(item.querySelector('.text-center').textContent);
                const subtotal = parseFloat(item.querySelector('.font-semibold').textContent.replace('S/ ', ''));

                productos.push({
                    id: id,
                    name: name,
                    price: price,
                    quantity: quantity,
                    subtotal: subtotal
                });
            });

            if (productos.length === 0) {
                alert('No hay productos en el carrito');
                return;
            }

            // Si hay una mesa seleccionada, asegurarse de que esté marcada como ocupada
            @if($table)
                // Verificar si la mesa está disponible y cambiarla a ocupada si es necesario
                if ('{{ $table->status }}' === 'available') {
                    Livewire.dispatch('changeTableStatus', { tableId: {{ $table->id }}, status: 'occupied' });
                }
            @endif

            // Crear la orden con los productos capturados directamente
            fetch('{{ route("pos.create-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    has_products: true,
                    cart_items: productos
                })
            })
            .then(procesarRespuesta)
            .then(orderId => {
                // Abrir el formulario de factura en una ventana nueva a pantalla completa
                const facturaWindow = window.open('{{ url("pos/invoice/form") }}/' + orderId, '_blank', 'fullscreen=yes,width='+screen.width+',height='+screen.height);

                // Cuando la factura se complete, vaciar el carrito
                if (facturaWindow) {
                    // Configurar el intervalo para chequear si la ventana se ha cerrado
                    const checkClosedInterval = setInterval(() => {
                        if (facturaWindow.closed) {
                            clearInterval(checkClosedInterval);
                            console.log('Ventana de factura cerrada - Vaciando carrito');
                            // Aquí sí vaciamos el carrito porque se ha emitido un comprobante
                            vaciarCarrito();
                        }
                    }, 1000);
                }
            })
            .catch(error => {
                alert('Error: ' + error.message);
                console.error('Error completo:', error);
            });
        }
    </script>
</div>
