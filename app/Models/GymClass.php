<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymClass extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','slug','description','image','instructor_id','capacity',
        'duration_minutes','difficulty','price','is_free_for_members','is_active',
    ];

    protected $casts = ['price' => 'decimal:2', 'is_active' => 'boolean', 'is_free_for_members' => 'boolean'];

    public function instructor() { return $this->belongsTo(User::class, 'instructor_id'); }
    public function schedules() { return $this->hasMany(ClassSchedule::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
}