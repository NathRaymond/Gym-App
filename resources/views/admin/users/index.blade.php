@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Users</h1>
            <p class="text-sm text-gray-500">Manage all system users</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            + Add User
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone..." class="rounded-lg border-gray-300 border px-3 py-2 text-sm w-64">
        <select name="role" class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
            <option value="">All Roles</option>
            @foreach($roles as $r)
                <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-lg border-gray-300 border px-3 py-2 text-sm">
            <option value="">All Status</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
        </select>
        <button class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-semibold text-white">Filter</button>
        @if(request()->hasAny(['search', 'role', 'status']))
            <a href="{{ route('admin.users.index') }}" class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium">Reset</a>
        @endif
    </form>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-4">Name</th>
                    <th class="text-left p-4">Email</th>
                    <th class="text-left p-4">Phone</th>
                    <th class="text-left p-4">Roles</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-right p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $user->avatar_url }}" class="h-8 w-8 rounded-full" alt="">
                                <div>
                                    <div class="font-medium">{{ $user->full_name }}</div>
                                    <div class="text-xs text-gray-400">{{ $user->membership_number }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="p-4">{{ $user->email }}</td>
                        <td class="p-4">{{ $user->phone ?? '—' }}</td>
                        <td class="p-4">
                            @foreach($user->getRoleNames() as $role)
                                <span class="inline-block rounded-full bg-brand-50 text-brand-700 px-2 py-0.5 text-xs">{{ $role }}</span>
                            @endforeach
                        </td>
                        <td class="p-4">
                            <span class="rounded-full px-2 py-0.5 text-xs
                                {{ $user->status === 'active' ? 'bg-green-50 text-green-700' : '' }}
                                {{ $user->status === 'inactive' ? 'bg-gray-100 text-gray-600' : '' }}
                                {{ $user->status === 'suspended' ? 'bg-red-50 text-red-700' : '' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.users.show', $user) }}" class="text-brand-600 hover:underline text-xs">View</a>
                            <a href="{{ route('admin.users.edit', $user) }}" class="text-gray-600 hover:underline text-xs ml-2">Edit</a>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
      onsubmit="return confirmSubmit(event, 'Delete {{ $user->full_name }}? This cannot be undone.', 'Yes, delete')">
    @csrf @method('DELETE')
    <button class="text-red-600 hover:underline text-xs">Delete</button>
</form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-500">No users found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->withQueryString()->links() }}</div>
</div>
@endsection