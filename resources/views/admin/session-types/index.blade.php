@extends('layouts.app')
@section('title', 'Session Types')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold">Session Types</h1>
            <p class="text-sm text-gray-500">What members can book (Yoga, PT, etc.)</p>
        </div>
        <a href="{{ route('admin.session-types.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">
            + Add Session Type
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-4">Session Type</th>
                    <th class="text-left p-4">Category</th>
                    <th class="text-left p-4">Duration</th>
                    <th class="text-left p-4">Price</th>
                    <th class="text-left p-4">Capacity</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-right p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessionTypes as $type)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xl">{{ $type->icon ?? '📅' }}</span>
                                <div>
                                    <div class="font-medium">{{ $type->name }}</div>
                                    @if($type->trainer)
                                        <div class="text-xs text-gray-500">Trainer: {{ $type->trainer->user->full_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="p-4">
                            @if($type->category)
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs">{{ $type->category->name }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="p-4 text-xs">{{ $type->duration_minutes }} min</td>
                        <td class="p-4 text-xs font-medium">₦{{ number_format($type->base_price, 2) }}</td>
                        <td class="p-4 text-xs">{{ $type->default_capacity }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $type->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100' }}">
                                {{ $type->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.session-types.edit', $type) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.session-types.destroy', $type) }}" class="inline" onsubmit="return confirmSubmit(event, 'Delete {{ $type->name }}?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-3">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-500">No session types yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $sessionTypes->links() }}</div>
</div>
@endsection