@extends('layouts.app')
@section('title', 'New Slot')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">New Session Slot</h1>

    <form method="POST" action="{{ route('admin.session-slots.store') }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Session Type *</label>
                <select name="session_type_id" id="session_type_id" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">Select session type</option>
                    @foreach($sessionTypes as $t)
                        <option value="{{ $t->id }}"
                                data-base-price="{{ $t->base_price ?? '' }}"
                                data-duration="{{ $t->duration_minutes ?? '' }}"
                                data-trainer-id="{{ $t->trainer_id ?? '' }}"
                                @selected(old('session_type_id') == $t->id)>{{ $t->name }} ({{ $t->duration_minutes }}min)</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Trainer</label>
                <select name="trainer_id" id="trainer_id" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($trainers as $t)
                        <option value="{{ $t->id }}" @selected(old('trainer_id') == $t->id)>{{ $t->user->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium">Date *</label>
                <input type="date" name="slot_date" value="{{ old('slot_date', date('Y-m-d')) }}" min="{{ date('Y-m-d') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Start Time *</label>
                <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">End Time *</label>
                <input type="time" name="end_time" value="{{ old('end_time', '10:00') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium">Capacity *</label>
                <input type="number" name="capacity" value="{{ old('capacity', 20) }}" required min="1" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Price (₦)</label>
                <input type="number" step="0.01" name="price" id="price_input"
                       value="{{ old('price') }}"
                       readonly
                       placeholder="Select session type"
                       class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2 bg-gray-50">
                <p class="text-xs text-gray-500 mt-1">Auto-filled from session type price</p>
            </div>
            <div>
                <label class="text-sm font-medium">Location</label>
                <input name="location" value="{{ old('location') }}" placeholder="e.g. Studio A" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Status *</label>
            <select name="status" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                @foreach(['open','full','cancelled','completed'] as $s)
                    <option value="{{ $s }}" @selected(old('status', 'open') === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-medium">Notes</label>
            <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('notes') }}</textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Create Slot</button>
            <a href="{{ route('admin.session-slots.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>

<script>
(function () {
    var sessionTypeEl = document.getElementById('session_type_id');
    var trainerEl     = document.getElementById('trainer_id');
    var priceEl       = document.getElementById('price_input');
    var startTimeEl   = document.querySelector('input[name="start_time"]');
    var endTimeEl     = document.querySelector('input[name="end_time"]');

    var lastUserEndTime  = endTimeEl ? endTimeEl.value : '';
    var lastUserPrice    = priceEl   ? priceEl.value   : '';

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function addMinutesToHm(hm, minutes) {
        if (!hm || !minutes) return null;
        var parts = hm.split(':');
        if (parts.length < 2) return null;
        var d = new Date();
        d.setHours(parseInt(parts[0], 10), parseInt(parts[1], 10), 0, 0);
        d.setTime(d.getTime() + minutes * 60 * 1000);
        return pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    function applySessionTypeDefaults() {
        if (!sessionTypeEl) return;
        var opt      = sessionTypeEl.options[sessionTypeEl.selectedIndex];
        var basePrice = opt ? opt.getAttribute('data-base-price') : '';
        var duration  = opt ? parseInt(opt.getAttribute('data-duration'), 10) : null;
        var trainerId = opt ? opt.getAttribute('data-trainer-id') : '';

        // Price: readonly auto-fill (keep old value if user manually typed one via old())
        if (priceEl) {
            if (basePrice !== '' && basePrice !== null) {
                priceEl.value = (parseFloat(basePrice)).toFixed(2);
                priceEl.readOnly = true;
                priceEl.classList.add('bg-gray-50');
            } else {
                priceEl.value = '';
                priceEl.readOnly = true;
                priceEl.classList.add('bg-gray-50');
            }
        }

        // Trainer: pre-select if session type has a default trainer
        if (trainerEl && trainerId) {
            var found = false;
            for (var i = 0; i < trainerEl.options.length; i++) {
                if (trainerEl.options[i].value === String(trainerId)) {
                    trainerEl.selectedIndex = i;
                    found = true;
                    break;
                }
            }
            if (!found) {
                // leave the current (old()) value alone if it doesn't match
            }
        }

        // End time: auto-compute end time from start + duration (don't override if user set explicitly)
        if (startTimeEl && endTimeEl && duration) {
            var computed = addMinutesToHm(startTimeEl.value, duration);
            if (computed) {
                // Preserve user's explicit old end time only if it's the currently selected value
                endTimeEl.value = computed;
            }
        }
    }

    function recalcEndTime() {
        if (!sessionTypeEl || !startTimeEl || !endTimeEl) return;
        var opt      = sessionTypeEl.options[sessionTypeEl.selectedIndex];
        var duration = opt ? parseInt(opt.getAttribute('data-duration'), 10) : null;
        if (!duration) return;
        var computed = addMinutesToHm(startTimeEl.value, duration);
        if (computed) endTimeEl.value = computed;
    }

    if (sessionTypeEl) {
        sessionTypeEl.addEventListener('change', applySessionTypeDefaults);
    }
    if (startTimeEl) {
        startTimeEl.addEventListener('change', recalcEndTime);
    }

    // Run once on page load so it works with old('session_type_id') too
    applySessionTypeDefaults();
})();
</script>
@endsection