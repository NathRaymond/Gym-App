<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Membership extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'membership_code','user_id','membership_plan_id','start_date','end_date',
        'amount_paid','discount','status','sessions_used','sessions_remaining',
        'frozen_days_used','frozen_until','cancellation_reason','created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'frozen_until' => 'date',
        'amount_paid' => 'decimal:2',
        'discount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            $m->membership_code = $m->membership_code ?: 'MEM-' . strtoupper(\Str::random(8));
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function plan() { return $this->belongsTo(MembershipPlan::class, 'membership_plan_id'); }
    public function tickets() { return $this->hasMany(Ticket::class); }

    public function scopeActive($q) {
        return $q->where('status', 'active')->where('end_date', '>=', now()->toDateString());
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    public function daysRemaining(): int
    {
        return max(0, now()->diffInDays($this->end_date, false));
    }
}