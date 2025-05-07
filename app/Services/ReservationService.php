<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Table as TableModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /**
     * Crear una nueva reserva
     */
    public function createReservation(array $data): Reservation
    {
        DB::beginTransaction();

        try {
            // Crear la reserva
            $reservation = Reservation::create($data);

            // Si la reserva está confirmada, actualizar el estado de la mesa
            if ($reservation->isConfirmed() && $reservation->table_id) {
                // Actualizar explícitamente el estado de la mesa a reservada
                $table = TableModel::find($reservation->table_id);
                if ($table) {
                    $table->status = TableModel::STATUS_RESERVED;
                    $table->save();
                }
            }

            DB::commit();
            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Actualizar una reserva existente
     */
    public function updateReservation(Reservation $reservation, array $data): Reservation
    {
        DB::beginTransaction();

        try {
            $oldTableId = $reservation->table_id;
            $oldStatus = $reservation->status;

            // Actualizar la reserva
            $reservation->update($data);

            // Si cambió la mesa o el estado, actualizar los estados de las mesas
            if ($oldTableId != $reservation->table_id || $oldStatus != $reservation->status) {
                // Si tenía una mesa asignada antes, liberarla
                if ($oldTableId && $oldStatus == Reservation::STATUS_CONFIRMED) {
                    // Verificar si hay otras reservas activas para esta mesa
                    $activeReservations = Reservation::where('table_id', $oldTableId)
                        ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])
                        ->where('id', '!=', $reservation->id)
                        ->exists();

                    // Si no hay otras reservas activas, cambiar el estado a disponible
                    if (!$activeReservations) {
                        $oldTable = TableModel::find($oldTableId);
                        if ($oldTable) {
                            $oldTable->status = TableModel::STATUS_AVAILABLE;
                            $oldTable->save();
                        }
                    }
                }

                // Si ahora tiene una mesa asignada y está confirmada, reservarla
                if ($reservation->table_id && $reservation->isConfirmed()) {
                    // Actualizar explícitamente el estado de la mesa a reservada
                    $table = TableModel::find($reservation->table_id);
                    if ($table) {
                        $table->status = TableModel::STATUS_RESERVED;
                        $table->save();
                    }
                }
            }

            DB::commit();
            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Cancelar una reserva
     */
    public function cancelReservation(Reservation $reservation): Reservation
    {
        DB::beginTransaction();

        try {
            // Actualizar el estado de la reserva
            $reservation->status = Reservation::STATUS_CANCELLED;
            $reservation->save();

            // Si tenía una mesa asignada, liberarla
            if ($reservation->table_id) {
                // Verificar si hay otras reservas activas para esta mesa
                $activeReservations = Reservation::where('table_id', $reservation->table_id)
                    ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])
                    ->where('id', '!=', $reservation->id)
                    ->exists();

                // Si no hay otras reservas activas, cambiar el estado a disponible
                if (!$activeReservations) {
                    $table = TableModel::find($reservation->table_id);
                    if ($table) {
                        $table->status = TableModel::STATUS_AVAILABLE;
                        $table->save();
                    }
                }
            }

            DB::commit();
            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Completar una reserva
     */
    public function completeReservation(Reservation $reservation): Reservation
    {
        DB::beginTransaction();

        try {
            // Actualizar el estado de la reserva
            $reservation->status = Reservation::STATUS_COMPLETED;
            $reservation->save();

            // Si tenía una mesa asignada, cambiar su estado a ocupada
            if ($reservation->table_id) {
                $table = TableModel::find($reservation->table_id);
                if ($table) {
                    $table->status = TableModel::STATUS_OCCUPIED;
                    $table->occupied_at = now(); // Registrar el tiempo de ocupación
                    $table->save();
                }
            }

            DB::commit();
            return $reservation;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Verificar disponibilidad de mesas para una fecha y hora específicas
     */
    public function getAvailableTables($date, $time, $guestsCount = 1): array
    {
        // Convertir a objetos Carbon si son strings
        if (is_string($date)) {
            $date = Carbon::parse($date);
        }

        // Obtener todas las mesas con capacidad suficiente
        $tables = TableModel::where('capacity', '>=', $guestsCount)
            ->where(function($query) {
                $query->where('status', TableModel::STATUS_AVAILABLE)
                      ->orWhere('status', TableModel::STATUS_RESERVED);
            })
            ->get();

        // Filtrar las mesas disponibles para la fecha y hora
        $availableTables = $tables->filter(function($table) use ($date, $time) {
            return $table->isAvailableFor($date->format('Y-m-d'), $time);
        });

        return $availableTables->values()->all();
    }

    /**
     * Verificar si una mesa está disponible para una fecha y hora específicas
     */
    public function isTableAvailable($tableId, $date, $time, $excludeReservationId = null): bool
    {
        $table = TableModel::find($tableId);

        if (!$table) {
            return false;
        }

        // Si la mesa no está disponible ni reservada, no está disponible para reserva
        if (!$table->isAvailable() && !$table->isReserved()) {
            return false;
        }

        // Convertir a objetos Carbon si son strings
        if (is_string($date)) {
            $date = Carbon::parse($date)->format('Y-m-d');
        } else {
            $date = $date->format('Y-m-d');
        }

        // Buscar reservas activas para esta mesa en la misma fecha
        $query = Reservation::where('table_id', $tableId)
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])
            ->whereDate('reservation_date', $date);

        // Excluir la reserva actual si se está editando
        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        $reservations = $query->get();

        // Convertir la hora a minutos para facilitar la comparación
        $timeMinutes = $this->timeToMinutes($time);
        $startTime = $timeMinutes;
        $endTime = $timeMinutes + 120; // Asumiendo 2 horas de duración

        // Verificar si hay reservas superpuestas
        foreach ($reservations as $reservation) {
            $reservationTimeMinutes = $this->timeToMinutes($reservation->reservation_time);
            $reservationEndTime = $reservationTimeMinutes + 120; // Asumiendo 2 horas de duración

            // Verificar si la nueva reserva se superpone con la existente
            if ($startTime < $reservationEndTime && $endTime > $reservationTimeMinutes) {
                return false;
            }
        }

        return true;
    }



    /**
     * Convertir hora a minutos para facilitar la comparación
     */
    private function timeToMinutes($time): int
    {
        if (is_string($time)) {
            list($hours, $minutes) = explode(':', $time);
            return (int)$hours * 60 + (int)$minutes;
        }

        // Si ya es un objeto DateTime
        return $time->format('H') * 60 + $time->format('i');
    }
}
