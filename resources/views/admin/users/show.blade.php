@extends('layouts.app')
@section('title', $user->full_name)
@section('content')
<div class="mx-auto max-w-5xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">{{ $user->full_name }}</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm text-white font-semibold">Edit</a>
            <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-semibold">Back</a>
        </div>
    </div>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <img src="{{ $user->avatar_url }}" class="h-24 w-24 rounded-full mx-auto mb-4" alt="">
            <div class="text-center">
                <div class="font-semibold">{{ $user->full_name }}</div>
                <div class="text-xs text-gray-500">{{ $user->email }}</div>
                <div class="mt-2 flex justify-center gap-1">
                    @foreach($user->getRoleNames() as $r)
                        <span class="rounded-full bg-brand-50 text-brand-700 px-2 py-0.5 text-xs">{{ $r }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="md:col-span-2 rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-4">Details</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-gray-500">Membership #</dt><dd class="font-mono">{{ $user->membership_number }}</dd></div>
                <div><dt class="text-gray-500">Phone</dt><dd>{{ $user->phone ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">Status</dt><dd>{{ ucfirst($user->status) }}</dd></div>
                <div><dt class="text-gray-500">Wallet Balance</dt><dd class="font-semibold">₦{{ number_format($user->wallet?->balance ?? 0, 2) }}</dd></div>
                <div><dt class="text-gray-500">Joined</dt><dd>{{ $user->created_at->format('M d, Y') }}</dd></div>
                <div><dt class="text-gray-500">Last Login</dt><dd>{{ $user->last_login_at?->format('M d, Y H:i') ?? 'Never' }}</dd></div>
            </dl>
        </div>
    </div>

    <div class="mt-6 grid md:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Bookings</div>
            <div class="text-2xl font-bold">{{ $user->bookings->count() }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Payments</div>
            <div class="text-2xl font-bold">{{ $user->payments->count() }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-xs text-gray-500">Memberships</div>
            <div class="text-2xl font-bold">{{ $user->memberships->count() }}</div>
        </div>
    </div>
</div>
@endsection