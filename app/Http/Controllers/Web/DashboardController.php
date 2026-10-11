<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Booking;
use App\Models\Feedback;
use App\Models\GymClass;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Payment;
use App\Models\Setting;
use App\Models\Ticket;
use App\Models\Trainer;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return view('member.index', [
            'user' => $user,
            'wallet' => $user->getOrCreateWallet(),
            'activeMembership' => $user->activeMembership,
            'upcomingBookings' => $user->bookings()
                ->upcoming()
                ->with(['sessionSlot.sessionType', 'sessionSlot.trainer.user', 'gymClass', 'trainer.user'])
                ->limit(5)
                ->get(),
            'recentPayments' => $user->payments()->latest()->limit(5)->get(),
            'notifications' => AppNotification::where('user_id', $user->id)
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }

    public function bookings(Request $request)
    {
        return view('member.bookings', [
            'bookings' => $request->user()->bookings()->with('trainer.user', 'gymClass')->latest()->paginate(15),
        ]);
    }

    public function createBooking()
    {
        return view('member.create-booking', [
            'classes' => GymClass::where('is_active', true)->get(),
            'trainers' => Trainer::with('user')->where('is_available', true)->get(),
        ]);
    }

    public function membership()
    {
        return view('member.membership', [
            'memberships' => auth()->user()->memberships()->with('plan')->latest()->get(),
            'plans' => MembershipPlan::active()->orderBy('display_order')->get(),
        ]);
    }

    public function wallet()
    {
        $wallet = auth()->user()->getOrCreateWallet();

        return view('member.wallet', [
            'wallet' => $wallet,
            'transactions' => $wallet->transactions()->latest()->paginate(20),
        ]);
    }

    public function payments()
    {
        return view('member.payments', [
            'payments' => auth()->user()->payments()->latest()->paginate(20),
        ]);
    }

    public function tickets()
    {
        return view('member.tickets', [
            'tickets' => auth()->user()->tickets()->with('booking', 'membership')->latest()->paginate(20),
        ]);
    }

    public function feedback()
    {
        return view('member.feedback', [
            'feedbacks' => auth()->user()->feedbacks()->latest()->get(),
        ]);
    }

    public function profile()
    {
        return view('member.profile', ['user' => auth()->user()]);
    }

    // ===== ADMIN =====
    public function adminDashboard()
    {
        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();

        return view('admin.dashboard', [
            'revenueToday' => Payment::where('status', 'successful')->whereDate('paid_at', $today)->sum('amount'),
            'revenueMonth' => Payment::where('status', 'successful')->where('paid_at', '>=', $monthStart)->sum('amount'),
            'totalMembers' => User::role('member')->count(),
            'activeMembers' => Membership::active()->count(),
            'bookingsToday' => Booking::whereDate('booking_date', $today)->count(),
            'pendingBookings' => Booking::where('status', 'pending')->count(),
            'recentPayments' => Payment::with('user')->latest()->limit(10)->get(),
            'recentBookings' => Booking::with('user', 'gymClass')->latest()->limit(10)->get(),
            'revenueChart' => Payment::where('status', 'successful')
                ->where('paid_at', '>=', now()->subDays(29)->startOfDay())
                ->selectRaw('DATE(paid_at) as date, SUM(amount) as total')
                ->groupBy('date')->orderBy('date')->get(),
            'topPlans' => Membership::selectRaw('membership_plan_id, COUNT(*) as count')
                ->with('plan')->groupBy('membership_plan_id')->orderByDesc('count')->limit(5)->get(),
        ]);
    }

    public function frontDeskDashboard()
    {
        return view('frontdesk.dashboard', [
            'todayBookings' => Booking::whereDate('booking_date', now()->toDateString())
                ->whereIn('status', ['pending', 'confirmed'])
                ->with('user', 'gymClass')
                ->latest()
                ->get(),

            'pendingPayments' => Booking::where('payment_status', '!=', 'paid')
                ->whereIn('status', ['pending', 'confirmed'])
                ->with('user')
                ->latest()
                ->limit(20)
                ->get(),

            'recentPayments' => Payment::with('user')
                ->whereDate('created_at', now()->toDateString())
                ->latest()
                ->limit(10)
                ->get(),

            'ticketsToday' => Ticket::whereDate('created_at', now()->toDateString())->count(),
            'pendingTickets' => Ticket::where('status', 'active')
                ->where('valid_until', '>=', now())
                ->count(),
            'membershipsExpiring' => Membership::where('status', 'active')
                ->whereBetween('end_date', [now(), now()->addDays(7)])
                ->with('user', 'plan')
                ->limit(10)
                ->get(),
        ]);
    }

    // ============================================
    // TRAINER DASHBOARD
    // ============================================
    public function trainerDashboard(Request $request)
    {
        $trainer = $request->user()->trainer;

        return view('trainer.dashboard', [
            'trainer' => $trainer,
            'todaySessions' => $trainer
                ? Booking::where('trainer_id', $trainer->id)
                    ->whereDate('booking_date', now()->toDateString())
                    ->with('user', 'gymClass')
                    ->orderBy('start_time')
                    ->get()
                : collect(),

            'upcomingSessions' => $trainer
                ? Booking::where('trainer_id', $trainer->id)
                    ->where('booking_date', '>', now()->toDateString())
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->with('user', 'gymClass')
                    ->orderBy('booking_date')
                    ->limit(10)
                    ->get()
                : collect(),

            'completedSessions' => $trainer
                ? Booking::where('trainer_id', $trainer->id)
                    ->where('status', 'completed')
                    ->count()
                : 0,

            'totalClients' => $trainer
                ? Booking::where('trainer_id', $trainer->id)
                    ->distinct('user_id')
                    ->count('user_id')
                : 0,

            'monthEarnings' => $trainer
                ? Booking::where('trainer_id', $trainer->id)
                    ->where('payment_status', 'paid')
                    ->where('status', 'completed')
                    ->whereMonth('booking_date', now()->month)
                    ->sum('amount_paid')
                : 0,

            'recentReviews' => $trainer
                ? Feedback::where('feedbackable_type', Trainer::class)
                    ->where('feedbackable_id', $trainer->id)
                    ->with('user')
                    ->latest()
                    ->limit(5)
                    ->get()
                : collect(),
        ]);
    }

    // ============================================
    // TRAINER SUB-PAGES
    // ============================================
    public function trainerSchedule(Request $request)
    {
        $trainer = $request->user()->trainer;

        return view('trainer.schedule', [
            'trainer' => $trainer,
            'sessions' => $trainer
                ? Booking::where('trainer_id', $trainer->id)
                    ->where('booking_date', '>=', now()->toDateString())
                    ->with('user', 'gymClass')
                    ->orderBy('booking_date')
                    ->paginate(20)
                : collect(),
        ]);
    }

    public function trainerClients(Request $request)
    {
        $trainer = $request->user()->trainer;
        $clients = $trainer
            ? User::whereIn('id',
                Booking::where('trainer_id', $trainer->id)->pluck('user_id')
            )->withCount(['bookings as sessions_count' => function ($q) use ($trainer) {
                $q->where('trainer_id', $trainer->id);
            }])->paginate(20)
            : collect();

        return view('trainer.clients', ['trainer' => $trainer, 'clients' => $clients]);
    }

    public function trainerEarnings(Request $request)
    {
        $trainer = $request->user()->trainer;

        return view('trainer.earnings', [
            'trainer' => $trainer,
            'monthly' => $trainer
                ? Booking::where('trainer_id', $trainer->id)
                    ->where('payment_status', 'paid')
                    ->where('status', 'completed')
                    ->selectRaw('DATE_FORMAT(booking_date, "%Y-%m") as month, COUNT(*) as sessions, SUM(amount_paid) as total')
                    ->groupBy('month')
                    ->orderByDesc('month')
                    ->limit(12)
                    ->get()
                : collect(),
        ]);
    }

    public function adminUsers()
    {
        return view('admin.users', ['users' => User::with('roles')->latest()->paginate(20)]);
    }

    public function adminMembers()
    {
        return view('admin.members', ['members' => User::role('member')->with('activeMembership.plan')->latest()->paginate(20)]);
    }

    public function adminPlans()
    {
        return view('admin.plans', ['plans' => MembershipPlan::orderBy('display_order')->get()]);
    }

    public function adminClasses()
    {
        return view('admin.classes', ['classes' => GymClass::with('instructor')->latest()->paginate(20)]);
    }

    public function adminTrainers()
    {
        return view('admin.trainers', ['trainers' => Trainer::with('user')->paginate(20), 'users' => User::all()]);
    }

    public function adminBookings()
    {
        return view('admin.bookings', ['bookings' => Booking::with('user', 'trainer.user', 'gymClass')->latest()->paginate(20)]);
    }

    public function adminPayments()
    {
        return view('admin.payments', ['payments' => Payment::with('user', 'processor')->latest()->paginate(20)]);
    }

    public function frontDesk()
    {
        return view('admin.frontdesk', [
            'bookings' => Booking::whereIn('status', ['pending', 'confirmed'])->with('user')->latest()->limit(50)->get(),
            'memberships' => Membership::whereIn('status', ['pending', 'active'])->with('user', 'plan')->latest()->limit(50)->get(),
        ]);
    }

    public function verifyTicketPage()
    {
        return view('admin.verify-ticket');
    }

    public function reportRevenue(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        return view('admin.reports.revenue', [
            'from' => $from,
            'to' => $to,
            'daily' => Payment::where('status', 'successful')->whereBetween(\DB::raw('DATE(paid_at)'), [$from, $to])
                ->selectRaw('DATE(paid_at) as date, method, SUM(amount) as total, COUNT(*) as count')
                ->groupBy('date', 'method')->orderBy('date')->get(),
            'total' => Payment::where('status', 'successful')->whereBetween(\DB::raw('DATE(paid_at)'), [$from, $to])->sum('amount'),
            'byMethod' => Payment::where('status', 'successful')->whereBetween(\DB::raw('DATE(paid_at)'), [$from, $to])
                ->selectRaw('method, SUM(amount) as total, COUNT(*) as count')->groupBy('method')->get(),
        ]);
    }

    public function reportBookings(Request $request)
    {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());

        return view('admin.reports.bookings', [
            'from' => $from, 'to' => $to,
            'byStatus' => Booking::whereBetween('booking_date', [$from, $to])->selectRaw('status, COUNT(*) as count, SUM(total_amount) as revenue')->groupBy('status')->get(),
            'byType' => Booking::whereBetween('booking_date', [$from, $to])->selectRaw('type, COUNT(*) as count')->groupBy('type')->get(),
        ]);
    }

    public function reportMembers()
    {
        return view('admin.reports.members', [
            'total' => User::role('member')->count(),
            'new' => User::role('member')->where('created_at', '>=', now()->startOfMonth())->count(),
            'byStatus' => Membership::selectRaw('status, COUNT(*) as count')->groupBy('status')->get(),
            'expiringSoon' => Membership::where('status', 'active')->whereBetween('end_date', [now(), now()->addDays(30)])->with('user', 'plan')->get(),
        ]);
    }

    public function activityLog(Request $request)
    {
        return view('admin.activity', [
            'activities' => Activity::with(['causer', 'subject'])->latest()->paginate(50),
        ]);
    }

    public function adminFeedback()
    {
        return view('admin.feedback', ['feedbacks' => Feedback::with('user')->latest()->paginate(20)]);
    }

    public function adminRoles()
    {
        return view('admin.roles', [
            'roles' => Role::with('permissions')->get(),
            'permissions' => Permission::all()->groupBy(fn ($p) => explode('.', $p->name)[0]),
        ]);
    }

    public function adminSettings()
    {
        return view('admin.settings', ['settings' => Setting::orderBy('group')->get()->groupBy('group')]);
    }

    // ============================================
    // FRONT DESK � ADDITIONAL METHODS
    // ============================================
    public function frontDeskPayments()
    {
        return view('frontdesk.payments', [
            'bookings' => Booking::whereIn('status', ['pending', 'confirmed'])
                ->where('payment_status', '!=', 'paid')
                ->with('user')
                ->latest()
                ->paginate(30),
            'memberships' => Membership::whereIn('status', ['pending', 'active'])
                ->with('user', 'plan')
                ->latest()
                ->paginate(30),
        ]);
    }

    public function frontDeskMembers()
    {
        return view('frontdesk.members', [
            'members' => User::role('member')
                ->with('activeMembership.plan', 'wallet')
                ->latest()
                ->paginate(30),
        ]);
    }

    public function frontDeskBookings()
    {
        return view('frontdesk.bookings', [
            'bookings' => Booking::with('user', 'gymClass', 'trainer.user')
                ->latest()
                ->paginate(30),
        ]);
    }
}
