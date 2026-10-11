<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingType;
use App\Models\GymClass;
use App\Models\Trainer;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function index(Request $request)
    {
        $q = Booking::with(['user', 'trainer.user', 'gymClass']);

        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('date'))   $q->whereDate('booking_date', $request->date);
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($x) use ($s) {
                $x->where('booking_code', 'like', "%{$s}%")
                  ->orWhereHas('user', fn($u) => $u->where('first_name', 'like', "%{$s}%")->orWhere('last_name', 'like', "%{$s}%"));
            });
        }

        return view('admin.bookings.index', [
            'bookings' => $q->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.bookings.create', [
            'users' => User::role('member')->get(),
            'classes' => GymClass::where('is_active', true)->get(),
            'trainers' => Trainer::with('user')->where('is_available', true)->get(),
            'bookingTypes' => BookingType::active()->orderBy('display_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'trainer_id' => 'nullable|exists:trainers,id',
            'gym_class_id' => 'nullable|exists:gym_classes,id',
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'type' => 'required|exists:booking_types,slug',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $user = User::findOrFail($data['user_id']);
        $booking = $this->bookingService->create($user, $data);

        ActivityLogger::log('Booking created by admin', $booking);

        return redirect()->route('admin.bookings.index')
            ->with('success', "Booking {$booking->booking_code} created.");
    }

    public function show(Booking $booking)
    {
        $booking->load(['user', 'trainer.user', 'gymClass', 'payments', 'tickets']);
        return view('admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        return view('admin.bookings.edit', [
            'booking' => $booking,
            'users' => User::role('member')->get(),
            'classes' => GymClass::where('is_active', true)->get(),
            'trainers' => Trainer::with('user')->where('is_available', true)->get(),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'booking_date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'status' => 'required|in:pending,confirmed,in_progress,completed,cancelled,no_show',
            'notes' => 'nullable|string',
        ]);

        $booking->update($data);
        ActivityLogger::log('Booking updated', $booking);

        return redirect()->route('admin.bookings.index')->with('success', 'Booking updated.');
    }

    public function destroy(Booking $booking)
    {
        ActivityLogger::log('Booking deleted', $booking);
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('success', 'Booking deleted.');
    }

    public function cancel(Request $request, Booking $booking)
    {
        $request->validate(['reason' => 'required|string|max:500']);
        $this->bookingService->cancel($booking, $request->reason);
        ActivityLogger::log('Booking cancelled', $booking, ['reason' => $request->reason]);

        return back()->with('success', 'Booking cancelled.');
    }
}