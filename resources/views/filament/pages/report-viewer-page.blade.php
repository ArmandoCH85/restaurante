<!DOCTYPE html>
<html lang="es" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->getTitle() }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        
        /* Estilos adicionales para mejorar la apariencia */
        .bg-primary-600 { background-color: #2563eb; }
        .text-primary-600 { color: #2563eb; }
        .border-primary-600 { border-color: #2563eb; }
        .hover\:bg-primary-700:hover { background-color: #1d4ed8; }
        .focus\:ring-primary-500:focus { --tw-ring-color: #3b82f6; }
        
        /* Responsive table */
        @media (max-width: 768px) {
            .table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
        
        /* Ajustes específicos para la columna Cliente */
        .customer-column {
            min-width: 200px;
            max-width: 300px;
            word-wrap: break-word;
            white-space: normal;
            line-height: 1.3;
        }
        
        .customer-name {
            display: block;
            white-space: normal;
            word-wrap: break-word;
            line-height: 1.3;
        }
        
        /* Columna Cliente estática/fija */
        .table-container {
            position: relative;
            overflow-x: auto;
            max-width: 100%;
        }
        
        .sticky-customer-header {
            position: sticky;
            left: 0;
            z-index: 10;
            background-color: #f9fafb;
            border-right: 2px solid #e5e7eb;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
        }
        
        .sticky-customer-cell {
            position: sticky;
            left: 0;
            z-index: 5;
            background-color: white;
            border-right: 2px solid #e5e7eb;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.05);
        }
        
        .sticky-customer-cell:hover {
            background-color: #f9fafb;
        }
        
        /* Asegurar que el resto de la tabla tenga scroll */
        .table-container table {
            min-width: 1200px; /* Fuerza scroll horizontal si es necesario */
        }
        
        /* Mejora visual para pantallas pequeñas */
        @media (max-width: 1024px) {
            .table-container table {
                min-width: 1400px;
            }
        }
    </style>
</head>
<body class="h-full">
    <div class="min-h-full">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $page->getTitle() }}</h1>
                    <a href="{{ route('filament.admin.pages.reportes') }}" 
                       class="inline-flex items-center px-4 py-2 border border-green-600 rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                        ← Volver a Reportes
                    </a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <div class="px-4 py-6 sm:px-0">
                    
                    <!-- Stats Cards -->
                    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                        <div class="p-6">
                            <div class="overflow-x-auto">
                                <table class="w-full">
                                    <tr>
                                        <td class="text-center px-4 py-2">
                                            <div class="text-3xl font-bold text-blue-600">{{ number_format($page->reportStats['total_operations'] ?? 0) }}</div>
                                        </td>
                                        <td class="text-center px-4 py-2">
                                            <div class="text-3xl font-bold text-green-600">S/ {{ number_format($page->reportStats['total_sales'] ?? 0, 2) }}</div>
                                        </td>
                                        <td class="text-center px-4 py-2">
                                            <div class="text-3xl font-bold text-purple-600">S/ {{ number_format($page->reportStats['total_sales_notes'] ?? 0, 2) }}</div>
                                        </td>
                                        <td class="text-center px-4 py-2">
                                            <div class="text-3xl font-bold text-yellow-600">S/ {{ number_format($page->reportStats['total_receipts'] ?? 0, 2) }}</div>
                                        </td>
                                        <td class="text-center px-4 py-2">
                                            <div class="text-3xl font-bold text-indigo-600">S/ {{ number_format($page->reportStats['total_invoices'] ?? 0, 2) }}</div>
                                        </td>
                                        <td class="text-center px-4 py-2">
                                            <div class="text-3xl font-bold text-red-600">S/ {{ number_format($page->reportStats['total_cancelled'] ?? 0, 2) }}</div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center px-4 py-1">
                                            <div class="text-sm text-gray-600 font-medium">N° Operaciones</div>
                                        </td>
                                        <td class="text-center px-4 py-1">
                                            <div class="text-sm text-gray-600 font-medium">Total Ventas</div>
                                        </td>
                                        <td class="text-center px-4 py-1">
                                            <div class="text-sm text-gray-600 font-medium">Total Notas de Venta</div>
                                        </td>
                                        <td class="text-center px-4 py-1">
                                            <div class="text-sm text-gray-600 font-medium">Total Boletas</div>
                                        </td>
                                        <td class="text-center px-4 py-1">
                                            <div class="text-sm text-gray-600 font-medium">Total Facturas</div>
                                        </td>
                                        <td class="text-center px-4 py-1">
                                            <div class="text-sm text-gray-600 font-medium">Total Anulados</div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">🔍 Filtros de Reporte</h3>

                            <!-- Botones rápidos -->
                            <div class="mb-6">
                                <p class="text-sm font-medium mb-3">🗓️ Filtros Rápidos</p>
                                <div class="flex flex-wrap gap-2">
                                    @php
                                        $baseUrl = route('filament.admin.pages.report-viewer', ['category' => $page->category, 'reportType' => $page->reportType]);
                                        $filters = [
                                            'today' => '📅 Hoy',
                                            'yesterday' => '📅 Ayer', 
                                            'week' => '📅 Esta Semana',
                                            'month' => '📅 Este Mes'
                                        ];
                                    @endphp
                                    
                                    @foreach($filters as $value => $label)
                                        <a href="{{ $baseUrl }}?dateRange={{ $value }}"
                                           class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg border transition-colors duration-200
                                                {{ $page->dateRange === $value 
                                                    ? 'bg-blue-600 text-white border-blue-600' 
                                                    : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50' 
                                                }}">
                                            {{ $label }}
                                        </a>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Filtro Personalizado -->
                            <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm font-medium mb-3">🎯 Filtro Personalizado</p>
                                <form method="GET" action="{{ $baseUrl }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                                    <input type="hidden" name="dateRange" value="custom">
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">📅 Fecha Inicio</label>
                                        <input type="date" 
                                               name="startDate" 
                                               value="{{ request('startDate', $page->startDate) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">📅 Fecha Fin</label>
                                        <input type="date" 
                                               name="endDate" 
                                               value="{{ request('endDate', $page->endDate) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                               required>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">🕐 Hora Inicio (opcional)</label>
                                        <input type="time" 
                                               name="startTime" 
                                               value="{{ request('startTime', $page->startTime) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">🕐 Hora Fin (opcional)</label>
                                        <input type="time" 
                                               name="endTime" 
                                               value="{{ request('endTime', $page->endTime) }}"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">📄 Tipo Comprobante (opcional)</label>
                                        <select name="invoiceType" 
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Todos los tipos</option>
                                            <option value="sales_note" {{ request('invoiceType') === 'sales_note' ? 'selected' : '' }}>📝 Nota de Venta</option>
                                            <option value="receipt" {{ request('invoiceType') === 'receipt' ? 'selected' : '' }}>🧾 Boleta</option>
                                            <option value="invoice" {{ request('invoiceType') === 'invoice' ? 'selected' : '' }}>📋 Factura</option>
                                        </select>
                                    </div>
                                    
                                    <div class="md:col-span-5">
                                        <button type="submit" 
                                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                            🔍 Aplicar Filtro Personalizado
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Formulario de exportación -->
                            <div class="flex gap-4 mt-6">
                                <button 
                                    type="button"
                                    onclick="alert('Función de exportar en desarrollo')"
                                    class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-700 focus:bg-green-700 active:bg-green-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    📥 Exportar
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Datos -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                📊 Datos del Reporte ({{ $page->reportData->count() }} registros)
                                <span class="text-sm text-gray-500 font-normal">
                                    - Período: {{ $page->reportStats['period'] ?? 'N/A' }}
                                    @if(request('invoiceType'))
                                        @php
                                            $typeLabels = [
                                                'sales_note' => '📝 Notas de Venta',
                                                'receipt' => '🧾 Boletas', 
                                                'invoice' => '📋 Facturas'
                                            ];
                                        @endphp
                                        - Filtro: {{ $typeLabels[request('invoiceType')] ?? 'Tipo desconocido' }}
                                    @endif
                                </span>
                            </h3>

                            @if($page->reportData->isNotEmpty())
                                <div class="table-container">
                                    <table class="min-w-full divide-y divide-gray-200 table-fixed">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                @if($page->reportType === 'all_sales' || $page->reportType === 'delivery_sales')
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha | Hora</th>
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Caja</th>
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider sticky-customer-header" style="min-width: 200px; max-width: 300px;">Cliente</th>
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Documento</th>
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Canal venta</th>
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo pago</th>
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                                    <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Opciones</th>
                                                @elseif($page->reportType === 'sales_by_waiter' || $page->reportType === 'sales_by_user')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Órdenes</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                                                @elseif($page->reportType === 'products_by_channel')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Producto</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Canal de Venta</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                                                @elseif($page->reportType === 'payment_methods')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Forma de Pago</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Operaciones</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                                                @elseif($page->reportType === 'all_purchases')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                @elseif($page->reportType === 'purchases_by_supplier')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proveedor</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Compras</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Compras</th>
                                                @elseif($page->reportType === 'purchases_by_category')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoría</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cantidad</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                                @elseif($page->reportType === 'cash_register')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Apertura</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario Apertura</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Inicial</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                                @elseif($page->reportType === 'profits')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Ventas</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">N° Órdenes</th>
                                                @elseif($page->reportType === 'daily_closing')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Cierre</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario Cierre</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Monto Final</th>
                                                @elseif($page->reportType === 'user_activity')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último Login</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Órdenes Creadas</th>
                                                @elseif($page->reportType === 'system_logs')
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Evento</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Descripción</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($page->reportData as $item)
                                                <tr class="hover:bg-gray-50">
                                                    @if($page->reportType === 'all_sales' || $page->reportType === 'delivery_sales')
                                                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                                                            <div class="flex items-center space-x-2">
                                                                <span>{{ $item->order_datetime->format('d/m/Y') }}</span>
                                                                <span class="text-gray-600">{{ $item->order_datetime->format('H:i') }}</span>
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-4 whitespace-nowrap text-sm">{{ $item->cashRegister?->name ?? 'C01' }}</td>
                                                        <td class="px-3 py-4 text-sm sticky-customer-cell" style="min-width: 200px; max-width: 300px;">
                                                            @php
                                                                $customerName = null;
                                                                $isComandaClient = false;
                                                                
                                                                // 1. Verificar cliente formal en order
                                                                if ($item->customer?->name) {
                                                                    $customerName = $item->customer->name;
                                                                }
                                                                // 2. Verificar cliente formal en invoice
                                                                elseif ($item->invoices->isNotEmpty() && $item->invoices->first()->customer?->name) {
                                                                    $customerName = $item->invoices->first()->customer->name;
                                                                }
                                                                // 3. Verificar client_name en invoice (comanda rápida)
                                                                elseif ($item->invoices->isNotEmpty() && $item->invoices->first()->client_name) {
                                                                    $customerName = $item->invoices->first()->client_name;
                                                                    $isComandaClient = true;
                                                                }
                                                                // 4. Extraer de notes en order (comanda rápida)
                                                                elseif ($item->notes && str_contains($item->notes, 'Cliente:')) {
                                                                    $customerName = trim(str_replace('Cliente:', '', $item->notes));
                                                                    $isComandaClient = true;
                                                                }
                                                            @endphp
                                                            
                                                            @if($customerName)
                                                                @if($isComandaClient)
                                                                    <span class="customer-name text-purple-600 font-medium" title="Cliente de comanda rápida">{{ $customerName }}</span>
                                                                @else
                                                                    <span class="customer-name" title="Cliente registrado">{{ $customerName }}</span>
                                                                @endif
                                                            @elseif($item->table?->number)
                                                                <span class="customer-name text-blue-600 font-medium" title="Mesa sin cliente">Mesa #{{ $item->table->number }}</span>
                                                            @else
                                                                <span class="customer-name text-gray-500" title="Venta sin datos de cliente">Sin cliente</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-4 whitespace-nowrap text-sm font-medium">
                                                            @if($item->invoices->isNotEmpty())
                                                                @php $invoice = $item->invoices->first(); @endphp
                                                                <div class="flex items-center space-x-2">
                                                                    @php
                                                                        // Detectar tipo real basándose en la serie del documento
                                                                        $actualType = $invoice->invoice_type;
                                                                        
                                                                        // Corrección automática basada en la serie
                                                                        if (str_starts_with($invoice->series, 'NV')) {
                                                                            $actualType = 'sales_note';
                                                                        } elseif (str_starts_with($invoice->series, 'B')) {
                                                                            $actualType = 'receipt'; 
                                                                        } elseif (str_starts_with($invoice->series, 'F')) {
                                                                            $actualType = 'invoice';
                                                                        }
                                                                        
                                                                        // Mapeo explícito de tipos de documento
                                                                        $documentTypes = [
                                                                            'sales_note' => ['label' => '📝 NV', 'class' => 'bg-yellow-100 text-yellow-800'],
                                                                            'receipt' => ['label' => '🧾 BOL', 'class' => 'bg-blue-100 text-blue-800'],
                                                                            'invoice' => ['label' => '📋 FAC', 'class' => 'bg-green-100 text-green-800']
                                                                        ];
                                                                        
                                                                        $docType = $documentTypes[$actualType] ?? ['label' => '❓ ' . strtoupper($actualType), 'class' => 'bg-gray-100 text-gray-800'];
                                                                    @endphp
                                                                    
                                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $docType['class'] }}" 
                                                                          title="Tipo: {{ $invoice->invoice_type }}">
                                                                        {{ $docType['label'] }}
                                                                    </span>
                                                                    <span>{{ $invoice->series }}-{{ str_pad($invoice->number, 6, '0', STR_PAD_LEFT) }}</span>
                                                                </div>
                                                            @else
                                                                <span class="text-gray-500">Orden #{{ $item->id }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                {{ $item->service_type === 'dine_in' ? 'bg-green-100 text-green-800' : 
                                                                   ($item->service_type === 'delivery' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                                {{ $item->service_type === 'dine_in' ? 'En Local' : 
                                                                   ($item->service_type === 'delivery' ? 'Delivery' : 'Para Llevar') }}
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                                                            {{ $item->payment_method ?? 'Efectivo' }}
                                                        </td>
                                                        <td class="px-3 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                                            S/ {{ number_format($item->total, 2) }}
                                                        </td>
                                                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                {{ $item->status ?? 'Completado' }}
                                                            </span>
                                                        </td>
                                                        <td class="px-3 py-4 whitespace-nowrap text-sm">
                                                            <div class="flex gap-2">
                                                                <button
                                                                    type="button"
                                                                    onclick="openOrderModal({{ $item->id }})"
                                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-blue-600 border border-transparent rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                                                    title="Ver Detalle"
                                                                >
                                                                    👁️
                                                                </button>
                                                                <button
                                                                    type="button"
                                                                    onclick="printOrder({{ $item->id }})"
                                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium text-white bg-green-600 border border-transparent rounded hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                                                    title="Imprimir"
                                                                >
                                                                    🖨️
                                                                </button>
                                                            </div>
                                                        </td>
                                                    @elseif($page->reportType === 'sales_by_waiter' || $page->reportType === 'sales_by_user')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">{{ $item->name }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->total_orders }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold">S/ {{ number_format($item->total_sales, 2) }}</td>
                                                    @elseif($page->reportType === 'products_by_channel')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">{{ $item->product_name }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                {{ $item->service_type === 'dine_in' ? 'bg-green-100 text-green-800' : 
                                                                   ($item->service_type === 'delivery' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                                {{ $item->service_type === 'dine_in' ? 'En Local' : 
                                                                   ($item->service_type === 'delivery' ? 'Delivery' : 'Para Llevar') }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ number_format($item->total_quantity) }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600">S/ {{ number_format($item->total_sales, 2) }}</td>
                                                    @elseif($page->reportType === 'payment_methods')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                {{ $item->payment_method === 'cash' ? 'bg-green-100 text-green-800' : 
                                                                   ($item->payment_method === 'card' ? 'bg-blue-100 text-blue-800' : 
                                                                   ($item->payment_method === 'transfer' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800')) }}">
                                                                💰 {{ ucfirst($item->payment_method ?? 'Sin especificar') }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ number_format($item->total_orders) }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600">S/ {{ number_format($item->total_sales, 2) }}</td>
                                                    @elseif($page->reportType === 'all_purchases')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->purchase_date->format('d/m/Y') }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->supplier?->name ?? 'Sin proveedor' }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->user?->name ?? 'Sin usuario' }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600">S/ {{ number_format($item->total, 2) }}</td>
                                                    @elseif($page->reportType === 'purchases_by_supplier')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">{{ $item->supplier_name }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ number_format($item->total_purchases) }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600">S/ {{ number_format($item->total_amount, 2) }}</td>
                                                    @elseif($page->reportType === 'purchases_by_category')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">{{ $item->category_name }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ number_format($item->total_quantity) }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600">S/ {{ number_format($item->total_amount, 2) }}</td>
                                                    @elseif($page->reportType === 'cash_register')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->opening_datetime->format('d/m/Y H:i') }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->openedBy?->name ?? 'Sin usuario' }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold">S/ {{ number_format($item->opening_amount, 2) }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                                {{ $item->closing_datetime ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                                {{ $item->closing_datetime ? 'Cerrada' : 'Abierta' }}
                                                            </span>
                                                        </td>
                                                    @elseif($page->reportType === 'profits')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600">S/ {{ number_format($item->total_sales, 2) }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ number_format($item->total_orders) }}</td>
                                                    @elseif($page->reportType === 'daily_closing')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->closing_datetime->format('d/m/Y H:i') }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->closedBy?->name ?? 'Sin usuario' }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-semibold text-green-600">S/ {{ number_format($item->actual_amount ?? 0, 2) }}</td>
                                                    @elseif($page->reportType === 'user_activity')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">{{ $item->name }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->email }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->last_login_at ? $item->last_login_at->format('d/m/Y H:i') : 'Nunca' }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ number_format($item->orders_created) }}</td>
                                                    @elseif($page->reportType === 'system_logs')
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->date }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm font-medium">{{ $item->event }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->user }}</td>
                                                        <td class="px-4 py-4 whitespace-nowrap text-sm">{{ $item->description }}</td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="h-16 w-16 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mt-4 text-lg font-medium">No hay datos</h3>
                                    <p class="text-gray-500">No se encontraron registros para el período seleccionado.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <!-- Modal para Ver Detalle -->
    <div id="orderModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Header del Modal -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900">📋 Detalle de la Orden</h3>
                    <button onclick="closeOrderModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Contenido del Modal -->
                <div id="orderModalContent" class="max-h-96 overflow-y-auto">
                    <div class="flex items-center justify-center p-8">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <span class="ml-2">Cargando detalle...</span>
                    </div>
                </div>
                
                <!-- Footer del Modal -->
                <div class="flex justify-end mt-4 pt-4 border-t">
                    <button onclick="closeOrderModal()" 
                            class="px-4 py-2 bg-gray-500 text-white rounded hover:bg-gray-600">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para abrir el modal de detalle
        function openOrderModal(orderId) {
            const modal = document.getElementById('orderModal');
            const content = document.getElementById('orderModalContent');
            
            // Mostrar modal
            modal.classList.remove('hidden');
            
            // Cargar contenido
            fetch(`/admin/orders/${orderId}/detail`)
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="text-center p-4">
                            <div class="text-red-600 mb-2">❌ Error al cargar el detalle</div>
                            <div class="text-sm text-gray-500">${error.message}</div>
                        </div>
                    `;
                });
        }
        
        // Función para cerrar el modal
        function closeOrderModal() {
            document.getElementById('orderModal').classList.add('hidden');
        }
        
        // Función para imprimir orden
        function printOrder(orderId) {
            // Abrir ventana de impresión
            const printUrl = `/admin/orders/${orderId}/print`;
            window.open(printUrl, '_blank', 'width=800,height=600,scrollbars=yes,resizable=yes');
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeOrderModal();
            }
        });
        
        // Cerrar modal con tecla Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeOrderModal();
            }
        });
    </script>

    @livewireScripts
</body>
</html>