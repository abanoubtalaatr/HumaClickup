@extends('layouts.app')

@section('title', 'Feedback Questions')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Feedback Questions</h1>
            <p class="mt-1 text-sm text-gray-500">Configure questions guests answer when submitting mentor feedback.</p>
        </div>
        <a href="{{ route('feedback-questions.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">Add Question</a>
    </div>
    @if(session('success'))
        <p class="mb-4 text-sm text-green-600">{{ session('success') }}</p>
    @endif
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <ul class="divide-y divide-gray-200">
            @forelse($questions as $q)
                <li class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900">{{ $q->question_text }}</p>
                        <p class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $q->type)) }} @if($q->type === 'rating')(1–{{ $q->rating_max }})@endif</p>
                        @if($q->options->isNotEmpty())
                            <p class="text-xs text-gray-400 mt-1">{{ $q->options->count() }} option(s)</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!$q->is_active)<span class="text-xs text-amber-600">Inactive</span>@endif
                        <a href="{{ route('feedback-questions.edit', $q) }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Edit</a>
                        <form action="{{ route('feedback-questions.destroy', $q) }}" method="POST" class="inline" onsubmit="return confirm('Delete this question?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm">Delete</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="px-6 py-12 text-center text-gray-500">No questions yet. Add one to let guests submit feedback.</li>
            @endforelse
        </ul>
    </div>
</div>
@endsection
