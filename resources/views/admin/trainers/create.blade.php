@extends('layouts.app')
@section('title', 'Add Trainer')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Add Trainer Profile</h1>

    <form method="POST" action="{{ route('admin.trainers.store') }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div><label class="text-sm font-medium">User Account (must have trainer role) *</label>
            <select name="user_id" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                <option value="">Select user</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" @selected(old('user_id') == $u->id)>{{ $u->full_name }} ({{ $u->email }})</option>
                @endforeach
            </select>
            @if($users->isEmpty())
                <p class="text-xs text-red-600 mt-1">No users with trainer role available. <a href="{{ route('admin.users.create') }}" class="underline">Create one</a> first.</p>
            @endif
        </div>

        <div><label class="text-sm font-medium">Bio</label>
            <textarea name="bio" rows="3" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('bio') }}</textarea></div>

        <div><label class="text-sm font-medium">Specializations (comma-separated)</label>
            <input name="specializations" value="{{ old('specializations') }}" placeholder="Strength Training, HIIT, Yoga" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>

        <div><label class="text-sm font-medium">Certifications (comma-separated)</label>
            <input name="certifications" value="{{ old('certifications') }}" placeholder="ACE Certified, First Aid" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Years of Experience</label>
                <input type="number" name="years_experience" value="{{ old('years_experience', 0) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Hourly Rate (₦)</label>
                <input type="number" step="0.01" name="hourly_rate" value="{{ old('hourly_rate', 0) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <label class="flex items-center gap-2"><input type="checkbox" name="is_available" value="1" @checked(old('is_available', true))> Available for bookings</label>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Create Trainer</button>
            <a href="{{ route('admin.trainers.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection