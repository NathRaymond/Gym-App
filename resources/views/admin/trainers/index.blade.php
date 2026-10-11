@extends('layouts.app')
@section('title', 'Trainers')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Trainers</h1>
            <p class="text-sm text-gray-500">{{ $trainers->total() }} trainer(s)</p>
        </div>
        <a href="{{ route('admin.trainers.create') }}"
           class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            + Add Trainer
        </a>
    </div>

    {{-- Trainers Grid --}}
    <div class="grid md:grid-cols-3 gap-4">
        @forelse($trainers as $t)
            @if($t->user)
                <div class="rounded-2xl bg-white border border-gray-200 p-5">

                    {{-- Avatar + Name --}}
                    <div class="flex items-center gap-3 mb-3">
                        <img src="{{ $t->user->avatar_url }}"
                             class="h-12 w-12 rounded-full object-cover"
                             alt="{{ $t->user->full_name }}"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($t->user->full_name) }}&background=6366f1&color=fff'">
                        <div>
                            <div class="font-semibold">{{ $t->user->full_name }}</div>
                            <div class="text-xs text-gray-500">{{ $t->user->email }}</div>
                        </div>
                    </div>

                    {{-- Specializations --}}
                    @if(!empty($t->specializations))
                        <div class="flex flex-wrap gap-1 mb-2">
                            @foreach($t->specializations as $s)
                                <span class="rounded-full bg-brand-50 text-brand-700 px-2 py-0.5 text-xs">{{ $s }}</span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Details --}}
                    <div class="text-xs text-gray-600 space-y-1">
                        <div>Experience: {{ $t->years_experience }} year(s)</div>
                        <div>Rate: ₦{{ number_format($t->hourly_rate, 2) }}/hr</div>
                        <div>Rating: ★ {{ number_format($t->rating, 1) }}</div>
                        <div class="{{ $t->is_available ? 'text-green-600' : 'text-red-600' }}">
                            {{ $t->is_available ? 'Available' : 'Unavailable' }}
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="mt-4 flex gap-2">
                        <a href="{{ route('admin.trainers.edit', $t) }}"
                           class="flex-1 rounded-lg bg-gray-100 py-1.5 text-center text-xs font-semibold hover:bg-gray-200">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('admin.trainers.destroy', $t) }}"
                              class="inline"
                              onsubmit="return confirmSubmit(event, 'Delete trainer {{ addslashes($t->user->full_name) }}?')">
                            @csrf @method('DELETE')
                            <button class="rounded-lg bg-red-50 text-red-700 px-3 py-1.5 text-xs font-semibold hover:bg-red-100">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        @empty
            <div class="col-span-full rounded-2xl bg-white border border-gray-200 p-12 text-center">
                <div class="text-4xl mb-3">🏋️</div>
                <p class="text-gray-500 font-medium">No trainers yet</p>
                <a href="{{ route('admin.trainers.create') }}"
                   class="inline-block mt-4 rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    + Add your first trainer
                </a>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($trainers->hasPages())
        <div class="mt-4">{{ $trainers->links() }}</div>
    @endif
</div>
@endsection