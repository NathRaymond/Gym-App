@extends('layouts.app')
@section('title', 'Announcements')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Announcements</h1>
        <a href="{{ route('admin.announcements.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">+ New</a>
    </div>

    <div class="space-y-3">
        @forelse($announcements as $a)
            <div class="rounded-2xl bg-white border border-gray-200 p-5">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold">{{ $a->title }}</h3>
                        <div class="text-xs text-gray-500 mt-1">
                            Audience: {{ ucfirst($a->audience) }} ·
                            {{ $a->created_at->format('M d, Y') }} ·
                            <span class="{{ $a->is_active ? 'text-green-600' : 'text-red-600' }}">{{ $a->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.announcements.edit', $a) }}" class="text-xs text-gray-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('admin.announcements.destroy', $a) }}" onsubmit="return confirmAction(event, 'Delete?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                </div>
                <p class="mt-2 text-sm text-gray-700">{{ Str::limit($a->content, 200) }}</p>
            </div>
        @empty
            <p class="text-gray-500 text-center py-8">No announcements yet.</p>
        @endforelse
    </div>
    <div class="mt-4">{{ $announcements->links() }}</div>
</div>
@endsection