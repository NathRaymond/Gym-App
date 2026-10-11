<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    public function index(Request $request)
    {
        $q = Feedback::with('user');
        if (!$request->user()?->isStaff() || $request->boolean('approved_only')) {
            $q->where('is_approved', true);
        }
        return response()->json($q->latest()->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'message' => 'required|string|max:2000',
        ]);

        $feedback = Feedback::create(array_merge($data, ['user_id' => $request->user()->id]));
        ActivityLogger::log('Feedback submitted', $feedback, ['rating' => $feedback->rating]);

        return response()->json(['message' => 'Thank you for your feedback!', 'feedback' => $feedback], 201);
    }

    public function approve(Request $request, Feedback $feedback)
    {
        $feedback->update([
            'is_approved' => true,
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);
        ActivityLogger::log('Feedback approved', $feedback);
        return response()->json(['message' => 'Feedback approved', 'feedback' => $feedback]);
    }

    public function destroy(Feedback $feedback)
    {
        ActivityLogger::log('Feedback deleted', $feedback);
        $feedback->delete();
        return response()->json(['message' => 'Feedback deleted']);
    }
}