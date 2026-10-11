<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(protected PaymentService $payments) {}

    public function create(User $user, array $data): Booking
    {
        return DB::transaction(function () use ($user, $data) {
            $amount = $data['amount'] ?? 0;

            // If user has active membership and class is free for members
            if (!empty($data['gym_class_id'])) {
                $class = GymClass::find($data['gym_class_id']);
                if ($class && $class->is_free_for_members && $user->hasActiveMembership()) {
                    $amount = 0;
                }
            }

            $booking = Booking::create([
                'user_id' => $user->id,
                'trainer_id' => $data['trainer_id'] ?? null,
                'gym_class_id' => $data['gym_class_id'] ?? null,
                'class_schedule_id' => $data['class_schedule_id'] ?? null,
                'booking_date' => $data['booking_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'type' => $data['type'] ?? 'session',
                'amount' => $amount,
                'discount' => $data['discount'] ?? 0,
                'total_amount' => $amount - ($data['discount'] ?? 0),
                'notes' => $data['notes'] ?? null,
                'status' => $amount == 0 ? 'confirmed' : 'pending',
                'payment_status' => $amount == 0 ? 'paid' : 'unpaid',
                'created_by' => auth()->id(),
            ]);

            if ($amount == 0) {
                $this->payments->generateBookingTicket($booking);
            }

            return $booking;
        });
    }

    public function cancel(Booking $booking, string $reason): void
    {
        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $reason,
            'cancelled_at' => now(),
        ]);

        $booking->tickets()->update(['status' => 'cancelled']);
    }
}