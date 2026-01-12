@extends('layouts.app')

@section('title', 'Edit Project')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Project</h1>
            <p class="mt-1 text-sm text-gray-500">Update project details</p>
        </div>

        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('projects.update', $project) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <!-- Project Name -->
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Project Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $project->name) }}" 
                           required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description
                    </label>
                    <textarea id="description" 
                              name="description" 
                              rows="4"
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('description', $project->description) }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Space -->
                @if($spaces && $spaces->count() > 0)
                <div class="mb-4">
                    <label for="space_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Space
                    </label>
                    <select id="space_id" 
                            name="space_id" 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="">No Space</option>
                        @foreach($spaces as $space)
                            <option value="{{ $space->id }}" {{ old('space_id', $project->space_id) == $space->id ? 'selected' : '' }}>
                                {{ $space->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('space_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <!-- Color -->
                <div class="mb-4">
                    <label for="color" class="block text-sm font-medium text-gray-700 mb-2">
                        Color
                    </label>
                    <div class="flex items-center space-x-2">
                        <input type="color" 
                               id="color" 
                               name="color" 
                               value="{{ old('color', $project->color ?? '#6366f1') }}"
                               class="h-10 w-20 border border-gray-300 rounded cursor-pointer">
                        <input type="text" 
                               id="color_hex" 
                               value="{{ old('color', $project->color ?? '#6366f1') }}"
                               pattern="^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                               placeholder="#6366f1"
                               class="block w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                    <script>
                        document.getElementById('color').addEventListener('input', function(e) {
                            document.getElementById('color_hex').value = e.target.value;
                        });
                        document.getElementById('color_hex').addEventListener('input', function(e) {
                            if (/^#[0-9A-F]{6}$/i.test(e.target.value)) {
                                document.getElementById('color').value = e.target.value;
                            }
                        });
                    </script>
                    @error('color')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Icon -->
                <div class="mb-6">
                    <label for="icon" class="block text-sm font-medium text-gray-700 mb-2">
                        Icon (Emoji)
                    </label>
                    <input type="text" 
                           id="icon" 
                           name="icon" 
                           value="{{ old('icon', $project->icon ?? '📁') }}"
                           maxlength="2"
                           class="block w-32 border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-2xl text-center">
                    <p class="mt-1 text-xs text-gray-500">Enter an emoji or icon character</p>
                    @error('icon')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Project Dates -->
                <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Start Date -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Start Date (Optional)
                        </label>
                        <input type="datetime-local" 
                               id="start_date" 
                               name="start_date" 
                               value="{{ old('start_date', $project->start_date?->format('Y-m-d\TH:i')) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Due Date -->
                    <div>
                        <label for="due_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Due Date (Optional)
                        </label>
                        <input type="datetime-local" 
                               id="due_date" 
                               name="due_date" 
                               value="{{ old('due_date', $project->due_date?->format('Y-m-d\TH:i')) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <p class="mt-1 text-xs text-gray-500">Set a due date to track project deadlines</p>
                        @error('due_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('projects.show', $project) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Update Project
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

