<?php

namespace App\Filament\Pages;

use App\Models\Reservation;
use App\Models\Table as TableModel;
use Carbon\Carbon;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class ReservationCalendar extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static string $view = 'filament.pages.reservation-calendar';

    protected static ?string $navigationGroup = 'Reservas';

    protected static ?string $navigationLabel = 'Calendario de Reservas';

    protected static ?int $navigationSort = 5;

    public $currentDate;
    public $viewType = 'week'; // 'day', 'week', 'month'
    protected $reservations = [];
    protected $tables = [];

    public function mount()
    {
        $this->currentDate = Carbon::today();
        // Cargar las reservas iniciales
        $this->getReservations();
    }

    public function getTables()
    {
        return TableModel::orderBy('number')->get();
    }

    public function getReservations()
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        // Asegurarse de que las fechas estén en el formato correcto para la consulta
        $startDateStr = $startDate->format('Y-m-d');
        $endDateStr = $endDate->format('Y-m-d');

        // Depuración
        \Illuminate\Support\Facades\Log::info("Buscando reservas entre {$startDateStr} y {$endDateStr}");

        $reservations = Reservation::with(['customer', 'table'])
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])
            ->whereBetween('reservation_date', [$startDateStr, $endDateStr])
            ->orderBy('reservation_date')
            ->orderBy('reservation_time')
            ->get();

        // Depuración
        \Illuminate\Support\Facades\Log::info("Encontradas {$reservations->count()} reservas");

        return $reservations->groupBy(function ($reservation) {
            return $reservation->reservation_date->format('Y-m-d');
        });
    }

    public function getStartDate()
    {
        if ($this->viewType === 'day') {
            return $this->currentDate->copy();
        } elseif ($this->viewType === 'week') {
            return $this->currentDate->copy()->startOfWeek();
        } else { // month
            return $this->currentDate->copy()->startOfMonth();
        }
    }

    public function getEndDate()
    {
        if ($this->viewType === 'day') {
            return $this->currentDate->copy();
        } elseif ($this->viewType === 'week') {
            return $this->currentDate->copy()->endOfWeek();
        } else { // month
            return $this->currentDate->copy()->endOfMonth();
        }
    }

    public function getDaysToShow()
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $days = [];
        $currentDay = $startDate->copy();

        while ($currentDay->lte($endDate)) {
            $days[] = $currentDay->copy();
            $currentDay->addDay();
        }

        return $days;
    }

    public function getTimeSlots()
    {
        // Horario de operación del restaurante (por ejemplo, de 10:00 a 23:00)
        $startHour = 10;
        $endHour = 23;

        $timeSlots = [];

        for ($hour = $startHour; $hour <= $endHour; $hour++) {
            $timeSlots[] = sprintf('%02d:00', $hour);
            $timeSlots[] = sprintf('%02d:30', $hour);
        }

        return $timeSlots;
    }

    public function changeView($viewType)
    {
        $this->viewType = $viewType;
        // Recargar las reservas cuando se cambia la vista
        $this->getReservations();
    }

    public function previousPeriod()
    {
        if ($this->viewType === 'day') {
            $this->currentDate = $this->currentDate->copy()->subDay();
        } elseif ($this->viewType === 'week') {
            $this->currentDate = $this->currentDate->copy()->subWeek();
        } else { // month
            $this->currentDate = $this->currentDate->copy()->subMonth();
        }
    }

    public function nextPeriod()
    {
        if ($this->viewType === 'day') {
            $this->currentDate = $this->currentDate->copy()->addDay();
        } elseif ($this->viewType === 'week') {
            $this->currentDate = $this->currentDate->copy()->addWeek();
        } else { // month
            $this->currentDate = $this->currentDate->copy()->addMonth();
        }
    }

    public function today()
    {
        $this->currentDate = Carbon::today();
    }

    public function getReservationsForDayAndTime($day, $time)
    {
        $dayString = $day->format('Y-m-d');
        $reservations = $this->getReservations();

        if (!isset($reservations[$dayString])) {
            return [];
        }

        return $reservations[$dayString]->filter(function ($reservation) use ($time) {
            // Manejar diferentes formatos de tiempo
            if (is_string($reservation->reservation_time)) {
                $reservationTime = substr($reservation->reservation_time, 0, 5); // HH:MM format
            } else {
                $reservationTime = $reservation->reservation_time->format('H:i');
            }

            return $reservationTime === $time;
        });
    }

    public function getReservationsStats()
    {
        $startDate = $this->getStartDate();
        $endDate = $this->getEndDate();

        $totalReservations = Reservation::whereBetween('reservation_date', [$startDate, $endDate])->count();
        $confirmedReservations = Reservation::where('status', Reservation::STATUS_CONFIRMED)
            ->whereBetween('reservation_date', [$startDate, $endDate])->count();
        $pendingReservations = Reservation::where('status', Reservation::STATUS_PENDING)
            ->whereBetween('reservation_date', [$startDate, $endDate])->count();
        $cancelledReservations = Reservation::where('status', Reservation::STATUS_CANCELLED)
            ->whereBetween('reservation_date', [$startDate, $endDate])->count();

        // Obtener las mesas más reservadas
        $topTables = DB::table('reservations')
            ->join('tables', 'reservations.table_id', '=', 'tables.id')
            ->select('tables.number', DB::raw('count(*) as total'))
            ->whereBetween('reservation_date', [$startDate, $endDate])
            ->whereIn('reservations.status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_COMPLETED])
            ->groupBy('tables.number')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return [
            'total' => $totalReservations,
            'confirmed' => $confirmedReservations,
            'pending' => $pendingReservations,
            'cancelled' => $cancelledReservations,
            'topTables' => $topTables,
        ];
    }

    protected function getViewData(): array
    {
        return [
            'days' => $this->getDaysToShow(),
            'timeSlots' => $this->getTimeSlots(),
        ];
    }
}
