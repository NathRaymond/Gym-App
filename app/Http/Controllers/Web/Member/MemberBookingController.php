<?php

namespace App\Http\Controllers\Web\Member;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\SessionSlot;
use App\Models\SessionType;
use App\Services\ActivityLogger;
use App\Services\BookingService;
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MemberBookingController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function index(Request $request)
    {
        return view('member.bookings', [
            'bookings' => $request->user()->bookings()
                ->with(['sessionSlot.sessionType', 'trainer.user', 'gymClass'])
                ->latest()
                ->paginate(15),
            'wallet' => $request->user()->getOrCreateWallet(),
        ]);
    }

    public function create()
    {
        return view('member.create-booking', [
            'sessionTypes' => SessionType::active()
                ->with('category')
                ->orderBy('display_order')
                ->get(),
        ]);
    }

    /**
     * Load available slots for a session type (AJAX).
     */
    public function loadSlots(Request $request)
    {
        $data = $request->validate([
            'session_type_id' => 'required|exists:session_types,id',
        ]);

        $slots = SessionSlot::with(['sessionType', 'trainer.user'])
            ->where('session_type_id', $data['session_type_id'])
            ->upcoming()
            ->get()
            ->filter(fn($slot) => !$slot->is_full)
            ->map(function ($slot) {
                return [
                    'id'          => $slot->id,
                    'date_label'  => $slot->slot_date->format('D, M j, Y'),
                    'time_label'  => $slot->start_time_formatted . ' - ' . $slot->end_time_formatted,
                    'location'    => $slot->location,
                    'trainer'     => $slot->trainer?->user?->full_name,
                    'price'       => (float) $slot->effective_price,
                    'capacity'    => $slot->capacity,
                    'spots_left'  => $slot->spots_left,
                ];
            })
            ->values();

        return response()->json(['slots' => $slots]);
    }

    /**
     * Store a new booking for the selected slot.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'session_slot_id' => 'required|exists:session_slots,id',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $slot = SessionSlot::with('sessionType')->findOrFail($data['session_slot_id']);

        if ($slot->is_full) {
            return back()->with('error', 'This slot is fully booked.');
        }

        if ($slot->slot_date->isPast()) {
            return back()->with('error', 'This slot has already passed.');
        }

        // Check for user conflict at same time
        $conflict = Booking::where('user_id', auth()->id())
            ->where('booking_date', $slot->slot_date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function ($q) use ($slot) {
                $q->whereBetween('start_time', [$slot->start_time, $slot->end_time])
                  ->orWhereBetween('end_time', [$slot->start_time, $slot->end_time])
                  ->orWhere(function ($x) use ($slot) {
                      $x->where('start_time', '<=', $slot->start_time)
                        ->where('end_time', '>=', $slot->end_time);
                  });
            })
            ->exists();

        if ($conflict) {
            return back()->with('error', 'You already have a booking in this time slot.');
        }

        $amount = $slot->effective_price;

        $booking = Booking::create([
            'user_id'         => auth()->id(),
            'session_slot_id' => $slot->id,
            'trainer_id'      => $slot->trainer_id,
            'gym_class_id'    => null,
            'booking_date'    => $slot->slot_date,
            'start_time'      => $slot->start_time,
            'end_time'        => $slot->end_time,
            'type'            => 'session',
            'amount'          => $amount,
            'discount'        => 0,
            'total_amount'    => $amount,
            'amount_paid'     => 0,
            'payment_status'  => $amount == 0 ? 'paid' : 'unpaid',
            'status'          => $amount == 0 ? 'confirmed' : 'pending',
            'notes'           => $data['notes'] ?? null,
            'created_by'      => auth()->id(),
        ]);

        // Auto-generate ticket if free
        if ($amount == 0) {
            app(PaymentService::class)->generateBookingTicket($booking);
        }

        ActivityLogger::log('Booking created by member', $booking, [
            'booking_code' => $booking->booking_code,
            'slot_id'      => $slot->id,
            'amount'       => $amount,
        ]);

        return redirect()->route('member.bookings')
            ->with('success', "Booking {$booking->booking_code} created successfully!");
    }

    public function cancel(Request $request, Booking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (in_array($booking->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'This booking cannot be cancelled.');
        }

        $request->validate(['reason' => 'required|string|max:500']);

        $this->bookingService->cancel($booking, $request->reason);
        ActivityLogger::log('Booking cancelled by member', $booking, ['reason' => $request->reason]);

        return back()->with('success', 'Booking cancelled successfully.');
    }
}