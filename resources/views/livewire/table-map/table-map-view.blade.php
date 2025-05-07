<div>
    <link rel="stylesheet" href="{{ asset('css/table-shapes.css') }}">
    <!-- Estilos inline para asegurar que se apliquen correctamente -->
    <style>
        .table-visual {
            width: 60px !important;
            height: 60px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            border: 2px solid black !important;
            background-color: white !important;
            margin: 0 auto !important;
        }

        .table-square {
            border-radius: 4px !important;
        }

        .table-round {
            border-radius: 50% !important;
        }
    </style>
    <style>
        /* Notificaciones */
        #notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            padding: 1rem;
            border-radius: 0.5rem;
            background-color: white;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 50;
            transform: translateY(-100%);
            opacity: 0;
            transition: transform 0.3s, opacity 0.3s;
        }

        #notification.show {
            transform: translateY(0);
            opacity: 1;
        }

        .notification-success {
            border-left: 4px solid #10b981;
        }

        .notification-error {
            border-left: 4px solid #ef4444;
        }

        .dark #notification {
            background-color: #1f2937;
            color: #f9fafb;
        }

        /* Estilos para indicadores de tiempo */
        .occupation-time {
            display: inline-flex;
            align-items: center;
            padding: 0.15rem 0.35rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .occupation-time-icon {
            margin-right: 0.2rem;
            width: 0.8rem;
            height: 0.8rem;
        }

        .occupation-time-short {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .occupation-time-medium {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .occupation-time-long {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .dark .occupation-time-short {
            background-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .dark .occupation-time-medium {
            background-color: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        .dark .occupation-time-long {
            background-color: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        /* Estilos para estadísticas */
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.5rem;
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            margin-bottom: 0.75rem;
        }

        .dark .stats-container {
            background-color: #1f2937;
        }

        .stat-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            min-width: 5rem;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .stat-value {
            font-size: 1.25rem;
            font-weight: 700;
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
            font-weight: 500;
        }

        .dark .stat-label {
            color: #9ca3af;
        }

        .stat-total {
            background-color: #f3f4f6;
        }

        .stat-available {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }

        .stat-occupied {
            background-color: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        .stat-reserved {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .stat-maintenance {
            background-color: rgba(107, 114, 128, 0.1);
            color: #6b7280;
        }

        .dark .stat-total {
            background-color: #374151;
            color: #f9fafb;
        }

        .dark .stat-available {
            background-color: rgba(16, 185, 129, 0.2);
            color: #34d399;
        }

        .dark .stat-occupied {
            background-color: rgba(239, 68, 68, 0.2);
            color: #f87171;
        }

        .dark .stat-reserved {
            background-color: rgba(245, 158, 11, 0.2);
            color: #fbbf24;
        }

        .dark .stat-maintenance {
            background-color: rgba(107, 114, 128, 0.2);
            color: #9ca3af;
        }

        /* Los estilos para las representaciones visuales de las mesas están en el archivo CSS externo */

        /* Indicador de reserva */
        .reservation-indicator {
            position: absolute;
            top: -6px;
            right: -6px;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            background-color: #f59e0b;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
            font-weight: bold;
            border: 2px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
            }
            70% {
                box-shadow: 0 0 0 6px rgba(245, 158, 11, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(245, 158, 11, 0);
            }
        }

        /* Estilos para las tarjetas de mesa */
        .table-card {
            transition: all 0.3s ease;
            border: 1px solid transparent;
            height: 170px;
        }

        .table-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: rgba(59, 130, 246, 0.5);
        }

        /* Asegurar que el contenido de la tarjeta ocupe todo el espacio disponible */
        .table-card > div {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        /* Asegurar que las acciones estén siempre al final de la tarjeta */
        .table-card .actions-container {
            margin-top: auto;
        }

        /* Estilos para los botones de acción */
        .action-button {
            transition: all 0.2s ease;
        }

        .action-button:hover {
            transform: translateY(-2px);
        }

        /* Estilos para las secciones de ubicación */
        .location-section {
            position: relative;
            padding-top: 1.5rem;
            margin-bottom: 2rem;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            background-color: white;
        }

        .dark .location-section {
            background-color: #1f2937;
        }

        .location-header {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid #e5e7eb;
            background-color: #f9fafb;
        }

        .dark .location-header {
            border-color: #374151;
            background-color: #111827;
        }

        .location-grid {
            padding: 1.5rem;
        }
    </style>

    <div class="flex flex-col h-screen overflow-hidden">
        <!-- Header/Navbar -->
        <header class="bg-white dark:bg-gray-800 shadow-md z-10">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <span class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 text-transparent bg-clip-text">Mapa de Mesas</span>
                    </div>
                    <div class="flex items-center space-x-2 overflow-x-auto py-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
                        <a href="{{ url('/admin') }}" class="action-button px-3 py-2 rounded-md text-sm font-medium bg-gray-700 hover:bg-gray-800 text-white flex items-center shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                            <span>Escritorio</span>
                        </a>
                        <a href="{{ route('pos.index') }}" class="action-button px-3 py-2 rounded-md text-sm font-medium bg-red-600 hover:bg-red-700 text-white flex items-center shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            <span>POS</span>
                        </a>
                        <div class="h-8 border-l border-gray-300 dark:border-gray-600 mx-1"></div>
                        <a href="{{ url('/dashboard') }}" class="action-button px-3 py-2 rounded-md text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white flex items-center shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Dashboard</span>
                        </a>
                        <a href="{{ route('tables.maintenance') }}" class="action-button px-3 py-2 rounded-md text-sm font-medium bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 flex items-center shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Mantenimiento</span>
                        </a>
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="action-button px-3 py-2 rounded-md text-sm font-medium bg-indigo-600 hover:bg-indigo-700 text-white flex items-center shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Panel Admin</span>
                        </a>
                        <div class="h-8 border-l border-gray-300 dark:border-gray-600 mx-1"></div>
                        <a href="{{ url('/admin/reservations/create') }}" class="action-button px-3 py-2 rounded-md text-sm font-medium bg-yellow-600 hover:bg-yellow-700 text-white flex items-center shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span>Nueva Reserva</span>
                        </a>
                        <a href="{{ route('filament.admin.resources.tables.create') }}" class="action-button px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-medium flex items-center shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            <span>Nueva Mesa</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 overflow-hidden">
            <div class="flex h-full">
                <!-- Sidebar -->
                <div class="w-48 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 overflow-y-auto">
                    <div class="p-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Filtros</h3>

                        <!-- Status Filter -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                            <select wire:model.live="statusFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="available">Disponible</option>
                                <option value="occupied">Ocupada</option>
                                <option value="reserved">Reservada</option>
                                <option value="maintenance">Mantenimiento</option>
                            </select>
                        </div>

                        <!-- Reservations Filter -->
                        <div class="mb-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="show-reservations" wire:model.live="showTodayReservations" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                <label for="show-reservations" class="ml-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Mostrar solo reservas de hoy</label>
                            </div>
                        </div>

                        <!-- Floor Filter -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Piso</label>
                            <select wire:model.live="floorFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                @foreach($this->getFloorOptions() as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Location Filter -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ubicación</label>
                            <select wire:model.live="locationFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todas</option>
                                @foreach($this->getLocationOptions() as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Capacity Filter -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Capacidad</label>
                            <select wire:model.live="capacityFilter" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <option value="1-2">1-2 personas</option>
                                <option value="3-4">3-4 personas</option>
                                <option value="5-8">5-8 personas</option>
                                <option value="9+">9+ personas</option>
                            </select>
                        </div>

                        <!-- Search -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Buscar</label>
                            <input type="text" wire:model.live.debounce.300ms="searchQuery" placeholder="Buscar mesa..." class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="flex-1 overflow-y-auto p-3">
                    @php $stats = $this->getTableStats(); @endphp
                    <div class="stats-container">
                        <div class="stat-item stat-total">
                            <div class="stat-value">{{ $stats['total'] }}</div>
                            <div class="stat-label">Total Mesas</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">100%</div>
                        </div>
                        <div class="stat-item stat-available">
                            <div class="stat-value">{{ $stats['available'] }}</div>
                            <div class="stat-label">Disponibles</div>
                            <div class="mt-1 text-xs text-green-600 dark:text-green-400">{{ $stats['available_percent'] }}%</div>
                        </div>
                        <div class="stat-item stat-occupied">
                            <div class="stat-value">{{ $stats['occupied'] }}</div>
                            <div class="stat-label">Ocupadas</div>
                            <div class="mt-1 text-xs text-red-600 dark:text-red-400">{{ $stats['occupied_percent'] }}%</div>
                        </div>
                        <div class="stat-item stat-reserved">
                            <div class="stat-value">{{ $stats['reserved'] }}</div>
                            <div class="stat-label">Reservadas</div>
                            <div class="mt-1 text-xs text-yellow-600 dark:text-yellow-400">{{ $stats['total'] > 0 ? round(($stats['reserved'] / $stats['total']) * 100) : 0 }}%</div>
                        </div>
                        <div class="stat-item stat-maintenance">
                            <div class="stat-value">{{ $stats['maintenance'] }}</div>
                            <div class="stat-label">Mantenimiento</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stats['total'] > 0 ? round(($stats['maintenance'] / $stats['total']) * 100) : 0 }}%</div>
                        </div>
                    </div>

                    <!-- Selector rápido de pisos y hora actual -->
                    <div class="mb-2 flex justify-between items-center">
                        <div class="flex space-x-2 overflow-x-auto pb-2">
                            @foreach($floors as $floor)
                                <button
                                    wire:click="$set('floorFilter', '{{ $floor->id }}')"
                                    class="px-2 py-1 rounded-lg text-xs font-medium shadow-sm transition-all duration-200 hover:-translate-y-1 {{ $floorFilter == $floor->id ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700' }}"
                                >
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 {{ $floorFilter == $floor->id ? 'text-white' : 'text-blue-600 dark:text-blue-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="{{ $floorFilter == $floor->id ? 'text-white' : 'text-gray-700 dark:text-gray-300' }}">{{ $floor->name }}</span>
                                    </span>
                                </button>
                            @endforeach
                            @if($floorFilter)
                                <button
                                    wire:click="$set('floorFilter', '')"
                                    class="px-2 py-1 rounded-lg text-xs font-medium bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 shadow-sm"
                                >
                                    <span class="flex items-center">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        <span class="text-gray-700 dark:text-gray-300 text-xs">Todos</span>
                                    </span>
                                </button>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Actualizado: {{ now()->format('H:i') }} - {{ now()->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>

                    <!-- Table Grid -->
                    @if($tables->isEmpty())
                        <div class="flex flex-col items-center justify-center h-full text-center p-6">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <h3 class="text-xl font-semibold text-gray-700 dark:text-gray-300 mb-2">No se encontraron mesas</h3>
                            <p class="text-gray-500 dark:text-gray-400 max-w-md">No hay mesas que coincidan con los filtros seleccionados. Intenta cambiar los filtros o añade nuevas mesas.</p>
                            <button wire:click="resetFilters" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                Limpiar filtros
                            </button>
                        </div>
                    @else
                        <!-- Mesas agrupadas por piso y ubicación -->
                        @foreach($this->getGroupedTables() as $floorData)
                            @php
                                $floor = $floorData['floor'];
                                $locationGroups = $floorData['locations'];
                            @endphp
                            <div class="floor-section mb-4">
                                <div class="floor-header bg-white dark:bg-gray-800 p-2 rounded-t-xl shadow-sm border border-gray-200 dark:border-gray-700">
                                    <h2 class="text-xl font-bold flex items-center text-gray-800 dark:text-gray-200">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 mr-2 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ $floor->name }}
                                        <span class="ml-2 text-sm bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 px-2 py-1 rounded-full">
                                            @php
                                                $totalTables = 0;
                                                foreach ($locationGroups as $tables) {
                                                    $totalTables += $tables->count();
                                                }
                                            @endphp
                                            {{ $totalTables }} {{ $totalTables == 1 ? 'mesa' : 'mesas' }}
                                        </span>
                                    </h2>
                                    @if($floor->description)
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $floor->description }}</p>
                                    @endif
                                </div>

                                <!-- Ubicaciones dentro del piso -->
                                @foreach($locationGroups as $location => $locationTables)
                                    <div class="location-section mb-2 border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden w-full">
                                        <div class="location-header bg-gray-50 dark:bg-gray-900 p-3 border-b border-gray-200 dark:border-gray-700">
                                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 flex items-center">
                                                @if($location == 'interior')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                    </svg>
                                                @elseif($location == 'exterior')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                                                    </svg>
                                                @elseif($location == 'bar')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                    </svg>
                                                @elseif($location == 'private')
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                @else
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                @endif
                                                {{ $this->getLocationName($location) }}
                                                <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">({{ $locationTables->count() }} {{ $locationTables->count() == 1 ? 'mesa' : 'mesas' }})</span>
                                            </h3>
                                        </div>
                                        <div class="location-grid p-2 bg-white dark:bg-gray-800">
                                            <div class="flex flex-wrap gap-0.5 justify-center">
                                                @foreach($locationTables->filter(function($table) { return $table->id && $table->number; }) as $table)
                                                    <a href="{{ route('pos.index', ['table_id' => $table->id]) }}" class="table-card bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden block transition-all duration-200 hover:shadow-md hover:border-blue-300 dark:hover:border-blue-700 border border-transparent relative cursor-pointer w-[140px]">
                                                        <div class="p-2">
                                                            <!-- Encabezado de la tarjeta -->
                                                            <div class="flex justify-between items-start mb-2">
                                                                <div>
                                                                    <h3 class="text-sm font-medium text-gray-900 dark:text-gray-100">Mesa {{ $table->number }}</h3>
                                                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                                                        Capacidad: {{ $table->capacity ?? '?' }} personas
                                                                    </p>
                                                                </div>
                                                                @php
                                                                    $statusInfo = $this->getTableStatus($table->status ?? 'available');
                                                                @endphp
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $statusInfo['bg'] }}; color: {{ $statusInfo['color'] }}">
                                                                    <svg class="-ml-0.5 mr-1.5 h-2 w-2" fill="currentColor" viewBox="0 0 8 8">
                                                                        <circle cx="4" cy="4" r="3" />
                                                                    </svg>
                                                                    {{ $statusInfo['text'] }}
                                                                </span>
                                                            </div>

                                                            <!-- Representación visual de la mesa -->
                                                            <div class="flex justify-center my-1">
                                                                @if($table->shape == 'round')
                                                                    <div class="table-visual table-round">
                                                                        <span class="text-lg font-bold" style="color: black;">{{ $table->number }}</span>
                                                                    </div>
                                                                @else
                                                                    <div class="table-visual table-square">
                                                                        <span class="text-lg font-bold" style="color: black;">{{ $table->number }}</span>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <!-- Tiempo de ocupación si está ocupada -->
                                                            @if(($table->status ?? '') === 'occupied' && $occupationTime = $this->getOccupationTime($table))
                                                                <div class="text-center mb-1">
                                                                    <span class="occupation-time {{ $this->getOccupationTimeClass($table) }}">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="occupation-time-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                        </svg>
                                                                        {{ $occupationTime }}
                                                                    </span>
                                                                </div>
                                                            @endif

                                                            <!-- Acciones -->
                                                            <div class="mt-auto pt-1 flex justify-between space-x-1 actions-container">
                                                                <div class="flex-1">
                                                                    <select class="w-full text-xs rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500" wire:change="changeTableStatus({{ $table->id }}, $event.target.value)">
                                                                        <option value="" disabled>Cambiar estado</option>
                                                                        @foreach($this->getStatusOptions() as $value => $label)
                                                                            <option value="{{ $value }}" {{ ($table->status ?? '') === $value ? 'disabled' : '' }}>
                                                                                {{ $label }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                                @if(($table->status ?? '') !== 'reserved')
                                                                    <a href="{{ url('/admin/reservations/create?table_id=' . $table->id) }}" class="action-button inline-flex items-center px-2 py-1 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-1 focus:ring-yellow-500">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                                        </svg>
                                                                        Reservar
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Notificación -->
    <div id="notification" class="notification-success">
        <div id="notification-message"></div>
    </div>

    <script src="{{ asset('js/table-shapes.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.getElementById('notification');
            const notificationMessage = document.getElementById('notification-message');

            window.addEventListener('table-status-changed', event => {
                notificationMessage.textContent = event.detail.message;
                notification.className = 'notification-' + event.detail.type;
                notification.classList.add('show');

                setTimeout(() => {
                    notification.classList.remove('show');
                }, 3000);
            });

            // Actualizar los indicadores de tiempo cada minuto
            setInterval(() => {
                Livewire.emit('refresh');
            }, 60000); // 60 segundos
        });
    </script>
</div>
