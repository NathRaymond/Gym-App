@extends('layouts.app')
@section('title', 'Revenue Report')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Revenue Report</h1>
    <form method="GET" class="flex gap-3 mb-6">
        <input type="date" name="from" value="{{ $from }}" class="rounded-lg border-gray-300 border px-3 py-2">
        <input type="date" name="to" value="{{ $to }}" class="rounded-lg border-gray-300 border px-3 py-2">
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-white font-semibold">Filter</button>
    </form>

    <div class="rounded-2xl bg-white border border-gray-200 p-6 mb-6">
        <div class="text-sm text-gray-500">Total Revenue ({{ $from }} → {{ $to }})</div>
        <div class="text-3xl font-bold">₦{{ number_format($total, 2) }}</div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-3">By Payment Method</h2>
            <table class="w-full text-sm">
                <thead class="text-gray-600"><tr><th class="text-left p-2">Method</th><th class="text-left p-2">Count</th><th class="text-left p-2">Total</th></tr></thead>
                <tbody>
                    @foreach($byMethod as $m)
                        <tr class="border-t"><td class="p-2">{{ ucfirst($m->method) }}</td><td class="p-2">{{ $m->count }}</td><td class="p-2 font-semibold">₦{{ number_format($m->total, 2) }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="rounded-2xl bg-white border border-gray-200 p-6">
            <h2 class="font-semibold mb-3">Daily Revenue</h2>
            <canvas id="dailyChart"></canvas>
        </div>
    </div>
</div>
@push('scripts')
<script>
const data = @json($daily->groupBy('date')->map(fn($items) => $items->sum('total')));
new Chart(document.getElementById('dailyChart'), {
    type: 'bar',
    data: { labels: Object.keys(data), datasets: [{ label: '₦', data: Object.values(data), backgroundColor: '#6366f1' }] },
    options: { plugins: { legend: { display: false } } }
});
</script>
@endpush
@endsection
