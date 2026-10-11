@extends('layouts.app')
@section('title', 'Edit Plan')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Edit: {{ $plan->name }}</h1>

    <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div><label class="text-sm font-medium">Plan Name *</label>
            <input name="name" value="{{ old('name', $plan->name) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>

        <div><label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('description', $plan->description) }}</textarea></div>

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Price (₦) *</label>
                <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Duration Type *</label>
                <select name="duration_type" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    @foreach(['daily','weekly','monthly','quarterly','yearly','custom'] as $t)
                        <option value="{{ $t }}" @selected(old('duration_type', $plan->duration_type) === $t)>{{ ucfirst($t) }}</option>
                    @endforeach
                </select></div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div><label class="text-sm font-medium">Duration (days) *</label>
                <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Sessions Included</label>
                <input type="number" name="sessions_included" value="{{ old('sessions_included', $plan->sessions_included) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Freeze Days</label>
                <input type="number" name="freeze_days" value="{{ old('freeze_days', $plan->freeze_days) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <label class="flex items-center gap-2 mt-6"><input type="checkbox" name="has_pt_sessions" value="1" @checked(old('has_pt_sessions', $plan->has_pt_sessions))> Has PT sessions</label>
            <div><label class="text-sm font-medium">PT Sessions Count</label>
                <input type="number" name="pt_sessions_count" value="{{ old('pt_sessions_count', $plan->pt_sessions_count) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Display Order</label>
                <input type="number" name="display_order" value="{{ old('display_order', $plan->display_order) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div><label class="text-sm font-medium">Features (one per line)</label>
            <textarea name="features" rows="4" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('features', implode("\n", $plan->features ?? [])) }}</textarea></div>

        <div class="flex gap-4">
            <label class="flex items-center gap-2"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->is_active))> Active</label>
            <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $plan->is_featured))> Featured</label>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Update Plan</button>
            <a href="{{ route('admin.plans.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection