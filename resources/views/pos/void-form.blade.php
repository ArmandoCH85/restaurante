<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anular Comprobante</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }
    </style>
</head>
<body class="p-6">
    <div class="max-w-3xl mx-auto bg-white shadow-md rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Anular Comprobante</h1>
            <a href="{{ route('pos.invoices.list') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                Volver a Comprobantes
            </a>
        </div>

        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Advertencia</h3>
                    <div class="text-sm text-red-700">
                        <p>La anulación de un comprobante es una operación irreversible. El comprobante quedará registrado como anulado tanto en el sistema como en SUNAT.</p>
                    </div>
                </div>
            </div>
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
                    <p class="text-sm text-gray-500">Monto:</p>
                    <p class="font-medium">S/ {{ number_format($invoice->total, 2) }}</p>
                </div>
                <div class="col-span-2">
                    <p class="text-sm text-gray-500">Cliente:</p>
                    <p class="font-medium">{{ $invoice->client_name }} ({{ $invoice->client_document }})</p>
                </div>
            </div>
        </div>

        <form action="{{ route('pos.void.process', $invoice->id) }}" method="post">
            @csrf
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-1">Motivo de Anulación</label>
                <textarea id="reason" name="reason" rows="3" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                <p class="mt-1 text-sm text-gray-500">Describa detalladamente el motivo por el cual está anulando este comprobante.</p>
            </div>

            <div class="mb-6">
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input id="confirm" name="confirm" type="checkbox" required
                            class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="confirm" class="font-medium text-gray-700">Confirmo que deseo anular este comprobante</label>
                        <p class="text-gray-500">Entiendo que esta acción no se puede deshacer y que el comprobante quedará registrado como anulado.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('pos.invoices.list') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                    Anular Comprobante
                </button>
            </div>
        </form>
    </div>

    <div class="max-w-3xl mx-auto mt-6 flex justify-center">
        <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" onclick="window.close()">
            Cerrar ventana
        </button>
    </div>
</body>
</html>
