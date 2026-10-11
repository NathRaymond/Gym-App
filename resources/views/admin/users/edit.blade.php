@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Edit: {{ $user->full_name }}</h1>

    <form method="POST" action="{{ route('admin.users.update', $user) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf @method('PUT')

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">First Name *</label>
                <input name="first_name" value="{{ old('first_name', $user->first_name) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Last Name *</label>
                <input name="last_name" value="{{ old('last_name', $user->last_name) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Email *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Phone</label>
                <input name="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">New Password (leave blank to keep)</label>
                <input type="password" name="password" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
            <div><label class="text-sm font-medium">Confirm New Password</label>
                <input type="password" name="password_confirmation" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"></div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div><label class="text-sm font-medium">Role *</label>
                <select name="role" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    @foreach($roles as $r)
                        <option value="{{ $r }}" @selected($user->hasRole($r))>{{ ucfirst($r) }}</option>
                    @endforeach
                </select></div>
            <div><label class="text-sm font-medium">Status *</label>
                <select name="status" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="active" @selected($user->status === 'active')>Active</option>
                    <option value="inactive" @selected($user->status === 'inactive')>Inactive</option>
                    <option value="suspended" @selected($user->status === 'suspended')>Suspended</option>
                </select></div>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Update User</button>
            <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold text-gray-700">Cancel</a>
        </div>
    </form>
</div>
@endsection