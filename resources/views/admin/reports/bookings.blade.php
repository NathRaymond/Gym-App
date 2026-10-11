@extends('layouts.app')
@section('title', 'Bookings Report')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Bookings Report</h1>
    <div class="grid md:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-3">By Status</h2>
            <table class="w-full text-sm">
                <thead class="text-gray-600"><tr><th class="text-left p-2">Status</th><th class="text-left p-2">Count</th><th class="text-left p-2">Revenue</th></tr></thead>
                <tbody>
                    @foreach($byStatus as $s)
                        <tr class="border-t"><td class="p-2">{{ ucfirst($s->status) }}</td><td class="p-2">{{ $s->count }}</td><td class="p-2">N{{ number_format($s->revenue ?? 0, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-3">By Type</h2>
            <table class="w-full text-sm">
                <thead class="text-gray-600"><tr><th class="text-left p-2">Type</th><th class="text-left p-2">Count</th></tr></thead>
                <tbody>
                    @foreach($byType as $t)
                        <tr class="border-t"><td class="p-2">{{ ucfirst(str_replace('_', ' ', $t->type)) }}</td><td class="p-2">{{ $t->count }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
