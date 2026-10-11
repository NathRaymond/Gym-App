@extends('layouts.app')
@section('title', 'Edit Session Type')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Edit: {{ $sessionType->name }}</h1>

    <form method="POST" action="{{ route('admin.session-types.update', $sessionType) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        <div class="grid md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="text-sm font-medium">Name *</label>
                <input name="name" value="{{ old('name', $sessionType->name) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Icon</label>
                <input name="icon" value="{{ old('icon', $sessionType->icon) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('description', $sessionType->description) }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Category</label>
                <select name="session_category_id" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('session_category_id', $sessionType->session_category_id) == $c->id)>{{ $c->icon ?? '' }} {{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Assigned Trainer</label>
                <select name="trainer_id" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">— None —</option>
                    @foreach($trainers as $t)
                        <option value="{{ $t->id }}" @selected(old('trainer_id', $sessionType->trainer_id) == $t->id)>{{ $t->user->full_name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium">Duration (min) *</label>
                <input type="number" name="duration_minutes" value="{{ old('duration_minutes', $sessionType->duration_minutes) }}" required min="5" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Base Price (₦) *</label>
                <input type="number" step="0.01" name="base_price" value="{{ old('base_price', $sessionType->base_price) }}" required min="0" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Default Capacity *</label>
                <input type="number" name="default_capacity" value="{{ old('default_capacity', $sessionType->default_capacity) }}" required min="1" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="flex gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="requires_trainer" value="1" @checked(old('requires_trainer', $sessionType->requires_trainer))>
                Requires trainer
            </label>
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $sessionType->is_active))>
                Active
            </label>
            <div class="ml-auto">
                <label class="text-sm font-medium">Display Order</label>
                <input type="number" name="display_order" value="{{ old('display_order', $sessionType->display_order) }}" class="w-24 rounded-lg border-gray-300 border px-3 py-2 inline-block">
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Update Session Type</button>
            <a href="{{ route('admin.session-types.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection