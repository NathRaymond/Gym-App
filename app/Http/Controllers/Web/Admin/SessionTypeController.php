<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\SessionCategory;
use App\Models\SessionType;
use App\Models\Trainer;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionTypeController extends Controller
{
    public function index()
    {
        return view('admin.session-types.index', [
            'sessionTypes' => SessionType::with(['category', 'trainer.user'])
                ->orderBy('display_order')
                ->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.session-types.create', [
            'categories' => SessionCategory::active()->orderBy('display_order')->get(),
            'trainers'   => Trainer::with('user')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        $type = SessionType::create($data);
        ActivityLogger::log('Session type created', $type);

        return redirect()->route('admin.session-types.index')
            ->with('success', "Session type '{$type->name}' created.");
    }

    public function edit(SessionType $sessionType)
    {
        return view('admin.session-types.edit', [
            'sessionType' => $sessionType,
            'categories'  => SessionCategory::active()->orderBy('display_order')->get(),
            'trainers'    => Trainer::with('user')->get(),
        ]);
    }

    public function update(Request $request, SessionType $sessionType)
    {
        $data = $this->validated($request);
        if ($data['name'] !== $sessionType->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $sessionType->update($data);
        ActivityLogger::log('Session type updated', $sessionType);

        return redirect()->route('admin.session-types.index')
            ->with('success', 'Session type updated.');
    }

    public function destroy(SessionType $sessionType)
    {
        if ($sessionType->slots()->whereDate('slot_date', '>=', today())->exists()) {
            return back()->with('error', 'Cannot delete a session type with upcoming slots.');
        }

        ActivityLogger::log('Session type deleted', $sessionType);
        $sessionType->delete();

        return redirect()->route('admin.session-types.index')
            ->with('success', 'Session type deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'name'                => 'required|string|max:150',
            'icon'                => 'nullable|string|max:50',
            'description'         => 'nullable|string|max:1000',
            'session_category_id' => 'nullable|exists:session_categories,id',
            'duration_minutes'    => 'required|integer|min:5|max:480',
            'base_price'          => 'required|numeric|min:0',
            'default_capacity'    => 'required|integer|min:1|max:500',
            'trainer_id'          => 'nullable|exists:trainers,id',
            'requires_trainer'    => 'boolean',
            'is_active'           => 'boolean',
            'display_order'       => 'nullable|integer|min:0',
        ]);

        $data['requires_trainer'] = $request->boolean('requires_trainer');
        $data['is_active']        = $request->boolean('is_active', true);

        return $data;
    }
}