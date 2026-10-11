<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    protected $fillable = ['gym_class_id','instructor_id','day_of_week','start_time','end_time','capacity','room','is_active'];
    protected $casts = ['is_active' => 'boolean'];

    public function gymClass() { return $this->belongsTo(GymClass::class); }
    public function instructor() { return $this->belongsTo(User::class, 'instructor_id'); }
}