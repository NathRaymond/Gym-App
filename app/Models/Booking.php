<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'booking_code', 'user_id', 'trainer_id', 'gym_class_id', 'class_schedule_id', 'session_slot_id',
        'booking_date', 'start_time', 'end_time', 'type', 'status',
        'amount', 'discount', 'total_amount', 'amount_paid', 'payment_status',
        'payment_method', 'notes', 'cancellation_reason', 'cancelled_at', 'created_by',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'cancelled_at' => 'datetime',
        'amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($b) {
            $b->booking_code = $b->booking_code ?: 'BK-'.strtoupper(\Str::random(8));
            $b->total_amount = ($b->amount - $b->discount);
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function gymClass()
    {
        return $this->belongsTo(GymClass::class);
    }

    public function schedule()
    {
        return $this->belongsTo(ClassSchedule::class, 'class_schedule_id');
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->total_amount - $this->amount_paid);
    }

    public function scopeUpcoming($q)
    {
        return $q->where('booking_date', '>=', now()->toDateString())
            ->whereIn('status', ['pending', 'confirmed']);
    }

   public function sessionSlot()
{
    return $this->belongsTo(\App\Models\SessionSlot::class);
}
}
