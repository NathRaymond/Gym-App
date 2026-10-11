@extends('layouts.app')
@section('title', 'Edit Session Category')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Edit: {{ $category->name }}</h1>

    <form method="POST" action="{{ route('admin.session-categories.update', $category) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Name *</label>
                <input name="name" value="{{ old('name', $category->name) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Icon</label>
                <input name="icon" value="{{ old('icon', $category->icon) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Color *</label>
                <select name="color" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    @foreach(['brand','green','blue','orange','red','purple','pink','gray'] as $c)
                        <option value="{{ $c }}" @selected(old('color', $category->color) === $c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Default Capacity *</label>
                <input type="number" name="default_capacity" value="{{ old('default_capacity', $category->default_capacity) }}" min="1" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <label class="flex items-center gap-2 mt-6">
                <input type="checkbox" name="requires_trainer" value="1" @checked(old('requires_trainer', $category->requires_trainer))>
                Requires trainer
            </label>
            <label class="flex items-center gap-2 mt-6">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active))>
                Active
            </label>
            <div>
                <label class="text-sm font-medium">Display Order</label>
                <input type="number" name="display_order" value="{{ old('display_order', $category->display_order) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Update Category</button>
            <a href="{{ route('admin.session-categories.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection