@extends('layouts.app')
@section('title', 'My Tickets')

@section('content')
<div class="mx-auto max-w-6xl px-4 py-10">

    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">My Tickets</h1>
            <p class="text-sm text-gray-500">Show these at the front desk to check in</p>
        </div>
        @if($tickets->total() > 0)
            <span class="text-xs bg-gray-100 text-gray-600 px-3 py-1.5 rounded-full">
                {{ $tickets->total() }} ticket{{ $tickets->total() !== 1 ? 's' : '' }}
            </span>
        @endif
    </div>

    {{-- Ticket Grid --}}
    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($tickets as $t)
            @php
                $isActive = $t->status === 'active' && now()->between($t->valid_from, $t->valid_until);
                $isExpired = $t->status === 'expired' || now()->isAfter($t->valid_until);
                $isUsed = $t->status === 'used';
                $isCancelled = $t->status === 'cancelled';

                // Days/hours remaining
                $expiresIn = null;
                if ($isActive) {
                    $expiresIn = now()->diffInHours($t->valid_until, false);
                }

                // Type-based styling
                $typeColor = match($t->type) {
                    'membership' => 'indigo',
                    'booking'    => 'emerald',
                    'day_pass'   => 'amber',
                    default      => 'gray',
                };

                $typeLabel = match($t->type) {
                    'membership' => '🎫 Membership',
                    'booking'    => '📅 Booking',
                    'day_pass'   => '☀️ Day Pass',
                    default      => ucfirst($t->type),
                };
            @endphp

            <div class="relative rounded-2xl bg-white border-2 {{ $isActive ? "border-{$typeColor}-300" : 'border-gray-200' }} overflow-hidden shadow-sm hover:shadow-md transition-shadow">

                {{-- Top color strip --}}
                <div class="h-1.5 bg-{{ $isActive ? $typeColor . '-500' : 'gray-300' }}"></div>

                {{-- Status Badge (top right) --}}
                <div class="absolute top-4 right-4">
                    @if($isActive)
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 text-green-700 text-[10px] font-bold px-2 py-1 uppercase tracking-wide">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            Active
                        </span>
                    @elseif($isUsed)
                        <span class="rounded-full bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-1 uppercase">Used</span>
                    @elseif($isExpired)
                        <span class="rounded-full bg-gray-100 text-gray-500 text-[10px] font-bold px-2 py-1 uppercase">Expired</span>
                    @elseif($isCancelled)
                        <span class="rounded-full bg-red-50 text-red-700 text-[10px] font-bold px-2 py-1 uppercase">Cancelled</span>
                    @endif
                </div>

                <div class="p-5">

                    {{-- Type Label --}}
                    <div class="text-xs font-semibold text-{{ $typeColor }}-600 uppercase tracking-wide">
                        {{ $typeLabel }}
                    </div>

                    {{-- Details --}}
                    @if($t->membership && $t->membership->plan)
                        <div class="text-sm font-medium text-gray-700 mt-1">
                            {{ $t->membership->plan->name }}
                        </div>
                    @endif

                    @if($t->booking && $t->booking->gymClass)
                        <div class="text-sm font-medium text-gray-700 mt-1">
                            {{ $t->booking->gymClass->name }}
                        </div>
                    @endif

                    {{-- QR Code --}}
                    <div class="mt-4 flex justify-center">
                        <div class="bg-white p-2 rounded-lg border border-gray-200 {{ !$isActive ? 'opacity-40' : '' }}">
                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::size(140)
                                ->margin(0)
                                ->color(30, 41, 59)
                                ->backgroundColor(255, 255, 255)
                                ->generate($t->ticket_code) !!}
                        </div>
                    </div>

                    {{-- Ticket Code --}}
                    <div class="mt-4 text-center">
                        <div class="font-mono text-base font-bold tracking-wider text-gray-800">
                            {{ $t->ticket_code }}
                        </div>
                    </div>

                    {{-- Validity --}}
                    <div class="mt-3 pt-3 border-t border-dashed border-gray-200 text-center">
                        @if($isActive)
                            <div class="text-[11px] text-gray-500">
                                Valid until
                            </div>
                            <div class="text-xs font-semibold text-gray-700">
                                {{ $t->valid_until->format('M d, Y · H:i') }}
                            </div>
                            @if($expiresIn !== null)
                                <div class="mt-1 text-[10px] font-medium {{ $expiresIn <= 24 ? 'text-orange-600' : 'text-green-600' }}">
                                    @if($expiresIn <= 0)
                                        Expiring very soon!
                                    @elseif($expiresIn <= 24)
                                        Expires in {{ $expiresIn }}h
                                    @else
                                        Expires in {{ ceil($expiresIn / 24) }} day(s)
                                    @endif
                                </div>
                            @endif
                        @elseif($isUsed)
                            <div class="text-[11px] text-gray-400">
                                Checked in {{ $t->used_at?->format('M d, Y · H:i') }}
                            </div>
                        @elseif($isExpired)
                            <div class="text-[11px] text-gray-400">
                                Expired {{ $t->valid_until->diffForHumans() }}
                            </div>
                        @elseif($isCancelled)
                            <div class="text-[11px] text-red-500">
                                Ticket was cancelled
                            </div>
                        @endif
                    </div>

                    {{-- Action hints --}}
                    @if($isActive)
                        <div class="mt-3 rounded-lg bg-{{ $typeColor }}-50 px-3 py-2 text-center">
                            <div class="text-[10px] font-medium text-{{ $typeColor }}-700">
                                📱 Show this QR code at the entrance
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="md:col-span-2 lg:col-span-3 rounded-2xl bg-white border border-gray-200 p-12 text-center">
                <div class="text-6xl mb-4">🎫</div>
                <h3 class="text-lg font-semibold text-gray-700">No tickets yet</h3>
                <p class="text-sm text-gray-500 mt-2 max-w-md mx-auto">
                    Tickets are automatically generated after you book a session or subscribe to a membership plan.
                </p>
                <div class="mt-6 flex justify-center gap-3">
                    <a href="{{ route('member.bookings.create') }}"
                       class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                        + Book a Session
                    </a>
                    <a href="{{ route('member.membership') }}"
                       class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-200">
                        View Plans
                    </a>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($tickets->hasPages())
        <div class="mt-6">{{ $tickets->links() }}</div>
    @endif

    {{-- Help text --}}
    @if($tickets->count() > 0)
        <div class="mt-8 rounded-xl bg-gray-50 border border-gray-200 p-4">
            <h3 class="font-semibold text-xs mb-2">💡 How to use your tickets</h3>
            <ul class="text-[11px] text-gray-600 space-y-1">
                <li>• <strong>Booking tickets</strong> — valid for a specific date and time slot</li>
                <li>• <strong>Membership tickets</strong> — valid for the entire duration of your plan</li>
                <li>• Show the <strong>QR code</strong> at the front desk for instant check-in</li>
                <li>• Screenshot your ticket for offline access</li>
                <li>• Tickets are single-use for bookings, unlimited for memberships</li>
            </ul>
        </div>
    @endif
</div>
@endsection