@extends('layouts.app')
@section('title', 'Edit Role')
@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Edit Role: {{ $role->name }}</h1>

    <form method="POST" action="{{ route('admin.roles.update', $role) }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-6">
        @csrf @method('PUT')

        <div><label class="text-sm font-medium">Role Name *</label>
            <input name="name" value="{{ old('name', $role->name) }}" required class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2"
                {{ in_array($role->name, ['super-admin', 'admin', 'member']) ? 'readonly' : '' }}></div>

        <div>
            <h2 class="font-semibold mb-3">Permissions</h2>
            @foreach($permissions as $group => $perms)
                <div class="mb-4 p-3 rounded-lg bg-gray-50">
                    <div class="flex justify-between items-center mb-2">
                        <div class="font-medium text-sm uppercase text-gray-600">{{ $group }}</div>
                        <button type="button" onclick="document.querySelectorAll('.perm-{{ $group }}').forEach(c => c.checked = true)" class="text-xs text-brand-600">Select all</button>
                    </div>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($perms as $perm)
                            <label class="flex items-center gap-2 text-xs">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" class="perm-{{ $group }}"
                                    @checked(in_array($perm->name, old('permissions', $role->permissions->pluck('name')->all())))>
                                {{ $perm->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white">Update Role</button>
            <a href="{{ route('admin.roles.index') }}" class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">Cancel</a>
        </div>
    </form>
</div>
@endsection