<?php

namespace App\Http\Controllers;

use App\Models\FeedbackQuestion;
use App\Models\FeedbackQuestionOption;
use App\Models\GuestFeedbackSubmission;
use App\Models\User;
use Illuminate\Http\Request;

class FeedbackQuestionController extends Controller
{
    public function index(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        $user = auth()->user();
        if (!$workspaceId) {
            abort(403);
        }
        $isAdmin = $user->isAdminInWorkspace($workspaceId);
        $questions = FeedbackQuestion::where('workspace_id', $workspaceId)->ordered()->with('options')->get();

        // Members (non-admin) can only view their own feedback results; admin can view all
        $membersWithFeedback = collect();
        $feedbackResults = null;
        $selectedMember = null;

        if ($isAdmin) {
            $mentorIds = GuestFeedbackSubmission::where('workspace_id', $workspaceId)->distinct()->pluck('mentor_id');
            $membersWithFeedback = User::whereIn('id', $mentorIds)->orderBy('name')->get(['id', 'name', 'email']);
            $memberId = $request->integer('member_id', 0);
            if ($memberId && $membersWithFeedback->contains('id', $memberId)) {
                $selectedMember = User::find($memberId);
                $feedbackResults = $this->getFeedbackResultsForMentor($workspaceId, $memberId);
            }
        } else {
            // Member: show my feedback (I am the mentor)
            $feedbackResults = $this->getFeedbackResultsForMentor($workspaceId, $user->id);
            $selectedMember = $user;
        }

        return view('feedback-questions.index', compact('questions', 'isAdmin', 'membersWithFeedback', 'feedbackResults', 'selectedMember'));
    }

    /**
     * Aggregate feedback submissions for a mentor (member) into per-question stats.
     */
    protected function getFeedbackResultsForMentor(int $workspaceId, int $mentorId): array
    {
        $submissions = GuestFeedbackSubmission::where('workspace_id', $workspaceId)
            ->where('mentor_id', $mentorId)
            ->with(['answers.question', 'answers.option', 'guest'])
            ->orderByDesc('submitted_at')
            ->get();

        $questions = FeedbackQuestion::where('workspace_id', $workspaceId)->ordered()->with('options')->get();
        $byQuestion = [];
        foreach ($questions as $q) {
            $answersForQ = $submissions->pluck('answers')->flatten()->where('feedback_question_id', $q->id);
            $count = $answersForQ->count();
            if ($q->type === 'rating') {
                $vals = $answersForQ->pluck('rating_value')->filter(fn($v) => $v !== null);
                $byQuestion[$q->id] = [
                    'question' => $q,
                    'type' => 'rating',
                    'count' => $count,
                    'average' => $vals->isEmpty() ? null : round($vals->avg(), 2),
                    'min' => $vals->isEmpty() ? null : $vals->min(),
                    'max' => $vals->isEmpty() ? null : $vals->max(),
                ];
            } else {
                $optionCounts = $answersForQ->pluck('feedback_question_option_id')->filter()->countBy();
                $byQuestion[$q->id] = [
                    'question' => $q,
                    'type' => 'multiple_choice',
                    'count' => $count,
                    'option_counts' => $optionCounts->all(),
                ];
            }
        }

        return [
            'submissions' => $submissions,
            'total_submissions' => $submissions->count(),
            'by_question' => $byQuestion,
        ];
    }

    public function create()
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId || !auth()->user()->isAdminInWorkspace($workspaceId)) {
            abort(403);
        }
        return view('feedback-questions.create');
    }

    public function store(Request $request)
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId || !auth()->user()->isAdminInWorkspace($workspaceId)) {
            abort(403);
        }
        $validated = $request->validate([
            'question_text' => 'required|string|max:500',
            'type' => 'required|in:multiple_choice,rating',
            'rating_max' => 'required_if:type,rating|nullable|integer|min:2|max:10',
            'order' => 'nullable|integer|min:0',
            'options' => 'required_if:type,multiple_choice|nullable|array',
            'options.*.option_text' => 'required_with:options|string|max:255',
            'options.*.value' => 'nullable|numeric',
        ]);
        $q = FeedbackQuestion::create([
            'workspace_id' => $workspaceId,
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'rating_max' => $validated['type'] === 'rating' ? (int) ($validated['rating_max'] ?? 5) : 5,
            'order' => (int) ($validated['order'] ?? 0),
        ]);
        if ($validated['type'] === 'multiple_choice' && !empty($validated['options'])) {
            foreach ($validated['options'] as $i => $opt) {
                FeedbackQuestionOption::create([
                    'feedback_question_id' => $q->id,
                    'option_text' => $opt['option_text'],
                    'value' => $opt['value'] ?? null,
                    'order' => $i,
                ]);
            }
        }
        return redirect()->route('feedback-questions.index')->with('success', 'Question created.');
    }

    public function edit(FeedbackQuestion $feedbackQuestion)
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId || !auth()->user()->isAdminInWorkspace($workspaceId) || $feedbackQuestion->workspace_id != $workspaceId) {
            abort(403);
        }
        $feedbackQuestion->load('options');
        return view('feedback-questions.edit', compact('feedbackQuestion'));
    }

    public function update(Request $request, FeedbackQuestion $feedbackQuestion)
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId || !auth()->user()->isAdminInWorkspace($workspaceId) || $feedbackQuestion->workspace_id != $workspaceId) {
            abort(403);
        }
        $validated = $request->validate([
            'question_text' => 'required|string|max:500',
            'type' => 'required|in:multiple_choice,rating',
            'rating_max' => 'required_if:type,rating|nullable|integer|min:2|max:10',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
            'options' => 'required_if:type,multiple_choice|nullable|array',
            'options.*.id' => 'nullable|exists:feedback_question_options,id',
            'options.*.option_text' => 'required_with:options|string|max:255',
            'options.*.value' => 'nullable|numeric',
        ]);
        $feedbackQuestion->update([
            'question_text' => $validated['question_text'],
            'type' => $validated['type'],
            'rating_max' => $validated['type'] === 'rating' ? (int) ($validated['rating_max'] ?? 5) : 5,
            'order' => (int) ($validated['order'] ?? 0),
            'is_active' => $request->boolean('is_active'),
        ]);
        if ($validated['type'] === 'multiple_choice' && isset($validated['options'])) {
            $ids = [];
            foreach ($validated['options'] as $i => $opt) {
                $data = [
                    'option_text' => $opt['option_text'],
                    'value' => $opt['value'] ?? null,
                    'order' => $i,
                ];
                if (!empty($opt['id'])) {
                    $option = FeedbackQuestionOption::where('feedback_question_id', $feedbackQuestion->id)->find($opt['id']);
                    if ($option) {
                        $option->update($data);
                        $ids[] = $option->id;
                    }
                } else {
                    $option = FeedbackQuestionOption::create(array_merge($data, ['feedback_question_id' => $feedbackQuestion->id]));
                    $ids[] = $option->id;
                }
            }
            $feedbackQuestion->options()->whereNotIn('id', $ids)->delete();
        } else {
            $feedbackQuestion->options()->delete();
        }
        return redirect()->route('feedback-questions.index')->with('success', 'Question updated.');
    }

    public function destroy(FeedbackQuestion $feedbackQuestion)
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId || !auth()->user()->isAdminInWorkspace($workspaceId) || $feedbackQuestion->workspace_id != $workspaceId) {
            abort(403);
        }
        $feedbackQuestion->delete();
        return redirect()->route('feedback-questions.index')->with('success', 'Question deleted.');
    }
}
