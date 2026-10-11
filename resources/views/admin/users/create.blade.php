@extends('layouts.app')
@section('title', 'Add User')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Add New User</h1>

    <form method="POST" action="{{ route('admin.users.store') }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">First Name *</label>
                <input name="first_name" value="{{ old('first_name') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Last Name *</label>
                <input name="last_name" value="{{ old('last_name') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Email *</label>
                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Phone</label>
                <input name="phone" value="{{ old('phone') }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Password *</label>
                <input type="password" name="password" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Confirm Password *</label>
                <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-medium">Role *</label>
                <select name="role" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">Select role</option>
                    @foreach($roles as $r)
                        <option value="{{ $r }}" @selected(old('role') === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Status *</label>
                <select name="status" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="active" @selected(old('status') === 'active')>Active</option>
                    <option value="inactive" @selected(old('status') === 'inactive')>Inactive</option>
                    <option value="suspended" @selected(old('status') === 'suspended')>Suspended</option>
                </select>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium">Gender</label>
                <select name="gender" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="">—</option>
                    <option value="male" @selected(old('gender') === 'male')>Male</option>
                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                    <option value="other" @selected(old('gender') === 'other')>Other</option>
                </select>
            </div>
            <div>
                <label class="text-sm font-medium">Date of Birth</label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">City</label>
                <input name="city" value="{{ old('city') }}" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <div>
            <label class="text-sm font-medium">Address</label>
            <textarea name="address" rows="2" class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('address') }}</textarea>
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">Create User</button>
            <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold text-gray-700">Cancel</a>
        </div>
    </form>
</div>
@endsection