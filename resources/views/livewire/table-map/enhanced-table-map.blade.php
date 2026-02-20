<div class="flex flex-col h-screen overflow-hidden" x-data>
    <!-- Cargar nuevos estilos CSS basados en proporción áurea -->
    <link href="{{ asset('css/golden-ratio-variables.css') }}" rel="stylesheet">
    <link href="{{ asset('css/table-card-enhanced.css') }}" rel="stylesheet">
    <link href="{{ asset('css/table-map-layout.css') }}" rel="stylesheet">
    <!-- Header/Navbar -->
    <header class="table-map-header">
        <div class="header-content">
            <div class="header-title">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span>Mapa de Mesas</span>
            </div>
            <div class="header-actions">
                    <a href="{{ route('filament.admin.resources.warehouse-resource.index') }}" class="btn-secondary flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Almacén
                    </a>
                    @if(auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                    <a href="{{ route('pos.index') }}" class="btn-primary flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        POS
                    </a>
                    @endif
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="btn-secondary flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Panel Admin
                    </a>
                </div>
            </div>
    </header>

    <!-- Main Content -->
    <div class="table-map-content">
            <!-- Sidebar -->
            <div class="table-map-sidebar">
                <div class="sidebar-section">
                    <h3 class="sidebar-title">Filtros</h3>

                    <!-- Status Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                        <div class="space-y-2">
                            <button wire:click="$set('statusFilter', null)" class="w-full flex items-center px-3 py-2 text-sm rounded-md {{ !$statusFilter ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                <span class="w-3 h-3 rounded-full bg-gray-400 mr-2"></span>
                                Todos
                            </button>
                            <button wire:click="$set('statusFilter', 'available')" class="w-full flex items-center px-3 py-2 text-sm rounded-md {{ $statusFilter === 'available' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                Disponibles
                            </button>
                            <button wire:click="$set('statusFilter', 'occupied')" class="w-full flex items-center px-3 py-2 text-sm rounded-md {{ $statusFilter === 'occupied' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                Ocupadas
                            </button>
                            <button wire:click="$set('statusFilter', 'reserved')" class="w-full flex items-center px-3 py-2 text-sm rounded-md {{ $statusFilter === 'reserved' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                <span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                                Reservadas
                            </button>
                            <button wire:click="$set('statusFilter', 'maintenance')" class="w-full flex items-center px-3 py-2 text-sm rounded-md {{ $statusFilter === 'maintenance' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                <span class="w-3 h-3 rounded-full bg-gray-500 mr-2"></span>
                                Mantenimiento
                            </button>
                        </div>
                    </div>

                    <!-- Location Filter -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ubicación</label>
                        <div class="space-y-2">
                            <button wire:click="$set('locationFilter', null)" class="w-full flex items-center px-3 py-2 text-sm rounded-md {{ !$locationFilter ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                Todas
                            </button>
                            @foreach($locations as $location)
                                <button wire:click="$set('locationFilter', '{{ $location }}')" class="w-full flex items-center px-3 py-2 text-sm rounded-md {{ $locationFilter === $location ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                                    {{ $location }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Search -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="searchQuery" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white" placeholder="Número o ubicación...">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Area -->
            <div class="tables-main">
                <!-- Estadísticas generales en barra horizontal superior con limpiar filtros -->
                <div class="stats-bar bg-white shadow-sm rounded-lg p-4 mb-4">
                    <div class="flex justify-between items-center mb-3">
                        <div class="text-sm font-medium text-gray-600 pl-2 flex items-center">Resumen:</div>
                        @if($statusFilter || $locationFilter || $searchQuery)
                        <button wire:click="clearFilters" class="text-sm text-blue-600 hover:text-blue-800 flex items-center transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpiar filtros
                        </button>
                        @endif
                    </div>
                    <div class="grid grid-cols-5 gap-4">
                        <!-- Total -->
                        <div class="stat-block text-center">
                            <div class="flex justify-center mb-1">
                                <div class="stats-icon bg-blue-200 rounded-full p-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                </div>
                            </div>
                            <div class="stat-value text-xl font-bold text-blue-700">{{ count($tables) }}</div>
                            <div class="stat-label text-xs text-blue-600">Total</div>
                        </div>

                        <!-- Disponibles -->
                        <div class="stat-block text-center">
                            <div class="flex justify-center mb-1">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                            </div>
                            <div class="stat-value text-xl font-bold text-green-700">{{ count($tables->where('status', 'available')) }}</div>
                            <div class="stat-label text-xs text-green-600">Disponibles</div>
                        </div>

                        <!-- Ocupadas -->
                        <div class="stat-block text-center">
                            <div class="flex justify-center mb-1">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                            </div>
                            <div class="stat-value text-xl font-bold text-red-700">{{ count($tables->where('status', 'occupied')) }}</div>
                            <div class="stat-label text-xs text-red-600">Ocupadas</div>
                        </div>

                        <!-- Reservadas -->
                        <div class="stat-block text-center">
                            <div class="flex justify-center mb-1">
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                            </div>
                            <div class="stat-value text-xl font-bold text-yellow-700">{{ count($tables->where('status', 'reserved')) }}</div>
                            <div class="stat-label text-xs text-yellow-600">Reservadas</div>
                        </div>

                        <!-- Mantenimiento -->
                        <div class="stat-block text-center">
                            <div class="flex justify-center mb-1">
                                <div class="w-3 h-3 rounded-full bg-gray-500"></div>
                            </div>
                            <div class="stat-value text-xl font-bold text-gray-700">{{ count($tables->where('status', 'maintenance')) }}</div>
                            <div class="stat-label text-xs text-gray-600">Mantenimiento</div>
                        </div>
                    </div>
                </div>

                <!-- Filtros rápidos -->
                <div class="quick-filters">
                    <span class="filter-pill {{ !$statusFilter ? 'bg-blue-100 text-blue-800 border border-blue-300' : 'bg-white border border-gray-300 text-gray-700' }}" wire:click="$set('statusFilter', null)">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                        Todas
                    </span>
                    <span class="filter-pill {{ $statusFilter === 'available' ? 'bg-green-100 text-green-800 border border-green-300' : 'bg-white border border-gray-300 text-gray-700' }}" wire:click="$set('statusFilter', 'available')">
                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                        Disponibles
                    </span>
                    <span class="filter-pill {{ $statusFilter === 'occupied' ? 'bg-red-100 text-red-800 border border-red-300' : 'bg-white border border-gray-300 text-gray-700' }}" wire:click="$set('statusFilter', 'occupied')">
                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                        Ocupadas
                    </span>
                    <span class="filter-pill {{ $statusFilter === 'reserved' ? 'bg-yellow-100 text-yellow-800 border border-yellow-300' : 'bg-white border border-gray-300 text-gray-700' }}" wire:click="$set('statusFilter', 'reserved')">
                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                        Reservadas
                    </span>
                </div>

                <!-- Tables Grid -->
                <div class="tables-grid">
                        @forelse($tables as $table)
                            <div wire:key="table-{{ $table->id }}" class="table-card {{ $table->status }}{{ $selectedTable && $selectedTable->id === $table->id ? ' selected' : '' }}">
                                <!-- Enlace al POS para toda la tarjeta - Solo para cashiers, admin y super_admin -->
                                @if(auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                <a href="{{ route('pos.index', ['table_id' => $table->id]) }}" class="table-link"></a>
                                @endif

                                <!-- Cabecera de la tarjeta -->
                                <div class="table-card-header">
                                    <h3 class="table-number">Mesa {{ $table->number }}</h3>
                                    <span class="table-status-badge-inline {{ $table->status }}">
                                        {{ $this->getStatusText($table->status) }}
                                    </span>
                                </div>

                                <!-- Cuerpo de la tarjeta -->
                                <div class="table-card-body" wire:click="selectTable({{ $table->id }})">
                                    <!-- Visualización de la mesa -->
                                    <div class="table-visual {{ $table->shape == 'round' ? 'table-round' : 'table-square' }}">
                                        <!-- Indicadores de capacidad -->
                                        @for ($i = 0; $i < min($table->capacity, 8); $i++)
                                            @php
                                                $angle = $i * (360 / min($table->capacity, 8));
                                                $x = sin(deg2rad($angle)) * 100;
                                                $y = cos(deg2rad($angle)) * 100;
                                            @endphp
                                            <div class="chair" style="left: calc(50% + {{ $x }}%); top: calc(50% - {{ $y }}%);"></div>
                                        @endfor
                                    </div>

                                    <!-- Indicador de capacidad -->
                                    <div class="capacity-indicator">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span>{{ $table->capacity }} personas</span>
                                    </div>

                                    @if ($table->status == 'occupied')
                                    <!-- Tiempo de ocupación (solo si está ocupada) -->
                                    <div class="occupation-time-container">
                                        <span class="occupation-time occupation-time-medium">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            45 min
                                        </span>
                                    </div>
                                    @endif
                                </div>

                                <!-- Pie de la tarjeta -->
                                <div class="table-card-footer">
                                    <div class="text-xs text-gray-600">{{ $table->location }}</div>
                                    <button wire:click.stop="showQrCode({{ $table->id }})" class="text-blue-600 hover:text-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full flex items-center justify-center h-64">
                                <div class="text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No se encontraron mesas</h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Intente con otros filtros o cree una nueva mesa.</p>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        <!-- Panel lateral de detalles de mesa -->
        <div class="flex">
            @if($selectedTable)
            <div class="w-80 bg-white shadow-lg border-l border-gray-200 overflow-y-auto" style="box-shadow: -4px 0 10px rgba(0, 0, 0, 0.05);">
                <div class="p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-primary flex items-center gap-2">
                                <span class="rounded-full bg-blue-100 p-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                    </svg>
                                </span>
                                Mesa {{ $selectedTable->number }}
                            </h3>
                            <button wire:click="$set('selectedTable', null)" type="button" aria-label="Cerrar panel de detalles de mesa" class="rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Estado con indicador visual -->
                                <div class="col-span-2">
                                    <span class="block text-sm font-medium text-gray-500 mb-1">Estado</span>
                                    <span class="table-status-badge-inline {{ $selectedTable->status }} inline-flex items-center px-3 py-1">
                                        <span class="w-2 h-2 rounded-full mr-2 {{ $selectedTable->status == 'available' ? 'bg-green-500' : ($selectedTable->status == 'occupied' ? 'bg-red-500' : ($selectedTable->status == 'reserved' ? 'bg-yellow-500' : 'bg-gray-500')) }}"></span>
                                        {{ $this->getStatusText($selectedTable->status) }}
                                    </span>
                                </div>

                                <!-- Ubicación con icono -->
                                <div>
                                    <span class="block text-sm font-medium text-gray-500 mb-1">Ubicación</span>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span class="text-sm text-gray-700">{{ $selectedTable->location }}</span>
                                    </div>
                                </div>

                                <!-- Capacidad con icono -->
                                <div>
                                    <span class="block text-sm font-medium text-gray-500 mb-1">Capacidad</span>
                                    <div class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                        <span class="text-sm text-gray-700">{{ $selectedTable->capacity }} personas</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                                </svg>
                                Cambiar Estado
                            </h4>
                            <div class="grid grid-cols-2 gap-3">
                                <button wire:click="changeTableStatus({{ $selectedTable->id }}, 'available')" class="transition-all px-3 py-2 text-sm rounded-md border border-green-200 bg-green-50 hover:bg-green-500 text-green-700 hover:text-white flex items-center justify-center">
                                    <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                    Disponible
                                </button>
                                <button wire:click="changeTableStatus({{ $selectedTable->id }}, 'occupied')" class="transition-all px-3 py-2 text-sm rounded-md border border-red-200 bg-red-50 hover:bg-red-500 text-red-700 hover:text-white flex items-center justify-center">
                                    <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                    Ocupada
                                </button>
                                <button wire:click="changeTableStatus({{ $selectedTable->id }}, 'reserved')" class="transition-all px-3 py-2 text-sm rounded-md border border-yellow-200 bg-yellow-50 hover:bg-yellow-500 text-yellow-700 hover:text-white flex items-center justify-center">
                                    <span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>
                                    Reservada
                                </button>
                                <button wire:click="changeTableStatus({{ $selectedTable->id }}, 'maintenance')" class="transition-all px-3 py-2 text-sm rounded-md border border-gray-200 bg-gray-50 hover:bg-gray-500 text-gray-700 hover:text-white flex items-center justify-center">
                                    <span class="w-3 h-3 rounded-full bg-gray-500 mr-2"></span>
                                    Mantenimiento
                                </button>
                            </div>
                        </div>

                        <!-- Acciones de la mesa -->
                        <div class="mb-6">
                            <h4 class="text-base font-semibold text-gray-800 mb-3 flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                Acciones
                            </h4>
                            <div class="space-y-3">
                                @if(auth()->user()->hasRole(['cashier', 'admin', 'super_admin']))
                                <a href="{{ route('pos.index', ['table_id' => $selectedTable->id]) }}" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Ir al POS para esta mesa
                                </a>
                                @endif

                                <button wire:click="showQrCode({{ $selectedTable->id }})" class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 shadow-sm transition-all">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    Ver código QR de la mesa
                                </button>
                            </div>
                        </div>

                        <!-- Visualización gráfica de la mesa -->
                        <div class="mb-6 bg-gray-50 rounded-lg p-5 flex flex-col items-center border border-gray-100">
                            <div class="table-visual {{ $selectedTable->shape == 'round' ? 'table-round' : 'table-square' }}" style="width: 100px; height: 100px; margin-bottom: 1rem;">
                                @for ($i = 0; $i < min($selectedTable->capacity, 8); $i++)
                                    @php
                                        $angle = $i * (360 / min($selectedTable->capacity, 8));
                                        $x = sin(deg2rad($angle)) * 100;
                                        $y = cos(deg2rad($angle)) * 100;
                                    @endphp
                                    <div class="chair" style="left: calc(50% + {{ $x }}%); top: calc(50% - {{ $y }}%);"></div>
                                @endfor
                            </div>
                            <p class="text-sm text-gray-500 text-center">Vista previa de la mesa</p>
                        </div>

                        @if($tableDetails && $tableDetails['activeOrder'])
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-4">
                                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Orden Activa</h4>
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-md p-3">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Orden #:</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $tableDetails['activeOrder']->id }}</span>
                                    </div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Fecha:</span>
                                        <span class="text-sm text-gray-600 dark:text-gray-400">{{ $tableDetails['activeOrder']->order_datetime->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Total:</span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white">S/ {{ number_format($tableDetails['activeOrder']->total, 2) }}</span>
                                    </div>
                                    <div class="mt-3">
                                        <h5 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Productos:</h5>
                                        <ul class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                            @foreach($tableDetails['activeOrder']->orderDetails as $detail)
                                                <li class="flex justify-between">
                                                    <span>{{ $detail->quantity }}x {{ $detail->product->name }}</span>
                                                    <span>S/ {{ number_format($detail->subtotal, 2) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- QR Code Modal con diseño mejorado -->
    @if($showQrModal)
    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-60 backdrop-blur-sm">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300 ease-in-out scale-100">
            <!-- Cabecera del modal -->
            <div class="relative p-6 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-100 rounded-full p-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Código QR - Mesa {{ $selectedTable->number }}</h3>
                </div>
                <button wire:click="closeQrModal" class="absolute top-4 right-4 rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-all">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Contenido del modal -->
            <div class="p-6">
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-8 rounded-lg flex items-center justify-center mb-4">
                    <!-- QR Code container -->
                    @if($currentQrCode)
                    <div class="bg-white p-4 rounded-lg shadow-inner">
                        <img src="{{ $currentQrCode }}" alt="QR Code" class="w-64 h-64">
                    </div>
                    @else
                    <div class="bg-white p-8 rounded-lg shadow-inner flex flex-col items-center justify-center">
                        <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <p class="mt-2 text-sm font-medium text-gray-800">No hay código QR disponible</p>
                    </div>
                    @endif
                </div>

                <div class="text-center space-y-4">
                    <div class="flex items-center justify-center gap-2 text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm">Escanea este código para acceder al menú digital</p>
                    </div>

                    @if($currentQrCode)
                    <div class="flex justify-center gap-3 pt-3">
                        <button wire:click="closeQrModal" class="px-5 py-2.5 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 shadow-sm transition-all">
                            Cerrar
                        </button>
                        <button onclick="window.print()" class="px-5 py-2.5 text-sm font-medium rounded-md bg-blue-600 hover:bg-blue-700 text-white shadow-sm transition-all flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                            </svg>
                            Imprimir QR
                        </button>
                    </div>
                    @else
                    <div class="flex justify-center pt-3">
                        <button wire:click="closeQrModal" class="px-5 py-2.5 text-sm font-medium rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 shadow-sm transition-all">
                            Cerrar
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
