@extends('layouts.app')
@section('title', 'Members Report')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Members Report</h1>
    <div class="grid md:grid-cols-2 gap-4 mb-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-sm text-gray-500">Total Members</div>
            <div class="text-3xl font-bold">{{ $total }}</div>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-5">
            <div class="text-sm text-gray-500">New This Month</div>
            <div class="text-3xl font-bold">{{ $new }}</div>
        </div>
    </div>
    <div class="rounded-2xl bg-white border border-gray-200 p-6">
        <h2 class="font-semibold mb-3">Memberships by Status</h2>
        <table class="w-full text-sm">
            <thead class="text-gray-600"><tr><th class="text-left p-2">Status</th><th class="text-left p-2">Count</th></tr></thead>
            <tbody>
                @foreach($byStatus as $s)
                    <tr class="border-t"><td class="p-2">{{ ucfirst($s->status) }}</td><td class="p-2">{{ $s->count }}</td></tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
