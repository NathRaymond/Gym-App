<?php

namespace App\Http\Controllers\Web\Member;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class MemberFeedbackController extends Controller
{
    public function index(Request $request)
    {
        return view('member.feedback', [
            'feedbacks' => $request->user()->feedbacks()->latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'title'   => 'nullable|string|max:150',
            'message' => 'required|string|min:5|max:2000',
        ]);

        $feedback = Feedback::create([
            'user_id' => $request->user()->id,
            'rating'  => $data['rating'],
            'title'   => $data['title'] ?? null,
            'message' => $data['message'],
            'is_approved' => false,
        ]);

        ActivityLogger::log('Feedback submitted', $feedback, [
            'rating' => $feedback->rating,
        ]);

        return redirect()->route('member.feedback')
            ->with('success', 'Thank you! Your feedback has been submitted for review.');
    }
}