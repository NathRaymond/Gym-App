@extends('layouts.app')
@section('title', 'Roles & Permissions')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Roles & Permissions</h1>
        <a href="{{ route('admin.roles.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">+ New Role</a>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        @foreach($roles as $role)
            <div class="rounded-2xl bg-white border border-gray-200 p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">{{ $role->name }}</h3>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $role->permissions->count() }} permissions · {{ $role->users_count ?? 0 }} users
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.roles.edit', $role) }}" class="text-xs text-gray-600 hover:underline">Edit</a>
                        @unless(in_array($role->name, ['super-admin', 'admin', 'member', 'front-desk', 'trainer', 'manager']))
                            <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" class="inline" onsubmit="return confirmAction(event, 'Delete role?')">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Delete</button>
                            </form>
                        @endunless
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach($role->permissions->take(15) as $perm)
                        <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs">{{ $perm->name }}</span>
                    @endforeach
                    @if($role->permissions->count() > 15)
                        <span class="text-xs text-gray-500">+{{ $role->permissions->count() - 15 }} more</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection