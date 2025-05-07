<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Controles de navegación -->
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center space-x-3">
                    <button wire:click="previousPeriod" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 transform hover:-translate-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <button wire:click="today" class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-all duration-200 font-medium shadow-sm hover:shadow flex items-center space-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <span>Hoy</span>
                    </button>
                    <button wire:click="nextPeriod" class="p-2 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 transform hover:translate-x-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

                <div class="text-xl font-bold bg-gradient-to-r from-primary-600 to-primary-500 text-transparent bg-clip-text">
                    @if($viewType === 'day')
                        {{ $currentDate->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                    @elseif($viewType === 'week')
                        {{ $this->getStartDate()->locale('es')->format('d') }} - {{ $this->getEndDate()->locale('es')->format('d') }} {{ $this->getEndDate()->locale('es')->format('F Y') }}
                    @else
                        {{ $currentDate->locale('es')->format('F Y') }}
                    @endif
                </div>

                <div class="flex space-x-1 bg-gray-100 dark:bg-gray-700 p-1 rounded-lg">
                    <button wire:click="changeView('day')" class="px-4 py-2 rounded-lg font-medium text-sm {{ $viewType === 'day' ? 'bg-white dark:bg-gray-800 text-primary-600 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} transition-all duration-200">
                        Día
                    </button>
                    <button wire:click="changeView('week')" class="px-4 py-2 rounded-lg font-medium text-sm {{ $viewType === 'week' ? 'bg-white dark:bg-gray-800 text-primary-600 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} transition-all duration-200">
                        Semana
                    </button>
                    <button wire:click="changeView('month')" class="px-4 py-2 rounded-lg font-medium text-sm {{ $viewType === 'month' ? 'bg-white dark:bg-gray-800 text-primary-600 shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }} transition-all duration-200">
                        Mes
                    </button>
                </div>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        @php
            $stats = $this->getReservationsStats();
            $totalReservations = max(1, $stats['total']); // Evitar división por cero
            $confirmedPercent = round(($stats['confirmed'] / $totalReservations) * 100);
            $pendingPercent = round(($stats['pending'] / $totalReservations) * 100);
            $cancelledPercent = round(($stats['cancelled'] / $totalReservations) * 100);
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Reservas</div>
                        <div class="text-3xl font-bold mt-1">{{ $stats['total'] }}</div>
                    </div>
                    <div class="bg-primary-100 dark:bg-primary-900 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-primary-600 dark:text-primary-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">Periodo actual</div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Confirmadas</div>
                        <div class="text-3xl font-bold text-green-600 dark:text-green-500 mt-1">{{ $stats['confirmed'] }}</div>
                    </div>
                    <div class="bg-green-100 dark:bg-green-900 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-green-600 dark:bg-green-500 h-2 rounded-full" style="width: {{ $confirmedPercent }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $confirmedPercent }}% del total</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendientes</div>
                        <div class="text-3xl font-bold text-yellow-600 dark:text-yellow-500 mt-1">{{ $stats['pending'] }}</div>
                    </div>
                    <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-yellow-600 dark:bg-yellow-500 h-2 rounded-full" style="width: {{ $pendingPercent }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $pendingPercent }}% del total</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 transition-all duration-200 hover:shadow-md hover:-translate-y-1">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Canceladas</div>
                        <div class="text-3xl font-bold text-red-600 dark:text-red-500 mt-1">{{ $stats['cancelled'] }}</div>
                    </div>
                    <div class="bg-red-100 dark:bg-red-900 p-3 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                        <div class="bg-red-600 dark:bg-red-500 h-2 rounded-full" style="width: {{ $cancelledPercent }}%"></div>
                    </div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $cancelledPercent }}% del total</div>
                </div>
            </div>
        </div>

        <!-- Mesas más reservadas -->
        @if(isset($stats['topTables']) && count($stats['topTables']) > 0)
        <div class="bg-white dark:bg-gray-800 p-5 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-primary-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                    </svg>
                    Mesas más reservadas
                </h3>
                <a href="{{ route('filament.admin.resources.tables.index') }}" class="text-sm text-primary-600 hover:text-primary-800 dark:text-primary-500 dark:hover:text-primary-400 flex items-center">
                    <span>Ver todas</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
                @foreach($stats['topTables'] as $table)
                <div class="flex flex-col items-center bg-gray-50 dark:bg-gray-900 p-4 rounded-lg transition-all duration-200 hover:shadow-md hover:-translate-y-1 border border-gray-100 dark:border-gray-800">
                    <div class="relative">
                        <div class="w-16 h-16 rounded-lg bg-primary-100 dark:bg-primary-900 flex items-center justify-center text-primary-800 dark:text-primary-200 font-bold text-xl shadow-sm">
                            {{ $table->number }}
                        </div>
                        <div class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-primary-600 text-white text-xs flex items-center justify-center font-bold shadow-sm">
                            #{{ $loop->iteration }}
                        </div>
                    </div>
                    <div class="text-sm font-medium mt-2 text-gray-900 dark:text-gray-100">Mesa {{ $table->number }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $table->total }} reservas</div>
                    <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                        @php
                            $maxReservations = $stats['topTables']->max('total');
                            $percentage = ($table->total / $maxReservations) * 100;
                        @endphp
                        <div class="bg-primary-600 dark:bg-primary-500 h-1.5 rounded-full" style="width: {{ $percentage }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Calendario -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <!-- Encabezados de días -->
            @php
                $days = $this->getDaysToShow();
            @endphp
            <div class="grid grid-cols-{{ count($days) }} border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                @foreach($days as $day)
                <div class="p-3 text-center {{ $day->isToday() ? 'bg-primary-50 dark:bg-primary-900 dark:bg-opacity-20 border-b-2 border-primary-500' : '' }}">
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $day->locale('es')->format('D') }}</div>
                    <div class="text-xl font-bold {{ $day->isToday() ? 'text-primary-600 dark:text-primary-400' : 'text-gray-900 dark:text-gray-100' }}">{{ $day->format('d') }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $day->locale('es')->format('M') }}</div>
                </div>
                @endforeach
            </div>

            <!-- Horarios y reservas -->
            @php
                $timeSlots = $this->getTimeSlots();
                $now = \Carbon\Carbon::now();
                $currentHour = $now->format('H:i');
            @endphp
            <div class="overflow-auto max-h-[600px] scrollbar-thin scrollbar-thumb-gray-300 dark:scrollbar-thumb-gray-600 scrollbar-track-gray-100 dark:scrollbar-track-gray-800">
                @foreach($timeSlots as $time)
                <div class="grid grid-cols-{{ count($days) }} border-b border-gray-200 dark:border-gray-700 {{ $time === $currentHour ? 'bg-yellow-50 dark:bg-yellow-900 dark:bg-opacity-10' : '' }}">
                    @foreach($days as $day)
                    <div class="p-2 border-r border-gray-200 dark:border-gray-700 relative min-h-[70px] hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors duration-150">
                        @if($loop->first)
                        <div class="absolute left-2 top-2 text-xs font-medium {{ $time === $currentHour ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-500 dark:text-gray-400' }}">
                            {{ $time }}
                        </div>
                        @endif

                        @php
                            $dayReservations = $this->getReservationsForDayAndTime($day, $time);
                        @endphp

                        @foreach($dayReservations as $reservation)
                        <a href="{{ route('filament.admin.resources.reservations.edit', $reservation) }}" class="block mt-6 p-2 rounded-lg text-xs {{ $reservation->status === 'confirmed' ? 'bg-green-100 dark:bg-green-900 dark:bg-opacity-50 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800' : 'bg-yellow-100 dark:bg-yellow-900 dark:bg-opacity-50 text-yellow-800 dark:text-yellow-200 border border-yellow-200 dark:border-yellow-800' }} hover:shadow-md transition-all duration-200 transform hover:-translate-y-1">
                            <div class="font-bold flex items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Mesa {{ $reservation->table->number ?? 'N/A' }}
                            </div>
                            <div class="flex items-center mt-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ $reservation->customer->name ?? 'Cliente' }}
                            </div>
                            <div class="flex items-center justify-between mt-1">
                                <span class="flex items-center">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    {{ $reservation->guests_count }}
                                </span>
                                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $reservation->status === 'confirmed' ? 'bg-green-200 dark:bg-green-800 text-green-800 dark:text-green-200' : 'bg-yellow-200 dark:bg-yellow-800 text-yellow-800 dark:text-yellow-200' }}">
                                    {{ $reservation->status === 'confirmed' ? 'Confirmada' : 'Pendiente' }}
                                </span>
                            </div>
                        </a>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @endforeach
            </div>
        </div>

        <!-- Botón para crear nueva reserva -->
        <div class="flex justify-between items-center">
            <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Actualizado: {{ now()->format('H:i') }}</span>
            </div>
            <a href="{{ route('filament.admin.resources.reservations.create') }}" class="px-5 py-2.5 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-all duration-200 font-medium shadow-sm hover:shadow flex items-center space-x-2 transform hover:-translate-y-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>Nueva Reserva</span>
            </a>
        </div>
    </div>
</x-filament-panels::page>
