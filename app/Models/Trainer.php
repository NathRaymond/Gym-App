<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trainer extends Model
{
    protected $fillable = [
        'user_id','bio','specializations','certifications','years_experience',
        'hourly_rate','rating','total_reviews','is_available','availability',
    ];

    protected $casts = [
        'specializations' => 'array',
        'certifications' => 'array',
        'availability' => 'array',
        'is_available' => 'boolean',
        'hourly_rate' => 'decimal:2',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
}