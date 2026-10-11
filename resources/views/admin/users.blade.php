@extends('layouts.app')
@section('title', 'Users')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Manage Users</h1>
    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600"><tr><th class="text-left p-4">Name</th><th class="text-left p-4">Email</th><th class="text-left p-4">Membership #</th><th class="text-left p-4">Roles</th><th class="text-left p-4">Status</th></tr></thead>
            <tbody>
                @foreach($users as $u)
                    <tr class="border-t">
                        <td class="p-4">{{ $u->full_name }}</td>
                        <td class="p-4">{{ $u->email }}</td>
                        <td class="p-4 font-mono text-xs">{{ $u->membership_number }}</td>
                        <td class="p-4">{{ $u->getRoleNames()->join(', ') }}</td>
                        <td class="p-4"><span class="rounded-full px-2 py-1 text-xs {{ $u->status==='active'?'bg-green-50 text-green-700':'bg-gray-100' }}">{{ $u->status }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
