<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SessionType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'icon', 'description', 'session_category_id',
        'duration_minutes', 'base_price', 'default_capacity',
        'trainer_id', 'requires_trainer', 'is_active', 'display_order',
    ];

    protected $casts = [
        'requires_trainer' => 'boolean',
        'is_active' => 'boolean',
        'base_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($t) {
            $t->slug = $t->slug ?: Str::slug($t->name);
        });
    }

    public function category()
    {
        return $this->belongsTo(SessionCategory::class, 'session_category_id');
    }

    public function trainer()
    {
        return $this->belongsTo(Trainer::class);
    }

    public function slots()
    {
        return $this->hasMany(SessionSlot::class);
    }

    public function upcomingSlots()
    {
        return $this->hasMany(SessionSlot::class)
            ->whereDate('slot_date', '>=', today())
            ->where('status', 'open')
            ->orderBy('slot_date')
            ->orderBy('start_time');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}