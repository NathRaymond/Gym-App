<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MembershipPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','slug','description','price','duration_days','duration_type',
        'sessions_included','freeze_days','has_pt_sessions','pt_sessions_count',
        'features','is_active','is_featured','display_order',
    ];

    protected $casts = [
        'features' => 'array',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'has_pt_sessions' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($plan) {
            $plan->slug = $plan->slug ?: Str::slug($plan->name);
        });
    }

    public function memberships() { return $this->hasMany(Membership::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }
}