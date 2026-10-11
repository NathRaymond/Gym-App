@extends('layouts.app')
@section('title', 'My Earnings')
@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">My Earnings</h1>
    <div class="rounded-2xl bg-white border border-gray-200 p-6">
        <table class="w-full text-sm">
            <thead class="text-gray-600">
                <tr><th class="text-left p-2">Month</th><th class="text-left p-2">Sessions</th><th class="text-left p-2">Earnings</th></tr>
            </thead>
            <tbody>
                @forelse($monthly as $m)
                    <tr class="border-t">
                        <td class="p-2">{{ $m->month }}</td>
                        <td class="p-2">{{ $m->sessions }}</td>
                        <td class="p-2 font-semibold">N{{ number_format($m->total, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="p-8 text-center text-gray-500">No earnings yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection