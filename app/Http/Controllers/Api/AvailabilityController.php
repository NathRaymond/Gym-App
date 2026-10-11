<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Trainer;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    /**
     * Get available time slots for a gym class on a given date.
     */
    public function classSlots(Request $request, GymClass $class)
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['message' => 'Date required'], 422);
        }

        try {
            $carbon = Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date'], 422);
        }

        if ($carbon->isPast()) {
            return response()->json(['slots' => []]);
        }

        $dayOfWeek = strtolower($carbon->format('l')); // "monday", "tuesday", etc.

        // Get all schedules for this class on that weekday
        $schedules = ClassSchedule::where('gym_class_id', $class->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        $slots = [];

        foreach ($schedules as $schedule) {
            // Count existing bookings for this slot
            $booked = Booking::where('gym_class_id', $class->id)
                ->where('class_schedule_id', $schedule->id)
                ->whereDate('booking_date', $carbon->toDateString())
                ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
                ->count();

            $remaining = max(0, $schedule->capacity - $booked);

            if ($remaining <= 0) {
                continue; // Fully booked
            }

            // Skip past slots if booking for today
            if ($carbon->isToday() && $schedule->start_time < now()->format('H:i:s')) {
                continue;
            }

            $slots[] = [
                'schedule_id'   => $schedule->id,
                'start_time'    => substr($schedule->start_time, 0, 5),
                'end_time'      => substr($schedule->end_time, 0, 5),
                'label'         => substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5),
                'room'          => $schedule->room,
                'capacity'      => $schedule->capacity,
                'remaining'     => $remaining,
            ];
        }

        // Sort by start time
        usort($slots, fn($a, $b) => strcmp($a['start_time'], $b['start_time']));

        return response()->json(['slots' => $slots]);
    }

    /**
     * Get available time slots for a trainer on a given date.
     */
    public function trainerSlots(Request $request, Trainer $trainer)
    {
        $date = $request->query('date');

        if (!$date) {
            return response()->json(['message' => 'Date required'], 422);
        }

        try {
            $carbon = Carbon::parse($date);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid date'], 422);
        }

        if ($carbon->isPast() || !$trainer->is_available) {
            return response()->json(['slots' => []]);
        }

        $dayOfWeek = strtolower($carbon->format('l'));

        // Trainer's availability stored as JSON: { "monday": [["09:00","12:00"]], ... }
        $availability = $trainer->availability ?? [];
        $dayWindows = $availability[$dayOfWeek] ?? [];

        // If no explicit availability configured, default to 9 AM – 6 PM
        if (empty($dayWindows)) {
            $dayWindows = [['09:00', '18:00']];
        }

        // Standard session duration (60 min)
        $sessionMinutes = 60;

        // Get existing bookings for this trainer on that date
        $bookedSlots = Booking::where('trainer_id', $trainer->id)
            ->whereDate('booking_date', $carbon->toDateString())
            ->whereIn('status', ['pending', 'confirmed', 'in_progress'])
            ->get(['start_time', 'end_time']);

        $slots = [];

        foreach ($dayWindows as $window) {
            $start = Carbon::parse($carbon->toDateString() . ' ' . $window[0]);
            $end   = Carbon::parse($carbon->toDateString() . ' ' . $window[1]);

            while ($start->copy()->addMinutes($sessionMinutes)->lte($end)) {
                $slotStart = $start->copy();
                $slotEnd   = $start->copy()->addMinutes($sessionMinutes);

                // Skip past slots for today
                if ($carbon->isToday() && $slotStart->isPast()) {
                    $start->addMinutes($sessionMinutes);
                    continue;
                }

                // Check for conflict with existing bookings
                $conflict = $bookedSlots->first(function ($b) use ($slotStart, $slotEnd) {
                    $bStart = Carbon::parse($b->start_time);
                    $bEnd   = Carbon::parse($b->end_time);

                    return $slotStart->lt($bEnd) && $slotEnd->gt($bStart);
                });

                if (!$conflict) {
                    $slots[] = [
                        'start_time' => $slotStart->format('H:i'),
                        'end_time'   => $slotEnd->format('H:i'),
                        'label'      => $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    ];
                }

                $start->addMinutes($sessionMinutes);
            }
        }

        return response()->json(['slots' => $slots]);
    }
}