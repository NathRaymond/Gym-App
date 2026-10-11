<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function record(
        User $user,
        float $amount,
        string $method,
        $payable = null,
        ?int $processedBy = null,
        ?string $notes = null,
        string $status = 'successful'
    ): Payment {
        return DB::transaction(function () use ($user, $amount, $method, $payable, $processedBy, $notes, $status) {

            $payment = Payment::create([
                'user_id'      => $user->id,
                'payable_type' => $payable ? get_class($payable) : null,
                'payable_id'   => $payable?->id,
                'amount'       => $amount,
                'method'       => $method,
                'status'       => $status,
                'notes'        => $notes,
                'processed_by' => $processedBy,
                'paid_at'      => $status === 'successful' ? now() : null,
            ]);

            if ($payable && $status === 'successful') {
                // ============================================
                // Determine the TOTAL for this payable type
                // ============================================
                if ($payable instanceof Booking) {
                    $total = (float) $payable->total_amount;
                } elseif ($payable instanceof Membership) {
                    // Memberships don't have total_amount column — use plan price
                    $total = (float) $payable->plan->price;
                } else {
                    $total = (float) ($payable->total_amount ?? 0);
                }

                $newPaid = (float) $payable->amount_paid + $amount;

                // ============================================
                // Determine new payment status
                // ============================================
                if ($newPaid >= $total) {
                    $paymentStatus = 'paid';
                } elseif ($newPaid > 0) {
                    $paymentStatus = 'partial';
                } else {
                    $paymentStatus = 'unpaid';
                }

                // ============================================
                // Build the update array
                // ============================================
                $update = [
                    'amount_paid'    => $newPaid,
                    'payment_status' => $paymentStatus,
                    'payment_method' => $method,
                ];

                // Booking → flip to confirmed when fully paid
                if ($payable instanceof Booking && $paymentStatus === 'paid') {
                    $update['status'] = 'confirmed';
                }

                // Membership → flip to active when fully paid
                if ($payable instanceof Membership && $paymentStatus === 'paid') {
                    $update['status'] = 'active';
                }

                $payable->update($update);

                // ============================================
                // Refresh so we see the updated values
                // ============================================
                $payable->refresh();

                // ============================================
                // Auto-generate tickets
                // ============================================
                if ($payable instanceof Booking && $payable->payment_status === 'paid') {
                    // Avoid duplicates
                    if (!$payable->tickets()->where('status', 'active')->exists()) {
                        $this->generateBookingTicket($payable);
                    }
                }

                if ($payable instanceof Membership && $payable->payment_status === 'paid') {
                    if (!$payable->tickets()->where('status', 'active')->exists()) {
                        $this->generateMembershipTicket($payable);
                    }
                }
            }

            return $payment;
        });
    }

    public function payWithWallet(User $user, float $amount, $payable, ?string $description = null): Payment
    {
        return DB::transaction(function () use ($user, $amount, $payable, $description) {
            $wallet = $user->getOrCreateWallet();
            $wallet->debit($amount, $description ?? 'Wallet payment', 'wallet', $payable);

            return $this->record($user, $amount, 'wallet', $payable, null, $description);
        });
    }

    public function generateBookingTicket(Booking $booking): Ticket
    {
        return Ticket::create([
            'user_id'     => $booking->user_id,
            'booking_id'  => $booking->id,
            'type'        => 'booking',
            'valid_from'  => $booking->booking_date->copy()->setTimeFromTimeString($booking->start_time),
            'valid_until' => $booking->booking_date->copy()->setTimeFromTimeString($booking->end_time)->addHour(),
            'status'      => 'active',
        ]);
    }

    public function generateMembershipTicket(Membership $membership): Ticket
    {
        return Ticket::create([
            'user_id'       => $membership->user_id,
            'membership_id' => $membership->id,
            'type'          => 'membership',
            'valid_from'    => $membership->start_date->startOfDay(),
            'valid_until'   => $membership->end_date->endOfDay(),
            'status'        => 'active',
        ]);
    }
}