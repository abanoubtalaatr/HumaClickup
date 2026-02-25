@extends('layouts.app')

@section('title', 'Submit Feedback')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Mentor Feedback</h1>
    <p class="text-sm text-gray-500 mb-6">Your feedback helps improve mentor performance. You are giving feedback for <strong>{{ $mentor->name }}</strong>.</p>
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 text-green-800 rounded-lg">{{ session('success') }}</div>
    @endif
    <form action="{{ route('guest-feedback.store') }}" method="POST" class="bg-white shadow rounded-lg p-6">
        @csrf
        <div class="space-y-6">
            @foreach($questions as $q)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ $q->question_text }} *</label>
                    @if($q->type === 'rating')
                        <div class="flex gap-2 items-center">
                            @for($i = 1; $i <= $q->rating_max; $i++)
                                <label class="inline-flex items-center">
                                    <input type="radio" name="answer[{{ $q->id }}]" value="{{ $i }}" {{ old('answer.'.$q->id) == $i ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-1">{{ $i }}</span>
                                </label>
                            @endfor
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($q->options as $opt)
                                <label class="flex items-center">
                                    <input type="radio" name="answer[{{ $q->id }}]" value="{{ $opt->id }}" {{ old('answer.'.$q->id) == $opt->id ? 'checked' : '' }} class="rounded border-gray-300">
                                    <span class="ml-2">{{ $opt->option_text }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    @error('answer.'.$q->id)<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
        <div class="mt-6">
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">Submit Feedback</button>
        </div>
    </form>
</div>
@endsection
