<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\ActivityLogger;
use App\Services\BookingService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(protected BookingService $service) {}

    public function index(Request $request)
    {
        $query = Booking::with(['user', 'trainer.user', 'gymClass']);

        if (!$request->user()->isStaff()) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('date')) $query->whereDate('booking_date', $request->date);
        if ($request->filled('user_id') && $request->user()->isStaff()) $query->where('user_id', $request->user_id);

        return response()->json($query->latest()->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'trainer_id' => 'nullable|exists:trainers,id',
            'gym_class_id' => 'nullable|exists:gym_classes,id',
            'class_schedule_id' => 'nullable|exists:class_schedules,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'type' => 'nullable|in:personal_training,group_class,session,consultation',
            'amount' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id', // staff booking for others
        ]);

        $user = ($request->user()->isStaff() && !empty($data['user_id']))
            ? \App\Models\User::find($data['user_id'])
            : $request->user();

        $booking = $this->service->create($user, $data);

        ActivityLogger::log('Booking created', $booking, [
            'booking_code' => $booking->booking_code,
            'user' => $user->full_name,
        ]);

        return response()->json(['message' => 'Booking created', 'booking' => $booking->load(['user', 'gymClass', 'trainer.user'])], 201);
    }

    public function show(Booking $booking)
    {
        $this->authorizeBookingAccess($booking);
        return response()->json($booking->load(['user', 'trainer.user', 'gymClass', 'payments', 'tickets']));
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorizeBookingAccess($booking);
        $request->validate(['reason' => 'required|string|max:500']);

        $this->service->cancel($booking, $request->reason);
        ActivityLogger::log('Booking cancelled', $booking, ['reason' => $request->reason]);

        return response()->json(['message' => 'Booking cancelled', 'booking' => $booking->fresh()]);
    }

    protected function authorizeBookingAccess(Booking $booking): void
    {
        $user = auth()->user();
        if (!$user->isStaff() && $booking->user_id !== $user->id) abort(403);
    }
}