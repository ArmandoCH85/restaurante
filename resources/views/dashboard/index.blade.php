<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Estadísticas del Restaurante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .dashboard-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .stat-card {
            background-color: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            padding: 1.25rem;
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        .stat-value {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1f2937;
        }
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Dashboard de Ventas</h1>
            <div class="flex space-x-3">
                <a href="{{ url('/admin') }}" class="flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition-all duration-200 font-medium text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Ir al Escritorio
                </a>
                <a href="{{ url('/pos') }}" class="flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md transition-all duration-200 font-medium text-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Ir al POS
                </a>
            </div>
        </div>

        <!-- Filtros de fecha -->
        <div class="dashboard-card mb-6">
            <form action="{{ route('dashboard.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Período</label>
                    <select id="period" name="period" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" onchange="toggleCustomDates()">
                        <option value="today" {{ $period == 'today' ? 'selected' : '' }}>Hoy</option>
                        <option value="yesterday" {{ $period == 'yesterday' ? 'selected' : '' }}>Ayer</option>
                        <option value="week" {{ $period == 'week' ? 'selected' : '' }}>Esta semana</option>
                        <option value="month" {{ $period == 'month' ? 'selected' : '' }}>Este mes</option>
                        <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Personalizado</option>
                    </select>
                </div>
                
                <div id="custom-dates" class="flex gap-4" style="{{ $period != 'custom' ? 'display: none;' : '' }}">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha inicial</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Fecha final</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                </div>
                
                <div>
                    <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md transition-all duration-200 font-medium text-sm">
                        Aplicar filtros
                    </button>
                </div>
            </form>
        </div>

        <!-- Estadísticas generales -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Estadísticas generales</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="stat-card">
                <div class="flex justify-between">
                    <div>
                        <div class="stat-value">S/ {{ number_format($stats['totalSales'], 2) }}</div>
                        <div class="stat-label">Ventas totales</div>
                    </div>
                    <div class="text-green-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex justify-between">
                    <div>
                        <div class="stat-value">{{ $stats['orderCount'] }}</div>
                        <div class="stat-label">Órdenes completadas</div>
                    </div>
                    <div class="text-blue-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex justify-between">
                    <div>
                        <div class="stat-value">S/ {{ number_format($stats['averageTicket'], 2) }}</div>
                        <div class="stat-label">Ticket promedio</div>
                    </div>
                    <div class="text-purple-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="flex justify-between">
                    <div>
                        <div class="stat-value">{{ $stats['productsSold'] }}</div>
                        <div class="stat-label">Productos vendidos</div>
                    </div>
                    <div class="text-yellow-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="dashboard-card">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ventas por hora</h3>
                <div class="chart-container">
                    <canvas id="hourlyChart"></canvas>
                </div>
            </div>
            
            <div class="dashboard-card">
                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ventas por tipo de servicio</h3>
                <div class="chart-container">
                    <canvas id="serviceTypeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Productos más vendidos -->
        <div class="dashboard-card mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Productos más vendidos</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ventas</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topProducts as $product)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($product->product->image_path)
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset($product->product->image_path) }}" alt="{{ $product->product->name }}">
                                            </div>
                                        @else
                                            <div class="flex-shrink-0 h-10 w-10 bg-gray-200 rounded-full flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                                </svg>
                                            </div>
                                        @endif
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $product->product->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->product->category->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $product->quantity_sold }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">S/ {{ number_format($product->total_sales, 2) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Clientes frecuentes -->
        <div class="dashboard-card mb-8">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Clientes frecuentes</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitas</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gasto total</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($topCustomers as $customer)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $customer->customer->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $customer->customer->document_type }} {{ $customer->customer->document_number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $customer->visit_count }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">S/ {{ number_format($customer->total_spent, 2) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleCustomDates() {
            const period = document.getElementById('period').value;
            const customDates = document.getElementById('custom-dates');
            
            if (period === 'custom') {
                customDates.style.display = 'flex';
            } else {
                customDates.style.display = 'none';
            }
        }
        
        // Gráfico de ventas por hora
        const hourlyCtx = document.getElementById('hourlyChart').getContext('2d');
        const hourlyData = @json($hourlyStats);
        
        const hours = hourlyData.map(item => item.hour + ':00');
        const hourlyTotals = hourlyData.map(item => item.total);
        const hourlyCounts = hourlyData.map(item => item.count);
        
        new Chart(hourlyCtx, {
            type: 'bar',
            data: {
                labels: hours,
                datasets: [
                    {
                        label: 'Ventas (S/)',
                        data: hourlyTotals,
                        backgroundColor: 'rgba(79, 70, 229, 0.6)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Número de órdenes',
                        data: hourlyCounts,
                        backgroundColor: 'rgba(245, 158, 11, 0.6)',
                        borderColor: 'rgba(245, 158, 11, 1)',
                        borderWidth: 1,
                        type: 'line',
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Ventas (S/)'
                        }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false
                        },
                        title: {
                            display: true,
                            text: 'Número de órdenes'
                        }
                    }
                }
            }
        });
        
        // Gráfico de ventas por tipo de servicio
        const serviceTypeCtx = document.getElementById('serviceTypeChart').getContext('2d');
        const serviceTypeData = @json($stats['salesByServiceType']);
        
        const serviceTypeLabels = {
            'dine_in': 'En restaurante',
            'takeout': 'Para llevar',
            'delivery': 'Delivery',
            'drive_thru': 'Auto servicio'
        };
        
        const serviceTypeColors = {
            'dine_in': 'rgba(79, 70, 229, 0.6)',
            'takeout': 'rgba(245, 158, 11, 0.6)',
            'delivery': 'rgba(16, 185, 129, 0.6)',
            'drive_thru': 'rgba(239, 68, 68, 0.6)'
        };
        
        const serviceTypeKeys = Object.keys(serviceTypeData);
        const serviceTypeValues = Object.values(serviceTypeData);
        const serviceTypeLabelsMapped = serviceTypeKeys.map(key => serviceTypeLabels[key] || key);
        const serviceTypeColorsMapped = serviceTypeKeys.map(key => serviceTypeColors[key] || 'rgba(156, 163, 175, 0.6)');
        
        new Chart(serviceTypeCtx, {
            type: 'pie',
            data: {
                labels: serviceTypeLabelsMapped,
                datasets: [{
                    data: serviceTypeValues,
                    backgroundColor: serviceTypeColorsMapped,
                    borderColor: serviceTypeColorsMapped.map(color => color.replace('0.6', '1')),
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: S/ ${value.toFixed(2)} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
