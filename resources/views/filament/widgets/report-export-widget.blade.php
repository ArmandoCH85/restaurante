<x-filament::section>
    <div class="space-y-6">
        <h2 class="text-xl font-bold tracking-tight">Exportar Reportes</h2>

        <p class="text-gray-500 dark:text-gray-400">
            Selecciona el tipo de reporte, período y formato para exportar los datos.
        </p>

        <form wire:submit="exportReport" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-2">
                    <x-filament::input.label for="reportType">
                        Tipo de Reporte
                    </x-filament::input.label>
                    <x-filament::input.select
                        wire:model="reportType"
                        id="reportType"
                    >
                        <option value="sales">Ventas</option>
                        <option value="profits">Ganancias</option>
                        <option value="products">Productos Vendidos</option>
                        <option value="service_types">Ventas por Tipo de Servicio</option>
                    </x-filament::input.select>
                </div>

                <div class="space-y-2">
                    <x-filament::input.label for="dateRange">
                        Período
                    </x-filament::input.label>
                    <x-filament::input.select
                        wire:model.live="dateRange"
                        id="dateRange"
                    >
                        <option value="today">Hoy</option>
                        <option value="yesterday">Ayer</option>
                        <option value="week">Esta semana</option>
                        <option value="month">Este mes</option>
                        <option value="custom">Personalizado</option>
                    </x-filament::input.select>
                </div>

                @if($dateRange === 'custom')
                <div class="space-y-2">
                    <x-filament::input.label for="startDate">
                        Fecha Inicio
                    </x-filament::input.label>
                    <x-filament::input
                        type="date"
                        wire:model="startDate"
                        id="startDate"
                    />
                </div>

                <div class="space-y-2">
                    <x-filament::input.label for="endDate">
                        Fecha Fin
                    </x-filament::input.label>
                    <x-filament::input
                        type="date"
                        wire:model="endDate"
                        id="endDate"
                    />
                </div>
                @endif

                <div class="space-y-2">
                    <x-filament::input.label for="format">
                        Formato
                    </x-filament::input.label>
                    <x-filament::input.select
                        wire:model="format"
                        id="format"
                    >
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </x-filament::input.select>
                </div>
            </div>

            <div>
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

    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('download-pdf', (data) => {
                // Crear un blob con el contenido del PDF
                const binaryData = atob(data.content);
                const bytes = new Uint8Array(binaryData.length);
                for (let i = 0; i < binaryData.length; i++) {
                    bytes[i] = binaryData.charCodeAt(i);
                }
                const blob = new Blob([bytes.buffer], { type: 'application/pdf' });

                // Crear un enlace temporal para la descarga
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = data.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);
            });

            @this.on('download-excel', (data) => {
                // Crear un blob con el contenido del Excel
                const binaryData = atob(data.content);
                const bytes = new Uint8Array(binaryData.length);
                for (let i = 0; i < binaryData.length; i++) {
                    bytes[i] = binaryData.charCodeAt(i);
                }
                const blob = new Blob([bytes.buffer], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });

                // Crear un enlace temporal para la descarga
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = data.filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                URL.revokeObjectURL(link.href);
            });

            @this.on('notify', (data) => {
                // Usar la notificación nativa de Filament
                window.dispatchEvent(new CustomEvent('notificationSent', {
                    detail: {
                        type: data.type,
                        message: data.message,
                        duration: 5000,
                    }
                }));
            });
        });
    </script>
</x-filament::section>
