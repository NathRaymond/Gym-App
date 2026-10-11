@extends('layouts.app')
@section('title', 'My Schedule')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">My Schedule</h1>
    <div class="rounded-2xl bg-white border border-gray-200 p-6">
        @forelse($sessions as $s)
            <div class="flex justify-between py-3 border-b last:border-0 text-sm">
                <div>{{ $s->user->full_name }} - {{ $s->gymClass?->name ?? ucfirst($s->type) }}</div>
                <div>{{ $s->booking_date->format('M d') }} {{ $s->start_time }}</div>
            </div>
        @empty
            <p class="text-sm text-gray-500">No sessions scheduled.</p>
        @endforelse
        @if(method_exists($sessions, 'links'))<div class="mt-4">{{ $sessions->links() }}</div>@endif
    </div>
</div>
@endsection