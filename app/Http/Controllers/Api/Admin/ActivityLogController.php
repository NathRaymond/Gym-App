<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $q = Activity::with(['causer', 'subject']);
        if ($request->filled('log_name')) $q->where('log_name', $request->log_name);
        if ($request->filled('causer_id')) $q->where('causer_id', $request->causer_id);
        if ($request->filled('from')) $q->whereDate('created_at', '>=', $request->from);
        if ($request->filled('to')) $q->whereDate('created_at', '<=', $request->to);
        if ($request->filled('search')) {
            $q->where('description', 'like', '%' . $request->search . '%');
        }
        return response()->json($q->latest()->paginate($request->get('per_page', 25)));
    }

    public function show(Activity $activity)
    {
        return response()->json($activity->load(['causer', 'subject']));
    }
}