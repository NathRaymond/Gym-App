<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'payment_code','user_id','payable_type','payable_id','amount','currency',
        'method','status','gateway_reference','gateway_response','notes','processed_by','paid_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($p) {
            $p->payment_code = $p->payment_code ?: 'PAY-' . strtoupper(\Str::random(10));
        });
    }

    public function user() { return $this->belongsTo(User::class); }
    public function payable() { return $this->morphTo(); }
    public function processor() { return $this->belongsTo(User::class, 'processed_by'); }
}