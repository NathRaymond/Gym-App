@extends('layouts.app')
@section('title', 'Add Equipment')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Add Equipment</h1>

    <form method="POST" action="{{ route('admin.equipment.store') }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Name *</label>
                <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Code *</label>
                <input name="code" value="{{ old('code') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div><label class="text-sm font-medium">Description</label>
            <textarea name="description" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('description') }}</textarea></div>

        <div class="grid md:grid-cols-3 gap-4">
            <div><label class="text-sm font-medium">Quantity *</label>
                <input type="number" name="quantity" value="{{ old('quantity', 1) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Condition *</label>
                <select name="condition" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    @foreach(['new','good','fair','poor','broken'] as $c)
                        <option value="{{ $c }}" @selected(old('condition') === $c)>{{ ucfirst($c) }}</option>
                    @endforeach
                </select></div>
            <div><label class="text-sm font-medium">Status *</label>
                <select name="status" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    @foreach(['available','in_use','maintenance','out_of_order'] as $s)
                        <option value="{{ $s }}" @selected(old('status') === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                    @endforeach
                </select></div>
        </div>

        <div class="grid md:grid-cols-4 gap-4">
            <div><label class="text-sm font-medium">Purchased At</label>
                <input type="date" name="purchased_at" value="{{ old('purchased_at') }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Purchase Price</label>
                <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price') }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Last Maintenance</label>
                <input type="date" name="last_maintenance" value="{{ old('last_maintenance') }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Next Maintenance</label>
                <input type="date" name="next_maintenance" value="{{ old('next_maintenance') }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Add Equipment</button>
            <a href="{{ route('admin.equipment.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection