<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Feedback;
use App\Models\GymClass;
use App\Models\MembershipPlan;
use App\Models\Trainer;

class PublicController extends Controller
{
    public function plans() { return response()->json(MembershipPlan::active()->orderBy('display_order')->get()); }
    public function classes() { return response()->json(GymClass::where('is_active', true)->limit(10)->get()); }
    public function trainers() { return response()->json(Trainer::with('user')->where('is_available', true)->limit(10)->get()); }
    public function testimonials() {
        return response()->json(Feedback::with('user')->where('is_approved', true)->orderByDesc('is_featured')->latest()->limit(20)->get());
    }
    public function announcements() {
        return response()->json(Announcement::active()->whereIn('audience', ['all'])->latest()->limit(5)->get());
    }
}