<?php

namespace App\Http\Controllers;

use App\Models\FeedbackQuestion;
use App\Models\GuestFeedbackAnswer;
use App\Models\GuestFeedbackSubmission;
use Illuminate\Http\Request;

class GuestFeedbackController extends Controller
{
    public function create()
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        if (!$workspaceId || !$user->isGuestInWorkspace($workspaceId)) {
            abort(403, 'Only guests can submit mentor feedback.');
        }
        $mentor = $this->getMyMentor($workspaceId, $user->id);
        if (!$mentor) {
            return redirect()->route('dashboard')->with('error', 'You do not have an assigned mentor to give feedback to.');
        }
        $questions = FeedbackQuestion::where('workspace_id', $workspaceId)->active()->ordered()->with('options')->get();
        if ($questions->isEmpty()) {
            return redirect()->route('dashboard')->with('error', 'No feedback questions are configured yet.');
        }
        return view('guest-feedback.create', compact('questions', 'mentor'));
    }

    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        if (!$workspaceId || !$user->isGuestInWorkspace($workspaceId)) {
            abort(403);
        }
        $mentor = $this->getMyMentor($workspaceId, $user->id);
        if (!$mentor) {
            return redirect()->route('dashboard')->with('error', 'You do not have an assigned mentor.');
        }
        $questions = FeedbackQuestion::where('workspace_id', $workspaceId)->active()->ordered()->with('options')->get();
        $rules = [];
        foreach ($questions as $q) {
            if ($q->type === 'rating') {
                $rules["answer.{$q->id}"] = 'required|integer|min:1|max:' . $q->rating_max;
            } else {
                $rules["answer.{$q->id}"] = 'required|exists:feedback_question_options,id';
            }
        }
        $validated = $request->validate($rules);
        $submission = GuestFeedbackSubmission::create([
            'workspace_id' => $workspaceId,
            'guest_id' => $user->id,
            'mentor_id' => $mentor->id,
            'submitted_at' => now(),
        ]);
        foreach ($questions as $q) {
            $val = $validated["answer"][$q->id] ?? null;
            if ($val === null) continue;
            $optionId = null;
            $ratingValue = null;
            if ($q->type === 'rating') {
                $ratingValue = (int) $val;
            } else {
                $optionId = (int) $val;
            }
            GuestFeedbackAnswer::create([
                'guest_feedback_submission_id' => $submission->id,
                'feedback_question_id' => $q->id,
                'feedback_question_option_id' => $optionId,
                'rating_value' => $ratingValue,
            ]);
        }
        return redirect()->route('guest-feedback.create')->with('success', 'Thank you! Your feedback has been submitted.');
    }

    protected function getMyMentor(int $workspaceId, int $guestId): ?\App\Models\User
    {
        $pivot = \DB::table('workspace_user')
            ->where('workspace_id', $workspaceId)
            ->where('user_id', $guestId)
            ->where('role', 'guest')
            ->first();
        if (!$pivot || !$pivot->created_by_user_id) {
            return null;
        }
        return \App\Models\User::find($pivot->created_by_user_id);
    }
}
