<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'ticket_code','user_id','booking_id','membership_id','qr_code_path',
        'type','valid_from','valid_until','status','used_at','verified_by',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'used_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($t) {
            $t->ticket_code = $t->ticket_code ?: 'TKT-' . strtoupper(\Str::random(10));
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function booking() { return $this->belongsTo(Booking::class); }
    public function membership() { return $this->belongsTo(Membership::class); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && now()->between($this->valid_from, $this->valid_until);
    }
}