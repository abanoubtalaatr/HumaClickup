<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Task;

class StoreBugRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Will be handled by policy
    }

    public function rules(): array
    {
        return [
            'main_task_id' => 'required|exists:tasks,id',
            'title' => 'required|string|max:500',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'estimated_time' => 'required|numeric|min:0.5|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'main_task_id.required' => 'Main task is required.',
            'main_task_id.exists' => 'The selected main task does not exist.',
            'title.required' => 'Bug title is required.',
            'estimated_time.required' => 'Estimated time is required.',
            'estimated_time.min' => 'Estimated time must be at least 0.5 hours (30 minutes).',
        ];
    }

    /**
     * Additional validation after rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->main_task_id && $this->estimated_time) {
                $mainTask = Task::find($this->main_task_id);
                
                if ($mainTask) {
                    // Check if main task is actually a main task
                    if (!$mainTask->isMainTask()) {
                        $validator->errors()->add('main_task_id', 'Bugs can only be created for main tasks.');
                    }
                    
                    // Check if bug time limit would be exceeded
                    if (!$mainTask->bug_time_limit) {
                        $mainTask->bug_time_limit = $mainTask->calculateBugTimeLimit();
                        $mainTask->save();
                    }
                    
                    if (!$mainTask->canAddBug($this->estimated_time)) {
                        $remaining = $mainTask->getRemainingBugTime();
                        $validator->errors()->add(
                            'estimated_time', 
                            "Bug time limit exceeded. Remaining time for bugs: {$remaining} hours. (20% of main task time: {$mainTask->bug_time_limit} hours)"
                        );
                    }
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'main_task_id' => 'main task',
            'estimated_time' => 'estimated time',
        ];
    }
}
