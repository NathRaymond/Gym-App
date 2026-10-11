@extends('layouts.app')
@section('title', 'Edit Booking')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Edit Booking: {{ $booking->booking_code }}</h1>

    <form method="POST" action="{{ route('admin.bookings.update', $booking) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        <div class="text-sm text-gray-600 mb-3">
            Member: <strong>{{ $booking->user->full_name }}</strong>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div><label class="text-sm font-medium">Date *</label>
                <input type="date" name="booking_date" value="{{ old('booking_date', $booking->booking_date->format('Y-m-d')) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Start *</label>
                <input type="time" name="start_time" value="{{ old('start_time', $booking->start_time) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">End *</label>
                <input type="time" name="end_time" value="{{ old('end_time', $booking->end_time) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div><label class="text-sm font-medium">Status *</label>
            <select name="status" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                @foreach(['pending','confirmed','in_progress','completed','cancelled','no_show'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $booking->status) === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select></div>

        <div><label class="text-sm font-medium">Notes</label>
            <textarea name="notes" rows="3" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('notes', $booking->notes) }}</textarea></div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Update Booking</button>
            <a href="{{ route('admin.bookings.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection