@extends('layouts.app')
@section('title', 'New Booking')

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10" x-data="bookingForm()">
    <h1 class="text-2xl font-bold mb-6">Book a Session</h1>

    <form method="POST" action="{{ route('member.bookings.store') }}"
          onsubmit="return confirmSubmit(event, 'Confirm this booking?', 'Yes, book it')"
          class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        <div>
            <label class="text-sm font-medium">Session Type *</label>
            <select x-model="typeId" @change="loadSlots()" required
                    class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                <option value="">-- Choose a session type --</option>
                <template x-for="t in @js($sessionTypes->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'icon' => $t->icon]))">
                    <option :value="t.id" x-text="(t.icon || '📅') + ' ' + t.name"></option>
                </template>
            </select>
        </div>

        <div x-show="loading" class="text-center py-4">
            <svg class="animate-spin h-6 w-6 mx-auto text-brand-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
            </svg>
            <p class="text-xs text-gray-500 mt-2">Loading available slots...</p>
        </div>

        <div x-show="!loading && slots.length > 0" x-cloak>
            <label class="text-sm font-medium">Choose a Time Slot *</label>
            <div class="mt-2 space-y-2 max-h-80 overflow-y-auto">
                <template x-for="slot in slots" :key="slot.id">
                    <label class="block cursor-pointer">
                        <input type="radio" name="session_slot_id" :value="slot.id"
                               x-model="selectedSlotId" class="peer sr-only" required>
                        <div class="rounded-lg border-2 border-gray-200 p-3 peer-checked:border-brand-500 peer-checked:bg-brand-50 hover:border-brand-300 transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <div class="font-medium text-sm" x-text="slot.date_label"></div>
                                    <div class="text-xs text-gray-500" x-text="slot.time_label + (slot.location ? ' · ' + slot.location : '')"></div>
                                    <div x-show="slot.trainer" class="text-[10px] text-gray-400 mt-0.5" x-text="'with ' + slot.trainer"></div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-brand-600 text-sm" x-text="'₦' + Number(slot.price).toLocaleString()"></div>
                                    <div class="text-[10px] text-gray-500" x-text="slot.spots_left + ' / ' + slot.capacity + ' left'"></div>
                                </div>
                            </div>
                        </div>
                    </label>
                </template>
            </div>
        </div>

        <div x-show="!loading && typeId && slots.length === 0" x-cloak
             class="rounded-lg bg-yellow-50 border border-yellow-200 p-3 text-sm text-yellow-800">
            😔 No available slots for this session type. Please check back later or contact the gym.
        </div>

        <div>
            <label class="text-sm font-medium">Notes (optional)</label>
            <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('notes') }}</textarea>
        </div>

        @if(auth()->user()->wallet)
            <div class="rounded-lg bg-brand-50 p-3 text-sm text-brand-800">
                💰 Wallet: <strong>₦{{ number_format(auth()->user()->wallet->balance, 2) }}</strong>
            </div>
        @endif

        <div class="flex gap-3 pt-4">
            <button type="submit" :disabled="!selectedSlotId"
                    class="flex-1 rounded-lg bg-brand-600 py-2.5 font-semibold text-white hover:bg-brand-700 disabled:opacity-50 disabled:cursor-not-allowed">
                Confirm Booking
            </button>
            <a href="{{ route('member.bookings') }}" class="rounded-lg bg-gray-100 px-6 py-2.5 font-semibold text-gray-700">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function bookingForm() {
    return {
        typeId: '',
        slots: [],
        selectedSlotId: null,
        loading: false,

        async loadSlots() {
            this.slots = [];
            this.selectedSlotId = null;
            if (!this.typeId) return;

            this.loading = true;
            try {
                const res = await fetch('{{ route('member.bookings.slots') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify({ session_type_id: this.typeId }),
                });
                const data = await res.json();
                this.slots = data.slots || [];
            } catch (e) {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not load slots.' });
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>
@endpush
@endsection