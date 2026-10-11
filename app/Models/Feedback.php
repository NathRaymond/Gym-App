<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['user_id', 'feedbackable_type', 'feedbackable_id', 'rating', 'title', 'message', 'is_approved', 'is_featured', 'approved_by', 'approved_at'];

    protected $casts = ['is_approved' => 'boolean', 'is_featured' => 'boolean', 'approved_at' => 'datetime'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function feedbackable()
    {
        return $this->morphTo();
    }
}
