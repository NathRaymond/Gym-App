<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class AnnouncementController extends Controller
{
    public function index()
    {
        return view('admin.announcements.index', [
            'announcements' => Announcement::with('creator')->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.announcements.create', [
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request)
    {
        // Build validation rules dynamically
        $validRoles = Role::pluck('name')->toArray();

        $data = $request->validate([
            'title'      => 'required|string|max:200',
            'content'    => 'required|string|max:5000',
            'audience'   => ['required', 'string', Rule::in(array_merge(['all'], $validRoles))],
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'is_active'  => 'boolean',
        ]);

        $data['created_by'] = auth()->id();
        $data['is_active'] = $request->boolean('is_active', true);

        $announcement = Announcement::create($data);
        ActivityLogger::log('Announcement created', $announcement);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement published.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'roles' => Role::orderBy('name')->pluck('name'),
        ]);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validRoles = Role::pluck('name')->toArray();

        $data = $request->validate([
            'title'      => 'required|string|max:200',
            'content'    => 'required|string|max:5000',
            'audience'   => ['required', 'string', Rule::in(array_merge(['all'], $validRoles))],
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
            'is_active'  => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $announcement->update($data);
        ActivityLogger::log('Announcement updated', $announcement);

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement updated.');
    }

    public function destroy(Announcement $announcement)
    {
        ActivityLogger::log('Announcement deleted', $announcement);
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'Announcement deleted.');
    }
}