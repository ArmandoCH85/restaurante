<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Cierre de Caja #{{ $cashRegister->id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Auto-imprimir cuando la página cargue completamente
        document.addEventListener('DOMContentLoaded', function() {
            // Esperar un momento para que los estilos se apliquen correctamente
            setTimeout(function() {
                window.print();
            }, 1000);
        });
    </script>
    <style>
        /* Estilos generales con colores forzados para visibilidad */
        body {
            font-size: 14px;
            line-height: 1.6;
            color: #000;
            background-color: #fff;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        h1, h2, h3, h4, h5, h6 {
            color: #000;
            font-weight: 600;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .bg-white {
            background-color: #fff;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            color: #000;
            padding: 8px;
            border: 1px solid #ccc;
        }

        th {
            background-color: #f0f0f0;
            font-weight: 600;
            text-align: left;
        }

        .border {
            border-color: #ccc;
        }

        .font-medium {
            font-weight: 500;
        }

        .text-sm {
            font-size: 0.875rem;
        }

        .text-xs {
            font-size: 0.75rem;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .rounded-xl {
            border-radius: 0.75rem;
        }

        .shadow {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .bg-gray-100 {
            background-color: #f0f0f0;
        }

        .bg-gray-50 {
            background-color: #f8f8f8;
        }

        /* Colores de estado con alto contraste */
        .bg-green-100 {
            background-color: #e6ffe6;
        }

        .bg-red-100 {
            background-color: #ffe6e6;
        }

        .bg-amber-100 {
            background-color: #fff2e6;
        }

        .text-green-800 {
            color: #006600;
        }

        .text-red-800 {
            color: #990000;
        }

        .text-amber-800 {
            color: #996600;
        }

        .text-red-600 {
            color: #cc0000;
        }

        .text-amber-600 {
            color: #cc6600;
        }

        .text-green-600 {
            color: #009900;
        }

        .border-t {
            border-top-width: 1px;
            border-color: #ccc;
        }

        .border-b {
            border-bottom-width: 1px;
            border-color: #ccc;
        }

        .rounded-full {
            border-radius: 9999px;
        }

        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 0.75rem; }
        .mb-4 { margin-bottom: 1rem; }
        .mb-6 { margin-bottom: 1.5rem; }
        .mb-8 { margin-bottom: 2rem; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mt-12 { margin-top: 3rem; }
        .mt-16 { margin-top: 4rem; }
        .ml-1 { margin-left: 0.25rem; }
        .mr-2 { margin-right: 0.5rem; }
        .p-3 { padding: 0.75rem; }
        .p-5 { padding: 1.25rem; }
        .p-6 { padding: 1.5rem; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .px-3 { padding-left: 0.75rem; padding-right: 0.75rem; }
        .py-1 { padding-top: 0.25rem; padding-bottom: 0.25rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
        .pt-2 { padding-top: 0.5rem; }
        .pt-4 { padding-top: 1rem; }
        .pb-2 { padding-bottom: 0.5rem; }
        .pb-6 { padding-bottom: 1.5rem; }

        .flex { display: flex; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
        .grid { display: grid; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid-cols-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .gap-8 { gap: 2rem; }

        .w-1\/2 { width: 50%; }
        .w-1\/3 { width: 33.333333%; }
        .w-5 { width: 1.25rem; }
        .h-5 { height: 1.25rem; }

        .font-semibold { font-weight: 600; }
        .font-bold { font-weight: 700; }

        .text-xs { font-size: 0.75rem; }
        .text-sm { font-size: 0.875rem; }
        .text-lg { font-size: 1.125rem; }
        .text-xl { font-size: 1.25rem; }
        .text-2xl { font-size: 1.5rem; }
        .text-3xl { font-size: 1.875rem; }

        /* Estilos para impresión */
        @media print {
            @page {
                size: A4;
                margin: 1cm;
            }

            body {
                font-size: 12pt;
                color: #000 !important;
                background-color: #fff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .print-hidden {
                display: none !important;
            }

            .shadow {
                box-shadow: none !important;
            }

            .border, .border-t, .border-b {
                border-color: #000 !important;
            }

            th, td {
                border-color: #000 !important;
                color: #000 !important;
            }

            h1, h2, h3, h4, h5, h6, .font-medium, .font-bold, p, span, div {
                color: #000 !important;
            }

            .bg-green-100, .bg-red-100, .bg-amber-100 {
                background-color: #fff !important;
                border: 1px solid #000 !important;
            }

            /* Asegurar que todo el contenido sea visible */
            * {
                overflow: visible !important;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="flex justify-between items-center mb-6 print-hidden">
            <h2 class="text-2xl font-bold">Informe de Cierre de Caja</h2>
            <div>
                <a href="{{ route('filament.admin.resources.operaciones-caja.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg mr-2" style="text-decoration: none; display: inline-block;">
                    Volver
                </a>
                <button onclick="window.print()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg" style="border: none; cursor: pointer;">
                    Imprimir
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6 print:shadow-none print:p-0">
            <!-- Encabezado -->
            <div class="text-center mb-8 border-b pb-6">
                <div class="flex justify-center items-center mb-4">
                    <div class="bg-indigo-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-3xl font-bold mb-2">INFORME DE CIERRE DE CAJA</h1>
                <h2 class="text-xl font-semibold text-indigo-800 mb-3">#{{ $cashRegister->id }}</h2>
                <p class="font-medium">{{ config('app.name') }}</p>
                <p class="text-sm mt-2">Documento generado el: {{ $printDate }}</p>
            </div>

            <!-- Información General -->
            <div class="grid grid-cols-2 gap-8 mb-8">
                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Información de Apertura
                    </h3>
                    <table class="w-full">
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-medium w-1/2">ID de Caja:</td>
                            <td class="py-2 font-semibold">{{ $cashRegister->id }}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-medium">Estado:</td>
                            <td class="py-2">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $cashRegister->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $cashRegister->status }}
                                </span>
                            </td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-medium">Abierto por:</td>
                            <td class="py-2">{{ $cashRegister->openedBy->name ?? 'N/A' }}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-medium">Fecha de Apertura:</td>
                            <td class="py-2">{{ $cashRegister->opening_datetime->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td class="py-2 font-medium">Monto Inicial:</td>
                            <td class="py-2 font-semibold">S/ {{ number_format($cashRegister->opening_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>

                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Información de Cierre
                    </h3>
                    <table class="w-full">
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-medium w-1/2">Cerrado por:</td>
                            <td class="py-2">{{ $cashRegister->closedBy->name ?? 'No cerrada' }}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-medium">Fecha de Cierre:</td>
                            <td class="py-2">{{ $cashRegister->closing_datetime ? $cashRegister->closing_datetime->format('d/m/Y H:i') : 'No cerrada' }}</td>
                        </tr>
                        @if($isSupervisor)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-medium">Monto Esperado:</td>
                            <td class="py-2 font-semibold">S/ {{ number_format($cashRegister->expected_amount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="border-b border-gray-100">
                            <td class="py-2 font-medium">Monto Final:</td>
                            <td class="py-2 font-semibold">S/ {{ number_format($cashRegister->actual_amount, 2) }}</td>
                        </tr>
                        @if($isSupervisor)
                        <tr>
                            <td class="py-2 font-medium">Diferencia:</td>
                            <td class="py-2 font-semibold {{ $cashRegister->difference < 0 ? 'text-red-600' : ($cashRegister->difference > 0 ? 'text-amber-600' : 'text-green-600') }}">
                                S/ {{ number_format($cashRegister->difference, 2) }}
                                @if($cashRegister->difference < 0)
                                    <span class="ml-1 text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">(Faltante)</span>
                                @elseif($cashRegister->difference > 0)
                                    <span class="ml-1 text-xs bg-amber-100 text-amber-800 px-2 py-1 rounded-full">(Sobrante)</span>
                                @else
                                    <span class="ml-1 text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">(Exacto)</span>
                                @endif
                            </td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Resumen de Ventas -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    Resumen de Ventas
                </h3>
                <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border-b border-gray-200 p-3 text-left font-semibold">Método de Pago</th>
                                <th class="border-b border-gray-200 p-3 text-right font-semibold">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="hover:bg-gray-50">
                                <td class="border-b border-gray-200 p-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z" />
                                    </svg>
                                    Efectivo
                                </td>
                                <td class="border-b border-gray-200 p-3 text-right font-semibold">S/ {{ number_format($cashRegister->cash_sales, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="border-b border-gray-200 p-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                    Tarjeta
                                </td>
                                <td class="border-b border-gray-200 p-3 text-right font-semibold">S/ {{ number_format($cashRegister->card_sales, 2) }}</td>
                            </tr>
                            <tr class="hover:bg-gray-50">
                                <td class="border-b border-gray-200 p-3 flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                    </svg>
                                    Otros
                                </td>
                                <td class="border-b border-gray-200 p-3 text-right font-semibold">S/ {{ number_format($cashRegister->other_sales, 2) }}</td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td class="p-3 font-bold">TOTAL</td>
                                <td class="p-3 text-right font-bold">S/ {{ number_format($cashRegister->total_sales, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Vouchers de Tarjeta -->
            @if($isSupervisor && !$cashRegister->is_active)
            @php
                $cardPayments = $cashRegister->payments()
                    ->where('payment_method', \App\Models\Payment::METHOD_CARD)
                    ->whereNotNull('reference_number')
                    ->where('reference_number', '!=', '')
                    ->orderBy('payment_datetime', 'desc')
                    ->get();
            @endphp
            @if($cardPayments->count() > 0)
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    Vouchers de Tarjeta
                </h3>
                
                <!-- Resumen de vouchers -->
                <div class="bg-blue-50 rounded-lg p-4 mb-4 border border-blue-200">
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p class="text-sm font-medium text-blue-800">Total Vouchers</p>
                            <p class="text-xl font-bold text-blue-900">{{ $cardPayments->count() }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-800">Monto Total</p>
                            <p class="text-xl font-bold text-blue-900">S/ {{ number_format($cardPayments->sum('amount'), 2) }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-800">Tipo</p>
                            <p class="text-sm text-blue-900">
                                Tarjeta: {{ $cardPayments->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Lista detallada de vouchers -->
                <div class="overflow-x-auto bg-white rounded-lg border border-gray-200">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border-b border-gray-200 p-3 text-left font-semibold">Tipo</th>
                                <th class="border-b border-gray-200 p-3 text-right font-semibold">Monto</th>
                                <th class="border-b border-gray-200 p-3 text-center font-semibold">Fecha/Hora</th>
                                <th class="border-b border-gray-200 p-3 text-left font-semibold">Código Voucher</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cardPayments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="border-b border-gray-200 p-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                        Tarjeta
                                    </span>
                                </td>
                                <td class="border-b border-gray-200 p-3 text-right font-semibold">S/ {{ number_format($payment->amount, 2) }}</td>
                                <td class="border-b border-gray-200 p-3 text-center text-sm">{{ $payment->payment_datetime->format('d/m/Y H:i') }}</td>
                                <td class="border-b border-gray-200 p-3 font-mono text-sm">{{ $payment->reference_number }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
            @endif

            <!-- Aprobación -->
            @if(!$cashRegister->is_active)
            <div class="mb-8">
                <h3 class="text-lg font-semibold mb-4 border-b pb-2 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Estado de Aprobación
                </h3>
                <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                    <table class="w-full">
                        <tr class="border-b border-gray-100">
                            <td class="py-3 font-medium w-1/3">Estado:</td>
                            <td class="py-3">
                                <span class="px-3 py-1 rounded-full text-xs font-medium {{ $cashRegister->is_approved ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ $cashRegister->is_approved ? 'Aprobado' : 'Pendiente de aprobación' }}
                                </span>
                            </td>
                        </tr>
                        @if($cashRegister->is_approved)
                        <tr class="border-b border-gray-100">
                            <td class="py-3 font-medium">Aprobado por:</td>
                            <td class="py-3">{{ $cashRegister->approvedBy->name ?? 'N/A' }}</td>
                        </tr>
                        <tr class="border-b border-gray-100">
                            <td class="py-3 font-medium">Fecha de Aprobación:</td>
                            <td class="py-3">{{ $cashRegister->approval_datetime ? $cashRegister->approval_datetime->format('d/m/Y H:i') : 'N/A' }}</td>
                        </tr>
                        @endif
                        @if($cashRegister->approval_notes)
                        <tr>
                            <td class="py-3 font-medium">Notas:</td>
                            <td class="py-3">{{ $cashRegister->approval_notes }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>
            @endif

            <!-- Firmas -->
            <div class="mt-16 mb-8">
                <div class="grid grid-cols-3 gap-8">
                    <div class="text-center">
                        <div class="border-t-2 border-gray-300 pt-4 mt-4">
                            <p class="font-semibold">Cajero</p>
                            <p class="mt-1">{{ $cashRegister->openedBy->name ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="border-t-2 border-gray-300 pt-4 mt-4">
                            <p class="font-semibold">Supervisor</p>
                            <p class="mt-1">{{ $cashRegister->approvedBy->name ?? '____________________' }}</p>
                        </div>
                    </div>

                    <div class="text-center">
                        <div class="border-t-2 border-gray-300 pt-4 mt-4">
                            <p class="font-semibold">Gerente</p>
                            <p class="mt-1">____________________</p>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12 text-xs">
                    <p>Este documento es un comprobante oficial de cierre de caja.</p>
                    <p class="mt-1">Conserve una copia para sus registros.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
