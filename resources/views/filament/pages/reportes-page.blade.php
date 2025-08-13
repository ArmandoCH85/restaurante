@php
    $heading = 'Reportes';
@endphp

<x-filament-panels::page>
    <div class="flex flex-col lg:flex-row gap-6">
        <!-- DIV 1 - Categorías (Izquierda) -->
        <div class="w-full lg:w-1/3 flex-shrink-0">
            <x-filament::section>
                <h2 class="text-xl font-bold tracking-tight mb-6">Categorías de Reportes</h2>
                
                <div class="space-y-4">
                    <!-- Informe de Ventas -->
                    <div class="p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                         wire:click="selectCategory('sales')"
                         x-data="{ selected: @entangle('selectedCategory') }"
                         :class="selected === 'sales' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700'">
                        <h3 class="font-semibold text-gray-900 dark:text-white">📊 Informe de Ventas</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic mt-1">
                            Diversos informes de las ventas realizadas.
                        </p>
                    </div>

                    <!-- Informe de Compras -->
                    <div class="p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                         wire:click="selectCategory('purchases')"
                         x-data="{ selected: @entangle('selectedCategory') }"
                         :class="selected === 'purchases' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700'">
                        <h3 class="font-semibold text-gray-900 dark:text-white">💰 Informe de Compras</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic mt-1">
                            Diversos informes de las compras realizadas en la empresa.
                        </p>
                    </div>

                    <!-- Informe de Finanzas -->
                    <div class="p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                         wire:click="selectCategory('finance')"
                         x-data="{ selected: @entangle('selectedCategory') }"
                         :class="selected === 'finance' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700'">
                        <h3 class="font-semibold text-gray-900 dark:text-white">💳 Informe de Finanzas</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic mt-1">
                            Todo concerniente al flujo de dinero en las cajas.
                        </p>
                    </div>

                    <!-- Información de Operaciones -->
                    <div class="p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors"
                         wire:click="selectCategory('operations')"
                         x-data="{ selected: @entangle('selectedCategory') }"
                         :class="selected === 'operations' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700'">
                        <h3 class="font-semibold text-gray-900 dark:text-white">⚙️ Información de Operaciones</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 italic mt-1">
                            Todo lo concerniente a las operaciones realizadas por los usuarios.
                        </p>
                    </div>
                </div>
            </x-filament::section>
        </div>

        <!-- DIV 2 - Reportes Específicos (Derecha) -->
        <div class="w-full lg:w-2/3 flex-grow">
            <x-filament::section>
                @if($this->selectedCategory)
                    <h2 class="text-xl font-bold tracking-tight mb-6">
                        {{ $this->getCategoryTitle() }}
                    </h2>
                    
                    <!-- Reportes específicos según categoría -->
                    <div class="space-y-4 mb-6">
                        @foreach($this->getReportsForCategory() as $reportKey => $reportTitle)
                            <div class="p-4 border rounded-lg cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors group"
                                 wire:click="openReport('{{ $reportKey }}')"
                                 x-data="{ selected: @entangle('selectedReport') }"
                                 :class="selected === '{{ $reportKey }}' ? 'border-primary-500 bg-primary-50 dark:bg-primary-900/20' : 'border-gray-200 dark:border-gray-700'">
                                <div class="flex items-center justify-between">
                                    <h4 class="font-medium text-gray-900 dark:text-white">{{ $reportTitle }}</h4>
                                    <x-filament::icon
                                        alias="heroicon-o-arrow-top-right-on-square"
                                        class="h-4 w-4 text-gray-400 group-hover:text-primary-500 transition-colors"
                                    />
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($this->selectedReport)
                        <!-- Filtros y configuración -->
                        <div class="border-t pt-6">
                            <h3 class="text-lg font-semibold mb-4">Configuración del Reporte</h3>
                            
                            <form wire:submit="exportReport">
                                {{ $this->form }}

                                <div class="mt-6">
                                    <x-filament::button type="submit" size="lg">
                                        <span class="flex items-center gap-1">
                                            <x-filament::icon
                                                alias="heroicon-m-arrow-down-tray"
                                                class="h-5 w-5"
                                            />
                                            Exportar Reporte
                                        </span>
                                    </x-filament::button>
                                </div>
                            </form>
                        </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="text-gray-400 dark:text-gray-500 mb-4">
                            <x-filament::icon
                                alias="heroicon-o-document-chart-bar"
                                class="h-16 w-16 mx-auto"
                            />
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Selecciona una categoría
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Elige una categoría de la izquierda para ver los reportes disponibles.
                        </p>
                    </div>
                @endif
            </x-filament::section>
        </div>
    </div>



    @push('scripts')
        <script>
            // Función para mostrar información de depuración (deshabilitada)
            function showDebug(message) {
                // Debug deshabilitado
            }

            // Función mejorada para descargar archivos
            function downloadFile(content, filename, mimeType) {
                try {
                    // Decodificar el contenido base64
                    const binaryData = atob(content);

                    // Convertir a array de bytes
                    const bytes = new Uint8Array(binaryData.length);
                    for (let i = 0; i < binaryData.length; i++) {
                        bytes[i] = binaryData.charCodeAt(i);
                    }

                    // Crear blob
                    const blob = new Blob([bytes.buffer], { type: mimeType });

                    // Crear URL del blob
                    const blobUrl = URL.createObjectURL(blob);

                    // Crear enlace de descarga
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = filename;
                    link.style.display = 'none';

                    // Añadir al DOM, hacer clic y limpiar
                    document.body.appendChild(link);

                    // Usar setTimeout para asegurar que el enlace se procese
                    setTimeout(() => {
                        link.click();

                        // Limpiar después de un breve retraso
                        setTimeout(() => {
                            document.body.removeChild(link);
                            URL.revokeObjectURL(blobUrl);
                        }, 100);
                    }, 100);
                } catch (error) {
                    console.error('Error al descargar archivo:', error);
                }
            }

            document.addEventListener('livewire:initialized', () => {

                Livewire.on('download-pdf', (data) => {
                    downloadFile(data.content, data.filename, 'application/pdf');
                });

                Livewire.on('download-excel', (data) => {
                    downloadFile(data.content, data.filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                });

                Livewire.on('show-notification', (data) => {
                    // No hacemos nada aquí, las notificaciones se manejan en el backend
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
