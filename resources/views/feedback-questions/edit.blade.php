@extends('layouts.app')

@section('title', 'Edit Feedback Question')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Feedback Question</h1>
    <form action="{{ route('feedback-questions.update', $feedbackQuestion) }}" method="POST" class="bg-white shadow rounded-lg p-6">
        @csrf
        @method('PUT')
        <div class="space-y-4">
            <div>
                <label for="question_text" class="block text-sm font-medium text-gray-700">Question text *</label>
                <input type="text" id="question_text" name="question_text" value="{{ old('question_text', $feedbackQuestion->question_text) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('question_text')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300" required>
                    <option value="multiple_choice" {{ old('type', $feedbackQuestion->type) === 'multiple_choice' ? 'selected' : '' }}>Multiple choice</option>
                    <option value="rating" {{ old('type', $feedbackQuestion->type) === 'rating' ? 'selected' : '' }}>Rating</option>
                </select>
            </div>
            <div id="rating_max_wrap" style="display:{{ $feedbackQuestion->type === 'rating' ? 'block' : 'none' }};">
                <label for="rating_max" class="block text-sm font-medium text-gray-700">Rating scale (max)</label>
                <input type="number" id="rating_max" name="rating_max" value="{{ old('rating_max', $feedbackQuestion->rating_max) }}" min="2" max="10" class="mt-1 block w-full rounded-md border-gray-300">
            </div>
            <div id="options_wrap" style="display:{{ $feedbackQuestion->type === 'multiple_choice' ? 'block' : 'none' }};">
                <label class="block text-sm font-medium text-gray-700 mb-2">Options</label>
                <div id="options_list" class="space-y-2">
                    @foreach($feedbackQuestion->options as $i => $opt)
                    <div class="flex gap-2 items-center">
                        <input type="hidden" name="options[{{ $i }}][id]" value="{{ $opt->id }}">
                        <input type="text" name="options[{{ $i }}][option_text]" value="{{ old('options.'.$i.'.option_text', $opt->option_text) }}" placeholder="Option text" class="flex-1 rounded-md border-gray-300">
                        <input type="number" step="0.01" name="options[{{ $i }}][value]" value="{{ old('options.'.$i.'.value', $opt->value) }}" placeholder="Value" class="w-24 rounded-md border-gray-300">
                    </div>
                    @endforeach
                </div>
                <button type="button" id="add_option" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">+ Add option</button>
            </div>
            <div>
                <label for="order" class="block text-sm font-medium text-gray-700">Order</label>
                <input type="number" id="order" name="order" value="{{ old('order', $feedbackQuestion->order) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300">
            </div>
            <div>
                <label class="inline-flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $feedbackQuestion->is_active) ? 'checked' : '' }} class="rounded border-gray-300">
                    <span class="ml-2 text-sm text-gray-700">Active</span>
                </label>
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Update</button>
            <a href="{{ route('feedback-questions.index') }}" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const type = document.getElementById('type');
    const ratingWrap = document.getElementById('rating_max_wrap');
    const optionsWrap = document.getElementById('options_wrap');
    let optionIndex = {{ $feedbackQuestion->options->count() }};
    function toggle() {
        const isRating = type.value === 'rating';
        ratingWrap.style.display = isRating ? 'block' : 'none';
        optionsWrap.style.display = isRating ? 'none' : 'block';
    }
    type.addEventListener('change', toggle);
    document.getElementById('add_option').addEventListener('click', function() {
        const list = document.getElementById('options_list');
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center';
        div.innerHTML = '<input type="text" name="options['+optionIndex+'][option_text]" placeholder="Option text" class="flex-1 rounded-md border-gray-300">' +
            '<input type="number" step="0.01" name="options['+optionIndex+'][value]" placeholder="Value" class="w-24 rounded-md border-gray-300">';
        list.appendChild(div);
        optionIndex++;
    });
});
</script>
@endsection
