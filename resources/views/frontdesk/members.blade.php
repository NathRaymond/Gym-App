@extends('layouts.app')
@section('title', 'Front Desk — Members')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Members</h1>
            <p class="text-sm text-gray-500">Search and manage member accounts</p>
        </div>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-4">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Search by name, email, or membership #..."
               class="rounded-lg border-gray-300 border px-3 py-2 text-sm w-80">
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white ml-2">Search</button>
        @if(request('search'))
            <a href="{{ route('frontdesk.members') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium ml-2">Reset</a>
        @endif
    </form>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="text-left p-4">Member</th>
                        <th class="text-left p-4">Contact</th>
                        <th class="text-left p-4">Membership #</th>
                        <th class="text-left p-4">Plan</th>
                        <th class="text-left p-4">Wallet</th>
                        <th class="text-right p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $m)
                        <tr class="border-t hover:bg-gray-50 transition">
                            <td class="p-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $m->avatar_url }}" class="h-8 w-8 rounded-full" alt="">
                                    <div>
                                        <div class="font-medium">{{ $m->full_name }}</div>
                                        <div class="text-xs text-gray-500">{{ $m->status }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-xs">
                                <div>{{ $m->email }}</div>
                                <div class="text-gray-500">{{ $m->phone ?? '—' }}</div>
                            </td>
                            <td class="p-4">
                                <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $m->membership_number }}</span>
                            </td>
                            <td class="p-4 text-xs">
                                @if($m->activeMembership)
                                    <div class="font-medium">{{ $m->activeMembership->plan->name }}</div>
                                    <div class="text-[10px] text-gray-500">
                                        till {{ $m->activeMembership->end_date->format('M d, Y') }}
                                    </div>
                                @else
                                    <span class="text-gray-400">No active plan</span>
                                @endif
                            </td>
                            <td class="p-4 text-xs font-semibold">
                                ₦{{ number_format($m->wallet?->balance ?? 0, 2) }}
                            </td>
                            <td class="p-4 text-right">
                                <a href="{{ route('frontdesk.payments') }}"
                                   class="text-xs text-brand-600 hover:underline">
                                    Fund / Pay →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center">
                                <div class="text-4xl mb-3">👥</div>
                                <div class="text-gray-500 font-medium">No members found</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($members->hasPages())
        <div class="mt-4">{{ $members->withQueryString()->links() }}</div>
    @endif
</div>
@endsection