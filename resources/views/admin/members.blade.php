@extends('layouts.app')
@section('title', 'Members')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Members</h1>
    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600"><tr>
                <th class="text-left p-4">Name</th><th class="text-left p-4">Email</th>
                <th class="text-left p-4">Membership #</th><th class="text-left p-4">Plan</th>
                <th class="text-left p-4">Status</th>
            </tr></thead>
            <tbody>
                @forelse($members as $m)
                    <tr class="border-t">
                        <td class="p-4">{{ $m->full_name }}</td>
                        <td class="p-4">{{ $m->email }}</td>
                        <td class="p-4 font-mono text-xs">{{ $m->membership_number }}</td>
                        <td class="p-4">{{ $m->activeMembership?->plan?->name ?? '-' }}</td>
                        <td class="p-4"><span class="rounded-full bg-green-50 text-green-700 px-2 py-1 text-xs">{{ $m->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">No members yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $members->links() }}</div>
</div>
@endsection
