<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class Table extends Model
{
    protected $fillable = [
        'floor_id',
        'number',
        'shape',
        'capacity',
        'location',
        'status',
        'qr_code'
    ];

    const SHAPE_SQUARE = 'square';
    const SHAPE_ROUND = 'round';

    const STATUS_AVAILABLE = 'available';
    const STATUS_OCCUPIED = 'occupied';
    const STATUS_RESERVED = 'reserved';
    const STATUS_MAINTENANCE = 'maintenance';

    public function scopeAvailable($query)
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', self::STATUS_OCCUPIED);
    }

    public function scopeReserved($query)
    {
        return $query->where('status', self::STATUS_RESERVED);
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', self::STATUS_MAINTENANCE);
    }

    public function isAvailable()
    {
        return $this->status === self::STATUS_AVAILABLE;
    }

    public function isOccupied()
    {
        return $this->status === self::STATUS_OCCUPIED;
    }

    public function isReserved()
    {
        return $this->status === self::STATUS_RESERVED;
    }

    public function isInMaintenance()
    {
        return $this->status === self::STATUS_MAINTENANCE;
    }

    public function isSquare()
    {
        return $this->shape === self::SHAPE_SQUARE;
    }

    public function isRound()
    {
        return $this->shape === self::SHAPE_ROUND;
    }

    /**
     * Get the floor that owns the table.
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    /**
     * Get the reservations for the table.
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /**
     * Get the orders for the table.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Obtiene solo las órdenes con estado "abierto" para esta mesa.
     * Esta es la relación que se usa para la funcionalidad de dividir cuentas.
     */
    public function openOrders(): HasMany
    {
        return $this->hasMany(Order::class)->where('status', Order::STATUS_OPEN);
    }

    /**
     * Get active reservations for the table.
     */
    public function activeReservations(): HasMany
    {
        return $this->reservations()->whereIn('status', ['pending', 'confirmed']);
    }

    /**
     * Check if the table is available for a specific date and time.
     */
    public function isAvailableFor($date, $time, $duration = 120): bool
    {
        // If table is not available or in maintenance, it's not available for reservation
        if (!$this->isAvailable() && !$this->isReserved()) {
            return false;
        }

        // Convert time to minutes for easier comparison
        $timeMinutes = $this->timeToMinutes($time);
        $startTime = $timeMinutes;
        $endTime = $timeMinutes + $duration;

        // Check for overlapping reservations
        $overlappingReservations = $this->activeReservations()
            ->whereDate('reservation_date', $date)
            ->get()
            ->filter(function($reservation) use ($startTime, $endTime) {
                $reservationTimeMinutes = $this->timeToMinutes($reservation->reservation_time);
                $reservationEndTime = $reservationTimeMinutes + 120; // Assuming 2 hours duration

                // Check if the new reservation overlaps with existing one
                return ($startTime < $reservationEndTime && $endTime > $reservationTimeMinutes);
            });

        return $overlappingReservations->isEmpty();
    }

    /**
     * Convert time string to minutes for easier comparison.
     */
    private function timeToMinutes($time): int
    {
        if (is_string($time)) {
            list($hours, $minutes) = explode(':', $time);
            return (int)$hours * 60 + (int)$minutes;
        }

        // If it's already a DateTime object
        return $time->format('H') * 60 + $time->format('i');
    }

    /**
     * Get the active order for this table.
     * An active order is one that is not completed, cancelled, or billed.
     */
    public function activeOrder()
    {
        return $this->hasOne(Order::class)
            ->where(function($query) {
                $query->where('status', '!=', Order::STATUS_COMPLETED)
                      ->where('status', '!=', Order::STATUS_CANCELLED);
            })
            ->where('billed', false)
            ->latest();
    }

    /**
     * Check if the table has an active order.
     */
    public function hasActiveOrder(): bool
    {
        return $this->activeOrder()->exists();
    }

    /**
     * Get all active orders for the table.
     */
    public function activeOrders(): HasMany
    {
        return $this->orders()->where(function($query) {
            $query->where('status', '!=', Order::STATUS_COMPLETED)
                  ->where('status', '!=', Order::STATUS_CANCELLED);
        })->where('billed', false);
    }

    /**
     * Get the time elapsed since the table was occupied.
     * Returns a formatted string like "2h 15m" or null if the table is not occupied.
     */
    public function getOccupationTime()
    {
        if (!$this->isOccupied()) {
            return null;
        }

        $activeOrder = $this->activeOrder()->first();

        if (!$activeOrder) {
            return null;
        }

        $start = $activeOrder->created_at;
        $now = now();
        $diffInMinutes = $start->diffInMinutes($now);

        $hours = floor($diffInMinutes / 60);
        $minutes = $diffInMinutes % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }

    /**
     * Mark the table as occupied and create a new order if needed.
     * Note: This method no longer changes the table status automatically.
     * The status should be changed explicitly by the caller if needed.
     */
    public function occupy($employeeId = null): Order
    {
        if ($this->isOccupied() && $this->hasActiveOrder()) {
            return $this->activeOrder()->first();
        }

        // Si no se proporciona employeeId, obtener el empleado del usuario autenticado
        if (!$employeeId) {
            $user = Auth::user();
            if ($user) {
                $employee = \App\Models\Employee::where('user_id', $user->id)->first();
                if ($employee) {
                    $employeeId = $employee->id;
                } else {
                    throw new \Exception('No se encontró un empleado asociado al usuario actual.');
                }
            } else {
                throw new \Exception('No hay un usuario autenticado.');
            }
        }

        // Create a new order for this table without changing the table status
        $order = new Order([
            'service_type' => 'dine_in',
            'table_id' => $this->id,
            'employee_id' => $employeeId,
            'order_datetime' => now(),
            'status' => Order::STATUS_OPEN,
            'subtotal' => 0,
            'tax' => 0,
            'total' => 0,
            'discount' => 0,
            'billed' => false
        ]);

        $order->save();

        return $order;
    }

    /**
     * Release the table, marking it as available.
     * This should only be called after the order is completed and billed.
     */
    public function release(): bool
    {
        if ($this->hasActiveOrder()) {
            $activeOrder = $this->activeOrder()->first();

            if (!$activeOrder->billed) {
                return false; // Cannot release if there's an active order that's not billed
            }
        }

        $this->status = self::STATUS_AVAILABLE;
        $this->save();

        return true;
    }
}
