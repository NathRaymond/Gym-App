@extends('layouts.app')
@section('title', 'Gym Classes')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Gym Classes</h1>
        <a href="{{ route('admin.classes.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">+ Add Class</a>
    </div>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-4">Name</th>
                    <th class="text-left p-4">Instructor</th>
                    <th class="text-left p-4">Difficulty</th>
                    <th class="text-left p-4">Capacity</th>
                    <th class="text-left p-4">Price</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-right p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $class)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4 font-medium">{{ $class->name }}</td>
                        <td class="p-4">{{ $class->instructor?->full_name ?? '—' }}</td>
                        <td class="p-4">{{ ucfirst(str_replace('_', ' ', $class->difficulty)) }}</td>
                        <td class="p-4">{{ $class->capacity }}</td>
                        <td class="p-4">₦{{ number_format($class->price, 2) }}</td>
                        <td class="p-4"><span class="rounded-full px-2 py-0.5 text-xs {{ $class->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100' }}">{{ $class->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.classes.edit', $class) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" class="inline" onsubmit="return confirmAction(event, 'Delete this class?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-500">No classes yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $classes->links() }}</div>
</div>
@endsection