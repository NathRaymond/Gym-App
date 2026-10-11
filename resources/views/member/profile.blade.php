@extends('layouts.app')
@section('title', 'My Profile')

@section('content')
<div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-bold mb-6">My Profile</h1>

    <div class="rounded-2xl bg-white border border-gray-200 p-6">
        <div class="flex items-center gap-6 mb-6">
            <img src="{{ $user->avatar_url }}" class="h-20 w-20 rounded-full" alt="">
            <div>
                <h2 class="text-xl font-semibold">{{ $user->full_name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <p class="text-xs text-gray-400 mt-1">Membership #{{ $user->membership_number }}</p>
            </div>
        </div>

        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Phone</dt>
                <dd class="font-medium">{{ $user->phone ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Date of Birth</dt>
                <dd class="font-medium">{{ $user->date_of_birth?->format('M d, Y') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Gender</dt>
                <dd class="font-medium">{{ ucfirst($user->gender ?? '—') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd class="font-medium">
                    <span class="rounded-full px-2 py-1 text-xs
                        {{ $user->status === 'active' ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($user->status) }}
                    </span>
                </dd>
            </div>
            <div class="sm:col-span-2">
                <dt class="text-gray-500">Address</dt>
                <dd class="font-medium">{{ $user->address ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">City</dt>
                <dd class="font-medium">{{ $user->city ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">State</dt>
                <dd class="font-medium">{{ $user->state ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Emergency Contact</dt>
                <dd class="font-medium">{{ $user->emergency_contact_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Emergency Phone</dt>
                <dd class="font-medium">{{ $user->emergency_contact_phone ?? '—' }}</dd>
            </div>
        </dl>

        <div class="mt-6 pt-6 border-t border-gray-100">
            <h3 class="text-sm font-semibold mb-3">Account</h3>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button class="rounded-lg bg-red-50 text-red-700 px-4 py-2 text-sm font-semibold hover:bg-red-100">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</div>
@endsection