@extends('layouts.app')
@section('title', 'Feedback')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Share Your Feedback</h1>

    {{-- Feedback Form --}}
    <div class="rounded-2xl bg-white border border-gray-200 p-6"
         x-data="{ rating: {{ old('rating', 5) }} }">

        <form method="POST" action="{{ route('member.feedback.store') }}"
              onsubmit="return confirmSubmit(event, 'Submit your feedback?', 'Yes, submit')"
              class="space-y-4">
            @csrf

            {{-- Rating --}}
            <div>
                <label class="text-sm font-medium">Rating *</label>
                <div class="flex gap-1 mt-1 text-3xl cursor-pointer select-none">
                    <template x-for="i in 5" :key="i">
                        <span @click="rating = i"
                              :class="i <= rating ? 'text-yellow-400' : 'text-gray-300'"
                              class="transition-colors">★</span>
                    </template>
                </div>
                <input type="hidden" name="rating" :value="rating">
                <p class="text-xs text-gray-500 mt-1">
                    You rated: <span x-text="rating"></span>/5
                </p>
            </div>

            {{-- Title --}}
            <div>
                <label class="text-sm font-medium">Title <span class="text-gray-400">(optional)</span></label>
                <input type="text" name="title" value="{{ old('title') }}"
                       placeholder="e.g. Best gym in Ibadan!"
                       class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>

            {{-- Message --}}
            <div>
                <label class="text-sm font-medium">Your Experience *</label>
                <textarea name="message" rows="4" required
                          placeholder="Tell us about your experience..."
                          class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('message') }}</textarea>
                <p class="text-xs text-gray-500 mt-1">Minimum 5 characters</p>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="rounded-lg bg-brand-600 px-6 py-2.5 font-semibold text-white hover:bg-brand-700 transition">
                Submit Feedback
            </button>
        </form>
    </div>

    {{-- Previous Feedback --}}
    <h2 class="font-semibold mt-10 mb-3">Your Previous Feedback</h2>
    <div class="space-y-3">
        @forelse($feedbacks as $f)
            <div class="rounded-xl bg-white border border-gray-200 p-4">
                <div class="flex justify-between items-start">
                    <div class="text-yellow-400 text-lg">
                        {{ str_repeat('★', $f->rating) }}{{ str_repeat('☆', 5 - $f->rating) }}
                    </div>
                    <span class="text-xs rounded-full px-2 py-0.5
                        {{ $f->is_approved ? 'bg-green-50 text-green-700' : 'bg-yellow-50 text-yellow-700' }}">
                        {{ $f->is_approved ? '✓ Published' : '⏳ Pending review' }}
                    </span>
                </div>
                @if($f->title)
                    <div class="font-medium mt-2">{{ $f->title }}</div>
                @endif
                <p class="text-sm text-gray-600 mt-1">{{ $f->message }}</p>
                <div class="text-xs text-gray-400 mt-2">
                    {{ $f->created_at->diffForHumans() }}
                </div>
            </div>
        @empty
            <div class="rounded-xl bg-white border border-gray-200 p-8 text-center">
                <div class="text-4xl mb-2">💬</div>
                <p class="text-sm text-gray-500">You haven't submitted any feedback yet.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection