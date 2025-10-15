<x-filament-panels::page>
    <div class="space-y-6">
        @php
            $inventoryData = $this->getInventoryData();
            $warehouses = $inventoryData['warehouses'];
            $totalGeneral = $inventoryData['total_general'];
            $totalWarehouses = $inventoryData['total_warehouses'];
            $warehousesWithStock = $inventoryData['warehouses_with_stock'];
        @endphp

        <!-- Estadísticas generales -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                            <x-heroicon-o-chart-bar class="h-5 w-5 text-blue-500 mr-2" />
                            Resumen de Inventario
                        </h2>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ $totalWarehouses }} almacén(es) total, {{ $warehousesWithStock }} con stock disponible
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Valor Total</p>
                        <p class="text-2xl font-bold text-blue-600">S/ {{ number_format($totalGeneral, 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        @if(empty($warehouses))
            <div class="text-center py-12">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                    <x-heroicon-o-building-storefront class="h-6 w-6 text-gray-400" />
                </div>
                <h3 class="mt-2 text-sm font-medium text-gray-900">No hay almacenes disponibles</h3>
                <p class="mt-1 text-sm text-gray-500">No se encontraron almacenes activos en el sistema.</p>
            </div>
        @else
            @foreach($warehouses as $warehouseData)
                <div class="bg-white overflow-hidden shadow rounded-lg {{ !$warehouseData['has_stock'] ? 'border-l-4 border-gray-300' : 'border-l-4 border-green-500' }}">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h3 class="text-lg leading-6 font-medium text-gray-900 flex items-center">
                                    <x-heroicon-o-building-storefront class="h-5 w-5 text-gray-400 mr-2" />
                                    {{ $warehouseData['warehouse']->name }}
                                    @if(!$warehouseData['has_stock'])
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            Sin stock
                                        </span>
                                    @else
                                        <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ count($warehouseData['ingredients']) }} ingrediente(s)
                                        </span>
                                    @endif
                                </h3>
                                @if($warehouseData['warehouse']->description)
                                    <p class="mt-1 text-sm text-gray-500">{{ $warehouseData['warehouse']->description }}</p>
                                @endif
                                @if($warehouseData['warehouse']->location)
                                    <p class="mt-1 text-sm text-gray-500">
                                        <x-heroicon-o-map-pin class="h-4 w-4 inline mr-1" />
                                        {{ $warehouseData['warehouse']->location }}
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">Subtotal Almacén</p>
                                <p class="text-2xl font-bold {{ $warehouseData['has_stock'] ? 'text-green-600' : 'text-gray-400' }}">
                                    S/ {{ number_format($warehouseData['subtotal'], 2) }}
                                </p>
                            </div>
                        </div>

                        @if($warehouseData['has_stock'])
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Ingrediente
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Código
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Cantidad
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Costo Unitario
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Valor Total
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Estado
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($warehouseData['ingredients'] as $ingredientData)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                                <x-heroicon-o-cube class="h-5 w-5 text-blue-500" />
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $ingredientData['ingredient']->name }}
                                                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                                                    Ingrediente
                                                                </span>
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $ingredientData['ingredient']->unit_of_measure }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $ingredientData['ingredient']->code ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($ingredientData['stock']->quantity, 3) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    S/ {{ number_format($ingredientData['stock']->unit_cost, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    S/ {{ number_format($ingredientData['total_value'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Disponible
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                        
                                        @foreach($warehouseData['products'] as $productData)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0 h-10 w-10">
                                                            <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                                <x-heroicon-o-shopping-bag class="h-5 w-5 text-green-500" />
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <div class="text-sm font-medium text-gray-900">
                                                                {{ $productData['product']->name }}
                                                                <span class="ml-2 inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                                    Producto
                                                                </span>
                                                            </div>
                                                            <div class="text-sm text-gray-500">
                                                                {{ $productData['product']->unit_of_measure ?? 'unidad' }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $productData['product']->code ?? 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ number_format($productData['stock']->quantity, 3) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    S/ {{ number_format($productData['stock']->unit_cost, 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    S/ {{ number_format($productData['total_value'], 2) }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                                        Disponible
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-gray-100">
                                    <x-heroicon-o-archive-box-x-mark class="h-6 w-6 text-gray-400" />
                                </div>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Almacén sin stock</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    Este almacén no tiene ingredientes disponibles actualmente.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach

            <!-- Total General -->
            <div class="bg-gradient-to-r from-green-50 to-blue-50 overflow-hidden shadow rounded-lg border-2 border-green-200">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl leading-6 font-bold text-gray-900 flex items-center">
                                <x-heroicon-o-currency-dollar class="h-6 w-6 text-green-500 mr-2" />
                                Total General de Inventario
                            </h3>
                            <p class="mt-1 text-sm text-gray-600">
                                Valor total de todos los ingredientes en {{ $warehousesWithStock }} de {{ $totalWarehouses }} almacén(es)
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-green-600">S/ {{ number_format($totalGeneral, 2) }}</p>
                            <p class="text-sm text-gray-500">
                                @if($warehousesWithStock < $totalWarehouses)
                                    {{ $totalWarehouses - $warehousesWithStock }} almacén(es) sin stock
                                @else
                                    Todos los almacenes tienen stock
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>