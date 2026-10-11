@extends('layouts.app')
@section('title', 'New Booking')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Create Booking (Admin)</h1>

    <form method="POST" action="{{ route('admin.bookings.store') }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div><label class="text-sm font-medium">Member *</label>
            <select name="user_id" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                <option value="">Select member</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->full_name }} ({{ $u->email }})</option>
                @endforeach
            </select></div>

        <div><label class="text-sm font-medium">Booking Type *</label>
            <select name="type" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                <option value="">— Select type —</option>
                @foreach($bookingTypes as $bt)
                    <option value="{{ $bt->slug }}" @selected(old('type') === $bt->slug)>{{ $bt->name }}</option>
                @endforeach
            </select></div>

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Gym Class (optional)</label>
                <select name="gym_class_id" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->id }}" @selected(old('gym_class_id') == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select></div>
            <div><label class="text-sm font-medium">Trainer (optional)</label>
                <select name="trainer_id" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($trainers as $t)
                        <option value="{{ $t->id }}" @selected(old('trainer_id') == $t->id)>{{ $t->user->full_name }}</option>
                    @endforeach
                </select></div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div><label class="text-sm font-medium">Date *</label>
                <input type="date" name="booking_date" value="{{ old('booking_date', date('Y-m-d')) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Start *</label>
                <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">End *</label>
                <input type="time" name="end_time" value="{{ old('end_time', '10:00') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Amount (₦) *</label>
                <input type="number" step="0.01" name="amount" value="{{ old('amount', 0) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Discount (₦)</label>
                <input type="number" step="0.01" name="discount" value="{{ old('discount', 0) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div><label class="text-sm font-medium">Notes</label>
            <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('notes') }}</textarea></div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Create Booking</button>
            <a href="{{ route('admin.bookings.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection