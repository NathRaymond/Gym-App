<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SessionSlot extends Model
{
    protected $fillable = [
        'session_type_id', 'trainer_id', 'slot_date',
        'start_time', 'end_time', 'capacity', 'price',
        'location', 'status', 'notes',
    ];

    protected $casts = [
        'slot_date' => 'date',
        'price' => 'decimal:2',
    ];

    public function sessionType()
    {
        return $this->belongsTo(SessionType::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function getSpotsTakenAttribute(): int
    {
        return $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->count();
    }

    public function getSpotsLeftAttribute(): int
    {
        return max(0, $this->capacity - $this->spots_taken);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->spots_left <= 0;
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->price ?? $this->sessionType?->base_price ?? 0);
    }

    public function getStartTimeFormattedAttribute(): string
    {
        return Carbon::parse($this->start_time)->format('g:i A');
    }

    public function getEndTimeFormattedAttribute(): string
    {
        return Carbon::parse($this->end_time)->format('g:i A');
    }

    public function scopeUpcoming($q)
    {
        return $q->whereDate('slot_date', '>=', today())
                 ->where('status', 'open')
                 ->orderBy('slot_date')
                 ->orderBy('start_time');
    }
}