<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function revenue(Request $request)
    {
        $request->validate(['from' => 'nullable|date', 'to' => 'nullable|date']);
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $data = Payment::where('status', 'successful')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
            ->selectRaw('DATE(paid_at) as date, method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('date', 'method')->orderBy('date')->get();

        $total = Payment::where('status', 'successful')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])->sum('amount');

        $byMethod = Payment::where('status', 'successful')
            ->whereBetween(DB::raw('DATE(paid_at)'), [$from, $to])
            ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('method')->get();

        return response()->json([
            'from' => $from, 'to' => $to,
            'total' => (float) $total,
            'daily' => $data,
            'by_method' => $byMethod,
        ]);
    }

    public function bookings(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        $stats = Booking::whereBetween('booking_date', [$from, $to])
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('status')->get();

        $byType = Booking::whereBetween('booking_date', [$from, $to])
            ->selectRaw('type, COUNT(*) as count')->groupBy('type')->get();

        return response()->json([
            'from' => $from, 'to' => $to,
            'by_status' => $stats,
            'by_type' => $byType,
        ]);
    }

    public function members(Request $request)
    {
        $total = User::role('member')->count();
        $new = User::role('member')->whereBetween('created_at', [
            $request->get('from', now()->startOfMonth()),
            $request->get('to', now()),
        ])->count();
        $byStatus = Membership::selectRaw('status, COUNT(*) as count')->groupBy('status')->get();
        $expiringSoon = Membership::where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays(30)])
            ->with('user', 'plan')->get();

        return response()->json([
            'total_members' => $total,
            'new_members' => $new,
            'by_status' => $byStatus,
            'expiring_soon' => $expiringSoon,
        ]);
    }
}