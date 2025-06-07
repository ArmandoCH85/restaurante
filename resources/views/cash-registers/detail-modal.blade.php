<div class="p-6 space-y-8">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-6">
        <!-- Información de Apertura: columnas 3 de 5 (proporción áurea) -->
        <div class="bg-white p-5 rounded-lg shadow md:col-span-3">
            <h3 class="text-xl font-medium text-gray-900 mb-4 border-b pb-2">Información de Apertura</h3>
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600 font-medium">Fecha de Apertura:</span>
                    <span class="font-medium text-gray-800">{{ $record->opening_datetime ? $record->opening_datetime->format('d/m/Y H:i') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600 font-medium">Abierto por:</span>
                    <span class="font-medium text-gray-800">{{ $record->openedBy ? $record->openedBy->name : 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600 font-medium">Monto Inicial:</span>
                    <span class="font-medium text-emerald-600">S/ {{ number_format($record->opening_amount, 2) }}</span>
                </div>
                @if($record->observations)
                <div class="pt-3 mt-2">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Observaciones:</h4>
                    <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-md">{{ $record->observations }}</p>
                </div>
                @endif
            </div>
        </div>

        <!-- Información de Cierre: columnas 2 de 5 (complemento de la proporción áurea) -->
        <div class="bg-white p-5 rounded-lg shadow md:col-span-2">
            <h3 class="text-xl font-medium text-gray-900 mb-4 border-b pb-2">Información de Cierre</h3>
            @if(!$record->is_active)
            <div class="space-y-4">
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600 font-medium">Fecha de Cierre:</span>
                    <span class="font-medium text-gray-800">{{ $record->closing_datetime ? $record->closing_datetime->format('d/m/Y H:i') : 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600 font-medium">Cerrado por:</span>
                    <span class="font-medium text-gray-800">{{ $record->closedBy ? $record->closedBy->name : 'N/A' }}</span>
                </div>
                <div class="flex justify-between items-center py-2 border-b border-gray-100">
                    <span class="text-sm text-gray-600 font-medium">Monto Final:</span>
                    <span class="font-medium text-indigo-600">S/ {{ number_format($record->actual_amount, 2) }}</span>
                </div>
                @if($record->closing_observations)
                <div class="pt-3 mt-2">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Observaciones de Cierre:</h4>
                    <p class="text-sm text-gray-700 bg-gray-50 p-3 rounded-md">{{ $record->closing_observations }}</p>
                </div>
                @endif
            </div>
            @else
            <div class="py-8 flex flex-col items-center justify-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-amber-100 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <span class="text-amber-600 font-medium">Caja actualmente abierta</span>
                <p class="text-gray-500 text-sm mt-1">Esta caja no ha sido cerrada todavía</p>
            </div>
            @endif
        </div>
    </div>
    
    @if(auth()->user()->hasAnyRole(['admin', 'super_admin', 'manager']) && !$record->is_active)
    <!-- Resumen de Ventas (solo para administradores) con proporción áurea 8:5 -->
    <div class="bg-white p-5 rounded-lg shadow">
        <h3 class="text-xl font-medium text-gray-900 mb-4 border-b pb-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-indigo-500" viewBox="0 0 20 20" fill="currentColor">
                <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z" />
            </svg>
            Resumen de Ventas
        </h3>
        
        <!-- Sección de métodos de pago (proporción 8 de 13) -->
        <div class="grid grid-cols-1 md:grid-cols-8 gap-4 mb-6">
            <div class="md:col-span-5 space-y-3">
                <h4 class="text-sm font-semibold text-gray-700 mb-2">Ventas por Método de Pago</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-md border border-blue-100">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-medium text-blue-700">Efectivo</span>
                        </div>
                        <p class="text-lg font-bold text-blue-800">S/ {{ number_format($record->cash_sales, 2) }}</p>
                    </div>
                    
                    <div class="bg-purple-50 p-4 rounded-md border border-purple-100">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-purple-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z" />
                                <path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-medium text-purple-700">Tarjeta</span>
                        </div>
                        <p class="text-lg font-bold text-purple-800">S/ {{ number_format($record->card_sales, 2) }}</p>
                    </div>
                    
                    <div class="bg-emerald-50 p-4 rounded-md border border-emerald-100">
                        <div class="flex items-center mb-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-emerald-500 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                            </svg>
                            <span class="text-xs font-medium text-emerald-700">Otras</span>
                        </div>
                        <p class="text-lg font-bold text-emerald-800">S/ {{ number_format($record->other_sales, 2) }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Total de ventas (proporción 3 de 8, siguiendo proporción áurea) -->
            <div class="md:col-span-3 bg-indigo-50 p-4 rounded-md border border-indigo-100 flex flex-col justify-center items-center">
                <span class="text-sm font-semibold text-indigo-700">Ventas Totales</span>
                <p class="text-2xl font-bold text-indigo-800 mt-2">S/ {{ number_format($record->total_sales, 2) }}</p>
            </div>
        </div>
        
        <!-- Balance final con proporción áurea 3:5:3 -->
        <div class="grid grid-cols-11 gap-4">
            <div class="col-span-3 bg-gray-50 p-4 rounded-md border border-gray-100">
                <div class="text-center">
                    <span class="text-xs font-medium text-gray-600">Monto Esperado</span>
                    <p class="text-lg font-bold text-gray-800">S/ {{ number_format($record->expected_amount, 2) }}</p>
                </div>
            </div>
            
            <div class="col-span-5 bg-indigo-50 p-4 rounded-md border border-indigo-100">
                <div class="text-center">
                    <span class="text-xs font-medium text-indigo-600">Monto Final</span>
                    <p class="text-lg font-bold text-indigo-800">S/ {{ number_format($record->actual_amount, 2) }}</p>
                </div>
            </div>
            
            <div class="col-span-3 {{ $record->difference < 0 ? 'bg-red-50 border-red-100' : ($record->difference > 0 ? 'bg-green-50 border-green-100' : 'bg-gray-50 border-gray-100') }} p-4 rounded-md border">
                <div class="text-center">
                    <span class="text-xs font-medium {{ $record->difference < 0 ? 'text-red-600' : ($record->difference > 0 ? 'text-green-600' : 'text-gray-600') }}">Diferencia</span>
                    <p class="text-lg font-bold {{ $record->difference < 0 ? 'text-red-700' : ($record->difference > 0 ? 'text-green-700' : 'text-gray-800') }}">
                        S/ {{ number_format($record->difference, 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Estado de aprobación con proporción áurea -->
    <div class="{{ $record->is_approved ? 'bg-green-50 border border-green-100' : 'bg-gray-50 border border-gray-100' }} p-5 rounded-lg shadow">
        <h3 class="text-xl font-medium text-gray-900 mb-4 border-b pb-2 flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 {{ $record->is_approved ? 'text-green-500' : 'text-gray-500' }}" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Estado de Aprobación
        </h3>
        
        <!-- Contenido con diseño de proporción áurea -->
        <div class="grid grid-cols-1 md:grid-cols-8 gap-6">
            <!-- Indicador de estado: ocupa 3 columnas siguiendo proporción áurea -->
            <div class="md:col-span-3 flex flex-col items-center justify-center">
                <div class="w-24 h-24 rounded-full flex items-center justify-center {{ $record->is_approved ? 'bg-green-100' : 'bg-gray-100' }} p-2">
                    @if($record->is_approved)
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                    @else
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm0-2a6 6 0 100-12 6 6 0 000 12zm-1-5a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1zm0-4a1 1 0 011-1h.01a1 1 0 110 2H10a1 1 0 01-1-1z" clip-rule="evenodd" />
                    </svg>
                    @endif
                </div>
                <span class="text-lg font-semibold mt-3 {{ $record->is_approved ? 'text-green-700' : 'text-gray-700' }}">
                    {{ $record->is_approved ? 'APROBADO' : 'PENDIENTE DE APROBACIÓN' }}
                </span>
            </div>
            
            <!-- Detalles de aprobación: ocupa 5 columnas siguiendo proporción áurea -->
            <div class="md:col-span-5">
                @if($record->is_approved && $record->approvedBy)
                <div class="bg-white p-4 rounded-lg border border-green-100">
                    <div class="flex items-center mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <h4 class="text-sm font-semibold text-gray-800">Detalles de la Aprobación</h4>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-4">
                        <div class="bg-green-50 p-3 rounded-lg">
                            <span class="text-xs font-medium text-gray-600">Aprobado por:</span>
                            <p class="text-sm font-semibold text-gray-900">{{ $record->approvedBy->name }}</p>
                        </div>
                        
                        @if($record->approved_at)
                        <div class="bg-green-50 p-3 rounded-lg">
                            <span class="text-xs font-medium text-gray-600">Fecha de Aprobación:</span>
                            <p class="text-sm font-semibold text-gray-900">{{ $record->approved_at ? $record->approved_at->format('d/m/Y H:i') : 'Fecha desconocida' }}</p>
                        </div>
                        @endif
                        
                        @if($record->approval_notes)
                        <div class="bg-green-50 p-3 rounded-lg">
                            <span class="text-xs font-medium text-gray-600">Notas:</span>
                            <p class="text-sm font-semibold text-gray-900">{{ $record->approval_notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
                @elseif(!$record->is_approved && auth()->user()->can('approve', $record))
                <div class="bg-white p-5 rounded-lg border border-gray-200 flex flex-col items-center justify-center h-full">
                    <p class="mb-4 text-gray-600 text-sm text-center">Esta operación de caja requiere aprobación para ser finalizada.</p>
                    <form method="POST" action="{{ route('cash-registers.approve', $record) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-5 py-2.5 bg-green-600 border border-transparent rounded-md font-semibold text-sm text-white tracking-wide hover:bg-green-700 focus:outline-none focus:border-green-700 focus:ring focus:ring-green-200 active:bg-green-700 transition ease-in-out duration-150">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                            </svg>
                            Aprobar esta Operación
                        </button>
                    </form>
                </div>
                @elseif(!$record->is_approved)
                <div class="bg-white p-5 rounded-lg border border-gray-200 flex flex-col items-center justify-center h-full">
                    <div class="text-gray-600 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-400 mb-3" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                        <p class="text-sm">Esta operación de caja está pendiente de aprobación por un administrador.</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
