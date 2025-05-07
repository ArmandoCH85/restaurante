<?php

namespace App\Filament\Widgets;

use App\Models\Reservation;
use App\Models\Table;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
// use Illuminate\Support\Facades\DB;

class ReservationStats extends BaseWidget
{
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // Reservas de hoy
        $todayReservations = Reservation::whereDate('reservation_date', Carbon::today())->count();
        $todayConfirmed = Reservation::whereDate('reservation_date', Carbon::today())
            ->where('status', Reservation::STATUS_CONFIRMED)->count();

        // Reservas de mañana
        $tomorrowReservations = Reservation::whereDate('reservation_date', Carbon::tomorrow())->count();

        // Reservas de esta semana (comentado porque no se usa actualmente)
        // $weekReservations = Reservation::whereBetween('reservation_date', [
        //     Carbon::now()->startOfWeek(),
        //     Carbon::now()->endOfWeek(),
        // ])->count();

        // Mesas disponibles hoy
        $availableTables = Table::where('status', Table::STATUS_AVAILABLE)->count();
        $totalTables = Table::count();

        // Próxima reserva
        $nextReservation = Reservation::whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])
            ->where(function ($query) {
                $query->whereDate('reservation_date', '>', Carbon::today())
                    ->orWhere(function ($q) {
                        $q->whereDate('reservation_date', Carbon::today())
                          ->whereTime('reservation_time', '>', Carbon::now()->format('H:i:s'));
                    });
            })
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->with(['customer', 'table'])
            ->first();

        $nextReservationText = 'No hay reservas próximas';

        if ($nextReservation) {
            $tableName = $nextReservation->table ? "Mesa {$nextReservation->table->number}" : 'Mesa no asignada';
            $customerName = $nextReservation->customer ? $nextReservation->customer->name : 'Cliente no especificado';
            $reservationDate = $nextReservation->reservation_date->format('d/m/Y');
            $reservationTime = $nextReservation->reservation_time;

            $nextReservationText = "{$tableName} - {$customerName} - {$reservationDate} {$reservationTime}";
        }

        return [
            Stat::make('Reservas Hoy', $todayReservations)
                ->description("{$todayConfirmed} confirmadas")
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Reservas Mañana', $tomorrowReservations)
                ->description('Planifica con anticipación')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning'),

            Stat::make('Mesas Disponibles', "{$availableTables} de {$totalTables}")
                ->description(round(($availableTables / max(1, $totalTables)) * 100) . '% disponibilidad')
                ->descriptionIcon('heroicon-m-squares-2x2')
                ->color('info'),

            Stat::make('Próxima Reserva', $nextReservationText)
                ->description('Prepárate')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}
