<x-filament-panels::page>
    @if($showTableDetails && $selectedTable)
        <div class="w-full h-full">
            <!-- Header con navegación y título -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-4 mb-4 flex items-center justify-between">
                <div class="flex items-center gap-x-4">
                    <x-filament::button
                        wire:click="unselectTable"
                        color="gray"
                        icon="heroicon-o-arrow-left"
                        class="flex-shrink-0"
                    >
                        Volver
                    </x-filament::button>

                    <div class="flex items-center gap-x-2">
                        <x-filament::icon
                            :icon="match($selectedTable->status) {
                                'available' => 'heroicon-o-check',
                                'occupied' => 'heroicon-o-users',
                                'reserved' => 'heroicon-o-clock',
                                'maintenance' => 'heroicon-o-wrench',
                                default => 'heroicon-o-table-cells',
                            }"
                            @class([
                                'h-6 w-6',
                                'text-success-500' => $selectedTable->status === 'available',
                                'text-danger-500' => $selectedTable->status === 'occupied',
                                'text-warning-500' => $selectedTable->status === 'reserved',
                                'text-gray-500' => $selectedTable->status === 'maintenance',
                            ])
                        />
                        <h1 class="text-xl font-semibold">Mesa {{ $selectedTable->number }}</h1>
                    </div>
                </div>

                @if($selectedTable->status === 'available' && !$showProductSelection)
                    <x-filament::button
                        wire:click="showProducts"
                        color="primary"
                        size="lg"
                        class="animate-pulse-subtle hidden sm:flex"
                        icon="heroicon-o-shopping-cart"
                    >
                        Ver productos
                    </x-filament::button>
                @endif
            </div>

            <!-- Contenido principal -->
            @if($showProductSelection)
                <!-- Vista de productos -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Panel de búsqueda y categorías (ocupa toda la anchura en móvil, 1/4 en escritorio) -->
                    <div class="md:col-span-1 space-y-4">
                        <!-- Barra de búsqueda -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-4">
                            <div class="flex gap-2 mb-4">
                                <div class="flex-1">
                                    <x-filament::input.wrapper>
                                        <x-filament::input
                                            type="text"
                                            wire:model.defer="productSearchQuery"
                                            placeholder="Buscar productos..."
                                            icon="heroicon-o-magnifying-glass"
                                        />
                                    </x-filament::input.wrapper>
                                </div>
                                <x-filament::button wire:click="searchProducts" size="sm">
                                    Buscar
                                </x-filament::button>
                            </div>
                            <x-filament::button wire:click="resetProductSearch" size="sm" color="gray" class="w-full">
                                Limpiar
                            </x-filament::button>
                        </div>

                        <!-- Categorías -->
                        <div class="bg-white dark:bg-gray-800 shadow rounded-xl overflow-hidden">
                            <h3 class="p-4 border-b border-gray-200 dark:border-gray-700 font-medium">Categorías</h3>
                            <div class="overflow-y-auto max-h-[400px]">
                                <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($categories as $category)
                                        <li>
                                            <button
                                                wire:click="loadProductsByCategory('{{ $category->id }}')"
                                                @class([
                                                    'w-full py-3 px-4 text-left transition hover:bg-gray-50 dark:hover:bg-gray-800',
                                                    'bg-primary-50 dark:bg-primary-900/20 font-medium text-primary-600 dark:text-primary-400' => $selectedCategoryId == $category->id,
                                                ])
                                            >
                                                {{ $category->name }}
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Panel de productos (ocupa toda la anchura en móvil, 3/4 en escritorio) -->
                    <div class="md:col-span-3">
                        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-4">
                            <h3 class="text-lg font-medium mb-4">
                                @if($categories->where('id', $selectedCategoryId)->first())
                                    {{ $categories->where('id', $selectedCategoryId)->first()->name }}
                                @else
                                    Productos
                                @endif
                            </h3>

                            <!-- Grid de productos -->
                            @if($products && $products->count() > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    @foreach($products as $product)
                                        <div class="product-card p-3 border rounded-lg dark:border-gray-700 hover:shadow-md transition cursor-pointer bg-white dark:bg-gray-800">
                                            <div class="flex flex-col h-full">
                                                <!-- Imagen del producto o placeholder -->
                                                <div class="mb-2 h-32 flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-md overflow-hidden">
                                                    @if($product->image_path)
                                                        <img src="{{ asset($product->image_path) }}"
                                                            alt="{{ $product->name }}"
                                                            class="object-cover h-full w-full"
                                                        >
                                                    @else
                                                        <x-filament::icon
                                                            icon="heroicon-o-shopping-bag"
                                                            class="h-12 w-12 text-gray-400"
                                                        />
                                                    @endif
                                                </div>

                                                <!-- Información del producto -->
                                                <div class="flex-1">
                                                    <h4 class="font-medium text-sm">{{ $product->name }}</h4>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 line-clamp-2 mt-1">
                                                        {{ $product->description ?: 'Sin descripción' }}
                                                    </p>
                                                </div>

                                                <!-- Precio -->
                                                <div class="mt-2 pt-2 border-t dark:border-gray-700">
                                                    <span class="font-semibold text-primary-600 dark:text-primary-400">
                                                        {{ number_format($product->sale_price, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="h-48 flex items-center justify-center">
                                    <div class="text-center p-6">
                                        <x-filament::icon
                                            icon="heroicon-o-shopping-bag"
                                            class="h-12 w-12 text-gray-400 mx-auto mb-4"
                                        />
                                        <h4 class="text-gray-500 dark:text-gray-400 text-lg font-medium">No hay productos disponibles</h4>
                                        <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
                                            No se encontraron productos en esta categoría o para la búsqueda actual.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Botón flotante para móvil: volver a detalles -->
                <div class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 md:hidden">
                    <x-filament::button
                        wire:click="hideProducts"
                        color="primary"
                        icon="heroicon-o-arrow-left"
                        class="shadow-lg"
                    >
                        Volver a detalles
                    </x-filament::button>
                </div>
            @else
                <!-- Vista de detalles de mesa -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Tarjeta de información de la mesa -->
                    <div class="md:col-span-2">
                        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
                            <h3 class="text-lg font-medium mb-4">Información de la mesa</h3>

                            <div class="grid grid-cols-2 gap-6 mb-8">
                                <div>
                                    <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Número</span>
                                    <span class="block font-semibold text-2xl">{{ $selectedTable->number }}</span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Capacidad</span>
                                    <span class="block font-semibold text-2xl flex items-center">
                                        <x-filament::icon
                                            icon="heroicon-o-user-group"
                                            class="h-5 w-5 mr-2"
                                        />
                                        {{ $selectedTable->capacity }} personas
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Ubicación</span>
                                    <span class="block font-semibold text-lg">
                                        {{ $this->getLocationOptions()[$selectedTable->location] ?? 'No especificada' }}
                                    </span>
                                </div>
                                <div>
                                    <span class="block text-sm font-medium text-gray-500 dark:text-gray-400">Estado</span>
                                    <x-filament::badge
                                        :color="$this->getStatusColor($selectedTable->status)"
                                        size="xl"
                                    >
                                        {{ $this->getStatusOptions()[$selectedTable->status] ?? $selectedTable->status }}
                                    </x-filament::badge>
                                </div>
                            </div>

                            <!-- Botón grande para Ver productos (solo visible en escritorio) -->
                            @if($selectedTable->status === 'available')
                                <div class="hidden md:block">
                                    <x-filament::button
                                        wire:click="showProducts"
                                        color="primary"
                                        size="lg"
                                        class="w-full animate-pulse-subtle"
                                        icon="heroicon-o-shopping-cart"
                                    >
                                        Ver productos
                                    </x-filament::button>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Cambiar estado y acciones -->
                    <div class="md:col-span-1">
                        <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
                            <h3 class="text-lg font-medium mb-4">Cambiar estado</h3>

                            <div class="grid grid-cols-1 gap-2 mb-6">
                                @if($selectedTable->status !== 'available')
                                    <x-filament::button
                                        color="success"
                                        wire:click="updateTableStatus('{{ $selectedTable->id }}', 'available')"
                                        icon="heroicon-o-check"
                                        class="justify-center"
                                    >
                                        Disponible
                                    </x-filament::button>
                                @endif

                                @if($selectedTable->status !== 'occupied')
                                    <x-filament::button
                                        color="danger"
                                        wire:click="updateTableStatus('{{ $selectedTable->id }}', 'occupied')"
                                        icon="heroicon-o-users"
                                        class="justify-center"
                                    >
                                        Ocupada
                                    </x-filament::button>
                                @endif

                                @if($selectedTable->status !== 'reserved')
                                    <x-filament::button
                                        color="warning"
                                        wire:click="updateTableStatus('{{ $selectedTable->id }}', 'reserved')"
                                        icon="heroicon-o-clock"
                                        class="justify-center"
                                    >
                                        Reservada
                                    </x-filament::button>
                                @endif

                                @if($selectedTable->status !== 'maintenance')
                                    <x-filament::button
                                        color="gray"
                                        wire:click="updateTableStatus('{{ $selectedTable->id }}', 'maintenance')"
                                        icon="heroicon-o-wrench"
                                        class="justify-center"
                                    >
                                        En mantenimiento
                                    </x-filament::button>
                                @endif
                            </div>

                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <h3 class="text-lg font-medium mb-4">Acciones</h3>

                                <x-filament::button
                                    tag="a"
                                    :href="route('filament.admin.resources.tables.edit', ['record' => $selectedTable])"
                                    color="gray"
                                    icon="heroicon-o-pencil-square"
                                    class="w-full mb-2"
                                >
                                    Editar mesa
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón flotante para móvil: Ver productos -->
                @if($selectedTable->status === 'available')
                    <div class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-50 md:hidden">
                        <x-filament::button
                            wire:click="showProducts"
                            color="primary"
                            icon="heroicon-o-shopping-cart"
                            class="shadow-lg"
                        >
                            Ver productos
                        </x-filament::button>
                    </div>
                @endif
            @endif
        </div>
    @else
        <div class="flex flex-col gap-6">
            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-4">
                <h2 class="text-xl font-semibold mb-4">Filtros</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="statusFilter"
                                placeholder="Filtrar por estado"
                            >
                                <option value="">Todos los estados</option>
                                @foreach ($this->getStatusOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                    <div>
                        <x-filament::input.wrapper>
                            <x-filament::input.select
                                wire:model.live="locationFilter"
                                placeholder="Filtrar por ubicación"
                            >
                                <option value="">Todas las ubicaciones</option>
                                @foreach ($this->getLocationOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </x-filament::input.select>
                        </x-filament::input.wrapper>
                    </div>
                    <div class="flex items-center gap-2">
                        <x-filament::button wire:click="applyFilter">
                            Aplicar filtros
                        </x-filament::button>
                        <x-filament::button color="gray" wire:click="resetFilters">
                            Restablecer
                        </x-filament::button>
                    </div>
                </div>
            </div>

            <!-- Leyenda de estados -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-4">
                <h2 class="text-xl font-semibold mb-4">Leyenda</h2>
                <div class="flex flex-wrap gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-md bg-success-100 dark:bg-success-900 border-2 border-success-500 flex items-center justify-center">
                            <x-filament::icon
                                icon="heroicon-o-check"
                                class="h-5 w-5 text-success-600 dark:text-success-400"
                            />
                        </div>
                        <span>Disponible</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-md bg-danger-100 dark:bg-danger-900 border-2 border-danger-500 flex items-center justify-center">
                            <x-filament::icon
                                icon="heroicon-o-users"
                                class="h-5 w-5 text-danger-600 dark:text-danger-400"
                            />
                        </div>
                        <span>Ocupada</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-md bg-warning-100 dark:bg-warning-900 border-2 border-warning-500 flex items-center justify-center">
                            <x-filament::icon
                                icon="heroicon-o-clock"
                                class="h-5 w-5 text-warning-600 dark:text-warning-400"
                            />
                        </div>
                        <span>Reservada</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-md bg-gray-100 dark:bg-gray-800 border-2 border-gray-400 flex items-center justify-center">
                            <x-filament::icon
                                icon="heroicon-o-wrench"
                                class="h-5 w-5 text-gray-600 dark:text-gray-400"
                            />
                        </div>
                        <span>En mantenimiento</span>
                    </div>
                </div>
            </div>

            <!-- Mapa de mesas -->
            <div class="bg-white dark:bg-gray-800 shadow rounded-xl p-6">
                <div class="flex justify-between mb-4">
                    <h2 class="text-xl font-semibold">Mapa de mesas</h2>
                    <div class="flex items-center gap-2">
                        <x-filament::button
                            wire:click="loadTables"
                            icon="heroicon-o-arrow-path"
                            color="gray"
                        >
                            Actualizar
                        </x-filament::button>
                    </div>
                </div>

                <!-- Secciones de mesas por ubicación -->
                @php
                    $groupedTables = $tables->groupBy('location');
                    $locationNames = [
                        'interior' => 'Interior',
                        'terraza' => 'Terraza',
                        'barra' => 'Barra',
                        'vip' => 'Zona VIP',
                        '' => 'Sin ubicación'
                    ];
                @endphp

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @foreach ($groupedTables as $location => $locationTables)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 location-container">
                            <h3 class="text-lg font-medium mb-4">{{ $locationNames[$location] ?? $location }}</h3>
                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                                @foreach ($locationTables as $table)
                                    <div
                                        wire:key="table-{{ $table->id }}"
                                        wire:click="selectTable('{{ $table->id }}')"
                                        @class([
                                            'relative cursor-pointer rounded-lg p-3 flex flex-col items-center justify-center transition-all table-card state-change',
                                            'ring-2 ring-primary-500 shadow-lg scale-105 table-selected' => $selectedTableId == $table->id,
                                            'hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:shadow-md hover:scale-102 transform transition-all duration-200',
                                        ])
                                        x-data="{}"
                                        role="button"
                                        tabindex="0"
                                        wire:keydown.enter="selectTable('{{ $table->id }}')"
                                    >
                                        <span class="sr-only">
                                            Mesa {{ $table->number }}, {{ $this->getStatusOptions()[$table->status] }}, {{ $table->capacity }} personas,
                                            en {{ $this->getLocationOptions()[$table->location] ?? 'ubicación no especificada' }}.
                                            Presiona Enter para ver detalles.
                                        </span>
                                        <div
                                            @class([
                                                'w-20 h-20 rounded-lg flex flex-col items-center justify-center mb-2 shadow-md border-2 transition-all state-change',
                                                'bg-success-100 dark:bg-success-900 border-success-500' => $table->status === 'available',
                                                'bg-danger-100 dark:bg-danger-900 border-danger-500' => $table->status === 'occupied',
                                                'bg-warning-100 dark:bg-warning-900 border-warning-500' => $table->status === 'reserved',
                                                'bg-gray-100 dark:bg-gray-800 border-gray-400' => $table->status === 'maintenance',
                                            ])
                                        >
                                            @php
                                                $icon = match($table->status) {
                                                    'available' => 'heroicon-o-check',
                                                    'occupied' => 'heroicon-o-users',
                                                    'reserved' => 'heroicon-o-clock',
                                                    'maintenance' => 'heroicon-o-wrench',
                                                    default => 'heroicon-o-table-cells',
                                                };

                                                $iconColor = match($table->status) {
                                                    'available' => 'text-success-600 dark:text-success-400',
                                                    'occupied' => 'text-danger-600 dark:text-danger-400',
                                                    'reserved' => 'text-warning-600 dark:text-warning-400',
                                                    'maintenance' => 'text-gray-600 dark:text-gray-400',
                                                    default => 'text-primary-600 dark:text-primary-400',
                                                };
                                            @endphp

                                            <div class="flex flex-col items-center">
                                                <span @class([
                                                    "text-xl font-bold $iconColor",
                                                    'table-number-selected' => $selectedTableId == $table->id,
                                                ])>{{ $table->number }}</span>
                                                <x-filament::icon
                                                    :icon="$icon"
                                                    @class([
                                                        "h-6 w-6 $iconColor table-icon",
                                                    ])
                                                />
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <div class="font-medium">Mesa {{ $table->number }}</div>
                                            <div class="flex items-center justify-center text-sm text-gray-500 dark:text-gray-400">
                                                <x-filament::icon
                                                    icon="heroicon-o-user-group"
                                                    class="h-4 w-4 mr-1"
                                                />
                                                {{ $table->capacity }} personas
                                            </div>
                                        </div>

                                        <!-- Indicador de estado -->
                                        <div @class([
                                            'absolute -top-1 -right-1 w-5 h-5 rounded-full border-2 border-white dark:border-gray-800',
                                            'bg-success-500' => $table->status === 'available',
                                            'bg-danger-500' => $table->status === 'occupied',
                                            'bg-warning-500' => $table->status === 'reserved',
                                            'bg-gray-500' => $table->status === 'maintenance',
                                            $selectedTableId == $table->id ? 'animate-pulse' : '',
                                        ])></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Mensaje si no hay mesas -->
                @if($tables->isEmpty())
                    <div class="text-center py-12">
                        <x-filament::icon
                            icon="heroicon-o-table"
                            class="mx-auto h-12 w-12 text-gray-400"
                        />
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No hay mesas</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            No se encontraron mesas con los filtros aplicados.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Estilos CSS adicionales -->
    <style>
        /* Clases para animaciones y transiciones */
        .state-change {
            transition: all 0.3s ease-in-out;
        }

        .table-selected {
            animation: pulse 2s infinite;
        }

        .table-number-selected {
            animation: pulse-text 2s infinite;
        }

        .animate-pulse-subtle {
            animation: pulse 3s infinite;
        }

        .transform {
            transition: transform 0.3s ease;
        }

        .scale-102:hover {
            transform: scale(1.02);
        }

        .scale-105 {
            transform: scale(1.05);
        }

        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-card {
            transition: all 0.2s ease-in-out;
        }

        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .table-card {
            transition: all 0.2s ease-in-out;
        }

        /* Estilos para scrollbar personalizado */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #cbd5e0;
            border-radius: 10px;
        }

        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a0aec0;
        }

        /* Dark mode para scrollbar */
        .dark .overflow-y-auto::-webkit-scrollbar-track {
            background: #2d3748;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #4a5568;
        }

        .dark .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #718096;
        }

        /* Definición de keyframes */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0.4);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(99, 102, 241, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(99, 102, 241, 0);
            }
        }

        @keyframes pulse-text {
            0% {
                text-shadow: 0 0 0 rgba(99, 102, 241, 0);
            }
            70% {
                text-shadow: 0 0 8px rgba(99, 102, 241, 0.6);
            }
            100% {
                text-shadow: 0 0 0 rgba(99, 102, 241, 0);
            }
        }

        /* Mejoras de accesibilidad */
        [role="button"]:focus {
            outline: 2px solid rgba(99, 102, 241, 0.5);
            outline-offset: 2px;
        }

        /* Animación para categorías seleccionadas */
        @media (prefers-reduced-motion: no-preference) {
            button:focus {
                transition: outline-offset 0.15s ease;
                outline-offset: 3px;
            }
        }

        /* Estados interactivos mejorados */
        .location-container {
            transition: all 0.3s ease-in-out;
        }

        .location-container:hover {
            border-color: rgba(99, 102, 241, 0.4);
        }
    </style>
</x-filament-panels::page>
