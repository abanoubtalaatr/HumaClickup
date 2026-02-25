<?php

namespace App\Http\Controllers;

use App\Models\FeedbackQuestion;
use App\Models\FeedbackQuestionOption;
use Illuminate\Http\Request;

class FeedbackQuestionController extends Controller
{
    public function index()
    {
        $workspaceId = session('current_workspace_id');
        if (!$workspaceId || !auth()->user()->isAdminInWorkspace($workspaceId)) {
            abort(403);
        }
        $questions = FeedbackQuestion::where('workspace_id', $workspaceId)->ordered()->with('options')->get();
        return view('feedback-questions.index', compact('questions'));
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
