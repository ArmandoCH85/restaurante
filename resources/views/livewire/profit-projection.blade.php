<div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800 dark:text-white">Proyección de Ganancias</h2>

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

    <!-- Tabla de Productos -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white dark:bg-gray-800 rounded-lg overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-700">
                <tr>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Producto</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Categoría</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cantidad</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ingresos</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Costos</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ganancia</th>
                    <th class="py-3 px-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Margen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($profitData as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="py-3 px-4 text-sm font-medium text-gray-900 dark:text-white">{{ $item['product_name'] }}</td>
                        <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-300">{{ $item['category'] }}</td>
                        <td class="py-3 px-4 text-sm text-gray-500 dark:text-gray-300">{{ $item['quantity'] }}</td>
                        <td class="py-3 px-4 text-sm text-blue-600 dark:text-blue-400 font-medium">S/ {{ number_format($item['revenue'], 2) }}</td>
                        <td class="py-3 px-4 text-sm text-red-600 dark:text-red-400 font-medium">S/ {{ number_format($item['cost'], 2) }}</td>
                        <td class="py-3 px-4 text-sm font-medium {{ $item['profit'] < 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            S/ {{ number_format($item['profit'], 2) }}
                        </td>
                        <td class="py-3 px-4 text-sm font-medium {{ $item['profit_margin'] < 20 ? 'text-red-600 dark:text-red-400' : ($item['profit_margin'] < 40 ? 'text-yellow-600 dark:text-yellow-400' : 'text-green-600 dark:text-green-400') }}">
                            {{ number_format($item['profit_margin'], 2) }}%
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-6 px-4 text-center text-gray-500 dark:text-gray-400">No hay datos disponibles para el período seleccionado</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Nota informativa -->
    <div class="mt-6 text-sm text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg">
        <p><strong>Nota:</strong> Esta proyección se basa en datos históricos de ventas y costos de ingredientes. Los costos se calculan utilizando:</p>
        <ul class="list-disc ml-5 mt-2">
            <li>Para productos con receta: Suma del costo de cada ingrediente según su última compra registrada</li>
            <li>Para productos sin receta: Costo actual registrado en el sistema</li>
        </ul>
    </div>
</div>