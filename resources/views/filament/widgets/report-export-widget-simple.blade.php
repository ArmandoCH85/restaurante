<x-filament::section>
    <div class="space-y-6">
        <h2 class="text-xl font-bold tracking-tight">Exportar Reportes</h2>
        
        <p class="text-gray-500 dark:text-gray-400">
            Selecciona el tipo de reporte, período y formato para exportar los datos.
        </p>
        
        <form wire:submit="exportReport" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-2">
                    <label for="reportType" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tipo de Reporte
                    </label>
                    <select
                        wire:model="reportType"
                        id="reportType"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    >
                        <option value="sales">Ventas</option>
                        <option value="profits">Ganancias</option>
                        <option value="products">Productos Vendidos</option>
                        <option value="service_types">Ventas por Tipo de Servicio</option>
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label for="dateRange" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Período
                    </label>
                    <select
                        wire:model.live="dateRange"
                        id="dateRange"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    >
                        <option value="today">Hoy</option>
                        <option value="yesterday">Ayer</option>
                        <option value="week">Esta semana</option>
                        <option value="month">Este mes</option>
                        <option value="custom">Personalizado</option>
                    </select>
                </div>
                
                @if($dateRange === 'custom')
                <div class="space-y-2">
                    <label for="startDate" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha Inicio
                    </label>
                    <input
                        type="date"
                        wire:model="startDate"
                        id="startDate"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    />
                </div>
                
                <div class="space-y-2">
                    <label for="endDate" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Fecha Fin
                    </label>
                    <input
                        type="date"
                        wire:model="endDate"
                        id="endDate"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    />
                </div>
                @endif
                
                <div class="space-y-2">
                    <label for="format" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Formato
                    </label>
                    <select
                        wire:model="format"
                        id="format"
                        class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-700 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-1 focus:ring-inset focus:ring-primary-500 disabled:opacity-70 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:focus:border-primary-500"
                    >
                        <option value="pdf">PDF</option>
                        <option value="excel">Excel</option>
                    </select>
                </div>
            </div>
            
            <div>
                <button 
                    type="submit" 
                    class="inline-flex items-center justify-center gap-1 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition duration-75 hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-70 dark:bg-primary-500 dark:hover:bg-primary-400 dark:focus:ring-primary-400 dark:focus:ring-offset-0"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Exportar Reporte
                </button>
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
