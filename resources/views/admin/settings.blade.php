@extends('layouts.app')
@section('title', 'Settings')
@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Settings</h1>
    @foreach($settings as $group => $items)
        <div class="rounded-2xl bg-white border border-gray-200 p-5 mb-4">
            <h2 class="font-semibold mb-3 capitalize">{{ $group }}</h2>
            <div class="space-y-2">
                @foreach($items as $s)
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $s->key }}</span>
                        <span class="font-medium">{{ $s->value }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@endsection
