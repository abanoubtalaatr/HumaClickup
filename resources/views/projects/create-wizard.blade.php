@extends('layouts.app')

@section('title', 'Create Project - Step by Step')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="projectWizard()"  x-init="init()">
    <!-- Enhanced Progress Steps -->
    <div class="mb-8">
        <nav aria-label="Progress">
            <ol class="flex items-center justify-between">
                <template x-for="(step, index) in steps" :key="index">
                    <li class="relative flex-1" :class="index < steps.length - 1 ? 'pr-8 sm:pr-20' : ''">
                        <div class="flex flex-col items-center">
                            <!-- Step Circle -->
                            <div class="relative flex items-center justify-center mb-2">
                                <div class="h-12 w-12 rounded-full flex items-center justify-center shadow-lg transition-all duration-300"
                                     :class="{
                                         'bg-gradient-to-r from-indigo-600 to-purple-600 text-white scale-110': currentStep === index + 1,
                                         'bg-indigo-600 text-white': currentStep > index + 1,
                                         'bg-gray-200 text-gray-500': currentStep < index + 1
                                     }">
                                    <!-- Checkmark for completed steps -->
                                    <svg x-show="currentStep > index + 1" class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    <!-- Number for current/future steps -->
                                    <span x-show="currentStep <= index + 1" class="font-bold" x-text="index + 1"></span>
                                </div>
                            </div>
                            
                            <!-- Step Label -->
                            <span class="text-sm font-medium text-center transition-colors duration-300"
                                  :class="{
                                      'text-indigo-600 font-semibold': currentStep === index + 1,
                                      'text-indigo-600': currentStep > index + 1,
                                      'text-gray-500': currentStep < index + 1
                                  }"
                                  x-text="step.title"></span>
                        </div>
                        
                        <!-- Connector Line -->
                        <div x-show="index < steps.length - 1" 
                             class="absolute top-6 left-1/2 w-full h-1 rounded-full transition-all duration-300"
                             :class="{
                                 'bg-gradient-to-r from-indigo-600 to-purple-600': currentStep > index + 1,
                                 'bg-gray-200': currentStep <= index + 1
                             }"></div>
                    </li>
                </template>
            </ol>
        </nav>
    </div>

    <form @submit.prevent="submitForm">
        <!-- Step 1: Project Info -->
        <div x-show="currentStep === 1" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">📋 Project Information</h2>
                <p class="text-gray-600">Set up your project basics and select team members</p>
            </div>
            
            @include('projects.wizard.step1-info')
        </div>

        <!-- Step 2: Task Planning Grid -->
        <div x-show="currentStep === 2" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">📝 Main Tasks Planning</h2>
                <p class="text-gray-600 mb-1">
                    Plan all <strong class="text-indigo-600" x-text="requiredTasks"></strong> main tasks for your project
                </p>
                <p class="text-sm text-gray-500">
                    <strong x-text="selectedMembers.length"></strong> guests × <strong x-text="workingDays"></strong> working days = <strong x-text="requiredTasks"></strong> tasks
                </p>
            </div>
            
            @include('projects.wizard.step2-tasks')
        </div>

        <!-- Step 3: Review & Submit -->
        <div x-show="currentStep === 3" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="bg-white shadow-xl rounded-2xl p-8 border border-gray-200">
            <div class="mb-6">
                <h2 class="text-3xl font-bold text-gray-900 mb-2">✅ Review & Create Project</h2>
                <p class="text-gray-600">Final review before creating your project</p>
            </div>
            
            @include('projects.wizard.step3-review')
        </div>

        <!-- Enhanced Navigation Buttons -->
        <div class="mt-8 flex justify-between items-center bg-white rounded-xl shadow-lg border border-gray-200 p-4">
            <button type="button"
                    x-show="currentStep > 1"
                    @click="previousStep()"
                    class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200 hover:shadow-md">
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Previous
            </button>
            
            <div class="flex space-x-3 ml-auto">
                <a href="{{ route('projects.index') }}"
                   class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-all duration-200">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                </a>
                
                <button type="button"
                        x-show="currentStep < 3"
                        @click="nextStep()"
                        :disabled="!canProceed()"
                        class="inline-flex items-center px-8 py-3 rounded-lg text-sm font-medium text-white transition-all duration-200 shadow-md"
                        :class="canProceed() ? 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 hover:shadow-lg' : 'bg-gray-400 cursor-not-allowed'">
                    Next
                    <svg class="h-5 w-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                
                <button type="submit"
                        x-show="currentStep === 3"
                        class="inline-flex items-center px-8 py-3 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-md hover:shadow-lg">
                    <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Project
                </button>
            </div>
        </div>
    </form>
</div>
</div>

<!-- Alpine Collapse Plugin for smooth animations -->
<script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>

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
        expandedGuests: {},
        expandedSubtasks: {},
        
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
                // Dispatch step change event
                window.dispatchEvent(new CustomEvent('step-changed', { detail: this.currentStep }));
            }
        },
        
        previousStep() {
            if (this.currentStep > 1) {
                this.currentStep--;
            }
        },
        
        generateMainTasks() {
            this.mainTasks = [];
            this.expandedGuests = {};
            this.expandedSubtasks = {};
            let taskId = 1;
            
            // For each guest
            this.selectedMembers.forEach((member, memberIndex) => {
                // Initialize guest as collapsed (false = expanded, true = collapsed)
                this.expandedGuests[member.user_id] = false;
                
                // For each working day
                for (let day = 0; day < this.workingDays; day++) {
                    const currentTaskId = taskId++;
                    this.mainTasks.push({
                        id: currentTaskId,
                        guest_user_id: member.user_id,
                        guest_name: member.name,
                        track_id: member.track_id,
                        day_number: day + 1,
                        title: `Task ${day + 1} for ${member.name}`,
                        description: '',
                        estimated_hours: 6,
                        subtasks: []
                    });
                    // Initialize subtasks as collapsed
                    this.expandedSubtasks[currentTaskId] = true;
                }
            });
            
            // Dispatch event for TinyMCE initialization
            window.dispatchEvent(new CustomEvent('step-changed', { detail: 2 }));
        },
        
        toggleGuestSection(guestUserId) {
            this.expandedGuests[guestUserId] = !this.expandedGuests[guestUserId];
        },
        
        toggleSubtasks(taskId) {
            this.expandedSubtasks[taskId] = !this.expandedSubtasks[taskId];
        },
        
        addSubtask(mainTaskId) {
            const mainTask = this.mainTasks.find(t => t.id === mainTaskId);
            if (mainTask) {
                mainTask.subtasks.push({
                    id: Date.now(),
                    title: '',
                    description: '',
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
