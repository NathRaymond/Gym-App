@extends('layouts.app')
@section('title', 'New Announcement')
@section('content')
<div class="mx-auto max-w-3xl px-4 py-10">
    <h1 class="text-2xl font-bold mb-6">New Announcement</h1>

    <form method="POST" action="{{ route('admin.announcements.store') }}" class="rounded-2xl bg-white border border-gray-200 p-6 space-y-4">
        @csrf

        @if($errors->any())
            <div class="rounded-lg bg-red-50 p-3 text-sm text-red-700 border border-red-200">
                <ul class="list-disc pl-5">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        <div>
            <label class="text-sm font-medium">Title *</label>
            <input name="title" value="{{ old('title') }}" required
                   class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
        </div>

        <div>
            <label class="text-sm font-medium">Content *</label>
            <textarea name="content" rows="5" required
                      class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">{{ old('content') }}</textarea>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm font-medium">Audience *</label>
                <select name="audience" required
                        class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
                    <option value="all" @selected(old('audience') === 'all')>
                        🌍 Everyone
                    </option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(old('audience') === $role)>
                            {{ ucfirst(str_replace('-', ' ', $role)) }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 mt-1">
                    Options come from your Roles list. Manage roles in <a href="{{ route('admin.roles.index') }}" class="text-brand-600 hover:underline">Roles &amp; Permissions</a>.
                </p>
            </div>
            <div>
                <label class="text-sm font-medium">Starts At</label>
                <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
            <div>
                <label class="text-sm font-medium">Ends At</label>
                <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                       class="mt-1 w-full rounded-lg border-gray-300 border px-3 py-2">
            </div>
        </div>

        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true))>
            Active
        </label>

        <div class="flex gap-3 pt-4">
            <button class="rounded-lg bg-brand-600 px-6 py-2 font-semibold text-white hover:bg-brand-700">
                Publish
            </button>
            <a href="{{ route('admin.announcements.index') }}"
               class="rounded-lg bg-gray-100 px-6 py-2 font-semibold">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection