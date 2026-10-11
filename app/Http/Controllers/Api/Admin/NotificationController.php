<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(AppNotification::where('user_id', $request->user()->id)->latest()->paginate(20));
    }

    public function unreadCount(Request $request)
    {
        return response()->json(['count' => AppNotification::where('user_id', $request->user()->id)->where('is_read', false)->count()]);
    }

    public function markRead(Request $request, AppNotification $notification)
    {
        if ($notification->user_id !== $request->user()->id) abort(403);
        $notification->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => 'Marked as read']);
    }

    public function markAllRead(Request $request)
    {
        AppNotification::where('user_id', $request->user()->id)->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);
        return response()->json(['message' => 'All marked as read']);
    }
}