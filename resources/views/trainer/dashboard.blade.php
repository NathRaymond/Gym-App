@extends('layouts.app')
@section('title', 'Trainer Dashboard')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Welcome, {{ auth()->user()->first_name }}</h1>
            <p class="text-sm text-gray-500">{{ $trainer?->bio ?? 'Trainer dashboard' }}</p>
        </div>
    </div>

    <div class="grid md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Today's Sessions</div>
            <div class="text-2xl font-bold mt-1">{{ $todaySessions->count() }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Total Clients</div>
            <div class="text-2xl font-bold mt-1">{{ $totalClients }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Completed Sessions</div>
            <div class="text-2xl font-bold mt-1">{{ $completedSessions }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">This Month Earnings</div>
            <div class="text-2xl font-bold mt-1">N{{ number_format($monthEarnings, 0) }}</div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-4">Today's Schedule</h2>
            @forelse($todaySessions as $s)
                <div class="flex justify-between py-3 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $s->user->full_name }}</div>
                        <div class="text-xs text-gray-500">{{ $s->gymClass?->name ?? ucfirst($s->type) }}</div>
                    </div>
                    <div class="text-xs">{{ $s->start_time }} - {{ $s->end_time }}</div>
                </div>
            @empty
                <p class="text-sm text-gray-500">No sessions today.</p>
            @endforelse
        </div>

        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-4">Upcoming Sessions</h2>
            @forelse($upcomingSessions as $s)
                <div class="flex justify-between py-3 border-b last:border-0 text-sm">
                    <div>
                        <div class="font-medium">{{ $s->user->full_name }}</div>
                        <div class="text-xs text-gray-500">{{ $s->booking_date->format('M d, Y') }} · {{ $s->start_time }}</div>
                    </div>
                    <span class="text-xs rounded-full bg-gray-100 px-2 py-1 h-fit">{{ ucfirst($s->status) }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-500">No upcoming sessions.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection