<div class="flex flex-col h-screen overflow-hidden bg-gray-100 dark:bg-gray-900 font-sans">
    <!-- Incluir Leaflet CSS y JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Incluir SweetAlert2 directamente en esta vista -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Estilos para las representaciones visuales de las mesas */
        .table-visual {
            width: 40px;
            height: 40px;
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

        /* Estilos específicos para el modal de transferencia */
        #modal-transferir-mesa .table-visual {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        /* Estilos para barras de desplazamiento personalizadas */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-track {
            background: #2d3748;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 3px;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #4a5568;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        .dark .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: #718096;
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

                    <!-- Botón para cambiar entre modo claro/oscuro -->
                    <button
                        type="button"
                        class="ml-3 p-1.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors duration-200"
                        title="Cambiar tema"
                        onclick="toggleDarkMode()"
                    >
                        <!-- Ícono sol (visible en modo oscuro) -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <!-- Ícono luna (visible en modo claro) -->
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-2 overflow-x-auto py-1 px-1 -mx-1 pb-2 sm:pb-0 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                    <!-- Botón para ir al mapa de mesas -->
                    <a
                        href="{{ url('/tables') }}"
                        title="Ir al mapa de mesas"
                        class="flex-shrink-0 p-2 rounded-md text-white bg-green-600 hover:bg-green-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5 border-2 border-green-400 relative z-10"
                        style="box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);"
                    >
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="sm:inline font-bold">MAPA DE MESAS</span>
                        </span>
                    </a>

                    <a href="{{ url('/admin') }}" title="Volver al Escritorio" class="flex-shrink-0 p-2 rounded-md text-white bg-green-600 hover:bg-green-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5 border-2 border-green-400 relative z-10" style="box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);">
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
                    <button onclick="irAMesas()" title="Mapa de Mesas" class="flex-shrink-0 p-2 rounded-md text-white bg-red-600 hover:bg-red-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5 animate-pulse border-2 border-red-400 relative z-10" style="box-shadow: 0 4px 6px rgba(220, 38, 38, 0.25);">
                        <span class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                            <span class="sm:inline">Mesas</span>
                        </span>
                    </button>
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

                    <!-- Botón para probar notificaciones -->
                    <div class="relative" x-data="{ open: false }">
                        <button
                            @click="open = !open"
                            class="flex-shrink-0 p-2 rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-all duration-200 text-sm font-medium shadow-sm hover:shadow-md hover:-translate-y-0.5"
                            title="Probar Notificaciones"
                        >
                            <span class="flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <span class="hidden sm:inline">Notificaciones</span>
                            </span>
                        </button>

                        <!-- Menú desplegable para tipos de notificaciones -->
                        <div
                            x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg z-50 border border-gray-200 dark:border-gray-700"
                        >
                            <div class="py-1">
                                <button
                                    onclick="testNotification('success')"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                                >
                                    <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                    Éxito
                                </button>
                                <button
                                    onclick="testNotification('error')"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                                >
                                    <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                    Error
                                </button>
                                <button
                                    onclick="testNotification('warning')"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                                >
                                    <span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                                    Advertencia
                                </button>
                                <button
                                    onclick="testNotification('info')"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                                >
                                    <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                                    Información
                                </button>
                                <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                <button
                                    onclick="testModalNotification()"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 flex items-center"
                                >
                                    <span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span>
                                    Modal
                                </button>
                            </div>
                        </div>
                    </div>
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
            <!-- Encabezado del pedido compacto -->
            <div class="p-2 border-b border-gray-200 dark:border-gray-700 flex-shrink-0 bg-gradient-to-r from-blue-50 to-white dark:from-blue-900/20 dark:to-gray-800">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-sm font-bold text-gray-800 dark:text-gray-100 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        Pedido Actual
                        <span class="ml-1 px-1.5 py-0.5 bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 rounded-full text-xs">
                            {{ count($cart) }} {{ count($cart) === 1 ? 'item' : 'items' }}
                        </span>
                    </h2>
                    <button
                        wire:click="clearCart"
                        type="button"
                        class="text-xs text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-red-50 dark:bg-red-900/20 px-1.5 py-0.5 rounded flex items-center"
                         {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Vaciar
                    </button>
                </div>

                <!-- Selector de tipo de servicio compacto -->
                <div class="mb-1">
                    <div class="flex items-center justify-between mb-1">
                        <h3 class="text-xs font-semibold text-gray-700 dark:text-gray-300">Tipo de Servicio</h3>
                    </div>
                    <div class="grid grid-cols-3 gap-1">
                        <button
                            type="button"
                            wire:click="setServiceType('dine_in')"
                            class="px-1 py-1 rounded-md text-xs font-medium transition-all duration-200 flex flex-col items-center justify-center border
                                {{ $serviceType === 'dine_in'
                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/50 dark:text-blue-300 border-blue-400 dark:border-blue-700'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border-gray-200 dark:border-gray-700' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="font-medium">En Local</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setServiceType('takeout')"
                            class="px-1 py-1 rounded-md text-xs font-medium transition-all duration-200 flex flex-col items-center justify-center border
                                {{ $serviceType === 'takeout'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/50 dark:text-green-300 border-green-400 dark:border-green-700'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border-gray-200 dark:border-gray-700' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span class="font-medium">Para Llevar</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setServiceType('delivery')"
                            class="px-1 py-1 rounded-md text-xs font-medium transition-all duration-200 flex flex-col items-center justify-center border
                                {{ $serviceType === 'delivery'
                                    ? 'bg-red-100 text-red-700 dark:bg-red-900/50 dark:text-red-300 border-red-400 dark:border-red-700'
                                    : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 border-gray-200 dark:border-gray-700' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                            </svg>
                            <span class="font-medium">Delivery</span>
                        </button>
                    </div>
                </div>

                <!-- Botones principales con diseño compacto -->
                <div class="grid {{ Auth::user()->hasRole('waiter') ? 'grid-cols-1' : 'grid-cols-3' }} gap-1 mb-2">
                    <!-- Botón Comanda (siempre visible) -->
                    <button
                        onclick="abrirComanda()"
                        type="button"
                        class="px-1 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                        <span class="text-xs font-medium">Comanda</span>
                    </button>

                    @if(!Auth::user()->hasRole('waiter'))
                    <!-- Botón Pre-Cuenta (no visible para meseros) -->
                    <button
                        onclick="abrirPreCuenta()"
                        type="button"
                        class="px-1 py-1.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" /> </svg>
                        <span class="text-xs font-medium">Pre-Cuenta</span>
                    </button>

                    <!-- Botón Facturar (no visible para meseros) -->
                    <button
                        onclick="abrirFactura()"
                        type="button"
                        class="px-1 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs disabled:opacity-50 disabled:cursor-not-allowed"
                        {{ count($cart) === 0 ? 'disabled' : '' }}
                    >
                         <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /> </svg>
                        <span class="text-xs font-medium">Facturar</span>
                    </button>
                    @endif
                </div>

                <!-- Botones de acciones adicionales (compactos) -->
                <div class="mt-1 mb-1 grid grid-cols-3 gap-1">
                    @if($table)
                        <!-- Botón Iniciar Mesa -->
                        <button
                            type="button"
                            wire:click="iniciarMesa"
                            class="px-1 py-1.5 bg-green-600 hover:bg-green-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs"
                            id="btn-iniciar-mesa"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-xs font-medium">Iniciar Mesa</span>
                        </button>

                        <!-- Botón Transferir Mesa -->
                        <button
                            type="button"
                            onclick="abrirModalTransferencia()"
                            class="px-1 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs"
                            id="btn-transferir-mesa"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /> </svg>
                            <span class="text-xs font-medium">Transferir</span>
                        </button>
                    @endif

                    @if(!Auth::user()->hasRole('waiter'))
                    <!-- Botón Cancelar Pedido (no visible para meseros) -->
                    <button
                        @if(count($cart) > 0)
                        onclick="if(confirm('¿Estás seguro de que deseas cancelar este pedido? Esta acción no se puede deshacer.')) { @this.cancelOrder(); }"
                        @else
                        onclick="alert('No hay productos en el carrito para cancelar.')"
                        @endif
                        class="px-1 py-1.5 bg-red-600 hover:bg-red-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs {{ count($cart) === 0 ? 'opacity-50' : '' }} {{ $table ? 'col-span-1' : 'col-span-2' }}"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"> <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /> </svg>
                        <span class="text-xs font-medium">Cancelar</span>
                    </button>
                    @endif

                    <!-- Botón Liberar Mesa (solo para consumo en tienda y no visible para meseros) -->
                    @if($table && ($serviceType === 'dine_in' || !$serviceType) && !Auth::user()->hasRole('waiter'))
                    <button
                        onclick="if(confirm('¿Estás seguro de que deseas liberar esta mesa? Esta acción cambiará el estado de la mesa a disponible y cancelará cualquier orden asociada. Esta acción es solo para casos excepcionales cuando un cliente se va sin consumir.')) { @this.releaseTable(); }"
                        class="px-1 py-1.5 bg-orange-600 hover:bg-orange-700 text-white rounded-md font-medium transition-all duration-200 flex flex-col items-center justify-center text-xs"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mb-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span class="text-xs font-medium">Liberar Mesa</span>
                    </button>
                    @endif
                </div>
            </div>

            <!-- Lista de productos en el pedido - Diseño estándar sin scroll -->
            <div class="cart-standard">
                <!-- Lista de productos con diseño estándar -->
                @forelse ($cart as $item)
                    <div wire:key="cart-item-{{ $item['id'] }}" class="cart-item-standard">
                        <div class="cart-item-standard-content">
                            <!-- Nombre del producto -->
                            <div class="cart-item-standard-name">{{ $item['name'] }}</div>

                            <!-- Precio unitario -->
                            <div class="cart-item-standard-price">
                                Precio unitario: <span>S/ {{ number_format($item['price'], 2) }}</span>
                                @can('admin')
                                <button type="button" wire:click="openEditPriceModal('{{ $item['id'] }}')" class="edit-price-btn" title="Editar precio">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                                </button>
                                @endcan
                            </div>

                            <!-- Controles de cantidad -->
                            <div class="cart-item-standard-quantity">
                                <button
                                    wire:click="updateCartItemQuantity('{{ $item['id'] }}', {{ $item['quantity'] - 1 }})"
                                    type="button"
                                    class="quantity-btn-minus"
                                >&minus;</button>
                                <span class="quantity-value">{{ $item['quantity'] }}</span>
                                <button
                                    wire:click="updateCartItemQuantity('{{ $item['id'] }}', {{ $item['quantity'] + 1 }})"
                                    type="button"
                                    class="quantity-btn-plus"
                                >+</button>
                            </div>

                            <!-- Subtotal -->
                            <div class="cart-item-standard-subtotal">
                                S/ {{ number_format($item['subtotal'], 2) }}
                            </div>

                            <!-- Botón eliminar -->
                            <button
                                wire:click="removeFromCart('{{ $item['id'] }}')"
                                type="button"
                                class="cart-item-standard-remove"
                                title="Eliminar del carrito"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></path></svg>
                                <span>Eliminar</span>
                            </button>
                        </div>

                        <!-- Notas del producto si existen -->
                        @if(!empty($item['notes']))
                        <div class="cart-item-standard-note">
                            <span>Nota: </span>{{ $item['notes'] }}
                        </div>
                        @endif
                    </div>
                @empty
                    <!-- Estado de carrito vacío -->
                    <div class="cart-empty-standard">
                        <svg xmlns="http://www.w3.org/2000/svg" class="cart-empty-icon-standard" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                        <p>El carrito está vacío</p>
                        <p>Agrega productos para comenzar tu pedido</p>
                    </div>
                @endforelse
            </div>

            <!-- Resumen del pedido - Diseño estándar -->
            <div class="order-summary-standard">
                <!-- Título del resumen -->
                <div class="order-summary-standard-title">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    RESUMEN DEL PEDIDO
                </div>

                <!-- Resumen del pedido - Contenido -->
                <div class="order-summary-standard-content">
                    <div class="order-summary-standard-row">
                        <span>Subtotal:</span>
                        <span>S/ {{ number_format($cartTotal, 2) }}</span>
                    </div>
                    <div class="order-summary-standard-row">
                        <span>IGV (18%):</span>
                        <span>S/ {{ number_format($cartTotal * 0.18, 2) }}</span>
                    </div>
                    <div class="order-summary-standard-total">
                        <span>TOTAL:</span>
                        <span>S/ {{ number_format($cartTotal * 1.18, 2) }}</span>
                    </div>
                </div>

                <!-- Notas del pedido -->
                <div class="order-note-standard">
                    <label for="customerNote" class="order-note-standard-label">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        NOTA PARA EL PEDIDO
                    </label>
                    <textarea
                        id="customerNote"
                        wire:model="customerNote"
                        placeholder="Ej: Sin ají, bien cocido..."
                        class="order-note-standard-input"
                        rows="2"
                    ></textarea>
                </div>
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
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full overflow-hidden" @click.away="cerrarModalTransferencia()">
            <div class="flex justify-between items-center px-3 py-2 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-white dark:from-gray-800 dark:to-gray-800">
                <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1.5 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                    </svg>
                    Transferir Mesa
                </h3>
                <button onclick="cerrarModalTransferencia()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full p-1 transition-all duration-200 hover:bg-gray-200 dark:hover:bg-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex flex-col h-[400px] overflow-hidden">
                <div class="px-3 py-2 flex-shrink-0">
                    <p class="text-xs text-gray-700 dark:text-gray-300 mb-1">
                        Selecciona los productos que deseas transferir de la mesa <span class="font-semibold">{{ $table ? $table->number : '' }}</span>.
                    </p>
                </div>

                <!-- Lista de productos con checkboxes -->
                <div class="px-3 flex-shrink-0">
                    <div class="border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-800 px-2 py-1 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center">
                                <input
                                    type="checkbox"
                                    id="select-all-products"
                                    wire:model="selectAllProductsForTransfer"
                                    wire:change="toggleSelectAllProducts"
                                    class="w-3.5 h-3.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                >
                                <label for="select-all-products" class="ml-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                    Seleccionar todos los productos
                                </label>
                            </div>
                        </div>
                        <div class="h-[120px] overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-800">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800 sticky top-0 z-10">
                                    <tr>
                                        <th scope="col" class="px-1.5 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-8">
                                            Sel.
                                        </th>
                                        <th scope="col" class="px-1.5 py-1.5 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                            Producto
                                        </th>
                                        <th scope="col" class="px-1.5 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-12">
                                            Cant.
                                        </th>
                                        <th scope="col" class="px-1.5 py-1.5 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                            Precio
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-700 divide-y divide-gray-200 dark:divide-gray-600">
                                    @forelse($cart as $productId => $item)
                                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-150">
                                            <td class="px-1.5 py-1.5 whitespace-nowrap">
                                                <input
                                                    type="checkbox"
                                                    id="product-{{ $productId }}"
                                                    wire:model="selectedProductsForTransfer"
                                                    value="{{ $productId }}"
                                                    wire:change="$refresh"
                                                    onclick="console.log('Checkbox clicked: {{ $productId }}', this.checked); if(this.checked) { window.livewire.find('point-of-sale').set('selectedProductsForTransfer', [...window.livewire.find('point-of-sale').get('selectedProductsForTransfer'), '{{ $productId }}']); } else { window.livewire.find('point-of-sale').set('selectedProductsForTransfer', window.livewire.find('point-of-sale').get('selectedProductsForTransfer').filter(id => id !== '{{ $productId }}')); }"
                                                    class="w-3.5 h-3.5 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                                >
                                            </td>
                                            <td class="px-1.5 py-1.5 whitespace-nowrap">
                                                <div class="text-xs font-medium text-gray-900 dark:text-white truncate max-w-[120px]">{{ $item['name'] }}</div>
                                            </td>
                                            <td class="px-1.5 py-1.5 whitespace-nowrap text-right text-xs text-gray-900 dark:text-white">
                                                {{ $item['quantity'] }}
                                            </td>
                                            <td class="px-1.5 py-1.5 whitespace-nowrap text-right text-xs text-gray-900 dark:text-white">
                                                S/ {{ number_format($item['price'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-1.5 py-1.5 text-center text-xs text-gray-500 dark:text-gray-400">
                                                No hay productos en el carrito
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Resumen de selección -->
                <div class="px-3 py-1 flex-shrink-0 flex justify-between items-center">
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <span class="font-medium">{{ count($selectedProductsForTransfer) }}</span> de <span class="font-medium">{{ count($cart) }}</span> productos seleccionados
                        <button
                            wire:click="refreshSelectedProducts"
                            class="ml-1 text-xs text-blue-500 hover:text-blue-700"
                            title="Actualizar selección"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline-block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                        </button>
                    </div>
                    @if(!empty($selectedProductsForTransfer))
                        <div class="text-xs text-green-600 dark:text-green-400">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 inline-block mr-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Listo para transferir
                        </div>
                    @endif
                </div>

                <!-- Selección de mesa destino -->
                <div class="px-3 flex-shrink-0">
                    <h3 class="text-xs font-medium text-gray-800 dark:text-gray-200 mb-1">Selecciona la mesa destino:</h3>
                </div>

                <div class="px-3 pb-2 flex-grow overflow-hidden">
                    <div class="h-full overflow-y-auto scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-800 p-0.5">
                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5">
                            @foreach($availableTables as $availableTable)
                                <button
                                    onclick="transferirMesa({{ $availableTable->id }})"
                                    class="bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-lg p-1.5 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors duration-200 flex flex-col items-center {{ empty($selectedProductsForTransfer) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                    {{ empty($selectedProductsForTransfer) ? 'disabled' : '' }}
                                    x-data="{}"
                                    x-init="
                                        $wire.on('selectedProductsUpdated', (event) => {
                                            console.log('Productos seleccionados actualizados:', event);
                                            if (event.count > 0) {
                                                $el.disabled = false;
                                                $el.classList.remove('opacity-50', 'cursor-not-allowed');
                                            } else {
                                                $el.disabled = true;
                                                $el.classList.add('opacity-50', 'cursor-not-allowed');
                                            }
                                        });
                                    "
                                >
                                    <div class="table-visual {{ $availableTable->shape === 'square' ? 'table-square' : 'table-round' }} bg-green-100 dark:bg-green-900 border-green-500 mb-0.5" style="width: 32px; height: 32px;">
                                        <span class="text-xs font-bold">{{ $availableTable->number }}</span>
                                    </div>
                                    <div class="text-center">
                                        <p class="text-xs font-medium text-gray-800 dark:text-gray-200">Mesa {{ $availableTable->number }}</p>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate">{{ ucfirst($availableTable->location) }}</p>
                                    </div>
                                </button>
                            @endforeach

                            @if(count($availableTables) === 0)
                                <div class="col-span-full flex flex-col items-center justify-center py-4 text-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400 mb-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">No hay mesas disponibles para transferir.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-3 py-1.5 flex justify-end gap-2 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    onclick="cerrarModalTransferencia()"
                    class="inline-flex justify-center py-1 px-2.5 border border-gray-300 dark:border-gray-500 shadow-sm text-xs font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors duration-200 focus:outline-none focus:ring-1 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Delivery -->
    <div
        class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-4"
        x-data="{
            get isOpen() {
                return $wire.showDeliveryModal
            }
        }"
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="display: none;"
        @click.self="$event.stopPropagation()"
    >
        <div
            class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-4xl w-full overflow-hidden"
            @click.stop>
            <div class="flex justify-between items-center px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-red-50 to-white dark:from-red-900/30 dark:to-gray-800">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    Pedido Delivery
                </h3>
                <button
                    wire:click="closeDeliveryModal"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-full p-1 transition-all duration-200 hover:bg-gray-200 dark:hover:bg-gray-600"
                    onclick="console.log('Botón cerrar clickeado'); $wire.closeDeliveryModal();"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-6 py-4">
                <div class="mb-4">
                    <p class="text-gray-700 dark:text-gray-300 mb-2">
                        Ingresa los datos del cliente y la dirección de entrega para el pedido de delivery.
                    </p>
                    <!-- Mensaje de alerta principal - Exactamente como en la imagen -->
                    <div class="bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 p-4 mb-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-yellow-700 dark:text-yellow-200">
                                    Los pedidos de delivery requieren un cliente registrado y una dirección de entrega.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Columna izquierda - Datos del cliente -->
                    <div>
                        <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Datos del Cliente
                        </h4>

                        <div class="space-y-4">
                            <!-- Búsqueda de cliente por teléfono -->
                            <div>
                                <label for="customerPhone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Teléfono</label>
                                <div class="flex">
                                    <input
                                        type="text"
                                        id="customerPhone"
                                        wire:model="customerPhone"
                                        placeholder="Número de teléfono"
                                        class="w-full px-3 py-2 text-sm rounded-l-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                                        onkeypress="if(event.keyCode === 13) { event.preventDefault(); buscarClientePorTelefono(); }"
                                    />
                                    <button
                                        onclick="buscarClientePorTelefono()"
                                        id="search-customer-btn"
                                        class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-r-lg transition-colors duration-200 flex items-center justify-center min-w-[40px]"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 search-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 animate-spin loading-icon hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Busque clientes por su número de teléfono</p>

                                <!-- Mensaje de cliente no encontrado (inicialmente oculto) -->
                                <div id="customer-not-found" class="hidden mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/30 border-l-4 border-yellow-400 rounded-r-md">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Cliente no encontrado</h3>
                                            <div class="mt-1 text-sm text-yellow-700 dark:text-yellow-300">
                                                <p>No se encontró ningún cliente con el teléfono <span id="not-found-phone" class="font-semibold"></span>.</p>
                                                <p class="mt-1">Por favor, complete los datos para registrar un nuevo cliente.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>



                                <!-- Mensaje de cliente encontrado (inicialmente oculto) -->
                                <div id="customer-found" class="hidden mt-2 p-3 bg-green-50 dark:bg-green-900/30 border-l-4 border-green-400 rounded-r-md">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-green-800 dark:text-green-200">Cliente encontrado</h3>
                                            <div class="mt-1 text-sm text-green-700 dark:text-green-300">
                                                <p>Se ha cargado la información de <span id="found-name" class="font-semibold"></span>.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Datos del cliente -->
                            <div>
                                <label for="customerName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Nombre / Razón Social</label>
                                <input
                                    type="text"
                                    id="customerName"
                                    wire:model="customerName"
                                    placeholder="Nombre completo o razón social"
                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                                />
                            </div>

                            <!-- Documento (opcional) -->
                            <div class="flex space-x-2">
                                <div class="w-1/3">
                                    <label for="customerDocumentType" class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Tipo (opcional)</label>
                                    <select
                                        id="customerDocumentType"
                                        wire:model="customerDocumentType"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                                    >
                                        <option value="DNI">DNI</option>
                                        <option value="RUC">RUC</option>
                                        <option value="CE">CE</option>
                                        <option value="Pasaporte">Pasaporte</option>
                                    </select>
                                </div>
                                <div class="w-2/3">
                                    <label for="customerDocument" class="block text-sm font-medium text-gray-500 dark:text-gray-400 mb-1">Documento (opcional)</label>
                                    <input
                                        type="text"
                                        id="customerDocument"
                                        wire:model="customerDocument"
                                        placeholder="Número de documento"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                                    />
                                </div>
                            </div>

                            <div>
                                <button
                                    wire:click="saveCustomer"
                                    id="save-customer-btn"
                                    class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 flex items-center justify-center font-medium shadow-md hover:shadow-lg hover:-translate-y-0.5"
                                    x-data="{
                                        isDisabled: function() {
                                            // Verificar si falta información del cliente
                                            return !$wire.customerName || !$wire.customerPhone;
                                        }
                                    }"
                                    x-on:click="
                                        if (!$wire.customerName || !$wire.customerPhone) {
                                            $wire.dispatch('notification', {
                                                type: 'error',
                                                title: 'Error',
                                                message: 'Debes ingresar el nombre y teléfono del cliente para guardarlo.',
                                                showModal: true
                                            });
                                            return false;
                                        }
                                    "
                                    x-bind:class="{ 'opacity-50 cursor-not-allowed': isDisabled() }"
                                    x-bind:disabled="isDisabled()"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    Guardar Información del Cliente
                                </button>
                                <p class="text-xs text-center text-gray-500 dark:text-gray-400 mt-1">
                                    Solo se requiere nombre y teléfono para registrar un cliente
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Columna derecha - Datos de entrega -->
                    <div>
                        <h4 class="text-md font-medium text-gray-800 dark:text-gray-200 mb-3 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Datos de Entrega
                        </h4>

                        <div class="space-y-4">
                            <div>
                                <label for="deliveryAddress" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Dirección de Entrega</label>
                                <div class="flex space-x-2">
                                    <input
                                        type="text"
                                        id="deliveryAddress"
                                        wire:model="deliveryAddress"
                                        placeholder="Dirección completa"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                                    />
                                    <button
                                        type="button"
                                        onclick="openMapModal();"
                                        class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-all duration-200 flex items-center shadow-md hover:shadow-lg hover:-translate-y-0.5"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Ubica en Mapa
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Ingrese la dirección o use el mapa para ubicar la posición exacta
                                </p>
                            </div>

                            <div>
                                <label for="deliveryReferences" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Referencias</label>
                                <textarea
                                    id="deliveryReferences"
                                    wire:model="deliveryReferences"
                                    placeholder="Referencias para ubicar la dirección (color de casa, puntos de referencia, etc.)"
                                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
                                    rows="3"
                                ></textarea>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    Agregue referencias que ayuden al repartidor a encontrar la dirección
                                </p>
                            </div>

                            <div class="pt-4 space-y-3">
                                <!-- Botón para agregar productos -->
                                <div x-data="{ cartEmpty: function() { return Object.keys($wire.cart).length === 0; } }">
                                    <button
                                        type="button"
                                        x-on:click="$wire.closeDeliveryModal(); setTimeout(() => { document.getElementById('product-search').focus(); }, 100);"
                                        class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors duration-200 flex items-center justify-center font-medium"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Agregar Productos al Carrito
                                    </button>

                                    <div x-show="cartEmpty()" class="mt-2 text-center text-sm text-red-500">
                                        <span class="flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                            Primero debes agregar productos al carrito
                                        </span>
                                    </div>
                                </div>

                                <!-- Botón para procesar pedido - Mejorado y más visible -->
                                <button
                                    wire:click="processDeliveryOrder"
                                    class="w-full px-4 py-4 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-all duration-300 flex items-center justify-center font-bold text-lg shadow-lg hover:shadow-xl hover:-translate-y-1 transform"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    PROCESAR PEDIDO DE DELIVERY
                                </button>

                                <!-- Mensaje informativo sobre el proceso -->
                                <div class="mt-3 text-sm text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 p-4 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <h5 class="font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Información del Proceso
                                    </h5>
                                    <ul class="space-y-1 list-disc list-inside pl-1">
                                        <li>Al procesar el pedido, se registrará la información del cliente y la dirección.</li>
                                        <li>Se generará una comanda para la cocina con los productos solicitados.</li>
                                        <li>El pedido quedará registrado en el sistema para su seguimiento.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-3 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-700">
                <button
                    type="button"
                    wire:click="closeDeliveryModal"
                    class="inline-flex justify-center py-2 px-4 border border-gray-300 dark:border-gray-500 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-600 hover:bg-gray-50 dark:hover:bg-gray-500 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    Cancelar
                </button>
            </div>
        </div>
    </div>

    <!-- Script para comunicación entre iframes -->
    <script>
        // Función para cambiar entre modo claro y oscuro
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                // Cambiar a modo claro
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';

                // Mostrar notificación
                Livewire.dispatch('notification', {
                    type: 'info',
                    title: 'Modo Claro Activado',
                    message: 'Se ha cambiado al tema claro',
                    timeout: 2000
                });
            } else {
                // Cambiar a modo oscuro
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';

                // Mostrar notificación
                Livewire.dispatch('notification', {
                    type: 'info',
                    title: 'Modo Oscuro Activado',
                    message: 'Se ha cambiado al tema oscuro',
                    timeout: 2000
                });
            }
        }

        // Función simplificada para probar notificaciones
        function testNotification(type) {
            const notifications = {
                success: {
                    title: 'Operación Exitosa',
                    message: 'La acción se completó correctamente. Todo funcionó como se esperaba.'
                },
                error: {
                    title: 'Error',
                    message: 'Ha ocurrido un error. No se pudo procesar la solicitud.'
                },
                warning: {
                    title: 'Advertencia',
                    message: 'Hay un problema que requiere tu atención.'
                },
                info: {
                    title: 'Información',
                    message: 'Aquí tienes información importante sobre el sistema.'
                }
            };

            const notification = notifications[type] || notifications.info;

            // Usar dispatch para enviar la notificación
            Livewire.dispatch('notification', {
                type: type,
                title: notification.title,
                message: notification.message,
                timeout: 5000
            });
        }

        // Función para probar notificaciones modales
        function testModalNotification() {
            Livewire.dispatch('notification', {
                type: 'warning',
                title: 'Notificación Modal',
                message: 'Esta es una notificación importante que requiere tu atención y se muestra como un modal para asegurar que la veas.',
                timeout: 5000,
                showModal: true
            });
        }

        // Escuchar eventos de Livewire
        document.addEventListener('livewire:initialized', function() {
            // Escuchar el evento para abrir el modal de delivery después de la renderización
            Livewire.on('open-delivery-modal-after-render', function() {
                console.log('Evento open-delivery-modal-after-render recibido');
                setTimeout(function() {
                    // Forzar la apertura del modal de delivery
                    if (window.Livewire && window.Livewire.find('point-of-sale')) {
                        window.Livewire.find('point-of-sale').set('showDeliveryModal', true);
                        console.log('Modal de delivery forzado a abrir desde JavaScript');
                    }
                }, 300);
            });

            // Escuchar el evento de búsqueda de cliente en progreso
            Livewire.on('search-customer-loading', function(params) {
                console.log('Buscando cliente con teléfono:', params.phone);

                // Mostrar indicador de carga
                const searchButton = document.getElementById('search-customer-btn');
                if (searchButton) {
                    searchButton.querySelector('.search-icon').classList.add('hidden');
                    searchButton.querySelector('.loading-icon').classList.remove('hidden');
                    searchButton.disabled = true;
                }

                // Ocultar mensajes previos
                document.getElementById('customer-not-found').classList.add('hidden');
                document.getElementById('customer-found').classList.add('hidden');
            });

            // Escuchar el evento de resultado de búsqueda de cliente
            Livewire.on('search-customer-result', function(params) {
                console.log('Resultado de búsqueda de cliente:', params);

                // Restaurar botón de búsqueda
                const searchButton = document.getElementById('search-customer-btn');
                if (searchButton) {
                    searchButton.querySelector('.search-icon').classList.remove('hidden');
                    searchButton.querySelector('.loading-icon').classList.add('hidden');
                    searchButton.disabled = false;
                }

                if (params.found) {
                    // Cliente encontrado
                    document.getElementById('customer-not-found').classList.add('hidden');

                    // Mostrar mensaje de cliente encontrado
                    const foundMessage = document.getElementById('customer-found');
                    const foundName = document.getElementById('found-name');

                    if (foundMessage && foundName) {
                        foundName.textContent = params.name;
                        foundMessage.classList.remove('hidden');

                        // Ocultar el mensaje después de 5 segundos
                        setTimeout(() => {
                            foundMessage.classList.add('hidden');
                        }, 5000);
                    }
                } else {
                    // Cliente no encontrado
                    document.getElementById('customer-found').classList.add('hidden');

                    // Mostrar mensaje de cliente no encontrado
                    const notFoundMessage = document.getElementById('customer-not-found');
                    const notFoundPhone = document.getElementById('not-found-phone');

                    if (notFoundMessage && notFoundPhone) {
                        notFoundPhone.textContent = params.phone;
                        notFoundMessage.classList.remove('hidden');

                        // Habilitar el botón de guardar cliente
                        const saveButton = document.querySelector('button[wire\\:click="saveCustomer"]');
                        if (saveButton) {
                            saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
                            saveButton.disabled = false;
                        }

                        // Hacer que el campo de nombre reciba el foco
                        setTimeout(() => {
                            document.getElementById('customerName')?.focus();
                        }, 500);
                    }
                }
            });

            // Escuchar el evento para cerrar el modal de delivery
            Livewire.on('delivery-modal-closed', function() {
                console.log('Evento delivery-modal-closed recibido');

                // Asegurarse de que el modal esté cerrado
                if (window.Livewire && window.Livewire.find('point-of-sale')) {
                    window.Livewire.find('point-of-sale').set('showDeliveryModal', false);
                    console.log('Modal de delivery forzado a cerrar desde JavaScript');

                    // Mostrar una alerta para confirmar que el pedido se procesó correctamente
                    // y redirigir al mapa de mesas después de hacer clic en Aceptar
                    setTimeout(function() {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Pedido Procesado!',
                            text: 'El pedido de delivery ha sido registrado correctamente.',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#10b981'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Redirigir al mapa de mesas
                                window.location.href = '{{ route("tables.map") }}';
                            }
                        });
                    }, 500);
                }
            });
        });

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
        // Función para procesar pedido de delivery
        function procesarPedidoDelivery() {
            // Verificar si hay productos en el carrito
            if (Object.keys(window.Livewire.find('point-of-sale').cart).length === 0) {
                Livewire.dispatch('notification', {
                    type: 'error',
                    title: 'Error',
                    message: 'No hay productos en el carrito. Añade productos para procesar el pedido.',
                    showModal: true
                });
                return;
            }

            // Verificar si falta información del cliente
            if (!window.Livewire.find('point-of-sale').customerName || !window.Livewire.find('point-of-sale').customerPhone) {
                Livewire.dispatch('notification', {
                    type: 'error',
                    title: 'Error',
                    message: 'Debes ingresar el nombre y teléfono del cliente.',
                    showModal: true
                });
                return;
            }

            // Verificar si falta dirección de entrega
            if (!window.Livewire.find('point-of-sale').deliveryAddress) {
                Livewire.dispatch('notification', {
                    type: 'error',
                    title: 'Error',
                    message: 'Debes ingresar la dirección de entrega.',
                    showModal: true
                });
                return;
            }

            // Si todas las validaciones pasan, llamar al método de Livewire
            window.Livewire.find('point-of-sale').processDeliveryOrder();
        }

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
            console.log('Ejecutando vaciarCarrito() - Limpiando carrito y liberando mesa');

            // Llamar al método clearSale del componente Livewire que limpia el carrito y libera la mesa
            if (window.Livewire) {
                // Usar dispatch para llamar al método clearSale
                Livewire.dispatch('clearSale');
                console.log('Evento clearSale enviado correctamente');

                // Redirigir al mapa de mesas después de un breve retraso
                setTimeout(function() {
                    console.log('Redirigiendo al mapa de mesas...');
                    window.location.href = '{{ url("/tables") }}';
                }, 500);
            } else {
                console.error('Livewire no está disponible');
                // Plan B: Recargar la página
                window.location.href = '{{ url("/tables") }}';
            }
        }

        // Escuchar cuando se cierra la ventana del comprobante
        window.addEventListener('message', function(event) {
            if (event.data === 'invoice-completed') {
                console.log('Comprobante generado - Vaciando carrito');
                vaciarCarrito();
            }
        });

        // Función para ir al mapa de mesas sin perder el carrito
        function irAMesas() {
            // Mostrar mensaje de carga
            Swal.fire({
                title: 'Guardando...',
                text: 'Guardando carrito y actualizando mesa',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Llamar al método de Livewire para guardar el carrito
            Livewire.dispatch('guardarCarritoYRedirigir');
        }

        // Función para buscar cliente por teléfono - Versión KISS mejorada
        function buscarClientePorTelefono() {
            console.log('Iniciando búsqueda de cliente por teléfono');

            // Mostrar indicador de carga
            const searchButton = document.getElementById('search-customer-btn');
            if (searchButton) {
                searchButton.querySelector('.search-icon').classList.add('hidden');
                searchButton.querySelector('.loading-icon').classList.remove('hidden');
                searchButton.disabled = true;
            }

            // Ocultar mensajes previos
            document.getElementById('customer-not-found')?.classList.add('hidden');
            document.getElementById('customer-found')?.classList.add('hidden');

            // Obtener el teléfono
            const phone = document.getElementById('customerPhone').value;

            if (!phone) {
                alert('Ingrese un número de teléfono para buscar el cliente');

                // Restaurar botón
                if (searchButton) {
                    searchButton.querySelector('.search-icon').classList.remove('hidden');
                    searchButton.querySelector('.loading-icon').classList.add('hidden');
                    searchButton.disabled = false;
                }

                return;
            }

            // Llamar directamente al método del componente Livewire
            @this.set('customerPhone', phone);
            @this.searchCustomerByPhone();

            // Verificar el resultado después de un breve retraso
            setTimeout(() => {
                // Restaurar botón
                if (searchButton) {
                    searchButton.querySelector('.search-icon').classList.remove('hidden');
                    searchButton.querySelector('.loading-icon').classList.add('hidden');
                    searchButton.disabled = false;
                }

                // Verificar si el cliente existe en la base de datos
                const customerExists = @this.get('customerId');

                if (!customerExists) {
                    // Cliente no encontrado - mostrar mensaje manualmente
                    const notFoundMessage = document.getElementById('customer-not-found');
                    const notFoundPhone = document.getElementById('not-found-phone');

                    if (notFoundMessage && notFoundPhone) {
                        notFoundPhone.textContent = phone;
                        notFoundMessage.classList.remove('hidden');

                        // Habilitar el botón de guardar cliente
                        const saveButton = document.querySelector('button[wire\\:click="saveCustomer"]');
                        if (saveButton) {
                            saveButton.classList.remove('opacity-50', 'cursor-not-allowed');
                            saveButton.disabled = false;
                        }

                        // Hacer que el campo de nombre reciba el foco
                        document.getElementById('customerName')?.focus();
                    }
                }
            }, 500);
        }

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
                const name = item.querySelector('.cart-item-standard-name').textContent;
                const price = parseFloat(item.querySelector('.cart-item-standard-price span').textContent.replace('S/ ', ''));
                const quantity = parseInt(item.querySelector('.quantity-value').textContent);
                const subtotal = parseFloat(item.querySelector('.cart-item-standard-subtotal').textContent.replace('S/ ', ''));

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

            // Obtener el tipo de servicio actual directamente del DOM
            // Esto asegura que obtenemos el valor más actualizado, incluso si el usuario cambió el tipo de servicio
            let currentServiceType = '';

            // Verificar qué botón de tipo de servicio está activo (tiene la clase de fondo específica)
            if (document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-100') ||
                document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-900\\/50')) {
                currentServiceType = 'takeout';
            } else if (document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-100') ||
                       document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-900\\/50')) {
                currentServiceType = 'dine_in';
            } else if (document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-100') ||
                       document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-900\\/50')) {
                currentServiceType = 'delivery';
            } else {
                // Si no podemos determinar por el DOM, usar el valor de Livewire como respaldo
                currentServiceType = '{{ $serviceType }}'.trim();
            }

            console.log('Tipo de servicio detectado:', currentServiceType);

            // Si el tipo de servicio es "takeout" (Para Llevar), solicitar el nombre del cliente
            if (currentServiceType === 'takeout') {
                console.log('Solicitando nombre del cliente para pedido Para Llevar');
                // Mostrar un modal para solicitar el nombre del cliente
                Swal.fire({
                    title: 'Nombre del Cliente',
                    input: 'text',
                    inputLabel: 'Por favor, ingrese el nombre del cliente para el pedido Para Llevar',
                    inputPlaceholder: 'Nombre del cliente',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    inputValidator: (value) => {
                        if (!value) {
                            return 'Debe ingresar un nombre para el cliente';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        console.log('Nombre del cliente ingresado:', result.value);
                        // Continuar con la creación de la orden incluyendo el nombre del cliente
                        crearOrdenComanda(productos, result.value, currentServiceType);
                    }
                });
            } else {
                console.log('Tipo de servicio no es takeout, continuando sin solicitar nombre');
                // Para otros tipos de servicio, continuar sin solicitar nombre
                crearOrdenComanda(productos, null, currentServiceType);
            }
        }

        // Función auxiliar para crear la orden de comanda
        function crearOrdenComanda(productos, customerName = null, serviceType = null) {
            // Si no se proporciona un tipo de servicio, intentar detectarlo
            if (!serviceType) {
                // Verificar qué botón de tipo de servicio está activo
                if (document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-100') ||
                    document.querySelector('button[wire\\:click="setServiceType(\'takeout\')"].bg-green-900\\/50')) {
                    serviceType = 'takeout';
                } else if (document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-100') ||
                           document.querySelector('button[wire\\:click="setServiceType(\'dine_in\')"].bg-blue-900\\/50')) {
                    serviceType = 'dine_in';
                } else if (document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-100') ||
                           document.querySelector('button[wire\\:click="setServiceType(\'delivery\')"].bg-red-900\\/50')) {
                    serviceType = 'delivery';
                } else {
                    // Si no podemos determinar por el DOM, usar el valor de Livewire como respaldo
                    serviceType = '{{ $serviceType }}'.trim();
                }
            }

            console.log('Tipo de servicio en crearOrdenComanda:', serviceType);

            // Crear el objeto de datos para la solicitud
            const requestData = {
                has_products: true,
                cart_items: productos,
                service_type: serviceType // Enviar el tipo de servicio detectado o proporcionado
            };

            // Si se proporcionó un nombre de cliente, agregarlo a la solicitud
            if (customerName) {
                requestData.customer_name = customerName;
                console.log('Enviando nombre de cliente:', customerName);
            }

            console.log('Datos de la solicitud:', requestData);

            // Crear la orden con los productos capturados directamente
            fetch('{{ route("pos.create-order") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(requestData)
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
                const name = item.querySelector('.cart-item-standard-name').textContent;
                const price = parseFloat(item.querySelector('.cart-item-standard-price span').textContent.replace('S/ ', ''));
                const quantity = parseInt(item.querySelector('.quantity-value').textContent);
                const subtotal = parseFloat(item.querySelector('.cart-item-standard-subtotal').textContent.replace('S/ ', ''));

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

        function abrirModalDelivery() {
            console.log('Abriendo modal de delivery...');
            try {
                @this.openDeliveryModal();
                console.log('Modal de delivery abierto:', @this.showDeliveryModal);

                // Asegurarse de que el modal permanezca abierto
                setTimeout(() => {
                    if (!@this.showDeliveryModal) {
                        console.log('Forzando apertura del modal de delivery...');
                        @this.showDeliveryModal = true;
                    }
                }, 100);
            } catch (error) {
                console.error('Error al abrir modal de delivery:', error);
                alert('Error al abrir el modal de delivery: ' + (error.message || 'Error desconocido'));
            }
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
                const name = item.querySelector('.cart-item-standard-name').textContent;
                const price = parseFloat(item.querySelector('.cart-item-standard-price span').textContent.replace('S/ ', ''));
                const quantity = parseInt(item.querySelector('.quantity-value').textContent);
                const subtotal = parseFloat(item.querySelector('.cart-item-standard-subtotal').textContent.replace('S/ ', ''));

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
                // Abrir el formulario de factura en una ventana nueva con tamaño optimizado
                const facturaWindow = window.open('{{ url("pos/invoice/form") }}/' + orderId, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');

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

        // Función para abrir el modal de transferencia
        function abrirModalTransferencia() {
            console.log('Abriendo modal de transferencia...');

            // Verificar si hay productos en el carrito
            if ({{ count($cart) }} === 0) {
                alert('No hay productos en el carrito para transferir. Primero debes añadir productos al carrito.');
                return;
            }

            try {
                // Llamar al método del componente Livewire para abrir el modal
                @this.openTransferModal();
                console.log('Modal abierto:', @this.showTransferModal);

                // Esperar a que el modal se abra completamente
                setTimeout(() => {
                    // Forzar la actualización de la UI después de que el modal esté visible
                    @this.refreshSelectedProducts();
                    console.log('Estado de selección actualizado');
                }, 300);
            } catch (error) {
                console.error('Error al abrir modal de transferencia:', error);
                alert('Error al abrir el modal de transferencia: ' + (error.message || 'Error desconocido'));
            }
        }

        // Función para cerrar el modal de transferencia
        function cerrarModalTransferencia() {
            @this.showTransferModal = false;
        }

        // Función para transferir mesa
        function transferirMesa(targetTableId) {
            @this.transferTable(targetTableId);
        }

        // Función para abrir el modal de delivery
        function abrirModalDelivery() {
            @this.showDeliveryModal = true;
        }

        // Ejecutar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar componentes si es necesario
        });

        // Función para ir a mesas
        function irAMesas() {
            // Mostrar mensaje de carga
            Swal.fire({
                title: 'Guardando...',
                text: 'Guardando carrito y actualizando mesa',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Llamar al método de Livewire para guardar el carrito
            Livewire.dispatch('guardarCarritoYRedirigir');
        }

        // Variables para el mapa
        let map;
        let marker;
        const defaultLat = -11.9865603;
        const defaultLng = -77.0679584;

        // Función para buscar dirección usando Nominatim
        function searchAddress() {
            const address = document.getElementById('searchAddress').value.trim();
            if (!address) {
                alert('Por favor ingresa una dirección para buscar');
                return;
            }

            // Mostrar indicador de carga
            const searchButton = document.querySelector('button[onclick="searchAddress();"]');
            const originalText = searchButton.innerHTML;
            searchButton.innerHTML = '<span class="inline-block animate-spin mr-2">↻</span> Buscando...';
            searchButton.disabled = true;

            // Realizar la búsqueda con Nominatim
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}&limit=1`)
                .then(response => response.json())
                .then(data => {
                    // Restaurar el botón
                    searchButton.innerHTML = originalText;
                    searchButton.disabled = false;

                    if (data && data.length > 0) {
                        // Encontró la ubicación
                        const lat = parseFloat(data[0].lat);
                        const lon = parseFloat(data[0].lon);

                        // Actualizar el mapa
                        map.setView([lat, lon], 16);
                        marker.setLatLng([lat, lon]);

                        // Actualizar los campos de latitud y longitud
                        document.getElementById('latitude').value = lat.toFixed(7);
                        document.getElementById('longitude').value = lon.toFixed(7);
                    } else {
                        // No encontró la ubicación
                        alert('No se encontró la dirección. Por favor, ubica manualmente en el mapa.');
                    }
                })
                .catch(error => {
                    // Error en la búsqueda
                    console.error('Error al buscar la dirección:', error);
                    searchButton.innerHTML = originalText;
                    searchButton.disabled = false;
                    alert('Error al buscar la dirección. Por favor, ubica manualmente en el mapa.');
                });
        }

        // Función para inicializar el mapa
        function initMap() {
            // Crear el mapa centrado en la ubicación por defecto
            map = L.map('map').setView([defaultLat, defaultLng], 13);

            // Agregar capa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Agregar marcador en la ubicación por defecto
            marker = L.marker([defaultLat, defaultLng], {
                draggable: true
            }).addTo(map);

            // Actualizar campos de latitud y longitud al mover el marcador
            marker.on('dragend', function(event) {
                const position = marker.getLatLng();
                document.getElementById('latitude').value = position.lat.toFixed(7);
                document.getElementById('longitude').value = position.lng.toFixed(7);
            });

            // Inicializar campos de latitud y longitud
            document.getElementById('latitude').value = defaultLat;
            document.getElementById('longitude').value = defaultLng;

            // Permitir hacer clic en el mapa para mover el marcador
            map.on('click', function(e) {
                marker.setLatLng(e.latlng);
                document.getElementById('latitude').value = e.latlng.lat.toFixed(7);
                document.getElementById('longitude').value = e.latlng.lng.toFixed(7);
            });
        }

        // Función para abrir el modal del mapa
        function openMapModal() {
            document.getElementById('mapModal').style.display = 'flex';
            // Inicializar el mapa después de que el modal sea visible
            setTimeout(function() {
                if (!map) {
                    initMap();
                } else {
                    // Si el mapa ya existe, actualizar su tamaño
                    map.invalidateSize();
                }
            }, 100);
        }

        // Función para cerrar el modal del mapa
        function closeMapModal() {
            document.getElementById('mapModal').style.display = 'none';
        }
    </script>

<!-- Modal del Mapa -->
<div id="mapModal" class="fixed inset-0 bg-gray-600/75 dark:bg-gray-900/80 flex items-center justify-center z-50 p-0 hidden" style="padding: 0 !important;">
  <div style="width: 95vw !important; height: 95vh !important; max-width: none !important; max-height: none !important;" class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl">
    <div class="flex justify-between items-center px-6 py-3 border-b border-gray-200 dark:border-gray-700">
      <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
        Ubicar Dirección en el Mapa
      </h3>
      <button onclick="closeMapModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>
    </div>
    <div class="flex flex-col" style="height: calc(95vh - 64px) !important;">
      <!-- Campo de búsqueda en una barra fija en la parte superior -->
      <div class="px-6 py-2 border-b border-gray-200 dark:border-gray-700">
        <div class="flex space-x-2">
          <input
            type="text"
            id="searchAddress"
            placeholder="Ej: Av. Principal 123, Ciudad"
            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
            onkeypress="if(event.key === 'Enter') { searchAddress(); return false; }"
          />
          <button
            type="button"
            onclick="searchAddress();"
            class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors duration-200 whitespace-nowrap"
          >
            Buscar
          </button>
        </div>
      </div>

      <!-- Área del mapa - Ocupa todo el espacio disponible -->
      <div class="flex-grow overflow-hidden" style="flex: 1 !important; min-height: 70vh !important;">
        <div id="map" class="w-full h-full"></div>
      </div>

      <!-- Controles en la parte inferior -->
      <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700">
        <!-- Campos de coordenadas -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div>
            <label for="latitude" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
              Latitud:
            </label>
            <input
              type="text"
              id="latitude"
              class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
              readonly
            />
          </div>
          <div>
            <label for="longitude" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
              Longitud:
            </label>
            <input
              type="text"
              id="longitude"
              class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition"
              readonly
            />
          </div>
        </div>

        <!-- Botones de acción -->
        <div class="flex justify-between space-x-4">
          <button
            type="button"
            onclick="closeMapModal()"
            class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition-colors duration-200 font-medium w-1/2"
          >
            Volver
          </button>
          <button
            type="button"
            onclick="console.log('Ubicación confirmada'); closeMapModal();"
            class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors duration-200 font-medium w-1/2"
          >
            Confirmar Ubicación
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Escuchar el evento para abrir el modal de facturación después de la renderización
    window.addEventListener('open-invoice-modal-after-render', function() {
      // Esperar un momento para asegurar que el componente esté completamente renderizado
      setTimeout(function() {
        abrirFactura();
      }, 500);
    });
  });
</script>