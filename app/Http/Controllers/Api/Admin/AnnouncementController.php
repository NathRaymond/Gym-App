<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        return response()->json(Announcement::with('creator')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'audience' => 'nullable|in:all,members,trainers,staff',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);
        $data['created_by'] = $request->user()->id;
        $ann = Announcement::create($data);
        ActivityLogger::log('Announcement created', $ann);
        return response()->json(['message' => 'Announcement created', 'announcement' => $ann], 201);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $data = $request->validate([
            'title' => 'sometimes|string|max:200',
            'content' => 'sometimes|string',
            'audience' => 'nullable|in:all,members,trainers,staff',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date',
            'is_active' => 'boolean',
        ]);
        $announcement->update($data);
        return response()->json(['message' => 'Announcement updated', 'announcement' => $announcement->fresh()]);
    }

    public function destroy(Announcement $announcement)
    {
        ActivityLogger::log('Announcement deleted', $announcement);
        $announcement->delete();
        return response()->json(['message' => 'Announcement deleted']);
    }

    public function public()
    {
        return response()->json(Announcement::active()->whereIn('audience', ['all', 'members'])->latest()->limit(10)->get());
    }
}