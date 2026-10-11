@extends('layouts.app')
@section('title', 'Activity Log')
@section('content')
<div class="mx-auto max-w-7xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">Activity Log (Audit Trail)</h1>
    <div class="rounded-2xl bg-white border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr><th class="text-left p-4">When</th><th class="text-left p-4">Causer</th><th class="text-left p-4">Description</th><th class="text-left p-4">Subject</th><th class="text-left p-4">IP</th></tr>
            </thead>
            <tbody>
                @foreach($activities as $a)
                    <tr class="border-t">
                        <td class="p-4 text-xs text-gray-500">{{ $a->created_at->format('M d, Y H:i:s') }}</td>
                        <td class="p-4">{{ $a->causer?->full_name ?? 'System' }}</td>
                        <td class="p-4">{{ $a->description }}</td>
                        <td class="p-4 text-xs">{{ $a->subject_type ? class_basename($a->subject_type).'#'.$a->subject_id : '—' }}</td>
                        <td class="p-4 text-xs font-mono">{{ $a->properties['ip'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $activities->links() }}</div>
</div>
@endsection
