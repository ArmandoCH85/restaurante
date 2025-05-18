@php
    $heading = 'Reportes';
@endphp

<x-filament-panels::page>
    <x-filament::section>
        <h2 class="text-xl font-bold tracking-tight">Exportar Reportes</h2>

        <p class="text-gray-500 dark:text-gray-400 mb-6">
            Selecciona el tipo de reporte, período y formato para exportar los datos.
        </p>

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
    </x-filament::section>

    <div id="debug-info" class="mt-4 p-4 bg-gray-100 dark:bg-gray-800 rounded-lg hidden">
        <h3 class="text-lg font-medium mb-2">Información de depuración</h3>
        <div id="debug-content" class="text-sm font-mono"></div>
    </div>

    @push('scripts')
        <script>
            // Función para mostrar información de depuración
            function showDebug(message) {
                const debugInfo = document.getElementById('debug-info');
                const debugContent = document.getElementById('debug-content');

                if (debugInfo && debugContent) {
                    debugInfo.classList.remove('hidden');
                    debugContent.innerHTML += `<div>${message}</div>`;
                }
            }

            // Función mejorada para descargar archivos
            function downloadFile(content, filename, mimeType) {
                try {
                    showDebug(`Iniciando descarga de ${filename}`);

                    // Decodificar el contenido base64
                    const binaryData = atob(content);
                    showDebug(`Contenido decodificado: ${binaryData.length} bytes`);

                    // Convertir a array de bytes
                    const bytes = new Uint8Array(binaryData.length);
                    for (let i = 0; i < binaryData.length; i++) {
                        bytes[i] = binaryData.charCodeAt(i);
                    }

                    // Crear blob
                    const blob = new Blob([bytes.buffer], { type: mimeType });
                    showDebug(`Blob creado: ${blob.size} bytes`);

                    // Crear URL del blob
                    const blobUrl = URL.createObjectURL(blob);
                    showDebug(`URL del blob: ${blobUrl}`);

                    // Crear enlace de descarga
                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = filename;
                    link.style.display = 'none';

                    // Añadir al DOM, hacer clic y limpiar
                    document.body.appendChild(link);
                    showDebug('Enlace añadido al DOM');

                    // Usar setTimeout para asegurar que el enlace se procese
                    setTimeout(() => {
                        showDebug('Haciendo clic en el enlace');
                        link.click();

                        // Limpiar después de un breve retraso
                        setTimeout(() => {
                            document.body.removeChild(link);
                            URL.revokeObjectURL(blobUrl);
                            showDebug('Enlace eliminado y URL liberada');
                        }, 100);
                    }, 100);
                } catch (error) {
                    showDebug(`Error: ${error.message}`);
                    console.error('Error al descargar archivo:', error);
                }
            }

            document.addEventListener('livewire:initialized', () => {
                showDebug('Livewire inicializado');

                Livewire.on('download-pdf', (data) => {
                    showDebug('Evento download-pdf recibido');
                    downloadFile(data.content, data.filename, 'application/pdf');
                });

                Livewire.on('download-excel', (data) => {
                    showDebug('Evento download-excel recibido');
                    downloadFile(data.content, data.filename, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                });

                Livewire.on('show-notification', (data) => {
                    showDebug('Mostrando notificación: ' + data.message);

                    // No hacemos nada aquí, las notificaciones se manejan en el backend
                });
            });
        </script>
    @endpush
</x-filament-panels::page>
