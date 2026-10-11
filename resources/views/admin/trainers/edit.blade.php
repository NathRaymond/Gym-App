@extends('layouts.app')
@section('title', 'Edit Trainer')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Edit Trainer: {{ $trainer->user->full_name }}</h1>

    <form method="POST" action="{{ route('admin.trainers.update', $trainer) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        <div><label class="text-sm font-medium">User Account *</label>
            <select name="user_id" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('user_id', $trainer->user_id) == $u->id)>{{ $u->full_name }}</option>
                @endforeach
            </select></div>

        <div><label class="text-sm font-medium">Bio</label>
            <textarea name="bio" rows="3" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('bio', $trainer->bio) }}</textarea></div>

        <div><label class="text-sm font-medium">Specializations (comma-separated)</label>
            <input name="specializations" value="{{ old('specializations', implode(', ', $trainer->specializations ?? [])) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>

        <div><label class="text-sm font-medium">Certifications (comma-separated)</label>
            <input name="certifications" value="{{ old('certifications', implode(', ', $trainer->certifications ?? [])) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Years of Experience</label>
                <input type="number" name="years_experience" value="{{ old('years_experience', $trainer->years_experience) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Hourly Rate (₦)</label>
                <input type="number" step="0.01" name="hourly_rate" value="{{ old('hourly_rate', $trainer->hourly_rate) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <label class="flex items-center gap-2"><input type="checkbox" name="is_available" value="1" @checked(old('is_available', $trainer->is_available))> Available for bookings</label>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Update Trainer</button>
            <a href="{{ route('admin.trainers.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection