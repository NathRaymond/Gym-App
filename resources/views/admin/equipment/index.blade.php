@extends('layouts.app')
@section('title', 'Equipment')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Equipment Inventory</h1>
        <a href="{{ route('admin.equipment.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white">+ Add Equipment</a>
    </div>

    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="text-left p-4">Name</th>
                    <th class="text-left p-4">Code</th>
                    <th class="text-left p-4">Qty</th>
                    <th class="text-left p-4">Condition</th>
                    <th class="text-left p-4">Status</th>
                    <th class="text-left p-4">Next Maintenance</th>
                    <th class="text-right p-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($equipment as $item)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="p-4 font-medium">{{ $item->name }}</td>
                        <td class="p-4 font-mono text-xs">{{ $item->code }}</td>
                        <td class="p-4">{{ $item->quantity }}</td>
                        <td class="p-4">{{ ucfirst($item->condition) }}</td>
                        <td class="p-4">
                            <span class="rounded-full px-2 py-0.5 text-xs
                                {{ $item->status === 'available' ? 'bg-green-50 text-green-700' : '' }}
                                {{ $item->status === 'in_use' ? 'bg-blue-50 text-blue-700' : '' }}
                                {{ $item->status === 'maintenance' ? 'bg-yellow-50 text-yellow-700' : '' }}
                                {{ $item->status === 'out_of_order' ? 'bg-red-50 text-red-700' : '' }}">
                                {{ ucfirst(str_replace('_',' ',$item->status)) }}
                            </span>
                        </td>
                        <td class="p-4">{{ $item->next_maintenance?->format('M d, Y') ?? '—' }}</td>
                        <td class="p-4 text-right">
                            <a href="{{ route('admin.equipment.edit', $item) }}" class="text-gray-600 hover:underline text-xs">Edit</a>
                            <form method="POST" action="{{ route('admin.equipment.destroy', $item) }}" class="inline" onsubmit="return confirmAction(event, 'Delete?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline text-xs ml-2">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="p-8 text-center text-gray-500">No equipment yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $equipment->links() }}</div>
</div>
@endsection