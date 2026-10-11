@extends('layouts.app')
@section('title', 'Roles')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Roles &amp; Permissions</h1>
    <div class="grid md:grid-cols-2 gap-4">
        @foreach($roles as $role)
            <div class="rounded-2xl bg-white border border-gray-200 p-5">
                <h3 class="font-semibold">{{ $role->name }}</h3>
                <div class="mt-2 text-xs text-gray-500">{{ $role->permissions->count() }} permissions</div>
                <div class="mt-3 flex flex-wrap gap-1">
                    @foreach($role->permissions->take(10) as $perm)
                        <span class="rounded-full bg-gray-100 px-2 py-1 text-xs">{{ $perm->name }}</span>
                    @endforeach
                    @if($role->permissions->count() > 10)
                        <span class="text-xs text-gray-500">+{{ $role->permissions->count() - 10 }} more</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
