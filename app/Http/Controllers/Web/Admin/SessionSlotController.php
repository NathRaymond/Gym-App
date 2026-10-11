<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionSlot;
use App\Models\SessionType;
use App\Models\Trainer;
use App\Services\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SessionSlotController extends Controller
{
    public function index(Request $request)
    {
        $q = SessionSlot::with(['sessionType', 'trainer.user']);

        if ($request->filled('session_type_id')) {
            $q->where('session_type_id', $request->session_type_id);
        }
        if ($request->filled('date_from')) {
            $q->whereDate('slot_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $q->whereDate('slot_date', '<=', $request->date_to);
        }
        if (!$request->filled('date_from') && !$request->filled('date_to')) {
            $q->whereDate('slot_date', '>=', today());
        }

        return view('admin.session-slots.index', [
            'slots'        => $q->orderBy('slot_date')->orderBy('start_time')->paginate(30),
            'sessionTypes' => SessionType::active()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.session-slots.create', [
            'sessionTypes' => SessionType::active()->orderBy('display_order')->get(),
            'trainers'     => Trainer::with('user')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'session_type_id' => 'required|exists:session_types,id',
            'trainer_id'      => 'nullable|exists:trainers,id',
            'slot_date'       => 'required|date|after_or_equal:today',
            'start_time'      => 'required',
            'end_time'        => 'required|after:start_time',
            'capacity'        => 'required|integer|min:1|max:500',
            'price'           => 'nullable|numeric|min:0',
            'location'        => 'nullable|string|max:100',
            'status'          => 'required|in:open,full,cancelled,completed',
            'notes'           => 'nullable|string|max:500',
        ]);

        $slot = SessionSlot::create($data);
        ActivityLogger::log('Session slot created', $slot);

        return redirect()->route('admin.session-slots.index')
            ->with('success', 'Slot created successfully.');
    }

    public function edit(SessionSlot $sessionSlot)
    {
        return view('admin.session-slots.edit', [
            'slot'         => $sessionSlot,
            'sessionTypes' => SessionType::active()->orderBy('display_order')->get(),
            'trainers'     => Trainer::with('user')->get(),
        ]);
    }

    public function update(Request $request, SessionSlot $sessionSlot)
    {
        $data = $request->validate([
            'session_type_id' => 'required|exists:session_types,id',
            'trainer_id'      => 'nullable|exists:trainers,id',
            'slot_date'       => 'required|date',
            'start_time'      => 'required',
            'end_time'        => 'required|after:start_time',
            'capacity'        => 'required|integer|min:1|max:500',
            'price'           => 'nullable|numeric|min:0',
            'location'        => 'nullable|string|max:100',
            'status'          => 'required|in:open,full,cancelled,completed',
            'notes'           => 'nullable|string|max:500',
        ]);

        $sessionSlot->update($data);
        ActivityLogger::log('Session slot updated', $sessionSlot);

        return redirect()->route('admin.session-slots.index')
            ->with('success', 'Slot updated.');
    }

    public function destroy(SessionSlot $sessionSlot)
    {
        if ($sessionSlot->bookings()->exists()) {
            return back()->with('error', 'Cannot delete a slot with existing bookings.');
        }

        ActivityLogger::log('Session slot deleted', $sessionSlot);
        $sessionSlot->delete();

        return redirect()->route('admin.session-slots.index')
            ->with('success', 'Slot deleted.');
    }

    /**
     * Bulk-generate slots for a session type.
     */
    public function bulkCreate()
    {
        return view('admin.session-slots.bulk', [
            'sessionTypes' => SessionType::active()->orderBy('display_order')->get(),
            'trainers'     => Trainer::with('user')->get(),
        ]);
    }

    public function bulkStore(Request $request)
    {
        // =====================================================================
        // Normalization layer (supports 3 generations of input format):
        //   (a) NEW — slot_times[monday]=08:00&slot_times[wednesday]=12:00 ...
        //   (b) Legacy — flat time_slots[] array (positional, maps to days_of_week order)
        //   (c) Legacy — time_slots JSON string / comma-separated textbox
        // =====================================================================

        // --- Legacy (c): decode JSON string input ---------------------------------
        $timeSlots = $request->input('time_slots');
        if (is_string($timeSlots)) {
            $decoded = json_decode($timeSlots, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $request->merge(['time_slots' => $decoded]);
            }
        }
        // --- Legacy (c): last-resort comma-text input -----------------------------
        if ($request->missing('time_slots') || !is_array($request->input('time_slots'))) {
            $raw = $request->input('time_slots_input', '');
            if (is_string($raw) && $raw !== '') {
                $parsed = array_map('trim', array_filter(explode(',', $raw)));
                $request->merge(['time_slots' => $parsed]);
            }
        }

        // --- Legacy (b) -> (a) conversion: positional time_slots[] to day-keyed slot_times[]
        // When only a flat array is present we use days_of_week ordering to map
        // time_slots[0] -> days_of_week[0], time_slots[1] -> days_of_week[1], etc.
        if ($request->missing('slot_times') || !is_array($request->input('slot_times'))) {
            $flatTimes = (array) $request->input('time_slots', []);
            $dow       = (array) $request->input('days_of_week', []);
            if (!empty($flatTimes) && !empty($dow)) {
                $mapped = [];
                foreach ($dow as $i => $dayKey) {
                    if (isset($flatTimes[$i]) && is_string($flatTimes[$i]) && $flatTimes[$i] !== '') {
                        $mapped[$dayKey] = $flatTimes[$i];
                    }
                }
                if (!empty($mapped)) {
                    $request->merge(['slot_times' => $mapped]);
                }
            }
        }

        // --- Normalize every slot_times[<day>] value into H:i ---------------------
        $normalized = [];
        foreach ((array) $request->input('slot_times', []) as $day => $t) {
            if (!is_string($t) || $t === '') continue;
            $parts = explode(':', $t);
            if (count($parts) >= 2) {
                $normalized[$day] = sprintf('%02s:%02s', $parts[0], $parts[1]);
            } else {
                $normalized[$day] = $t; // let validation reject malformed entries
            }
        }
        $request->merge(['slot_times' => $normalized]);

        // =====================================================================
        // Validation
        // =====================================================================
        $validDayKeys = implode(',', ['monday','tuesday','wednesday','thursday','friday','saturday','sunday']);
        $data = $request->validate([
            'session_type_id' => 'required|integer|exists:session_types,id',
            'start_date'      => 'required|date|after_or_equal:today',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'days_of_week'    => 'required|array|min:1',
            'days_of_week.*'  => 'in:'.$validDayKeys,
            'slot_times'      => 'required|array|min:1',
            'slot_times.*'    => 'date_format:H:i',
            'capacity'        => 'required|integer|min:1|max:500',
            'price'           => 'nullable|numeric|min:0',
            'location'        => 'nullable|string|max:100',
        ]);

        // Additional integrity: every ticked day MUST have a matching time entry.
        $missingTimes = array_diff($data['days_of_week'], array_keys($data['slot_times']));
        if (!empty($missingTimes)) {
            return back()->withInput()->withErrors([
                'slot_times' => 'Time is missing for: '.implode(', ', array_map('ucfirst', $missingTimes)).'. Please uncheck those days or enter a time for them.',
            ]);
        }

        $type = SessionType::find($data['session_type_id']);
        if (!$type) {
            return back()->withInput()->withErrors(['session_type_id' => 'Selected session type is not available.']);
        }
        if ((int) ($type->duration_minutes ?? 0) < 1) {
            return back()->withInput()->withErrors(['session_type_id' => 'Session type has an invalid duration ('.$type->duration_minutes.' min). Fix it in Session Types first.']);
        }

        $created = 0;
        $skippedDup = 0;
        $skippedWrap = [];
        $totalMatchingDates = 0;

        $cursor = Carbon::parse($data['start_date'])->startOfDay();
        $end    = Carbon::parse($data['end_date'])->startOfDay();

        while ($cursor->lte($end)) {
            $dayKey = strtolower($cursor->format('l'));
            if (in_array($dayKey, $data['days_of_week'], true) && isset($data['slot_times'][$dayKey])) {
                $totalMatchingDates++;
                $startTime = $data['slot_times'][$dayKey];

                $slotStart = $cursor->copy()->setTimeFromTimeString($startTime);
                $slotEnd   = $slotStart->copy()->addMinutes($type->duration_minutes);

                // Reject cross-midnight slots (we only store ONE slot_date column;
                // a multi-day slot would misbehave in reports and date filtering).
                if (!$slotEnd->isSameDay($slotStart)) {
                    $skippedWrap[] = $cursor->format('D M j').' '.$startTime.' ('.
                                     $type->duration_minutes.'min wraps past midnight)';
                    $cursor->addDay();
                    continue;
                }

                $startTimeDb = $slotStart->format('H:i:s');
                $endTimeDb   = $slotEnd->format('H:i:s');

                $exists = SessionSlot::where('session_type_id', $type->id)
                    ->whereDate('slot_date', $cursor->toDateString())
                    ->where('start_time', $startTimeDb)
                    ->exists();

                if (!$exists) {
                    SessionSlot::create([
                        'session_type_id' => $type->id,
                        'trainer_id'      => $type->trainer_id,
                        'slot_date'       => $cursor->toDateString(),
                        'start_time'      => $startTimeDb,
                        'end_time'        => $endTimeDb,
                        'capacity'        => $data['capacity'],
                        'price'           => $data['price'] ?? null,
                        'location'        => $data['location'] ?? null,
                        'status'          => 'open',
                    ]);
                    $created++;
                } else {
                    $skippedDup++;
                }
            }
            $cursor->addDay();
        }

        ActivityLogger::log("Bulk created {$created} session slots", $type);

        $successMsg = "{$created} slot(s) created successfully.";
        $warnings   = [];

        if ($totalMatchingDates === 0) {
            $warnings[] = 'No dates in your range (' . Carbon::parse($data['start_date'])->format('D M j') . ' → ' .
                          Carbon::parse($data['end_date'])->format('D M j') . ') fall on the ' . count($data['days_of_week']) .
                          ' weekday(s) you ticked (' . implode(', ', array_map('ucfirst', $data['days_of_week'])) .
                          '). Tick more days or widen the range.';
        }
        if ($skippedDup > 0) {
            $warnings[] = "{$skippedDup} slot(s) were skipped because identical (type + date + start_time) slots already exist.";
        }
        if (!empty($skippedWrap)) {
            $s = 'Skipped '.count($skippedWrap).' cross-midnight slot(s): '.implode('; ', array_slice($skippedWrap, 0, 3));
            if (count($skippedWrap) > 3) $s .= ' (+'.(count($skippedWrap) - 3).' more)';
            $warnings[] = $s;
        }

        if ($created === 0 && !empty($warnings)) {
            return redirect()->route('admin.session-slots.index')
                ->with('success', $successMsg)
                ->with('warning', implode(' ', $warnings));
        }
        if (!empty($warnings)) {
            return redirect()->route('admin.session-slots.index')
                ->with('success', $successMsg . ' (' . implode(' ', $warnings) . ')');
        }

        return redirect()->route('admin.session-slots.index')->with('success', $successMsg);
    }
}