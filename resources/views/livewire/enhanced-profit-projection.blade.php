<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Proyección de Ganancias Mejorada</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- Filtros -->
        <div class="col-span-1 md:col-span-3 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
            <h3 class="text-lg font-medium mb-3 text-gray-700 dark:text-gray-200">Filtros</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Selector de Mes -->
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mes</label>
                    <select id="month" wire:model.live="selectedMonth" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        @foreach($monthNames as $num => $name)
                            <option value="{{ $num }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Selector de Año -->
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Año</label>
                    <select id="year" wire:model.live="selectedYear" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        @for($i = date('Y') - 2; $i <= date('Y') + 1; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Selector de Categoría -->
                <div>
                    <label for="category" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categoría</label>
                    <select id="category" wire:model.live="selectedCategory" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50 dark:bg-gray-800 dark:border-gray-600 dark:text-white">
                        <option value="">Todas las categorías</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Tarjetas de Resumen -->
        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-2 text-blue-800 dark:text-blue-200">Ingresos Totales</h3>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-300">S/ {{ number_format($totalRevenue, 2) }}</p>
        </div>

        <div class="bg-red-50 dark:bg-red-900 p-4 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-2 text-red-800 dark:text-red-200">Costos Totales</h3>
            <p class="text-2xl font-bold text-red-600 dark:text-red-300">S/ {{ number_format($totalCost, 2) }}</p>
        </div>

        <div class="bg-green-50 dark:bg-green-900 p-4 rounded-lg shadow">
            <h3 class="text-lg font-medium mb-2 text-green-800 dark:text-green-200">Ganancia Neta</h3>
            <p class="text-2xl font-bold text-green-600 dark:text-green-300">S/ {{ number_format($totalProfit, 2) }}</p>
            <p class="text-sm font-medium mt-1 {{ $profitMargin < 20 ? 'text-red-500 dark:text-red-300' : ($profitMargin < 40 ? 'text-yellow-500 dark:text-yellow-300' : 'text-green-500 dark:text-green-300') }}">
                Margen: {{ number_format($profitMargin, 2) }}%
            </p>
        </div>
    </div>

    <!-- Tendencia Mensual -->
    <div class="mb-8">
        <h3 class="text-lg font-medium mb-4 text-gray-700 dark:text-gray-200">Tendencia de los Últimos 6 Meses</h3>
        <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow">
            <div class="p-4 h-64 relative">
                <!-- Gráfico de tendencia (representación visual simple) -->
                <div class="flex h-full items-end space-x-2">
                    @foreach($monthlyTrend as $trend)
                        @php
                            $height = $trend['profit'] > 0 ? min(100, max(5, ($trend['profit'] / max(1, collect($monthlyTrend)->max('profit'))) * 100)) : 0;
                            $color = $trend['margin'] < 20 ? 'bg-red-500' : ($trend['margin'] < 40 ? 'bg-yellow-500' : 'bg-green-500');
                        @endphp
                        <div class="flex-1 flex flex-col items-center justify-end">
                            <div class="w-full {{ $color }} rounded-t" style="height: {{ $height }}%"></div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">
                                {{ substr($trend['month'], 0, 3) }}<br>{{ $trend['year'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
                <!-- Líneas de referencia -->
                <div class="absolute left-0 right-0 top-0 border-b border-gray-200 dark:border-gray-700 text-xs text-gray-400 dark:text-gray-500">100%</div>
                <div class="absolute left-0 right-0 top-1/4 border-b border-gray-200 dark:border-gray-700 text-xs text-gray-400 dark:text-gray-500">75%</div>
                <div class="absolute left-0 right-0 top-1/2 border-b border-gray-200 dark:border-gray-700 text-xs text-gray-400 dark:text-gray-500">50%</div>
                <div class="absolute left-0 right-0 top-3/4 border-b border-gray-200 dark:border-gray-700 text-xs text-gray-400 dark:text-gray-500">25%</div>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700 p-4 border-t border-gray-200 dark:border-gray-600">
                <div class="grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Ingresos Promedio</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">S/ {{ number_format(collect($monthlyTrend)->avg('revenue'), 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Costos Promedio</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">S/ {{ number_format(collect($monthlyTrend)->avg('cost'), 2) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Margen Promedio</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ number_format(collect($monthlyTrend)->avg('margin'), 2) }}%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top 5 Productos Más Rentables -->
    <div class="mb-8">
        <h3 class="text-lg font-medium mb-4 text-gray-700 dark:text-gray-200">Top 5 Productos Más Rentables</h3>
        <div class="bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ventas</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ganancia</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Margen</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($topProducts as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $product['product_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $product['category'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">S/ {{ number_format($product['revenue'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600 dark:text-green-400">S/ {{ number_format($product['profit'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $product['profit_margin'] < 20 ? 'text-red-600 dark:text-red-400' : ($product['profit_margin'] < 40 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                                {{ number_format($product['profit_margin'], 2) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">No hay datos disponibles</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabla de Productos -->
    <div>
        <h3 class="text-lg font-medium mb-4 text-gray-700 dark:text-gray-200">Detalle de Productos</h3>
        <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-lg shadow">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingresos</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Costos</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ganancia</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Margen</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($profitData as $item)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $item['product_name'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item['category'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{{ $item['quantity'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-blue-600 dark:text-blue-400 font-medium">S/ {{ number_format($item['revenue'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-red-600 dark:text-red-400 font-medium">S/ {{ number_format($item['cost'], 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $item['profit'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                S/ {{ number_format($item['profit'], 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium {{ $item['profit_margin'] < 20 ? 'text-red-600 dark:text-red-400' : ($item['profit_margin'] < 40 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                                {{ number_format($item['profit_margin'], 2) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">No hay datos disponibles para el período seleccionado</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Nota informativa -->
    <div class="mt-8 bg-blue-50 dark:bg-blue-900/30 p-4 rounded-lg text-sm text-blue-800 dark:text-blue-200">
        <p class="font-medium mb-2">Nota sobre el cálculo de costos:</p>
        <p>Los costos se calculan utilizando la siguiente metodología:</p>
        <ul class="list-disc pl-5 mt-2 space-y-1">
            <ol class="list-decimal pl-5 mt-2 space-y-1">
                <li>Para productos con receta: Se suman los costos de cada ingrediente según su última compra registrada.</li>
                <li>Para productos sin receta: Se utiliza el costo actual registrado en el producto.</li>
                <li>Los márgenes de ganancia se calculan como (Ganancia / Ingresos) * 100.</li>
            </ol>
        </ul>
    </div>
</div>