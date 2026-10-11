<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SessionCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'icon', 'color', 'description',
        'default_capacity', 'requires_trainer',
        'is_active', 'display_order',
    ];

    protected $casts = [
        'requires_trainer' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($c) {
            $c->slug = $c->slug ?: Str::slug($c->name);
        });
    }

    public function sessionTypes()
    {
        return $this->hasMany(SessionType::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}