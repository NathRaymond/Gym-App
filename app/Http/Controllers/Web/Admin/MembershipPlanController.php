<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipPlan;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MembershipPlanController extends Controller
{
    public function index()
    {
        return view('admin.plans.index', [
            'plans' => MembershipPlan::orderBy('display_order')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.plans.create');
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        $plan = MembershipPlan::create($data);
        ActivityLogger::log('Plan created', $plan);

        return redirect()->route('admin.plans.index')
            ->with('success', "Plan '{$plan->name}' created successfully.");
    }

    public function edit(MembershipPlan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    public function update(Request $request, MembershipPlan $plan)
    {
        $data = $this->validated($request);

        if ($data['name'] !== $plan->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $plan->update($data);
        ActivityLogger::log('Plan updated', $plan);

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(MembershipPlan $plan)
    {
        if ($plan->memberships()->exists()) {
            return back()->with('error', 'Cannot delete a plan with active memberships.');
        }

        ActivityLogger::log('Plan deleted', $plan);
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Plan deleted successfully.');
    }

    /**
     * Validate and normalize all inputs.
     */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name'               => 'required|string|max:150',
            'description'        => 'nullable|string',
            'price'              => 'required|numeric|min:0',
            'duration_days'      => 'required|integer|min:1',
            'duration_type'      => 'required|in:daily,weekly,monthly,quarterly,yearly,custom',
            'sessions_included'  => 'nullable|integer|min:0',
            'freeze_days'        => 'nullable|integer|min:0',
            'has_pt_sessions'    => 'boolean',
            'pt_sessions_count'  => 'nullable|integer|min:0',
            'features'           => 'nullable',           // accept string OR array
            'is_active'          => 'boolean',
            'is_featured'        => 'boolean',
            'display_order'      => 'nullable|integer',
        ]);

        // ============================================
        // Normalize `features` into an array
        // ============================================
        $data['features'] = $this->normalizeFeatures($request->input('features'));

        // Normalize booleans
        $data['has_pt_sessions'] = $request->boolean('has_pt_sessions');
        $data['is_active']       = $request->boolean('is_active', true);
        $data['is_featured']     = $request->boolean('is_featured');

        // Defaults
        $data['freeze_days']       = $data['freeze_days'] ?? 0;
        $data['pt_sessions_count'] = $data['pt_sessions_count'] ?? 0;
        $data['display_order']     = $data['display_order'] ?? 0;

        return $data;
    }

    /**
     * Turn a textarea string or array into a clean array of features.
     */
    protected function normalizeFeatures($features): array
    {
        // Already an array → just clean it
        if (is_array($features)) {
            return array_values(array_filter(array_map('trim', $features)));
        }

        // String → split on newlines, trim, remove empties
        if (is_string($features)) {
            return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $features))));
        }

        return [];
    }
}