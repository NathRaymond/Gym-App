<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Feedback;
use App\Models\GymClass;
use App\Models\MembershipPlan;
use App\Models\Trainer;

class HomeController extends Controller
{
    public function index()
    {
        return view('home', [
            'plans' => MembershipPlan::active()
                ->orderBy('display_order')
                ->get(),

            'classes' => GymClass::where('is_active', true)
                ->limit(6)
                ->get(),

            'trainers' => Trainer::with('user')
                ->whereHas('user')                 // ← only trainers whose user exists
                ->where('is_available', true)
                ->limit(4)
                ->get(),

            'testimonials' => Feedback::with('user')
                ->whereHas('user')                 // ← only testimonials whose user exists
                ->where('is_approved', true)
                ->orderByDesc('is_featured')
                ->latest()
                ->limit(6)
                ->get(),

            'announcements' => Announcement::active()
                ->where('audience', 'all')
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }
}
