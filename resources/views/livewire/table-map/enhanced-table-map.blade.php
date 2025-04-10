<div class="flex flex-col h-screen overflow-hidden">
    <!-- Header/Navbar -->
    <header class="bg-white dark:bg-gray-800 shadow-sm z-10">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <span class="text-2xl font-bold text-blue-700 dark:text-blue-400">Mapa de Mesas Mejorado</span>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('filament.admin.resources.warehouse-resource.index') }}" class="px-3 py-1 rounded-md text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                        </svg>
                        Almacén
                    </a>
                    <a href="{{ route('pos.index') }}" class="px-3 py-1 rounded-md text-sm font-medium bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        POS
                    </a>
                    <a href="{{ route('filament.admin.pages.dashboard') }}" class="px-3 py-1 rounded-md text-sm font-medium bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        Panel Admin
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 overflow-hidden">
        <div class="flex h-full">
            <!-- Sidebar -->
            <div class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
                <div class="p-4">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filtros</h3>

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
            <div class="flex-1 flex overflow-hidden">
                <!-- Tables Grid -->
                <div class="flex-1 p-4 overflow-y-auto">
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                        @forelse($tables as $table)
                            <div wire:key="table-{{ $table->id }}" wire:click="selectTable({{ $table->id }})" class="relative bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden cursor-pointer hover:shadow-lg transition-shadow duration-200 border-2 {{ $selectedTable && $selectedTable->id === $table->id ? 'border-blue-500' : 'border-transparent' }}">
                                <div class="absolute top-0 right-0 m-2">
                                    <span class="inline-flex items-center justify-center w-3 h-3 rounded-full {{ $this->getStatusColor($table->status) }}"></span>
                                </div>
                                <div class="p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Mesa {{ $table->number }}</h3>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        <p>{{ $table->location }}</p>
                                        <p>Capacidad: {{ $table->capacity }} personas</p>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $this->getStatusColor($table->status) }} text-white">
                                            {{ $this->getStatusText($table->status) }}
                                        </span>
                                        <button wire:click.stop="showQrCode({{ $table->id }})" class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                            </svg>
                                        </button>
                                    </div>
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

                <!-- Table Details Sidebar -->
                @if($selectedTable)
                <div class="w-80 bg-white dark:bg-gray-800 border-l border-gray-200 dark:border-gray-700 overflow-y-auto">
                    <div class="p-4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Mesa {{ $selectedTable->number }}</h3>
                            <button wire:click="$set('selectedTable', null)" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Estado:</span>
                                <span class="px-2 py-1 text-xs rounded-full {{ $this->getStatusColor($selectedTable->status) }} text-white">
                                    {{ $this->getStatusText($selectedTable->status) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Ubicación:</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedTable->location }}</span>
                            </div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Capacidad:</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $selectedTable->capacity }} personas</span>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Cambiar Estado</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button wire:click="changeTableStatus({{ $selectedTable->id }}, 'available')" class="px-2 py-1 text-xs rounded-md bg-green-500 hover:bg-green-600 text-white">
                                    Disponible
                                </button>
                                <button wire:click="changeTableStatus({{ $selectedTable->id }}, 'occupied')" class="px-2 py-1 text-xs rounded-md bg-red-500 hover:bg-red-600 text-white">
                                    Ocupada
                                </button>
                                <button wire:click="changeTableStatus({{ $selectedTable->id }}, 'reserved')" class="px-2 py-1 text-xs rounded-md bg-yellow-500 hover:bg-yellow-600 text-white">
                                    Reservada
                                </button>
                                <button wire:click="changeTableStatus({{ $selectedTable->id }}, 'maintenance')" class="px-2 py-1 text-xs rounded-md bg-gray-500 hover:bg-gray-600 text-white">
                                    Mantenimiento
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">Acciones</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button wire:click="showQrCode({{ $selectedTable->id }})" class="flex items-center justify-center px-3 py-2 text-sm rounded-md bg-blue-600 hover:bg-blue-700 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    Ver QR
                                </button>
                                <a href="{{ route('pos.index', ['table_id' => $selectedTable->id]) }}" class="flex items-center justify-center px-3 py-2 text-sm rounded-md bg-green-600 hover:bg-green-700 text-white">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    Crear Orden
                                </a>
                            </div>
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

    <!-- QR Code Modal -->
    @if($showQrModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Código QR - Mesa {{ $selectedTable->number }}</h3>
                <button wire:click="closeQrModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex flex-col items-center justify-center p-4">
                @if($currentQrCode)
                    <div class="bg-white p-4 rounded-lg">
                        <img src="{{ $currentQrCode }}" alt="QR Code" class="w-64 h-64">
                    </div>
                    <p class="mt-4 text-sm text-gray-600 dark:text-gray-400 text-center">Escanee este código QR para acceder rápidamente a la mesa.</p>
                @else
                    <div class="text-center py-8">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No hay código QR disponible</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Esta mesa no tiene un código QR asignado.</p>
                    </div>
                @endif
            </div>
            <div class="mt-4 flex justify-end">
                <button wire:click="closeQrModal" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
