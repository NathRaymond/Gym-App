@extends('layouts.app')
@section('title', 'Classes')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Gym Classes</h1>
    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600"><tr>
                <th class="text-left p-4">Name</th><th class="text-left p-4">Difficulty</th>
                <th class="text-left p-4">Capacity</th><th class="text-left p-4">Price</th>
            </tr></thead>
            <tbody>
                @foreach($classes as $c)
                    <tr class="border-t">
                        <td class="p-4">{{ $c->name }}</td>
                        <td class="p-4">{{ ucfirst(str_replace('_', ' ', $c->difficulty)) }}</td>
                        <td class="p-4">{{ $c->capacity }}</td>
                        <td class="p-4">N{{ number_format($c->price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $classes->links() }}</div>
</div>
@endsection
