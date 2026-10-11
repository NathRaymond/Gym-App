@extends('layouts.app')
@section('title', 'Verify Ticket')
@section('content')
<div class="mx-auto max-w-2xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Verify Ticket</h1>
    <div class="rounded-2xl bg-white border border-gray-200 p-6">
        <input type="text" placeholder="Ticket code" class="w-full rounded-lg border-gray-300 border px-3 py-2 mb-3">
        <button class="rounded-lg bg-brand-600 px-4 py-2 text-white font-semibold">Verify</button>
    </div>
</div>
@endsection
