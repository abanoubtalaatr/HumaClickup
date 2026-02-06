@extends('layouts.app')

@section('title', 'Create Project - Step by Step')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-6" x-data="projectWizard()">
    <!-- Progress Steps -->
    <div class="mb-8">
        <nav aria-label="Progress">
            <ol class="flex items-center">
                <template x-for="(step, index) in steps" :key="index">
                    <li class="relative" :class="index < steps.length - 1 ? 'pr-8 sm:pr-20 flex-1' : ''">
                        <div class="flex items-center">
                            <div class="relative flex items-center justify-center">
                                <div class="h-10 w-10 rounded-full flex items-center justify-center"
                                     :class="{
                                         'bg-indigo-600 text-white': currentStep >= index + 1,
                                         'bg-gray-200 text-gray-500': currentStep < index + 1
                                     }">
                                    <span x-text="index + 1"></span>
                                </div>
                            </div>
                            <span class="ml-3 text-sm font-medium"
                                  :class="{
                                      'text-indigo-600': currentStep >= index + 1,
                                      'text-gray-500': currentStep < index + 1
                                  }"
                                  x-text="step.title"></span>
                        </div>
                        <div x-show="index < steps.length - 1" 
                             class="absolute top-5 left-5 w-full h-0.5"
                             :class="{
                                 'bg-indigo-600': currentStep > index + 1,
                                 'bg-gray-200': currentStep <= index + 1
                             }"></div>
                    </li>
                </template>
            </ol>
        </nav>
    </div>

    <form @submit.prevent="submitForm">
        <!-- Step 1: Project Info -->
        <div x-show="currentStep === 1" class="bg-white shadow rounded-lg p-6 space-y-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Project Information</h2>
            
            @include('projects.wizard.step1-info')
        </div>

        <!-- Step 2: Task Planning Grid -->
        <div x-show="currentStep === 2" class="bg-white shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Main Tasks Planning</h2>
            <p class="text-sm text-gray-600 mb-6">
                Create <strong x-text="requiredTasks"></strong> main tasks 
                (<strong x-text="selectedMembers.length"></strong> guests × <strong x-text="workingDays"></strong> working days)
            </p>
            
            @include('projects.wizard.step2-tasks')
        </div>

        <!-- Step 3: Review & Submit -->
        <div x-show="currentStep === 3" class="bg-white shadow rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-4">Review & Create Project</h2>
            
            @include('projects.wizard.step3-review')
        </div>

        <!-- Navigation Buttons -->
        <div class="mt-6 flex justify-between">
            <button type="button"
                    x-show="currentStep > 1"
                    @click="previousStep()"
                    class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                ← Previous
            </button>
            
            <div class="flex space-x-3 ml-auto">
                <a href="{{ route('projects.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    Cancel
                </a>
                
                <button type="button"
                        x-show="currentStep < 3"
                        @click="nextStep()"
                        :disabled="!canProceed()"
                        class="px-6 py-2 rounded-md shadow-sm text-sm font-medium text-white"
                        :class="canProceed() ? 'bg-indigo-600 hover:bg-indigo-700' : 'bg-gray-400 cursor-not-allowed'">
                    Next →
                </button>
                
                <button type="submit"
                        x-show="currentStep === 3"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 rounded-md shadow-sm text-sm font-medium text-white">
                    Create Project
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function projectWizard() {
    return {
        currentStep: 1,
        steps: [
            { title: 'Project Info' },
            { title: 'Plan Tasks' },
            { title: 'Review' }
        ],
        
        // Step 1 data
        projectName: '',
        description: '',
        startDate: '{{ now()->format('Y-m-d') }}',
        totalDays: 20,
        excludeWeekends: true,
        selectionMode: 'guests',
        selectedMembers: [],
        guestSearch: '',
        groupSearch: '',
        
        // Step 2 data
        mainTasks: [],
        
        get workingDays() {
            // Estimate: 5/7 of total days if weekends excluded
            return this.excludeWeekends ? Math.floor(this.totalDays * (5/7)) : this.totalDays;
        },
        
        get requiredTasks() {
            return this.selectedMembers.length * this.workingDays;
        },
        
        init() {
            this.generateMainTasks();
        },
        
        canProceed() {
            if (this.currentStep === 1) {
                return this.projectName && this.selectedMembers.length > 0 && this.startDate;
            }
            if (this.currentStep === 2) {
                return this.mainTasks.length === this.requiredTasks && 
                       this.mainTasks.every(t => t.title && t.estimated_hours >= 6);
            }
            return true;
        },
        
        nextStep() {
            if (!this.canProceed()) return;
            
            if (this.currentStep === 1) {
                this.generateMainTasks();
            }
            
            if (this.currentStep < 3) {
                this.currentStep++;
            }
        },
        
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        
        generateMainTasks() {
            this.mainTasks = [];
            let taskId = 1;
            
            // For each guest
            this.selectedMembers.forEach((member, memberIndex) => {
                // For each working day
                for (let day = 0; day < this.workingDays; day++) {
                    this.mainTasks.push({
                        id: taskId++,
                        guest_user_id: member.user_id,
                        guest_name: member.name,
                        track_id: member.track_id,
                        day_number: day + 1,
                        title: `Task ${day + 1} for ${member.name}`,
                        description: '',
                        estimated_hours: 6,
                        subtasks: []
                    });
                }
            });
        },
        
        addSubtask(mainTaskId) {
            const mainTask = this.mainTasks.find(t => t.id === mainTaskId);
            if (mainTask) {
                mainTask.subtasks.push({
                    id: Date.now(),
                    title: '',
                    estimated_hours: 0
                });
            }
        },
        
        removeSubtask(mainTaskId, subtaskId) {
            const mainTask = this.mainTasks.find(t => t.id === mainTaskId);
            if (mainTask) {
                mainTask.subtasks = mainTask.subtasks.filter(st => st.id !== subtaskId);
            }
        },
        
        calculateSubtaskTotal(mainTask) {
            return mainTask.subtasks.reduce((sum, st) => sum + parseFloat(st.estimated_hours || 0), 0);
        },
        
        selectGuests(members) {
            this.selectedMembers = members;
        },
        
        selectGroup(groupId, groupMembers) {
            this.selectedMembers = groupMembers;
        },
        
        submitForm() {
            const formData = {
                name: this.projectName,
                description: this.description,
                start_date: this.startDate,
                total_days: this.totalDays,
                exclude_weekends: this.excludeWeekends,
                guest_members: this.selectedMembers,
                main_tasks: this.mainTasks
            };
            
            // Submit via AJAX
            fetch('{{ route('projects.store-with-tasks') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert('Error: ' + (data.message || 'Failed to create project'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        }
    }
}
</script>
@endsection
