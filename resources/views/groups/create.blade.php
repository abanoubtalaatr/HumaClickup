@extends('layouts.app')

@section('title', 'Create Group')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create New Group</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Create a group and assign guests to it</p>
        </div>

        <!-- Form -->
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            <form action="{{ route('groups.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Group Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Group Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           id="name" 
                           value="{{ old('name') }}"
                           required
                           class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="e.g., Frontend Team, Backend Developers">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Description
                    </label>
                    <textarea name="description" 
                              id="description" 
                              rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                              placeholder="Brief description of this group...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Color -->
                <div>
                    <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Group Color
                    </label>
                    <div class="mt-2 flex items-center space-x-3">
                        <input type="color" 
                               name="color" 
                               id="color" 
                               value="{{ old('color', '#3b82f6') }}"
                               class="h-10 w-20 rounded border-gray-300 dark:border-gray-600">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Choose a color for this group</span>
                    </div>
                    @error('color')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Links Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- WhatsApp Link -->
                    <div>
                        <label for="whatsapp_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            WhatsApp Group Link
                        </label>
                        <input type="url" 
                               name="whatsapp_link" 
                               id="whatsapp_link" 
                               value="{{ old('whatsapp_link') }}"
                               placeholder="https://chat.whatsapp.com/..."
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('whatsapp_link')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slack Link -->
                    <div>
                        <label for="slack_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Slack Channel Link
                        </label>
                        <input type="url" 
                               name="slack_link" 
                               id="slack_link" 
                               value="{{ old('slack_link') }}"
                               placeholder="https://workspace.slack.com/archives/..."
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('slack_link')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Repository Link -->
                    <div>
                        <label for="repo_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Repository Link (GitHub, GitLab, etc.)
                        </label>
                        <input type="url" 
                               name="repo_link" 
                               id="repo_link" 
                               value="{{ old('repo_link') }}"
                               placeholder="https://github.com/username/repo"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('repo_link')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Service Link -->
                    <div>
                        <label for="service_link" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Service/Project Link
                        </label>
                        <input type="url" 
                               name="service_link" 
                               id="service_link" 
                               value="{{ old('service_link') }}"
                               placeholder="https://yourservice.com"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('service_link')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Assign Guests -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Assign Guests
                    </label>
                    @if($availableGuests->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">You haven't created any guests yet. Create guests first to assign them to groups.</p>
                    @else
                        <div class="border border-gray-300 dark:border-gray-600 rounded-md max-h-60 overflow-y-auto p-4 space-y-2">
                            @foreach($availableGuests as $guest)
                                <label class="flex items-center p-2 hover:bg-gray-50 dark:hover:bg-gray-700 rounded cursor-pointer">
                                    <input type="checkbox" 
                                           name="guest_ids[]" 
                                           value="{{ $guest->id }}"
                                           {{ in_array($guest->id, old('guest_ids', [])) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <div class="ml-3 flex items-center">
                                        <div class="h-8 w-8 rounded-full bg-gray-400 flex items-center justify-center text-white text-sm">
                                            {{ strtoupper(substr($guest->name, 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $guest->name }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 ml-2">{{ $guest->email }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('guest_ids')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <a href="{{ route('groups.index') }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
