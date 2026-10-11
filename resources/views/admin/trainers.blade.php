@extends('layouts.app')
@section('title', 'Trainers')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Trainers</h1>
    <div class="grid md:grid-cols-3 gap-4">
        @foreach($trainers as $t)
            <div class="rounded-2xl bg-white border border-gray-200 p-5">
                <div class="font-semibold">{{ $t->user->full_name ?? '-' }}</div>
                <div class="text-xs text-gray-500">{{ $t->user->email ?? '' }}</div>
                <div class="mt-2 text-sm">Rate: N{{ number_format($t->hourly_rate, 2) }}/hr</div>
                <div class="text-sm">Rating: {{ $t->rating }} / 5</div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $trainers->links() }}</div>
</div>
@endsection
