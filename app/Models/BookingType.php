<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class BookingType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'color', 'icon',
        'description', 'display_order', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($t) {
            $t->slug = $t->slug ?: Str::slug($t->name);
        });
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}