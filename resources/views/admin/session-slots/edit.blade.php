@extends('layouts.app')
@section('title', 'Edit Slot')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">
        Edit Slot: {{ $slot->slot_date->format('D, M j') }} · {{ $slot->start_time_formatted }}
    </h1>

    <form method="POST" action="{{ route('admin.session-slots.update', $slot) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Session Type *</label>
                <select name="session_type_id" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    @foreach($sessionTypes as $t)
                        <option value="{{ $t->id }}" @selected(old('session_type_id', $slot->session_type_id) == $t->id)>{{ $t->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Trainer</label>
                <select name="trainer_id" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($trainers as $t)
                        <option value="{{ $t->id }}" @selected(old('trainer_id', $slot->trainer_id) == $t->id)>{{ $t->user->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium">Date *</label>
                <input type="date" name="slot_date" value="{{ old('slot_date', $slot->slot_date->format('Y-m-d')) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Start *</label>
                <input type="time" name="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($slot->start_time)->format('H:i')) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">End *</label>
                <input type="time" name="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($slot->end_time)->format('H:i')) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium">Capacity *</label>
                <input type="number" name="capacity" value="{{ old('capacity', $slot->capacity) }}" required min="1" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Price (₦)</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $slot->price) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Location</label>
                <input name="location" value="{{ old('location', $slot->location) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Status *</label>
            <select name="status" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                @foreach(['open','full','cancelled','completed'] as $s)
                    <option value="{{ $s }}" @selected(old('status', $slot->status) === $s)>{{ ucfirst($s) }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="text-sm font-medium">Notes</label>
            <textarea name="notes" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('notes', $slot->notes) }}</textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Update Slot</button>
            <a href="{{ route('admin.session-slots.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection