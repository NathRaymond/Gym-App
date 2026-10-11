<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['title', 'content', 'audience', 'starts_at', 'ends_at', 'is_active', 'created_by'];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)
            ->where(function ($x) {
                $x->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($x) {
                $x->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForAudience($q, string $role = 'all')
    {
        return $q->where(function ($x) use ($role) {
            $x->where('audience', 'all');
            if ($role !== 'all') {
                $x->orWhere('audience', $role);
            }
        });
    }
}
