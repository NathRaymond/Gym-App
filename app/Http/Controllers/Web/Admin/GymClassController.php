<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\GymClass;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GymClassController extends Controller
{
    public function index()
    {
        return view('admin.classes.index', [
            'classes' => GymClass::with('instructor')->latest()->paginate(20),
        ]);
    }

    public function create()
    {
        return view('admin.classes.create', [
            'instructors' => User::role('trainer')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['name']);

        $class = GymClass::create($data);
        ActivityLogger::log('Class created', $class);

        return redirect()->route('admin.classes.index')
            ->with('success', "Class '{$class->name}' created.");
    }

    public function edit(GymClass $class)
    {
        return view('admin.classes.edit', [
            'class' => $class,
            'instructors' => User::role('trainer')->get(),
        ]);
    }

    public function update(Request $request, GymClass $class)
    {
        $data = $this->validated($request);
        if ($data['name'] !== $class->name) {
            $data['slug'] = Str::slug($data['name']);
        }

        $class->update($data);
        ActivityLogger::log('Class updated', $class);

        return redirect()->route('admin.classes.index')->with('success', 'Class updated.');
    }

    public function destroy(GymClass $class)
    {
        ActivityLogger::log('Class deleted', $class);
        $class->delete();

        return redirect()->route('admin.classes.index')->with('success', 'Class deleted.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'description' => 'nullable|string',
            'instructor_id' => 'nullable|exists:users,id',
            'capacity' => 'required|integer|min:1',
            'duration_minutes' => 'required|integer|min:1',
            'difficulty' => 'required|in:beginner,intermediate,advanced,all_levels',
            'price' => 'nullable|numeric|min:0',
            'is_free_for_members' => 'boolean',
            'is_active' => 'boolean',
        ]);
    }
}