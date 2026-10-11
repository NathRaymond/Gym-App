@extends('layouts.app')
@section('title', 'Bookings')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">All Bookings</h1>
    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600"><tr>
                <th class="text-left p-4">Code</th><th class="text-left p-4">User</th>
                <th class="text-left p-4">Type</th><th class="text-left p-4">Date</th>
                <th class="text-left p-4">Amount</th><th class="text-left p-4">Status</th>
            </tr></thead>
            <tbody>
                @forelse($bookings as $b)
                    <tr class="border-t">
                        <td class="p-4 font-mono text-xs">{{ $b->booking_code }}</td>
                        <td class="p-4">{{ $b->user->full_name ?? '-' }}</td>
                        <td class="p-4">{{ $b->gymClass?->name ?? ucfirst(str_replace('_', ' ', $b->type)) }}</td>
                        <td class="p-4">{{ $b->booking_date->format('M d, Y') }} {{ $b->start_time }}</td>
                        <td class="p-4">N{{ number_format($b->total_amount, 2) }}</td>
                        <td class="p-4"><span class="rounded-full bg-gray-100 px-2 py-1 text-xs">{{ ucfirst($b->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-500">No bookings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $bookings->links() }}</div>
</div>
@endsection
