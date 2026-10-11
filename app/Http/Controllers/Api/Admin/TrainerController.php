<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trainer;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class TrainerController extends Controller
{
    public function index()
    {
        return response()->json(Trainer::with('user')->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id|unique:trainers,user_id',
            'bio' => 'nullable|string',
            'specializations' => 'nullable|array',
            'certifications' => 'nullable|array',
            'years_experience' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_available' => 'boolean',
            'availability' => 'nullable|array',
        ]);
        $trainer = Trainer::create($data);
        // Ensure user role
        $trainer->user->assignRole('trainer');
        ActivityLogger::log('Trainer created', $trainer);
        return response()->json(['message' => 'Trainer created', 'trainer' => $trainer->load('user')], 201);
    }

    public function update(Request $request, Trainer $trainer)
    {
        $data = $request->validate([
            'bio' => 'nullable|string',
            'specializations' => 'nullable|array',
            'certifications' => 'nullable|array',
            'years_experience' => 'nullable|integer|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_available' => 'boolean',
            'availability' => 'nullable|array',
        ]);
        $trainer->update($data);
        ActivityLogger::log('Trainer updated', $trainer);
        return response()->json(['message' => 'Trainer updated', 'trainer' => $trainer->fresh()->load('user')]);
    }

    public function destroy(Trainer $trainer)
    {
        ActivityLogger::log('Trainer deleted', $trainer);
        $trainer->delete();
        return response()->json(['message' => 'Trainer deleted']);
    }
}