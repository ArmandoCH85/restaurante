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
