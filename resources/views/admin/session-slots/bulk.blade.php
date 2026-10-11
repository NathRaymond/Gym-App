@extends('layouts.app')
@section('title', 'Bulk Generate Slots')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">⚡ Bulk Generate Slots</h1>

    <form id="bulkSlotForm" method="POST" action="{{ route('admin.session-slots.bulk.store') }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

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

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Start Date *</label>
                <div class="relative mt-1">
                    <input type="text" id="start_date_display" readonly
                           class="w-full rounded-lg border-gray-300 border px-3 py-2 pr-10 cursor-pointer bg-white text-sm"
                           placeholder="Pick start date">
                    <input type="date" id="start_date" name="start_date"
                           value="{{ old('start_date', date('Y-m-d')) }}"
                           min="{{ date('Y-m-d') }}" required
                           class="sr-only">
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">📅</span>
                </div>
            </div>
            <div>
                <label class="text-sm font-medium">End Date *</label>
                <div class="relative mt-1">
                    <input type="text" id="end_date_display" readonly
                           class="w-full rounded-lg border-gray-300 border px-3 py-2 pr-10 cursor-pointer bg-white text-sm"
                           placeholder="Pick end date">
                    <input type="date" id="end_date" name="end_date"
                           value="{{ old('end_date', date('Y-m-d', strtotime('+30 days'))) }}" required
                           class="sr-only">
                    <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">📅</span>
                </div>
            </div>
        </div>

        <div>
            <label class="text-sm font-medium mb-2 block">Days of Week *</label>
            <div class="flex flex-wrap gap-2" id="days_of_week_group">
                @foreach(['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day)
                    <label class="cursor-pointer">
                        <input type="checkbox" name="days_of_week[]" value="{{ $day }}" class="peer sr-only day-checkbox"
                               @checked(in_array($day, old('days_of_week', ['monday','wednesday','friday'])))>
                        <div class="rounded-lg border-2 border-gray-200 px-3 py-2 text-xs font-medium peer-checked:border-brand-500 peer-checked:bg-brand-50 transition">
                            {{ ucfirst(substr($day, 0, 3)) }}
                        </div>
                    </label>
                @endforeach
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium">Time per Selected Day *</label>
                <span class="text-xs text-gray-500" id="rowCountLabel">0 rows</span>
            </div>

            <div id="timeSlotsContainer" class="space-y-2">
            </div>
            <p class="text-xs text-gray-500 mt-2">Check/uncheck days above — a time row appears/disappears per day. Each day's time is remembered if you toggle the day off then on again.</p>
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
                <input name="location" value="{{ old('location', 'Main Studio') }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div id="bulkPreview" class="rounded-lg border p-3 text-xs">
            <div class="flex items-center justify-between mb-1">
                <div class="font-semibold text-gray-800 flex items-center gap-2">
                    <span>📋 Preview</span>
                    <span id="previewBadge" class="rounded-full px-2 py-0.5 text-[10px] font-bold">...</span>
                </div>
            </div>
            <ul id="previewList" class="text-gray-700 space-y-1 list-disc pl-5">
                <li>Updating…</li>
            </ul>
            <p id="previewWarn" class="mt-2 hidden font-medium"></p>
            <p class="mt-2 text-gray-500">Only dates whose weekday is ticked above will be generated. Existing identical (type + date + start_time) slots are skipped automatically.</p>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Generate Slots</button>
            <a href="{{ route('admin.session-slots.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>

<script>
(function () {
    'use strict';

    // ============================
    // Formatters
    // ============================
    var MON_SHORT = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var DAY_NAMES = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
    var DAY_KEYS  = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    var DAY_KEY_IDX = {monday:0,tuesday:1,wednesday:2,thursday:3,friday:4,saturday:5,sunday:6};

    // "2026-10-10" -> "10-Oct-2026"
    function fmtHumanDate(ymd) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(ymd || '');
        if (!m) return '';
        return parseInt(m[3],10) + '-' + MON_SHORT[parseInt(m[2],10)-1] + '-' + m[1];
    }
    function fmtHumanDateObj(d) {
        return d.getDate() + '-' + MON_SHORT[d.getMonth()] + '-' + d.getFullYear();
    }
    // "14:00" -> "14:00 (2:00 PM)"
    function fmtHumanTime(hi) {
        var m = /^(\d{2}):(\d{2})/.exec(hi || '');
        if (!m) return '';
        var h24 = parseInt(m[1],10), mm = parseInt(m[2],10);
        var ampm = h24 >= 12 ? 'PM' : 'AM';
        var h12 = h24 % 12;
        if (h12 === 0) h12 = 12;
        return m[1] + ':' + m[2] + ' (' + h12 + ':' + String(mm).padStart(2,'0') + ' ' + ampm + ')';
    }
    // Add minutes to an HH:MM -> returns {t24, wraps}
    function addMinutes24(t, mins) {
        var m = /^(\d{2}):(\d{2})/.exec(t || '');
        if (!m || !isFinite(mins)) return { t24: t || '', wraps: false };
        var d0 = new Date(2000,0,1, parseInt(m[1],10), parseInt(m[2],10), 0);
        var d1 = new Date(d0.getTime() + mins * 60000);
        return {
            t24:   String(d1.getHours()).padStart(2,'0') + ':' + String(d1.getMinutes()).padStart(2,'0'),
            wraps: d1.getDate() !== d0.getDate()
        };
    }
    function parseYMDtoObj(v) {
        var m = /^(\d{4})-(\d{2})-(\d{2})$/.exec(v || '');
        return m ? new Date(+m[1], +m[2]-1, +m[3]) : null;
    }
    function keyOfDate(d) { return DAY_KEYS[d.getDay()===0 ? 6 : d.getDay()-1]; }
    function nameOfDate(d){ return DAY_NAMES[d.getDay()===0 ? 6 : d.getDay()-1]; }
    function fireEvent(el, name) {
        try { el.dispatchEvent(new Event(name, { bubbles: true })); } catch (_) {}
    }

    // ============================
    // Session type -> price
    // ============================
    var sessionTypeEl = document.getElementById('session_type_id');
    var priceEl       = document.getElementById('price_input');
    function currentSessionTypeMeta() {
        if (!sessionTypeEl) return { duration: 60 };
        var opt = sessionTypeEl.options[sessionTypeEl.selectedIndex];
        if (!opt || !opt.value) return { duration: 0 };
        var d = parseInt(opt.getAttribute('data-duration'), 10);
        return { duration: (isFinite(d) && d > 0) ? d : 0 };
    }
    function applySessionTypeDefaults() {
        if (!sessionTypeEl || !priceEl) return;
        var opt = sessionTypeEl.options[sessionTypeEl.selectedIndex];
        var bp  = opt ? opt.getAttribute('data-base-price') : '';
        priceEl.value = (bp !== '' && bp !== null) ? (parseFloat(bp)).toFixed(2) : '';
        priceEl.readOnly = true;
        priceEl.classList.add('bg-gray-50');
    }

    // ============================
    // Overlay picker (display + hidden native)
    // ============================
    function openNativePicker(hiddenEl) {
        // showPicker() is the standard API on Chromium/Firefox; Safari fallback: focus hidden briefly
        var worked = false;
        try {
            if (typeof hiddenEl.showPicker === 'function') {
                hiddenEl.showPicker();
                worked = true;
            }
        } catch (_) { worked = false; }
        if (!worked) {
            var savedClass = hiddenEl.className;
            hiddenEl.className = 'absolute opacity-0 w-px h-px -left-full pointer-events-auto';
            hiddenEl.classList.remove('sr-only');
            try { hiddenEl.focus(); } catch (_) {}
            try { hiddenEl.click(); } catch (_) {}
            setTimeout(function(){ hiddenEl.className = savedClass; }, 0);
        }
    }

    function wireDatePair(hiddenId, displayId) {
        var hid  = document.getElementById(hiddenId);
        var disp = document.getElementById(displayId);
        if (!hid || !disp) return;

        function sync() { disp.value = fmtHumanDate(hid.value); }
        sync();

        disp.addEventListener('click', function(){ openNativePicker(hid); });
        disp.addEventListener('focus', function(){ openNativePicker(hid); });

        hid.addEventListener('change', function(){ sync(); updatePreview(); });
        hid.addEventListener('input',  function(){ sync(); updatePreview(); });
    }

    function wireTimeRow(hiddenEl, dispEl, dayKey, onChange) {
        function sync() { dispEl.value = fmtHumanTime(hiddenEl.value); }
        sync();
        dispEl.addEventListener('click', function(){ openNativePicker(hiddenEl); });
        dispEl.addEventListener('focus', function(){ openNativePicker(hiddenEl); });
        var handler = function () {
            sync();
            if (onChange) onChange(dayKey, hiddenEl.value);
        };
        hiddenEl.addEventListener('change', handler);
        hiddenEl.addEventListener('input',  handler);
    }

    // ============================
    // State
    // ============================
    var startDateEl = document.getElementById('start_date');
    var endDateEl   = document.getElementById('end_date');
    var previewWrap = document.getElementById('bulkPreview');
    var previewBadge= document.getElementById('previewBadge');
    var previewList = document.getElementById('previewList');
    var previewWarn = document.getElementById('previewWarn');
    var container   = document.getElementById('timeSlotsContainer');
    var rowCountLbl = document.getElementById('rowCountLabel');
    var daysGroup   = document.getElementById('days_of_week_group');

    var DEFAULT_PER_DAY = {
        monday:'09:00',tuesday:'09:00',wednesday:'14:00',thursday:'09:00',
        friday:'18:00',saturday:'10:00',sunday:'10:00'
    };

    var SEED_FROM_SERVER = {};
    @php
        $oldByDay = old('slot_times');
        $seedOut = [];
        if (is_array($oldByDay) && !empty($oldByDay)) {
            foreach ($oldByDay as $d => $v) {
                if (is_string($d) && is_string($v) && $v !== '') $seedOut[$d] = $v;
            }
        } else {
            $legacyFlat = old('time_slots', []);
            if (is_array($legacyFlat)) {
                $legacyDow  = old('days_of_week', []);
                if (is_array($legacyDow)) {
                    foreach ($legacyDow as $i => $dkey) {
                        if (isset($legacyFlat[$i]) && is_string($legacyFlat[$i]) && $legacyFlat[$i] !== '') {
                            $seedOut[$dkey] = $legacyFlat[$i];
                        }
                    }
                }
            }
        }
    @endphp
    SEED_FROM_SERVER = @json($seedOut);

    var savedTimes = {};
    DAY_KEYS.forEach(function (d) {
        if (SEED_FROM_SERVER && typeof SEED_FROM_SERVER[d]==='string' && SEED_FROM_SERVER[d] !== '') {
            savedTimes[d] = SEED_FROM_SERVER[d];
        } else if (DEFAULT_PER_DAY[d]) {
            savedTimes[d] = DEFAULT_PER_DAY[d];
        }
    });

    function getSelectedDays() {
        if (!daysGroup) return [];
        var out = [];
        daysGroup.querySelectorAll('input.day-checkbox[type="checkbox"]').forEach(function(b){
            if (b.checked) out.push(b.value);
        });
        return out;
    }
    function saveCurrentTimesByRowDay() {
        if (!container) return;
        container.querySelectorAll('.time-slot-row').forEach(function (row) {
            var day = row.getAttribute('data-day');
            var hid = row.querySelector('input.hidden-time[type="time"]');
            if (day && hid) savedTimes[day] = hid.value;
        });
    }

    function buildRow(day, value) {
        var dayIdx  = DAY_KEY_IDX[day] !== undefined ? DAY_KEY_IDX[day] : DAY_KEYS.indexOf(day);
        var dayName = dayIdx >= 0 ? DAY_NAMES[dayIdx] : day;
        var wrap = document.createElement('div');
        wrap.className = 'time-slot-row flex items-center gap-2';
        wrap.setAttribute('data-day', day);
        wrap.innerHTML =
            '<span class="time-slot-label w-20 text-xs text-gray-500 font-medium flex items-center gap-1">' +
            '  <span class="time-slot-num">1</span>.' +
            '  <span class="time-slot-day text-brand-600">(' + dayName + ')</span>' +
            '</span>' +
            '<div class="flex-1 relative">' +
            '  <input type="text" readonly class="time-display w-full rounded-lg border-gray-300 border px-3 py-2 pr-10 cursor-pointer bg-white">' +
            '  <input type="time" name="slot_times[' + day + ']" class="hidden-time sr-only" required>' +
            '  <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">⏰</span>' +
            '</div>';
        var hidEl  = wrap.querySelector('input.hidden-time');
        var dispEl = wrap.querySelector('input.time-display');
        if (value) hidEl.value = value;
        wireTimeRow(hidEl, dispEl, day, function (dk, val) {
            savedTimes[dk] = val;
            updatePreview();
        });
        return wrap;
    }

    function rebuildRows() {
        if (!container) return;
        saveCurrentTimesByRowDay();
        var days = getSelectedDays();
        container.innerHTML = '';
        days.forEach(function (d) {
            container.appendChild(buildRow(d, (savedTimes[d]) ? savedTimes[d] : ''));
        });
        container.querySelectorAll('.time-slot-row').forEach(function (row, i) {
            var n = row.querySelector('.time-slot-num');
            if (n) n.textContent = String(i + 1);
        });
        if (rowCountLbl) {
            rowCountLbl.textContent = days.length + ' row' + (days.length === 1 ? '' : 's');
        }
        updatePreview();
    }

    // ============================
    // Live preview
    // ============================
    function updatePreview() {
        if (!previewWrap) return;
        var st = parseYMDtoObj(startDateEl && startDateEl.value);
        var en = parseYMDtoObj(endDateEl   && endDateEl.value);
        var days = getSelectedDays();
        var dur  = currentSessionTypeMeta().duration;

        previewWrap.className = 'rounded-lg border p-3 text-xs';
        previewWarn.classList.add('hidden');

        if (!st || !en) {
            previewBadge.textContent = 'pick dates';
            previewBadge.className   = 'rounded-full px-2 py-0.5 text-[10px] font-bold bg-gray-200 text-gray-700';
            previewList.innerHTML = '<li>Choose a start date and end date to see the plan.</li>';
            return;
        }
        if (en < st) {
            previewBadge.textContent = 'invalid range';
            previewBadge.className   = 'rounded-full px-2 py-0.5 text-[10px] font-bold bg-red-100 text-red-800';
            previewWrap.classList.add('bg-red-50','border-red-200');
            previewList.innerHTML = '<li>End date must be on or after start date.</li>';
            return;
        }
        if (days.length === 0) {
            previewBadge.textContent = '0 slots';
            previewBadge.className   = 'rounded-full px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800';
            previewWrap.classList.add('bg-amber-50','border-amber-200');
            previewList.innerHTML = '<li>No weekdays are ticked above. Check at least one day.</li>';
            return;
        }

        var items = [];
        var midnights = [];
        var daySet = {};
        days.forEach(function(k){ daySet[k] = true; });
        var c = new Date(st.getTime());
        while (c <= en) {
            var k = keyOfDate(c);
            if (daySet[k]) {
                var tHi = savedTimes[k] || '';
                if (tHi !== '') {
                    var endT = addMinutes24(tHi, dur);
                    var row = fmtHumanDateObj(c) + ' ' + nameOfDate(c) + ' at ' + fmtHumanTime(tHi);
                    if (dur > 0) row += ' → ' + fmtHumanTime(endT.t24);
                    if (endT.wraps) {
                        row += ' ⚠️ wraps midnight (will be skipped)';
                        midnights.push(fmtHumanDateObj(c) + ' ' + nameOfDate(c));
                    }
                    items.push(row);
                } else {
                    items.push(fmtHumanDateObj(c) + ' ' + nameOfDate(c) + ' — time not entered yet');
                }
            }
            c.setDate(c.getDate() + 1);
        }

        var willCreate = 0;
        items.forEach(function (s) {
            if (!/time not entered/.test(s) && !/wraps midnight/.test(s)) willCreate++;
        });
        var needTimes = items.filter(function(s){ return /time not entered/.test(s); }).length;
        var wrapsMidn = midnights.length;

        if (items.length === 0) {
            previewBadge.textContent = '0 slots';
            previewBadge.className   = 'rounded-full px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800';
            previewWrap.classList.add('bg-amber-50','border-amber-200');
            previewList.innerHTML =
                '<li>Range <b>' + fmtHumanDateObj(st) + ' → ' + fmtHumanDateObj(en) +
                '</b> but none of those dates are on your ticked weekdays (' +
                days.map(function(d){ return DAY_NAMES[DAY_KEY_IDX[d]]; }).join(', ') + ').</li>' +
                '<li>Either widen the range or tick more days (Sat/Sun for weekend ranges).</li>';
            previewWarn.className = 'mt-2 font-medium text-amber-800';
            previewWarn.textContent = '⚠️ No dates in your range match your ticked weekdays — nothing will be generated.';
            previewWarn.classList.remove('hidden');
            return;
        }

        if (willCreate === 0) {
            previewBadge.textContent = '0 new slots';
            previewBadge.className   = 'rounded-full px-2 py-0.5 text-[10px] font-bold bg-amber-100 text-amber-800';
            previewWrap.classList.add('bg-amber-50','border-amber-200');
        } else {
            previewBadge.textContent = '≈ ' + willCreate + ' new slot' + (willCreate===1 ? '' : 's');
            previewBadge.className   = 'rounded-full px-2 py-0.5 text-[10px] font-bold bg-green-100 text-green-800';
            previewWrap.classList.add('bg-green-50','border-green-200');
        }

        var previewItems = items.slice(0, 10).map(function(s){ return '<li>'+s+'</li>'; }).join('');
        if (items.length > 10) previewItems += '<li>… and ' + (items.length-10) + ' more matching day(s)</li>';
        previewList.innerHTML = previewItems;

        var warns = [];
        if (needTimes > 0) warns.push('Time not yet entered for ' + needTimes + ' matching day(s).');
        if (wrapsMidn > 0) warns.push(wrapsMidn + ' slot(s) end after midnight and will be skipped.');
        if (warns.length) {
            previewWarn.className = 'mt-2 font-medium text-amber-800';
            previewWarn.textContent = '⚠️ ' + warns.join(' ');
            previewWarn.classList.remove('hidden');
        }
    }

    // ============================
    // Wire up
    // ============================
    wireDatePair('start_date', 'start_date_display');
    wireDatePair('end_date',   'end_date_display');

    if (sessionTypeEl) sessionTypeEl.addEventListener('change', function(){
        applySessionTypeDefaults();
        updatePreview();
    });
    applySessionTypeDefaults();

    if (startDateEl) { startDateEl.addEventListener('change', updatePreview); }
    if (endDateEl)   { endDateEl.addEventListener('change',   updatePreview); }
    if (daysGroup)   { daysGroup.addEventListener('change',   function(){ rebuildRows(); }); }

    rebuildRows();
})();
</script>
@endsection
