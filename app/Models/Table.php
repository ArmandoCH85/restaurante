<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
