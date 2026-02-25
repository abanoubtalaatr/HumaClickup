<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\User;

class AssignTestersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Will be handled by policy
    }

    public function rules(): array
    {
        return [
            'project_id' => 'required|exists:projects,id',
            'tester_ids' => 'required|array|min:1|max:5',
            'tester_ids.*' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'tester_ids.required' => 'Please select at least one tester.',
            'tester_ids.min' => 'Please select at least one tester.',
            'tester_ids.max' => 'You can assign a maximum of 5 testers.',
            'tester_ids.*.exists' => 'One or more selected testers do not exist.',
        ];
    }

    /**
     * Additional validation after rules.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->tester_ids) {
                $workspaceId = session('current_workspace_id');
                
                // Validate each tester is from testing track
                foreach ($this->tester_ids as $testerId) {
                    $user = User::find($testerId);
                    
                    if ($user && !$user->hasTestingTrackInWorkspace($workspaceId)) {
                        $validator->errors()->add(
                            'tester_ids', 
                            "User '{$user->name}' is not assigned to the Testing track."
                        );
                    }
                }
            }
        });
    }

    public function attributes(): array
    {
        return [
            'tester_ids' => 'testers',
            'project_id' => 'project',
        ];
    }
}
