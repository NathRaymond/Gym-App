<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();

        $revenueToday = Payment::where('status', 'successful')->whereDate('paid_at', $today)->sum('amount');
        $revenueMonth = Payment::where('status', 'successful')->where('paid_at', '>=', $monthStart)->sum('amount');
        $totalMembers = User::role('member')->count();
        $activeMembers = Membership::active()->count();
        $bookingsToday = Booking::whereDate('booking_date', $today)->count();
        $pendingBookings = Booking::where('status', 'pending')->count();

        // Revenue last 30 days for chart
        $chart = Payment::where('status', 'successful')
            ->where('paid_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
            ->groupBy('date')->orderBy('date')->get();

        // Recent transactions
        $recentPayments = Payment::with('user')->latest()->limit(10)->get();
        $recentBookings = Booking::with('user', 'gymClass')->latest()->limit(10)->get();

        // Top plans
        $topPlans = Membership::selectRaw('membership_plan_id, COUNT(*) as count')
            ->with('plan')->groupBy('membership_plan_id')->orderByDesc('count')->limit(5)->get();

        return response()->json([
            'revenue_today' => (float) $revenueToday,
            'revenue_month' => (float) $revenueMonth,
            'total_members' => $totalMembers,
            'active_members' => $activeMembers,
            'bookings_today' => $bookingsToday,
            'pending_bookings' => $pendingBookings,
            'revenue_chart' => $chart,
            'recent_payments' => $recentPayments,
            'recent_bookings' => $recentBookings,
            'top_plans' => $topPlans,
        ]);
    }
}