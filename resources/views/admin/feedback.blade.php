@extends('layouts.app')
@section('title', 'Feedback')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Member Feedback</h1>
    <div class="space-y-3">
        @forelse($feedbacks as $f)
            <div class="rounded-2xl bg-white border border-gray-200 p-5">
                <div class="flex justify-between">
                    <div class="font-semibold">{{ $f->user->full_name ?? 'Anonymous' }}</div>
                    <div class="text-yellow-500">{{ str_repeat('*', $f->rating) }}</div>
                </div>
                <div class="text-sm text-gray-500 mt-1">{{ $f->title }}</div>
                <p class="mt-2 text-sm">{{ $f->message }}</p>
                <div class="mt-3 text-xs">
                    <span class="rounded-full px-2 py-1 {{ $f->is_approved ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                        {{ $f->is_approved ? 'Approved' : 'Pending' }}
                    </span>
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-center py-8">No feedback yet.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $feedbacks->links() }}</div>
</div>
@endsection
