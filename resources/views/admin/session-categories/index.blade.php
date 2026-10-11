@extends('layouts.app')
@section('title', 'Session Categories')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Session Categories</h1>
            <p class="text-sm text-gray-500">Group your session types (e.g. Class, PT, Consultation)</p>
        </div>
        <a href="{{ route('admin.session-categories.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
            + Add Category
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-4">Category</th>
                    <th class="text-left p-4">Session Types</th>
                    <th class="text-left p-4">Trainer Required</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-right p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $cat)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4">
                            <div class="flex items-center gap-3">
                                <div class="text-2xl">{{ $cat->icon ?? '📅' }}</div>
                                <div>
                                    <div class="font-medium">{{ $cat->name }}</div>
                                    @if($cat->description)
                                        <div class="text-xs text-gray-500">{{ Str::limit($cat->description, 60) }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4">{{ $cat->session_types_count }}</td>
                        <td class="p-4">{{ $cat->requires_trainer ? 'Yes' : 'No' }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $cat->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100' }}">
                                {{ $cat->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.session-categories.edit', $cat) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.session-categories.destroy', $cat) }}" class="inline" onsubmit="return confirmSubmit(event, 'Delete category {{ $cat->name }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-3">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-500">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection