<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class MembershipPlanController extends Controller
{
    public function index()
    {
        return response()->json(MembershipPlan::orderBy('display_order')->get());
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $plan = MembershipPlan::create($data);
        ActivityLogger::log('Plan created', $plan, ['name' => $plan->name]);
        return response()->json(['message' => 'Plan created', 'plan' => $plan], 201);
    }

    public function update(Request $request, MembershipPlan $plan)
    {
        $data = $this->validated($request, $plan->id);
        $plan->update($data);
        ActivityLogger::log('Plan updated', $plan);
        return response()->json(['message' => 'Plan updated', 'plan' => $plan->fresh()]);
    }

    public function destroy(MembershipPlan $plan)
    {
        ActivityLogger::log('Plan deleted', $plan);
        $plan->delete();
        return response()->json(['message' => 'Plan deleted']);
    }

    protected function validated(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
            'duration_type' => 'required|in:daily,weekly,monthly,quarterly,yearly,custom',
            'sessions_included' => 'nullable|integer|min:0',
            'freeze_days' => 'nullable|integer|min:0',
            'has_pt_sessions' => 'boolean',
            'pt_sessions_count' => 'nullable|integer|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);
    }
}