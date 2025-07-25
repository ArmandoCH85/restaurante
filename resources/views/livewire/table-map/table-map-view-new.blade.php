<div>
    <!-- Cargar el CSS mejorado  -->
    <link rel="stylesheet" href="{{ asset('css/table-map-improved.css') }}?v={{ time() }}">

    <div class="table-map-container">
        <!-- Header -->
        <div class="table-map-header">
            <h1 class="header-title">
                Mapa de Mesas y Delivery
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2 inline-block text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                </svg>
            </h1>
            <div class="header-actions">
                <a href="{{ url('/admin') }}" class="header-button bg-gray-700 hover:bg-gray-800 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Escritorio
                </a>
                <a href="{{ route('pos.index', ['preserve_cart' => $preserve_cart]) }}" class="header-button bg-red-600 hover:bg-red-700 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    POS
                </a>
                <a href="{{ route('pos.index', ['serviceType' => 'delivery']) }}" class="header-button bg-green-600 hover:bg-green-700 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                    </svg>
                    Nuevo Delivery
                </a>
                <a href="{{ url('/admin/reservations/create') }}" class="header-button bg-yellow-600 hover:bg-yellow-700 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Nueva Reserva
                </a>
            </div>
        </div>

        <!-- Contenido principal -->
        <div class="table-map-content">
            <!-- Sidebar -->
            <div class="table-map-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Filtros</h3>

                    <!-- Status Filter -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Estado</label>
                        <select wire:model.live="statusFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todos</option>
                            <option value="available">Disponible</option>
                            <option value="occupied">Ocupada</option>
                            <option value="reserved">Reservada</option>
                            <option value="maintenance">Mantenimiento</option>
                        </select>
                    </div>

                    <!-- Floor Filter -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Piso</label>
                        <select wire:model.live="floorFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todos</option>
                            @foreach($this->getFloorOptions() as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Location Filter -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Ubicación</label>
                        <select wire:model.live="locationFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todas</option>
                            @foreach($this->getLocationOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Capacity Filter -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Capacidad</label>
                        <select wire:model.live="capacityFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                            <option value="">Todas</option>
                            <option value="1-2">1-2 personas</option>
                            <option value="3-4">3-4 personas</option>
                            <option value="5-8">5-8 personas</option>
                            <option value="9+">9+ personas</option>
                        </select>
                    </div>

                    <!-- Search -->
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Buscar</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Buscar mesa..." class="pl-10 w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                        </div>
                    </div>

                    <!-- Reservations Filter -->
                    <div class="mb-3">
                        <div class="flex items-center">
                            <input type="checkbox" id="show-reservations" wire:model.live="showTodayReservations" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <label for="show-reservations" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Mostrar solo reservas de hoy</label>
                        </div>
                    </div>

                    <!-- Delivery Orders Filter -->
                    <div class="mb-3">
                        <div class="flex items-center">
                            <input type="checkbox" id="show-delivery" wire:model.live="showDeliveryOrders" class="rounded border-gray-300 text-red-600 shadow-sm focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50" checked>
                            <label for="show-delivery" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                </svg>
                                Mostrar pedidos de delivery
                            </label>
                        </div>
                    </div>

                    <!-- Reset Filters -->
                    <button wire:click="resetFilters" class="w-full mt-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-md text-sm font-medium transition-colors flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Limpiar filtros
                    </button>
                </div>
            </div>

            <!-- Área principal -->
            <div class="tables-main">
                <!-- Estadísticas horizontales -->
                @php $stats = $this->getTableStats(); @endphp
                <div class="stats-container bg-white shadow-sm rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center mb-2">
                        <div class="text-sm font-medium text-gray-600 pl-2 flex items-center">Estadísticas:</div>
                        @if($statusFilter || $locationFilter || $searchQuery)
                        <button wire:click="clearFilters" class="text-sm text-blue-600 hover:text-blue-800 flex items-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpiar filtros
                        </button>
                        @endif
                    </div>
                    <div class="grid grid-cols-4 gap-4">
                        <!-- Total Mesas -->
                        <div class="text-center">
                            <div class="flex justify-center mb-1">
                                <div class="bg-blue-200 rounded-full p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                            </div>
                            <div class="text-3xl font-bold text-blue-700">{{ $stats['total'] }}</div>
                            <div class="text-xs text-blue-600">Total Mesas</div>
                        </div>

                        <!-- Disponibles -->
                        <div class="text-center">
                            <div class="flex justify-center mb-1">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <div class="text-3xl font-bold text-green-700">{{ $stats['available'] }}</div>
                            <div class="text-xs text-green-600">Disponibles</div>
                        </div>

                        <!-- Ocupadas -->
                        <div class="text-center">
                            <div class="flex justify-center mb-1">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            </div>
                            <div class="text-3xl font-bold text-red-700">{{ $stats['occupied'] }}</div>
                            <div class="text-xs text-red-600">Ocupadas</div>
                        </div>

                        <!-- Reservadas -->
                        <div class="text-center">
                            <div class="flex justify-center mb-1">
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            </div>
                            <div class="text-3xl font-bold text-yellow-700">{{ $stats['reserved'] }}</div>
                            <div class="text-xs text-yellow-600">Reservadas</div>
                        </div>

                        <!-- Mantenimiento -->
                        <div class="text-center">
                            <div class="flex justify-center mb-1">
                                <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                            </div>
                            <div class="text-3xl font-bold text-gray-700">{{ $stats['maintenance'] }}</div>
                            <div class="text-xs text-gray-600">Mantenimiento</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros rápidos -->
                <div class="quick-filters">
                    @foreach($floors as $floor)
                        <button
                            wire:click="$set('floorFilter', '{{ $floor->id }}')"
                            class="filter-pill {{ $floorFilter == $floor->id ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ $floor->name }}
                        </button>
                    @endforeach
                    @if($floorFilter)
                        <button
                            wire:click="$set('floorFilter', '')"
                            class="filter-pill bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Todos los pisos
                        </button>
                    @endif
                </div>

                <!-- Hora actual -->
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-4 flex justify-end">
                    <span class="inline-flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Actualizado: {{ now()->format('H:i') }} - {{ now()->format('d/m/Y') }}
                    </span>
                </div>

                <!-- Mesas agrupadas por piso y ubicación -->
                @if($tables->isEmpty() && $deliveryOrders->isEmpty())
                    <div class="flex flex-col items-center justify-center py-12 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">No se encontraron mesas ni pedidos</h3>
                        <p class="text-gray-500 dark:text-gray-400 max-w-md">No hay mesas ni pedidos de delivery que coincidan con los filtros seleccionados. Intenta cambiar los filtros.</p>
                        <button wire:click="resetFilters" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                            Limpiar filtros
                        </button>
                    </div>
                @else
                    @php
                        $groupedTables = $this->getGroupedTables();
                    @endphp

                    <!-- Mesas Físicas -->
                    @foreach($groupedTables as $floorId => $floorTables)
                        <div class="floor-section">
                            <div class="floor-header">
                                <h2 class="floor-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                    {{ $this->getFloorName($floorId) }}
                                </h2>
                                <div class="flex items-center space-x-2">
                                    @php
                                        $floorStats = [
                                            'available' => $floorTables->flatten()->where('status', 'available')->count(),
                                            'occupied' => $floorTables->flatten()->where('status', 'occupied')->count(),
                                            'reserved' => $floorTables->flatten()->where('status', 'reserved')->count(),
                                            'maintenance' => $floorTables->flatten()->where('status', 'maintenance')->count(),
                                        ];
                                    @endphp
                                    <span class="table-status-badge-inline available">
                                        {{ $floorStats['available'] }} Disponibles
                                    </span>
                                    <span class="table-status-badge-inline occupied">
                                        {{ $floorStats['occupied'] }} Ocupadas
                                    </span>
                                    <span class="table-status-badge-inline reserved">
                                        {{ $floorStats['reserved'] }} Reservadas
                                    </span>
                                </div>
                            </div>

                            @foreach($floorTables as $location => $locationTables)
                                <div class="location-section">
                                    <div class="location-header">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $this->getLocationOptions()[$location] ?? 'Sin ubicación' }}
                                    </div>

                                    <div class="tables-grid">
                                        @foreach($locationTables as $table)
                                            @php
                                                $statusInfo = $this->getTableStatus($table->status ?? 'available');
                                            @endphp
                                            <div class="table-container">
                                                <div class="table-card {{ $table->status ?? 'available' }}">
                                                    <div class="table-card-header">
                                                        <h3 class="table-number">Mesa {{ $table->number }}</h3>
                                                        <span class="table-status-badge-inline {{ $table->status ?? 'available' }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="status-icon">
                                                                <circle cx="12" cy="12" r="4" fill="currentColor" />
                                                            </svg>
                                                            {{ $statusInfo['text'] }}
                                                        </span>
                                                    </div>

                                                    <div class="table-card-body">
                                                        <a href="javascript:void(0)" class="table-link w-full h-full" wire:click.prevent="goToPos({{ $table->id }})">
                                                            @php
                                                                $tableShape = 'table-square';
                                                                if ($table->shape == 'round') {
                                                                    $tableShape = 'table-round';
                                                                } elseif ($table->capacity > 4) {
                                                                    $tableShape = $table->shape == 'rectangular' ? 'table-rectangular' : 'table-oval';
                                                                }
                                                            @endphp
                                                            <div class="table-visual {{ $tableShape }} {{ $table->status ?? 'available' }}">
                                                                @if($tableShape == 'table-square')
                                                                    <div class="chair-left"></div>
                                                                    <div class="chair-right"></div>
                                                                @endif

                                                                @if($tableShape == 'table-round' && ($table->capacity ?? 0) >= 6)
                                                                    <div class="chair-top-left"></div>
                                                                    <div class="chair-top-right"></div>
                                                                    <div class="chair-left"></div>
                                                                    <div class="chair-right"></div>
                                                                    <div class="chair-bottom-left"></div>
                                                                    <div class="chair-bottom-right"></div>
                                                                @elseif($tableShape == 'table-round')
                                                                    <div class="chair-left"></div>
                                                                    <div class="chair-right"></div>
                                                                @endif

                                                                @if($tableShape == 'table-rectangular' || $tableShape == 'table-oval')
                                                                    <div class="chair-left-top"></div>
                                                                    <div class="chair-left-bottom"></div>
                                                                    <div class="chair-right-top"></div>
                                                                    <div class="chair-right-bottom"></div>
                                                                @endif

                                                                <span class="table-number">{{ $table->number }}</span>
                                                                <div class="table-capacity-indicator">{{ $table->capacity ?? '?' }}</div>

                                                                @if(($table->status ?? '') === 'occupied')
                                                                    <div class="table-order-info">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                        </svg>
                                                                        Orden
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </a>

                                                        <p class="table-capacity">Capacidad: {{ $table->capacity ?? '?' }} personas</p>

                                                        @if(($table->status ?? '') === 'occupied' && ($occupationTime = $this->getOccupationTime($table)))
                                                            <div class="occupation-time-container">
                                                                <span class="occupation-time {{ $this->getOccupationTimeClass($table) }}">
                                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="time-icon">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                    </svg>
                                                                    {{ $occupationTime }}
                                                                </span>
                                                            </div>
                                                        @endif

                                                        @if($table->activeReservations && $table->activeReservations->count() > 0)
                                                            <div class="absolute top-0 left-0 transform -translate-x-1/4 -translate-y-1/4 w-4 h-4 bg-yellow-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold border border-white dark:border-gray-800 shadow-sm">
                                                                {{ $table->activeReservations->count() }}
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="table-card-footer">
                                                        <div class="status-select-container">
                                                            <select class="status-select" wire:change="changeTableStatus({{ $table->id }}, $event.target.value)">
                                                                <option value="" disabled selected>Cambiar estado</option>
                                                                @foreach($this->getStatusOptions() as $value => $label)
                                                                    <option value="{{ $value }}" {{ ($table->status ?? '') === $value ? 'disabled' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        @if(($table->status ?? '') === 'available')
                                                            <a href="{{ url('/admin/reservations/create?table_id=' . $table->id) }}" class="reserve-button">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="reserve-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                                Reservar
                                                            </a>
                                                        @else
                                                            <button class="reserve-button disabled" disabled>
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="reserve-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                </svg>
                                                                Reservar
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach

                    <!-- Pedidos de Delivery -->
                    @if($showDeliveryOrders)
                        <div class="delivery-section">
                            <div class="delivery-header">
                                <h2 class="delivery-title">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                    </svg>
                                    Pedidos de Delivery ({{ $deliveryOrders->count() }})
                                </h2>

                                <div class="flex items-center space-x-2">
                                    <span class="delivery-status-badge pending">
                                        {{ $deliveryOrders->where('status', 'pending')->count() }} Pendientes
                                    </span>
                                    <span class="delivery-status-badge assigned">
                                        {{ $deliveryOrders->where('status', 'assigned')->count() }} Asignados
                                    </span>
                                    <span class="delivery-status-badge in-transit">
                                        {{ $deliveryOrders->where('status', 'in_transit')->count() }} En tránsito
                                    </span>
                                </div>
                            </div>

                            <div class="location-section">
                                <div class="location-header">
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Pedidos Activos
                                    </div>
                                    <a href="{{ route('pos.index', ['serviceType' => 'delivery']) }}" class="delivery-action-button">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="delivery-action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Nuevo Pedido Delivery
                                    </a>
                                </div>

                                <div class="delivery-grid">
                                    @if($deliveryOrders->isEmpty())
                                        <div class="p-8 text-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                            </svg>
                                            <h3 class="text-lg font-medium text-gray-700 dark:text-gray-300 mb-2">No hay pedidos de delivery activos</h3>
                                            <p class="text-gray-500 dark:text-gray-400 mb-4">Crea un nuevo pedido de delivery para que aparezca aquí.</p>
                                            <a href="{{ route('pos.index', ['serviceType' => 'delivery']) }}" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors duration-200">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                                </svg>
                                                Crear Pedido de Delivery
                                            </a>
                                        </div>
                                    @else
                                        @foreach($deliveryOrders as $delivery)
                                        @php
                                            $statusInfo = $this->getDeliveryStatusInfo($delivery->status);
                                        @endphp
                                        <div class="table-container" data-delivery-id="{{ $delivery->id }}">
                                            <div class="delivery-card {{ $delivery->status }}" data-delivery-id="{{ $delivery->id }}">
                                                <!-- Cabecera con número y estado -->
                                                <div class="delivery-header-container">
                                                    <div class="delivery-header-content">
                                                        <div class="delivery-header-main">
                                                            <h3 class="flex items-center">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
                                                                </svg>
                                                                Pedido #{{ $delivery->order_id }}
                                                            </h3>
                                                            <span class="delivery-status-badge {{ $delivery->status }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="status-icon">
                                                                    <circle cx="12" cy="12" r="4" fill="currentColor" />
                                                                </svg>
                                                                {{ $statusInfo['text'] }}
                                                            </span>
                                                        </div>

                                                        <!-- SEMÁFORO DE DELIVERY -->
                                                        <div class="delivery-traffic-light-container">
                                                            <x-delivery-traffic-light
                                                                :status="$delivery->status"
                                                                size="sm"
                                                                :animate="in_array($delivery->status, ['pending', 'in_transit'])"
                                                                :showLabel="false"
                                                                class="delivery-semaphore"
                                                            />
                                                        </div>
                                                    </div>
                                                    <p class="delivery-time">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                        </svg>
                                                        {{ $delivery->order->customer->name ?? 'Sin cliente' }}
                                                    </p>
                                                </div>

                                                <!-- Contenido del pedido -->
                                                <div class="delivery-content">
                                                    <div class="delivery-info">
                                                        <div class="delivery-info-item">
                                                            <span class="delivery-info-label">Dirección:</span>
                                                            <span class="delivery-info-value">{{ $delivery->delivery_address }}</span>
                                                        </div>

                                                        @if($delivery->deliveryPerson)
                                                            <div class="delivery-info-item">
                                                                <span class="delivery-info-label">Repartidor:</span>
                                                                <span class="delivery-info-value">{{ $delivery->deliveryPerson->full_name }}</span>
                                                            </div>
                                                        @endif

                                                        @if($delivery->estimated_delivery_time)
                                                            <div class="delivery-info-item">
                                                                <span class="delivery-info-label">Entrega est.:</span>
                                                                <span class="delivery-info-value">{{ $delivery->estimated_delivery_time->format('H:i') }}</span>
                                                            </div>
                                                        @endif

                                                        <div class="delivery-info-item">
                                                            <span class="delivery-info-label">Creado:</span>
                                                            <span class="delivery-info-value">{{ $delivery->created_at->diffForHumans() }}</span>
                                                        </div>
                                                    </div>

                                                    <div class="mt-2">
                                                        <a href="{{ route('pos.index', ['order_id' => $delivery->order_id, 'preserve_cart' => 'true']) }}" class="delivery-action-button">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="delivery-action-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                            </svg>
                                                            Ver Detalles
                                                        </a>
                                                    </div>
                                                </div>

                                                <!-- Acciones de delivery -->
                                                <div class="delivery-actions">
                                                    <select class="delivery-status-select"
                                                            data-delivery-id="{{ $delivery->id }}"
                                                            wire:change="updateDeliveryStatus({{ $delivery->id }}, $event.target.value)"
                                                            onchange="handleDeliveryStatusChange(this, {{ $delivery->id }})">
                                                        <option value="" disabled selected>Cambiar estado</option>
                                                        @if($delivery->status === 'pending')
                                                            <option value="assigned">Asignar repartidor</option>
                                                            <option value="cancelled">Cancelar pedido</option>
                                                        @elseif($delivery->status === 'assigned')
                                                            <option value="in_transit">Marcar en tránsito</option>
                                                            <option value="cancelled">Cancelar pedido</option>
                                                        @elseif($delivery->status === 'in_transit')
                                                            @if(auth()->user()->hasRole('admin') || auth()->user()->hasRole('super_admin') || auth()->user()->hasRole('cashier'))
                                                                <option value="delivered">Marcar como entregado</option>
                                                            @endif
                                                            <option value="cancelled">Cancelar pedido</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <!-- Notificación -->
    <div id="notification" class="notification notification-success">
        <div id="notification-message"></div>
    </div>

    <!-- Modal para asignar repartidor -->
    <div x-data="{
        open: false,
        deliveryOrderId: null,
        init() {
            // Escuchar el evento de Livewire 3
            Livewire.on('openAssignDeliveryPersonModal', (deliveryOrderId) => {
                console.log('Evento openAssignDeliveryPersonModal recibido:', deliveryOrderId);
                this.deliveryOrderId = deliveryOrderId;
                this.open = true;
                // Resetear el dropdown cuando se abre el modal
                resetDeliveryDropdown(deliveryOrderId);
            });
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Asignar Repartidor
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Seleccione un repartidor para asignar a este pedido.
                                </p>
                            </div>

                            <div class="mt-4">
                                <label for="deliveryPerson" class="block text-sm font-medium text-gray-700">Repartidor</label>
                                <select id="deliveryPerson" x-ref="deliveryPerson" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm rounded-md">
                                    <option value="">Seleccione un repartidor</option>
                                    @foreach(\App\Models\Employee::where('position', 'Delivery')->get() as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="console.log('Asignando repartidor:', deliveryOrderId, $refs.deliveryPerson.value); $wire.assignDeliveryPerson(deliveryOrderId, $refs.deliveryPerson.value); resetDeliveryDropdown(deliveryOrderId); open = false;" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Asignar
                    </button>
                    <button type="button" @click="resetDeliveryDropdown(deliveryOrderId); open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cancelar pedido -->
    <div x-data="{
        open: false,
        deliveryOrderId: null,
        reason: '',
        init() {
            // Escuchar el evento de Livewire 3
            Livewire.on('openCancelDeliveryModal', (deliveryOrderId) => {
                console.log('Evento openCancelDeliveryModal recibido:', deliveryOrderId);
                this.deliveryOrderId = deliveryOrderId;
                this.reason = '';
                this.open = true;
                // Resetear el dropdown cuando se abre el modal
                resetDeliveryDropdown(deliveryOrderId);
            });
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="open" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Cancelar Pedido
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    ¿Está seguro que desea cancelar este pedido? Esta acción no se puede deshacer.
                                </p>
                            </div>

                            <div class="mt-4">
                                <label for="cancelReason" class="block text-sm font-medium text-gray-700">Motivo de cancelación</label>
                                <textarea id="cancelReason" x-model="reason" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" @click="console.log('Cancelando pedido:', deliveryOrderId, reason); $wire.cancelDelivery(deliveryOrderId, reason); resetDeliveryDropdown(deliveryOrderId); open = false;" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancelar Pedido
                    </button>
                    <button type="button" @click="resetDeliveryDropdown(deliveryOrderId); open = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Volver
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.getElementById('notification');
            const notificationMessage = document.getElementById('notification-message');

            // Asegurarse de que Livewire esté inicializado
            document.addEventListener('livewire:initialized', function() {
                console.log('Livewire inicializado, listo para escuchar eventos');
            });

            // Resetear dropdowns después de que Livewire actualice el componente
            document.addEventListener('livewire:updated', function() {
                console.log('Livewire actualizado, reseteando dropdowns');
                // Usar un pequeño delay para asegurar que el DOM esté completamente actualizado
                setTimeout(() => {
                    resetAllDeliveryDropdowns();
                }, 50);
            });

            // Observer para detectar cambios en el DOM y resetear dropdowns automáticamente
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'childList') {
                        // Verificar si se agregaron nuevos dropdowns de delivery
                        const addedNodes = Array.from(mutation.addedNodes);
                        addedNodes.forEach(node => {
                            if (node.nodeType === 1) { // Element node
                                const dropdowns = node.querySelectorAll ? node.querySelectorAll('.delivery-status-select') : [];
                                dropdowns.forEach(dropdown => {
                                    if (dropdown.value !== '') {
                                        dropdown.value = '';
                                        console.log('Dropdown auto-reseteado por observer:', dropdown.getAttribute('data-delivery-id'));
                                    }
                                });
                            }
                        });
                    }
                });
            });

            // Iniciar el observer
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });

            // Manejar notificaciones de cambio de estado de mesa
            Livewire.on('table-status-changed', (data) => {
                console.log('Evento table-status-changed recibido:', data);
                notificationMessage.textContent = data.message;
                notification.className = 'notification notification-' + data.type;
                notification.classList.add('show');

                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            });

            // Manejar notificaciones generales
            Livewire.on('notification', (data) => {
                console.log('Evento notification recibido:', data);
                notificationMessage.textContent = data.message;
                notification.className = 'notification notification-' + data.type;
                notification.classList.add('show');

                // Si es una notificación de error relacionada con delivery, resetear todos los dropdowns
                if (data.type === 'error' && data.message.toLowerCase().includes('pedido')) {
                    resetAllDeliveryDropdowns();
                }

                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            });

            // SISTEMA DE SEMÁFORO: Escuchar cambios de estado de delivery
            Livewire.on('delivery-status-changed', (data) => {
                console.log('Estado de delivery cambiado:', data);
                // Actualizar inmediatamente el semáforo correspondiente
                updateDeliveryTrafficLight(data.deliveryId, data.newStatus);
                // Mostrar notificación con color del semáforo
                showTrafficLightNotification(data.newStatus, data.message);
                // RESETEAR EL DROPDOWN después del cambio exitoso
                resetDeliveryDropdown(data.deliveryId);
            });

            // Escuchar evento específico para resetear dropdown
            Livewire.on('reset-delivery-dropdown', (data) => {
                console.log('Evento reset-delivery-dropdown recibido:', data);
                resetDeliveryDropdown(data.deliveryId);
            });

            // SISTEMA DE ACTUALIZACIONES EN TIEMPO REAL
            // Actualizar los indicadores de tiempo y semáforos cada 30 segundos
            // TEMPORALMENTE DESHABILITADO: Auto-actualización cada 30 segundos
            /*
            setInterval(() => {
                try {
                    // Comprobar si estamos usando Livewire 2 o Livewire 3
                    if (typeof Livewire !== 'undefined') {
                        if (typeof Livewire.emit === 'function') {
                            // Livewire 2
                            Livewire.emit('refresh');
                        } else if (typeof Livewire.dispatch === 'function') {
                            // Livewire 3
                            Livewire.dispatch('refresh');
                        }
                    }

                    // Actualizar animaciones del semáforo
                    updateTrafficLightAnimations();
                } catch (e) {
                    console.error('Error al actualizar los indicadores de tiempo:', e);
                }
            }, 30000); // 30 segundos para actualizaciones más frecuentes
            */

            // Función para actualizar animaciones del semáforo
            function updateTrafficLightAnimations() {
                const trafficLights = document.querySelectorAll('.delivery-traffic-light-component');
                trafficLights.forEach(light => {
                    if (light) {
                        const activeLight = light.querySelector('.traffic-light-light.active');
                        if (activeLight && activeLight.classList) {
                            // Añadir efecto de pulso temporal para indicar actualización
                            activeLight.style.animation = 'none';
                            setTimeout(() => {
                                if (activeLight.classList.contains('red') || activeLight.classList.contains('yellow')) {
                                    activeLight.style.animation = activeLight.classList.contains('red') ?
                                        'pulse-red 2s infinite' : 'pulse-yellow 1.5s infinite';
                                }
                            }, 100);
                        }
                    }
                });
            }

            // Función para manejar el cambio de estado inmediatamente
            window.handleDeliveryStatusChange = function(selectElement, deliveryId) {
                console.log('handleDeliveryStatusChange llamado:', deliveryId, selectElement.value);

                // Resetear inmediatamente el dropdown después de un pequeño delay
                setTimeout(() => {
                    selectElement.value = '';
                    console.log('Dropdown reseteado inmediatamente para delivery ID:', deliveryId);
                }, 100);
            }

            // Función para resetear el dropdown de delivery
            window.resetDeliveryDropdown = function(deliveryId) {
                const deliveryCard = document.querySelector(`[data-delivery-id="${deliveryId}"]`);
                if (!deliveryCard) return;

                const dropdown = deliveryCard.querySelector('.delivery-status-select');
                if (dropdown) {
                    dropdown.value = '';
                    console.log('Dropdown reseteado para delivery ID:', deliveryId);
                }
            }

            // Función para resetear todos los dropdowns de delivery (en caso de error)
            function resetAllDeliveryDropdowns() {
                const allDropdowns = document.querySelectorAll('.delivery-status-select');
                allDropdowns.forEach(dropdown => {
                    if (dropdown.value !== '') {
                        dropdown.value = '';
                        console.log('Dropdown reseteado:', dropdown.getAttribute('data-delivery-id'));
                    }
                });
                console.log('Todos los dropdowns de delivery verificados y reseteados si era necesario');
            }

            // Función para actualizar un semáforo específico
            function updateDeliveryTrafficLight(deliveryId, newStatus) {
                const deliveryCard = document.querySelector(`[data-delivery-id="${deliveryId}"]`);
                if (!deliveryCard) return;

                const trafficLight = deliveryCard.querySelector('.delivery-traffic-light-component');
                if (!trafficLight) return;

                // Remover estado activo de todas las luces
                const lights = trafficLight.querySelectorAll('.traffic-light-light');
                lights.forEach(light => {
                    if (light && light.classList) {
                        light.classList.remove('active');
                        light.style.animation = 'none';
                    }
                });

                // Activar la luz correspondiente al nuevo estado
                const statusMap = {
                    'pending': 'red',
                    'assigned': 'orange',
                    'in_transit': 'yellow',
                    'delivered': 'green',
                    'cancelled': 'gray'
                };

                const lightColor = statusMap[newStatus];
                if (lightColor) {
                    const targetLight = trafficLight.querySelector(`.traffic-light-light.${lightColor}`);
                    if (targetLight && targetLight.classList) {
                        targetLight.classList.add('active');

                        // Añadir animación para estados activos
                        if (newStatus === 'pending') {
                            targetLight.style.animation = 'pulse-red 2s infinite';
                        } else if (newStatus === 'in_transit') {
                            targetLight.style.animation = 'pulse-yellow 1.5s infinite';
                        }

                        // Efecto de transición suave
                        targetLight.style.transform = 'scale(1.2)';
                        setTimeout(() => {
                            targetLight.style.transform = 'scale(1.1)';
                        }, 200);
                    }
                }
            }

            // Función para mostrar notificación con color del semáforo
            function showTrafficLightNotification(status, message) {
                const statusColors = {
                    'pending': '#dc2626',      // Rojo
                    'assigned': '#ea580c',     // Naranja
                    'in_transit': '#eab308',   // Amarillo
                    'delivered': '#16a34a',    // Verde
                    'cancelled': '#6b7280'     // Gris
                };

                const color = statusColors[status] || '#6b7280';

                notificationMessage.textContent = message;
                notification.className = 'notification notification-success';
                notification.style.borderLeftColor = color;
                notification.style.backgroundColor = color + '15'; // Color con transparencia
                notification.classList.add('show');

                setTimeout(() => {
                    notification.classList.remove('show');
                    notification.style.borderLeftColor = '';
                    notification.style.backgroundColor = '';
                }, 4000);
            }
        });
    </script>

    <!-- Notificación -->
    <div id="notification" class="notification">
        <span id="notification-message"></span>
    </div>

    <!-- Botón flotante para nuevo pedido de delivery -->
    <a href="{{ route('pos.index', ['serviceType' => 'delivery']) }}" class="new-delivery-button">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0" />
        </svg>
    </a>
</div>
