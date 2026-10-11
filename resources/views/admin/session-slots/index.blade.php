@extends('layouts.app')
@section('title', 'Session Slots')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Session Slots</h1>
            <p class="text-sm text-gray-500">Scheduled dates & times for session types</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.session-slots.bulk') }}" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">
                ⚡ Bulk Generate
            </a>
            <a href="{{ route('admin.session-slots.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
                + Add Slot
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <select name="session_type_id" class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
            <option value="">All Session Types</option>
            @foreach($sessionTypes as $t)
                <option value="{{ $t->id }}" @selected(request('session_type_id') == $t->id)>{{ $t->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        <a href="{{ route('admin.session-slots.index') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm">Reset</a>
    </form>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-4">Date</th>
                    <th class="text-left p-4">Time</th>
                    <th class="text-left p-4">Session</th>
                    <th class="text-left p-4">Trainer</th>
                    <th class="text-left p-4">Spots</th>
                    <th class="text-left p-4">Price</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-right p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($slots as $slot)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4 text-xs">
                            <div class="font-medium">{{ $slot->slot_date->format('D, M j') }}</div>
                            <div class="text-gray-500">{{ $slot->slot_date->format('Y') }}</div>
                        </td>
                        <td class="p-4 text-xs">{{ $slot->start_time_formatted }} - {{ $slot->end_time_formatted }}</td>
                        <td class="p-4">
                            <div class="font-medium text-xs">{{ $slot->sessionType?->name }}</div>
                            @if($slot->location)
                                <div class="text-[10px] text-gray-500">{{ $slot->location }}</div>
                            @endif
                        </td>
                        <td class="p-4 text-xs">{{ $slot->trainer?->user?->full_name ?? '—' }}</td>
                        <td class="p-4 text-xs">
                            <span class="{{ $slot->spots_left <= 2 ? 'text-orange-600 font-medium' : '' }}">
                                {{ $slot->spots_left }} / {{ $slot->capacity }}
                            </span>
                        </td>
                        <td class="p-4 text-xs font-medium">₦{{ number_format($slot->effective_price, 2) }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-2 py-0.5 text-[11px]
                                {{ $slot->status === 'open' ? 'bg-green-50 text-green-700' : '' }}
                                {{ $slot->status === 'full' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                {{ $slot->status === 'cancelled' ? 'bg-red-50 text-red-700' : '' }}
                                {{ $slot->status === 'completed' ? 'bg-gray-100' : '' }}">
                                {{ ucfirst($slot->status) }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.session-slots.edit', $slot) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.session-slots.destroy', $slot) }}" class="inline" onsubmit="return confirmSubmit(event, 'Delete this slot?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-3">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="p-8 text-center text-gray-500">No slots found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $slots->withQueryString()->links() }}</div>
</div>
@endsection