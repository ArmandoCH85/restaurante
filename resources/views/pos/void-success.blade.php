<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante Anulado</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
        .print-button {
            padding: 10px 20px;
            background-color: #1a56db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .print-button:hover {
            background-color: #1e429f;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body class="p-6">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
        <div class="text-center mb-6">
            <svg class="w-16 h-16 text-green-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <h1 class="text-2xl font-bold text-gray-800">Comprobante Anulado</h1>
            <p class="text-gray-600 mt-1">El comprobante ha sido anulado exitosamente.</p>
        </div>

        <div class="mb-6 bg-gray-50 p-4 rounded-md">
            <h2 class="text-lg font-semibold mb-3">Información del Comprobante</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-500">Tipo:</p>
                    <p class="font-medium">
                        @if($invoice->invoice_type == 'invoice')
                            Factura
                        @elseif($invoice->invoice_type == 'receipt')
                            Boleta
                        @else
                            Nota de Venta
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Número:</p>
                    <p class="font-medium">{{ $invoice->series }}-{{ $invoice->number }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Fecha de emisión:</p>
                    <p class="font-medium">{{ $invoice->issue_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Fecha de anulación:</p>
                    <p class="font-medium">{{ $invoice->voided_date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Cliente:</p>
                    <p class="font-medium">{{ $invoice->client_name }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Monto:</p>
                    <p class="font-medium">S/ {{ number_format($invoice->total, 2) }}</p>
                </div>
            </div>
        </div>

        <div class="mb-6 bg-gray-50 p-4 rounded-md">
            <h2 class="text-lg font-semibold mb-2">Motivo de Anulación</h2>
            <p>{{ $invoice->voided_reason }}</p>
        </div>

        <div class="mb-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Importante</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>La anulación ha sido registrada en el sistema interno. Recuerde que debe comunicar esta anulación a SUNAT en un plazo máximo de 7 días.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-between no-print">
            <div class="max-w-3xl mx-auto mt-6 flex justify-center">
                <button type="button" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 flex items-center" onclick="window.close()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="mr-2" viewBox="0 0 16 16">
                        <path d="M1 8a7 7 0 1 0 14 0A7 7 0 0 0 1 8zm15 0A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-4.5-.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H11.5z"/>
                    </svg>
                    Cerrar ventana
                </button>
            </div>
            <button onclick="window.print()" class="print-button">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir Confirmación
            </button>
        </div>
    </div>
</body>
</html>
