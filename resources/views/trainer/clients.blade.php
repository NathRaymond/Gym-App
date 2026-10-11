@extends('layouts.app')
@section('title', 'My Clients')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">My Clients</h1>
    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr><th class="text-left p-4">Name</th><th class="text-left p-4">Email</th><th class="text-left p-4">Sessions</th></tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                    <tr class="border-t">
                        <td class="p-4">{{ $c->full_name }}</td>
                        <td class="p-4">{{ $c->email }}</td>
                        <td class="p-4">{{ $c->sessions_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center text-gray-500">No clients yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection