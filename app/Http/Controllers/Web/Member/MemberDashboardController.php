<?php

namespace App\Http\Controllers\Web\Member;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Payment;
use Illuminate\Http\Request;

class MemberDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        return view('member.index', [
            'user' => $user,
            'wallet' => $user->getOrCreateWallet(),
            'activeMembership' => $user->activeMembership,
            'upcomingBookings' => $user->bookings()->upcoming()->with('trainer.user', 'gymClass')->limit(5)->get(),
            'recentPayments' => $user->payments()->latest()->limit(5)->get(),
            'notifications' => AppNotification::where('user_id', $user->id)->latest()->limit(5)->get(),
        ]);
    }

    public function payments(Request $request)
    {
        return view('member.payments', [
            'payments' => $request->user()->payments()->latest()->paginate(20),
        ]);
    }

    public function tickets(Request $request)
    {
        return view('member.tickets', [
            'tickets' => $request->user()->tickets()->with('booking', 'membership')->latest()->paginate(20),
        ]);
    }

    public function profile(Request $request)
    {
        return view('member.profile', ['user' => $request->user()]);
    }
}