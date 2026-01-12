@extends('layouts.app')

@section('title', 'Create Guest Report')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ showHint: false }">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Create Weekly Report</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Provide feedback and assessment for your guest</p>
        </div>

        <!-- Positive Feedback Hint -->
        <div class="mb-6 bg-gradient-to-r from-green-50 to-teal-50 dark:from-green-900/20 dark:to-teal-900/20 border-l-4 border-green-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800 dark:text-green-200">💡 Tips for Effective Feedback</h3>
                    <div class="mt-2 text-sm text-green-700 dark:text-green-300 space-y-1">
                        <p>✓ <strong>Be positive and constructive</strong> - Focus on growth and improvement</p>
                        <p>✓ <strong>Be specific</strong> - Give concrete examples of behaviors or achievements</p>
                        <p>✓ <strong>Balance criticism with praise</strong> - Start with strengths, then areas for improvement</p>
                        <p>✓ <strong>Be encouraging</strong> - Motivate them to continue improving</p>
                        <p>✓ <strong>Be actionable</strong> - Provide clear steps they can take to improve</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <form action="{{ route('reports.store') }}" method="POST">
                @csrf

                <!-- Guest Selection -->
                <div class="mb-6">
                    <label for="guest_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Select Guest <span class="text-red-500">*</span>
                    </label>
                    <select name="guest_id" id="guest_id" required
                            class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Choose a guest...</option>
                        @foreach($guests as $guest)
                            <option value="{{ $guest->id }}" {{ old('guest_id', $selectedGuestId) == $guest->id ? 'selected' : '' }}>
                                {{ $guest->name }} ({{ $guest->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('guest_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Week Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="week_start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Week Start Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="week_start_date" id="week_start_date" required
                               value="{{ old('week_start_date', now()->startOfWeek()->format('Y-m-d')) }}"
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('week_start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="week_end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Week End Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="week_end_date" id="week_end_date" required
                               value="{{ old('week_end_date', now()->endOfWeek()->format('Y-m-d')) }}"
                               class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('week_end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Strong Points -->
                <div class="mb-6">
                    <label for="strong_points" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        💪 Strong Points (Optional)
                    </label>
                    <textarea name="strong_points" id="strong_points" rows="4"
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="What did they do well this week? List their strengths and achievements...">{{ old('strong_points') }}</textarea>
                    @error('strong_points')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Weaknesses -->
                <div class="mb-6">
                    <label for="weaknesses" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        📋 Areas for Improvement (Optional)
                    </label>
                    <textarea name="weaknesses" id="weaknesses" rows="4"
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="What can they improve? Be constructive and specific...">{{ old('weaknesses') }}</textarea>
                    @error('weaknesses')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Feedback -->
                <div class="mb-6">
                    <label for="feedback" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        💬 Overall Feedback <span class="text-red-500">*</span>
                    </label>
                    <textarea name="feedback" id="feedback" rows="6" required
                              class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                              placeholder="Provide detailed, positive, and constructive feedback for this week...">{{ old('feedback') }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Remember to be positive, specific, and encouraging! Your feedback helps them grow.
                    </p>
                    @error('feedback')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('reports.index') }}" 
                       class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
