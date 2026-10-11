@extends('layouts.app')
@section('title', 'Membership Plans')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Membership Plans</h1>
            <p class="text-sm text-gray-500">{{ $plans->count() }} plan(s) available</p>
        </div>
        <a href="{{ route('admin.plans.create') }}"
           class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            + Add Plan
        </a>
    </div>

    {{-- Plans Grid --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($plans as $plan)
            <div class="rounded-2xl bg-white border {{ $plan->is_featured ? 'border-brand-500 ring-2 ring-brand-500/20' : 'border-gray-200' }} p-5 flex flex-col">

                @if($plan->is_featured)
                    <span class="text-xs font-semibold text-brand-600">⭐ FEATURED</span>
                @endif

                <h3 class="font-semibold mt-1">{{ $plan->name }}</h3>
@if($plan->description)
    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $plan->description }}</p>
@endif

<div class="text-2xl font-bold mt-2">₦{{ number_format($plan->price) }}</div>
                <div class="text-2xl font-bold mt-2">₦{{ number_format($plan->price) }}</div>
                <div class="text-xs text-gray-500">/ {{ $plan->duration_days }} days</div>

                @if($plan->features)
                    <ul class="mt-3 text-xs text-gray-600 space-y-1 flex-1">
                        @foreach(array_slice($plan->features, 0, 4) as $f)
                            <li>• {{ $f }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="mt-3 text-xs {{ $plan->is_active ? 'text-green-600' : 'text-red-600' }}">
                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                </div>

                {{-- Actions --}}
                <div class="mt-4 flex gap-2">
                    <a href="{{ route('admin.plans.edit', $plan) }}"
                       class="flex-1 rounded-lg bg-gray-100 py-1.5 text-center text-xs font-semibold hover:bg-gray-200">
                        Edit
                    </a>

                    <form method="POST"
                          action="{{ route('admin.plans.destroy', $plan) }}"
                          onsubmit="return confirmSubmit(event, 'Delete plan &quot;{{ $plan->name }}&quot;? This cannot be undone.', 'Yes, delete')"
                          class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="rounded-lg bg-red-50 text-red-700 px-3 py-1.5 text-xs font-semibold hover:bg-red-100">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-2xl bg-white border border-gray-200 p-12 text-center">
                <div class="text-4xl mb-3">📋</div>
                <p class="text-gray-500 font-medium">No plans yet</p>
                <a href="{{ route('admin.plans.create') }}"
                   class="inline-block mt-4 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    + Create your first plan
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection