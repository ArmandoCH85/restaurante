<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mantenimiento de Mesas - Restaurante</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .table-card {
            transition: all 0.3s ease;
        }
        .table-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="h-full bg-gray-100 text-gray-900 antialiased">
    <div class="flex flex-col h-screen overflow-hidden">
        <!-- Header -->
        <header class="bg-white shadow-sm z-10">
            <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <span class="text-2xl font-bold text-blue-700">Mantenimiento de Mesas</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('tables.map') }}" class="px-3 py-1 rounded-md text-sm font-medium bg-gray-200 hover:bg-gray-300 text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" />
                            </svg>
                            Mapa de Mesas
                        </a>
                        <a href="{{ route('pos.index') }}" class="px-3 py-1 rounded-md text-sm font-medium bg-gray-200 hover:bg-gray-300 text-gray-800 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            Venta Directa
                        </a>
                        <a href="{{ route('filament.admin.pages.dashboard') }}" class="px-3 py-1 rounded-md text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            Panel Admin
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Mensajes de éxito o error -->
                @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
                @endif

                @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
                @endif

                <!-- Filtros de búsqueda -->
                <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-3">Filtros de Búsqueda</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                            <select id="statusFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <option value="disponible">Disponible</option>
                                <option value="ocupada">Ocupada</option>
                                <option value="reservada">Reservada</option>
                                <option value="mantenimiento">Mantenimiento</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ubicación</label>
                            <select id="locationFilter" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <option value="interior">Interior</option>
                                <option value="terraza">Terraza</option>
                                <option value="barra">Barra</option>
                                <option value="privado">Sala Privada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                            <input type="text" id="searchInput" placeholder="Buscar mesa..." class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-between mb-6">
                    <div>
                        <a href="{{ route('filament.admin.resources.tables.create') }}" class="py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Crear Nueva Mesa
                        </a>
                    </div>
                    <div>
                        <button type="button" id="refreshButton" class="py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Actualizar
                        </button>
                    </div>
                </div>

                <!-- Tabla de mesas -->
                <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Número
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Capacidad
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Ubicación
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Estado
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Última Actualización
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($tables as $table)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">#{{ $table->number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $table->capacity }} personas</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($table->location === 'interior') bg-blue-100 text-blue-800
                                        @elseif($table->location === 'exterior') bg-green-100 text-green-800
                                        @elseif($table->location === 'bar') bg-yellow-100 text-yellow-800
                                        @elseif($table->location === 'private') bg-purple-100 text-purple-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ ucfirst($table->location) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($table->status === 'available') bg-green-100 text-green-800
                                        @elseif($table->status === 'occupied') bg-red-100 text-red-800
                                        @elseif($table->status === 'reserved') bg-yellow-100 text-yellow-800
                                        @elseif($table->status === 'maintenance') bg-gray-100 text-gray-800
                                        @endif">
                                        @if($table->status === 'available') Disponible
                                        @elseif($table->status === 'occupied') Ocupada
                                        @elseif($table->status === 'reserved') Reservada
                                        @elseif($table->status === 'maintenance') En Mantenimiento
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $table->updated_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end items-center space-x-2">
                                        <form action="{{ route('tables.update-status', $table) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()" class="text-sm border-gray-300 rounded-md shadow-sm">
                                                <option value="available" {{ $table->status === 'available' ? 'selected' : '' }}>Disponible</option>
                                                <option value="occupied" {{ $table->status === 'occupied' ? 'selected' : '' }}>Ocupada</option>
                                                <option value="reserved" {{ $table->status === 'reserved' ? 'selected' : '' }}>Reservada</option>
                                                <option value="maintenance" {{ $table->status === 'maintenance' ? 'selected' : '' }}>Mantenimiento</option>
                                            </select>
                                        </form>
                                        <a href="{{ route('filament.admin.resources.tables.edit', ['record' => $table->id]) }}" class="text-blue-600 hover:text-blue-900">Editar</a>
                                        <button type="button" onclick="confirmDelete({{ $table->id }})" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Confirmar eliminación</h3>
                <p class="text-gray-700 mb-6">¿Estás seguro que deseas eliminar esta mesa? Esta acción no se puede deshacer.</p>

                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeModal()" class="py-2 px-4 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancelar
                    </button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Filtrado de mesas
        const statusFilter = document.getElementById('statusFilter');
        const locationFilter = document.getElementById('locationFilter');
        const searchInput = document.getElementById('searchInput');
        const refreshButton = document.getElementById('refreshButton');
        const tableRows = document.querySelectorAll('tbody tr');

        function filterTables() {
            const status = statusFilter.value.toLowerCase();
            const location = locationFilter.value.toLowerCase();
            const search = searchInput.value.toLowerCase();

            tableRows.forEach(row => {
                const tableNumber = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                const tableLocation = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                const tableStatus = row.querySelector('td:nth-child(4)').textContent.toLowerCase();

                const matchesStatus = !status || tableStatus.includes(status);
                const matchesLocation = !location || tableLocation.includes(location);
                const matchesSearch = !search || tableNumber.includes(search);

                row.style.display = (matchesStatus && matchesLocation && matchesSearch) ? '' : 'none';
            });
        }

        statusFilter.addEventListener('change', filterTables);
        locationFilter.addEventListener('change', filterTables);
        searchInput.addEventListener('input', filterTables);
        refreshButton.addEventListener('click', () => window.location.reload());

        // Modal de confirmación
        const modal = document.getElementById('confirmationModal');
        const deleteForm = document.getElementById('deleteForm');

        function confirmDelete(tableId) {
            deleteForm.action = `/tables/${tableId}`;
            modal.classList.remove('hidden');
        }

        function closeModal() {
            modal.classList.add('hidden');
        }

        // Cerrar modal al hacer clic fuera
        window.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });
    </script>
</body>
</html>
