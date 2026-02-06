<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Group;

class StoreProjectWithPlanningRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Will be handled by policy
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workspace_id' => 'required|exists:workspaces,id',
            'space_id' => 'nullable|exists:spaces,id',
            'group_id' => 'required|exists:groups,id',
            'total_days' => 'required|integer|min:1|max:365',
            'start_date' => 'required|date',
            'exclude_weekends' => 'boolean',
            'min_task_hours' => 'nullable|numeric|min:1|max:24',
            'bug_time_allocation_percentage' => 'nullable|numeric|min:1|max:50',
            'weekly_hours_target' => 'nullable|numeric|min:1|max:60',
            'color' => 'nullable|string|regex:/^#[0-9A-F]{6}$/i',
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.required' => 'You must select a group for this project.',
            'group_id.exists' => 'The selected group does not exist.',
            'total_days.required' => 'Please specify the project duration in days.',
            'total_days.min' => 'Project must be at least 1 day.',
            'start_date.required' => 'Please specify the project start date.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set defaults
        $this->merge([
            'exclude_weekends' => $this->exclude_weekends ?? true,
            'min_task_hours' => $this->min_task_hours ?? 6,
            'bug_time_allocation_percentage' => $this->bug_time_allocation_percentage ?? 20,
            'weekly_hours_target' => $this->weekly_hours_target ?? 30,
        ]);
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'group_id' => 'group',
            'total_days' => 'project duration',
            'start_date' => 'start date',
            'min_task_hours' => 'minimum task hours',
            'bug_time_allocation_percentage' => 'bug time allocation',
            'weekly_hours_target' => 'weekly hours target',
        ];
    }

    /**
     * Additional validation after rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Check if group is active and has members
            if ($this->group_id) {
                $group = Group::find($this->group_id);
                
                if ($group && !$group->is_active) {
                    $validator->errors()->add('group_id', 'The selected group is not active.');
                }
                
                if ($group && !$group->meetsMinimum()) {
                    $validator->errors()->add('group_id', "The selected group must have at least {$group->min_members} members.");
                }
            }
        });
    }
}
