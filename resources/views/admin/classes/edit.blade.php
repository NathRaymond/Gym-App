@extends('layouts.app')
@section('title', 'Edit Class')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Edit: {{ $class->name }}</h1>

    <form method="POST" action="{{ route('admin.classes.update', $class) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        <div><label class="text-sm font-medium">Class Name *</label>
            <input name="name" value="{{ old('name', $class->name) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>

        <div><label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="3" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('description', $class->description) }}</textarea></div>

        <div><label class="text-sm font-medium">Instructor</label>
            <select name="instructor_id" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                <option value="">— None —</option>
                @foreach($instructors as $i)
                    <option value="{{ $i->id }}" @selected(old('instructor_id', $class->instructor_id) == $i->id)>{{ $i->full_name }}</option>
                @endforeach
            </select></div>

        <div class="grid md:grid-cols-3 gap-4">
            <div><label class="text-sm font-medium">Capacity *</label>
                <input type="number" name="capacity" value="{{ old('capacity', $class->capacity) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Duration (min) *</label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $class->duration_minutes) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Price (₦)</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $class->price) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div><label class="text-sm font-medium">Difficulty *</label>
            <select name="difficulty" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                @foreach(['beginner','intermediate','advanced','all_levels'] as $d)
                    <option value="{{ $d }}" @selected(old('difficulty', $class->difficulty) === $d)>{{ ucfirst(str_replace('_', ' ', $d)) }}</option>
                @endforeach
            </select></div>

        <div class="flex gap-4">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_free_for_members" value="1" @checked(old('is_free_for_members', $class->is_free_for_members))> Free for members</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $class->is_active))> Active</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Update Class</button>
            <a href="{{ route('admin.classes.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection