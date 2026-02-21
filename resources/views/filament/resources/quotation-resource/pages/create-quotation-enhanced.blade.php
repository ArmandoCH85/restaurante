<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Formulario principal -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-xl">
            {{ $this->form }}
        </div>

        <!-- Sección de productos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Panel de selección de productos -->
            <div class="col-span-1 bg-white dark:bg-gray-800 shadow rounded-xl p-4">
                <h2 class="text-xl font-bold mb-4">Seleccionar Productos</h2>

                <!-- Búsqueda de productos -->
                <div class="mb-4">
                    <div class="relative">
                        <input
                            type="search"
                            wire:model.live.debounce.300ms="productSearchQuery"
                            placeholder="Buscar productos..."
                            class="w-full block rounded-lg shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        />
                    </div>
                </div>



                <!-- Lista de productos -->
                <div class="space-y-2 max-h-[500px] overflow-y-auto pr-2">
                    @forelse($products as $product)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition">
                            <div>
                                <p class="font-medium">{{ $product->name }}</p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">S/ {{ number_format($product->price, 2) }}</p>
                            </div>
                            <x-filament::button
                                size="sm"
                                color="primary"
                                wire:click="addProduct('{{ $product->id }}')"
                                wire:loading.attr="disabled"
                                wire:target="addProduct('{{ $product->id }}')"
                                icon="heroicon-m-plus"
                                :disabled="in_array($product->id, $selectedProducts)"
                            >
                                {{ in_array($product->id, $selectedProducts) ? 'Agregado' : 'Agregar' }}
                            </x-filament::button>
                        </div>
                    @empty
                        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                            No se encontraron productos. Intente con otra búsqueda.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Panel de productos seleccionados y totales -->
            <div class="col-span-1 lg:col-span-2 bg-white dark:bg-gray-800 shadow rounded-xl p-4">
                <h2 class="text-xl font-bold mb-4">Productos Seleccionados</h2>

                <!-- Tabla de productos seleccionados -->
                <div class="overflow-x-auto mb-6">
                    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-4 py-3">Producto</th>
                                <th scope="col" class="px-4 py-3 text-center">Cantidad</th>
                                <th scope="col" class="px-4 py-3 text-right">Precio</th>
                                <th scope="col" class="px-4 py-3 text-right">Subtotal</th>
                                <th scope="col" class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($selectedProducts as $productId)
                                @php
                                    $product = \App\Models\Product::find($productId);
                                @endphp
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3">
                                        <div>
                                            <p class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</p>
                                            @if(!empty($productNotes[$productId]))
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    <span class="font-medium">Nota:</span> {{ $productNotes[$productId] }}
                                                </p>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center">
                                            <button
                                                type="button"
                                                class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-xs p-1 text-center inline-flex items-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                                                wire:click="updateQuantity('{{ $productId }}', {{ $productQuantities[$productId] - 1 }})"
                                            >
                                                <x-heroicon-m-minus class="w-3 h-3" />
                                            </button>
                                            <input
                                                type="number"
                                                class="mx-2 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-14 text-center dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                                value="{{ $productQuantities[$productId] }}"
                                                min="1"
                                                wire:change="updateQuantity('{{ $productId }}', $event.target.value)"
                                            >
                                            <button
                                                type="button"
                                                class="text-white bg-primary-600 hover:bg-primary-700 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-xs p-1 text-center inline-flex items-center dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800"
                                                wire:click="updateQuantity('{{ $productId }}', {{ $productQuantities[$productId] + 1 }})"
                                            >
                                                <x-heroicon-m-plus class="w-3 h-3" />
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        S/ {{ number_format($productPrices[$productId], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium">
                                        S/ {{ number_format($productSubtotals[$productId], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center space-x-2">
                                            <button
                                                type="button"
                                                class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                                wire:click="openEditPriceModal('{{ $productId }}')"
                                                title="Editar precio"
                                            >
                                                <x-heroicon-m-currency-dollar class="w-5 h-5" />
                                            </button>
                                            <button
                                                type="button"
                                                class="text-yellow-600 hover:text-yellow-900 dark:text-yellow-400 dark:hover:text-yellow-300"
                                                wire:click="openAddNoteModal('{{ $productId }}')"
                                                title="Agregar nota"
                                            >
                                                <x-heroicon-m-pencil-square class="w-5 h-5" />
                                            </button>
                                            <button
                                                type="button"
                                                class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300"
                                                wire:click="removeProduct('{{ $productId }}')"
                                                title="Eliminar producto"
                                            >
                                                <x-heroicon-m-trash class="w-5 h-5" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                        No hay productos seleccionados. Agregue productos desde el panel izquierdo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Sección de totales -->
                <div class="flex flex-col items-end space-y-2 mb-6">
                    <div class="w-full max-w-md bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 dark:text-gray-300">Subtotal:</span>
                            <span class="font-medium">S/ {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 dark:text-gray-300">IGV (10.50%):</span>
                            <span class="font-medium">S/ {{ number_format($tax, 2) }}</span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span class="text-gray-700 dark:text-gray-300">Descuento:</span>
                            <div class="flex items-center">
                                <span class="mr-2">S/</span>
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    wire:model.live="discount"
                                    wire:change="updateDiscount($event.target.value)"
                                    class="w-24 text-right rounded-lg shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                                />
                            </div>
                        </div>
                        <div class="flex justify-between pt-2 border-t border-gray-200 dark:border-gray-600">
                            <span class="text-lg font-bold text-gray-900 dark:text-white">Total:</span>
                            <span class="text-lg font-bold text-primary-600 dark:text-primary-400">S/ {{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex justify-end space-x-3">
                    <x-filament::button
                        color="gray"
                        tag="a"
                        href="{{ \App\Filament\Resources\QuotationResource::getUrl('index') }}"
                    >
                        Cancelar
                    </x-filament::button>
                    <x-filament::button
                        wire:click="create"
                        wire:loading.attr="disabled"
                        wire:target="create"
                    >
                        <span wire:loading.remove wire:target="create">Crear Proforma</span>
                        <span wire:loading wire:target="create">Creando...</span>
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para editar precio -->
    <x-filament::modal
        width="md"
        wire:model.live="showEditPriceModal"
    >
        <x-slot name="heading">
            Editar Precio
        </x-slot>

        <div class="p-4 space-y-4">
            <div>
                <div class="space-y-2">
                    <label class="inline-flex text-sm font-medium text-gray-700 dark:text-gray-300">
                        Precio Unitario
                    </label>
                    <input
                        type="number"
                        wire:model="editingPrice"
                        min="0"
                        step="0.01"
                        class="w-full block rounded-lg shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-x-4">
                <x-filament::button
                    color="gray"
                    wire:click="$set('showEditPriceModal', false)"
                >
                    Cancelar
                </x-filament::button>
                <x-filament::button
                    wire:click="savePrice"
                >
                    Guardar
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>

    <!-- Modal para agregar nota -->
    <x-filament::modal
        width="md"
        wire:model.live="showAddNoteModal"
    >
        <x-slot name="heading">
            Agregar Nota
        </x-slot>

        <div class="p-4 space-y-4">
            <div>
                <div class="space-y-2">
                    <label class="inline-flex text-sm font-medium text-gray-700 dark:text-gray-300">
                        Nota para el producto
                    </label>
                    <textarea
                        wire:model="editingNote"
                        class="w-full block rounded-lg shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                        rows="3"
                    ></textarea>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <div class="flex justify-end gap-x-4">
                <x-filament::button
                    color="gray"
                    wire:click="$set('showAddNoteModal', false)"
                >
                    Cancelar
                </x-filament::button>
                <x-filament::button
                    wire:click="saveNote"
                >
                    Guardar
                </x-filament::button>
            </div>
        </x-slot>
    </x-filament::modal>
</x-filament-panels::page>
