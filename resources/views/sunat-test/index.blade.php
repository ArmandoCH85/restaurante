<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🧪 Prueba de Facturación SUNAT - Sistema Restaurante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .log-container {
            max-height: 400px;
            overflow-y: auto;
        }
        .log-line {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            line-height: 1.4;
        }
        .log-error {
            color: #ef4444;
        }
        .log-info {
            color: #3b82f6;
        }
        .log-warning {
            color: #f59e0b;
        }
        .spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100" x-data="sunatTest()">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">
                🧪 Prueba de Facturación SUNAT
            </h1>
            <p class="text-gray-600">
                Sistema de prueba para envío de comprobantes electrónicos a SUNAT usando Greenter
            </p>
            <div class="mt-4 flex items-center space-x-4">
                <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                    📡 Integración Greenter
                </span>
                <span class="bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm">
                    🔄 Numeración Automática
                </span>
                <span class="bg-purple-100 text-purple-800 px-3 py-1 rounded-full text-sm">
                    📊 Logs Detallados
                </span>
            </div>
        </div>

        @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <strong>❌ Error:</strong> {{ $error }}
        </div>
        @endif

        @if($testData)
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Panel de Datos de Prueba -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">
                    📋 Datos de Prueba Generados
                </h2>
                
                <!-- Tipo de Comprobante -->
                <div class="mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">Tipo de Comprobante:</h3>
                    <span class="inline-block bg-{{ $testData['invoice_type'] === 'invoice' ? 'blue' : 'green' }}-100 text-{{ $testData['invoice_type'] === 'invoice' ? 'blue' : 'green' }}-800 px-3 py-1 rounded-full">
                        {{ $testData['invoice_type'] === 'invoice' ? '📄 Factura' : '🧾 Boleta' }}
                    </span>
                </div>

                <!-- Datos del Cliente -->
                <div class="mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">👤 Cliente:</h3>
                    <div class="bg-gray-50 p-3 rounded">
                        <p><strong>Nombre:</strong> {{ $testData['customer']['name'] }}</p>
                        <p><strong>{{ $testData['customer']['document_type'] }}:</strong> {{ $testData['customer']['document_number'] }}</p>
                        <p><strong>Dirección:</strong> {{ $testData['customer']['address'] }}</p>
                        <p><strong>Email:</strong> {{ $testData['customer']['email'] }}</p>
                    </div>
                </div>

                <!-- Productos -->
                <div class="mb-4">
                    <h3 class="font-medium text-gray-700 mb-2">🍽️ Productos ({{ count($testData['products']) }}):</h3>
                    <div class="space-y-2">
                        @foreach($testData['products'] as $product)
                        <div class="bg-gray-50 p-3 rounded flex justify-between">
                            <div>
                                <p class="font-medium">{{ $product['product_name'] }}</p>
                                <p class="text-sm text-gray-600">Cantidad: {{ $product['quantity'] }} × S/ {{ number_format($product['unit_price'], 2) }}</p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium">S/ {{ number_format($product['total'], 2) }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Totales -->
                <div class="border-t pt-4">
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>S/ {{ number_format($testData['totals']['subtotal'], 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>IGV (18%):</span>
                            <span>S/ {{ number_format($testData['totals']['igv'], 2) }}</span>
                        </div>
                        <div class="flex justify-between font-bold text-lg border-t pt-2">
                            <span>Total:</span>
                            <span>S/ {{ number_format($testData['totals']['total'], 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Botón de Envío -->
                <div class="mt-6">
                    <button 
                        @click="sendToSunat()"
                        :disabled="loading"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-3 px-4 rounded-lg transition duration-200 flex items-center justify-center"
                    >
                        <div x-show="loading" class="spinner mr-2"></div>
                        <span x-text="loading ? 'Enviando a SUNAT...' : '🚀 Enviar a SUNAT'"></span>
                    </button>
                </div>
            </div>

            <!-- Panel de Resultados y Logs -->
            <div class="space-y-6">
                <!-- Resultado del Envío -->
                <div x-show="result" class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold text-gray-800 mb-4">
                        📤 Resultado del Envío
                    </h2>
                    <div x-show="result && result.success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        <div class="flex items-center">
                            <span class="text-2xl mr-2">✅</span>
                            <div>
                                <p class="font-bold">¡Envío Exitoso!</p>
                                <p x-text="result ? result.message : ''"></p>
                            </div>
                        </div>
                    </div>
                    <div x-show="result && !result.success" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <div class="flex items-center">
                            <span class="text-2xl mr-2">❌</span>
                            <div>
                                <p class="font-bold">Error en el Envío</p>
                                <p x-text="result ? result.message : ''"></p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Detalles del Resultado -->
                    <div x-show="result" class="space-y-3">
                        <div>
                            <strong>ID Prueba:</strong> <code x-text="result ? result.test_id : ''"></code>
                        </div>
                        <div>
                            <strong>Factura:</strong> <span x-text="result ? result.series_number : ''"></span>
                        </div>
                        <div>
                            <strong>Estado SUNAT:</strong> 
                            <span x-text="result ? result.sunat_status : ''" 
                                  :class="result && result.sunat_status === 'ACEPTADO' ? 'text-green-600 font-bold' : 'text-yellow-600 font-bold'"></span>
                        </div>
                        <div x-show="result && result.sunat_code">
                            <strong>Código SUNAT:</strong> <span x-text="result ? result.sunat_code : ''"></span>
                        </div>
                        <div x-show="result && result.sunat_description">
                            <strong>Descripción:</strong> <span x-text="result ? result.sunat_description : ''"></span>
                        </div>
                        
                        <!-- Archivos Generados -->
                        <div x-show="result && result.files" class="mt-4">
                            <h4 class="font-medium mb-2">📁 Archivos Generados:</h4>
                            <div class="text-sm space-y-1">
                                <div x-show="result && result.files && result.files.xml_path">
                                    <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded">XML</span>
                                    <span x-text="result ? result.files.xml_path : ''"></span>
                                </div>
                                <div x-show="result && result.files && result.files.pdf_path">
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded">PDF</span>
                                    <span x-text="result ? result.files.pdf_path : ''"></span>
                                </div>
                                <div x-show="result && result.files && result.files.cdr_path">
                                    <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded">CDR</span>
                                    <span x-text="result ? result.files.cdr_path : ''"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Panel de Logs -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">
                            📊 Logs del Sistema
                        </h2>
                        <button 
                            @click="refreshLogs()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1 rounded text-sm"
                        >
                            🔄 Actualizar
                        </button>
                    </div>
                    <div class="log-container bg-gray-900 text-white p-4 rounded">
                        <div x-show="logs.length === 0" class="text-gray-400">
                            No hay logs disponibles...
                        </div>
                        <template x-for="log in logs" :key="log">
                            <div class="log-line mb-1" 
                                 :class="{
                                     'log-error': log.includes('ERROR') || log.includes('🚨'),
                                     'log-info': log.includes('INFO') || log.includes('🧪'),
                                     'log-warning': log.includes('WARNING') || log.includes('⚠️')
                                 }"
                                 x-text="log">
                            </div>
                        </template>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span x-text="'Última actualización: ' + lastLogUpdate"></span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Botones de Utilidad -->
        <div class="mt-6 flex justify-center space-x-4">
            <a href="{{ route('sunat-test.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">
                🔄 Generar Nuevos Datos
            </a>
            <button 
                @click="refreshLogs()"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded"
            >
                📊 Ver Logs Completos
            </button>
        </div>
    </div>

    <script>
        function sunatTest() {
            return {
                loading: false,
                result: null,
                logs: [],
                lastLogUpdate: '',
                
                async sendToSunat() {
                    this.loading = true;
                    this.result = null;
                    
                    try {
                        const response = await fetch('{{ route("sunat-test.send") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(@json($testData ?? []))
                        });
                        
                        this.result = await response.json();
                        
                        // Actualizar logs después del envío
                        setTimeout(() => {
                            this.refreshLogs();
                        }, 1000);
                        
                    } catch (error) {
                        console.error('Error:', error);
                        this.result = {
                            success: false,
                            message: 'Error de conexión: ' + error.message
                        };
                    } finally {
                        this.loading = false;
                    }
                },
                
                async refreshLogs() {
                    try {
                        const response = await fetch('{{ route("sunat-test.logs") }}');
                        const data = await response.json();
                        this.logs = data.logs || [];
                        this.lastLogUpdate = data.last_update;
                    } catch (error) {
                        console.error('Error loading logs:', error);
                    }
                },
                
                init() {
                    // Cargar logs inicialmente
                    this.refreshLogs();
                    
                    // Auto-refresh logs cada 10 segundos si hay actividad
                    setInterval(() => {
                        if (this.loading) {
                            this.refreshLogs();
                        }
                    }, 10000);
                }
            }
        }
    </script>
</body>
</html>