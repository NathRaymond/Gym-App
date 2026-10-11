@extends('layouts.app')
@section('title', 'New Session Category')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">New Session Category</h1>

    <form method="POST" action="{{ route('admin.session-categories.store') }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Name *</label>
                <input name="name" value="{{ old('name') }}" required placeholder="e.g. Group Class, Personal Training" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Icon (emoji)</label>
                <input name="icon" value="{{ old('icon', '📅') }}" placeholder="e.g. 🧘 💪 💬" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('description') }}</textarea>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Color *</label>
                <select name="color" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    @foreach(['brand','green','blue','orange','red','purple','pink','gray'] as $c)
                        <option value="{{ $c }}" @selected(old('color', 'brand') === $c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Default Capacity *</label>
                <input type="number" name="default_capacity" value="{{ old('default_capacity', 20) }}" min="1" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <label class="flex items-center gap-2 mt-6">
                <input type="checkbox" name="requires_trainer" value="1" @checked(old('requires_trainer'))>
                Requires trainer
            </label>
            <label class="flex items-center gap-2 mt-6">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
                Active
            </label>
            <div>
                <label class="text-sm font-medium">Display Order</label>
                <input type="number" name="display_order" value="{{ old('display_order', 0) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Create Category</button>
            <a href="{{ route('admin.session-categories.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection