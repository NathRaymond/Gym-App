@extends('layouts.app')
@section('title', 'Plans')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Membership Plans</h1>
    <div class="grid md:grid-cols-3 gap-4">
        @foreach($plans as $p)
            <div class="rounded-2xl bg-white border border-gray-200 p-5">
                <h3 class="font-semibold">{{ $p->name }}</h3>
                <div class="text-2xl font-bold mt-2">N{{ number_format($p->price) }}</div>
                <div class="text-xs text-gray-500">/ {{ $p->duration_days }} days</div>
                <div class="mt-3 text-xs {{ $p->is_active ? 'text-green-600' : 'text-red-600' }}">{{ $p->is_active ? 'Active' : 'Inactive' }}</div>
            </div>
        @endforeach
    </div>
</div>
@endsection
