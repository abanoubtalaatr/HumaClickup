@extends('layouts.app')

@section('title', 'Add Feedback Question')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Add Feedback Question</h1>
    <form action="{{ route('feedback-questions.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
        @csrf
        <div class="space-y-4">
            <div>
                <label for="question_text" class="block text-sm font-medium text-gray-700">Question text *</label>
                <input type="text" id="question_text" name="question_text" value="{{ old('question_text') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                @error('question_text')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                <select id="type" name="type" class="mt-1 block w-full rounded-md border-gray-300" required>
                    <option value="multiple_choice" {{ old('type') === 'multiple_choice' ? 'selected' : '' }}>Multiple choice</option>
                    <option value="rating" {{ old('type') === 'rating' ? 'selected' : '' }}>Rating</option>
                </select>
            </div>
            <div id="rating_max_wrap" style="display:none;">
                <label for="rating_max" class="block text-sm font-medium text-gray-700">Rating scale (max)</label>
                <input type="number" id="rating_max" name="rating_max" value="{{ old('rating_max', 5) }}" min="2" max="10" class="mt-1 block w-full rounded-md border-gray-300">
                @error('rating_max')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>
            <div id="options_wrap">
                <label class="block text-sm font-medium text-gray-700 mb-2">Options (for multiple choice)</label>
                <div id="options_list" class="space-y-2"></div>
                <button type="button" id="add_option" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800">+ Add option</button>
            </div>
            <div>
                <label for="order" class="block text-sm font-medium text-gray-700">Order</label>
                <input type="number" id="order" name="order" value="{{ old('order', 0) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300">
            </div>
        </div>
        <div class="mt-6 flex gap-3">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Save</button>
            <a href="{{ route('feedback-questions.index') }}" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const type = document.getElementById('type');
    const ratingWrap = document.getElementById('rating_max_wrap');
    const optionsWrap = document.getElementById('options_wrap');
    let optionIndex = 0;
    function toggle() {
        const isRating = type.value === 'rating';
        ratingWrap.style.display = isRating ? 'block' : 'none';
        optionsWrap.style.display = isRating ? 'none' : 'block';
    }
    type.addEventListener('change', toggle);
    toggle();
    document.getElementById('add_option').addEventListener('click', function() {
        const list = document.getElementById('options_list');
        const div = document.createElement('div');
        div.className = 'flex gap-2 items-center';
        div.innerHTML = '<input type="text" name="options['+optionIndex+'][option_text]" placeholder="Option text" class="flex-1 rounded-md border-gray-300">' +
            '<input type="number" step="0.01" name="options['+optionIndex+'][value]" placeholder="Score value" class="w-24 rounded-md border-gray-300">';
        list.appendChild(div);
        optionIndex++;
    });
});
</script>
@endsection
