<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GymClass;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GymClassController extends Controller
{
    public function index()
    {
        return response()->json(GymClass::with(['schedules', 'instructor'])->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
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
        $data['slug'] = Str::slug($data['name']);
        $class = GymClass::create($data);
        ActivityLogger::log('Class created', $class, ['name' => $class->name]);
        return response()->json(['message' => 'Class created', 'class' => $class], 201);
    }

    public function update(Request $request, GymClass $gymClass)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:150',
            'description' => 'nullable|string',
            'instructor_id' => 'nullable|exists:users,id',
            'capacity' => 'sometimes|integer|min:1',
            'duration_minutes' => 'sometimes|integer|min:1',
            'difficulty' => 'sometimes|in:beginner,intermediate,advanced,all_levels',
            'price' => 'nullable|numeric|min:0',
            'is_free_for_members' => 'boolean',
            'is_active' => 'boolean',
        ]);
        $gymClass->update($data);
        ActivityLogger::log('Class updated', $gymClass);
        return response()->json(['message' => 'Class updated', 'class' => $gymClass->fresh()]);
    }

    public function destroy(GymClass $gymClass)
    {
        ActivityLogger::log('Class deleted', $gymClass);
        $gymClass->delete();
        return response()->json(['message' => 'Class deleted']);
    }
}