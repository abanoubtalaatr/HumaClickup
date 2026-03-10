@extends('layouts.app')

@section('title', $isAdmin ? 'Feedback Questions' : 'My Feedback')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ $isAdmin ? 'Feedback Questions' : 'My Feedback' }}</h1>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                @if($isAdmin)
                    Configure questions and view feedback results for each member.
                @else
                    Feedback from guests about your mentoring.
                @endif
            </p>
        </div>
        @if($isAdmin)
            <a href="{{ route('feedback-questions.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">Add Question</a>
        @endif
    </div>
    @if(session('success'))
        <p class="mb-4 text-sm text-green-600 dark:text-green-400">{{ session('success') }}</p>
    @endif

    @if($isAdmin)
    <!-- Admin: Questions list -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-3 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Questions</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Guests answer these when submitting mentor feedback.</p>
        </div>
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($questions as $q)
                <li class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $q->question_text }}</p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst(str_replace('_', ' ', $q->type)) }} @if($q->type === 'rating')(1–{{ $q->rating_max }})@endif</p>
                        @if($q->options->isNotEmpty())
                            <p class="text-xs text-gray-400 mt-1">{{ $q->options->count() }} option(s)</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if(!$q->is_active)<span class="text-xs text-amber-600">Inactive</span>@endif
                        <a href="{{ route('feedback-questions.edit', $q) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 text-sm">Edit</a>
                        <form action="{{ route('feedback-questions.destroy', $q) }}" method="POST" class="inline" onsubmit="return confirm('Delete this question?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 dark:text-red-400 hover:text-red-800 text-sm">Delete</button>
                        </form>
                    </div>
                </li>
            @empty
                <li class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">No questions yet. Add one to let guests submit feedback.</li>
            @endforelse
        </ul>
    </div>
    @endif

    <!-- Feedback results: Admin = per member selector, Member = my feedback -->
    <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                @if($isAdmin)
                    Feedback results by member
                @else
                    My feedback from guests
                @endif
            </h2>
            @if($isAdmin)
                <form method="GET" action="{{ route('feedback-questions.index') }}" class="mt-3 flex items-center gap-3">
                    <label for="member_id" class="text-sm font-medium text-gray-700 dark:text-gray-300">Select member:</label>
                    <select name="member_id" id="member_id" onchange="this.form.submit()" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        <option value="">— Choose a member —</option>
                        @foreach($membersWithFeedback as $m)
                            <option value="{{ $m->id }}" {{ $selectedMember && $selectedMember->id === $m->id ? 'selected' : '' }}>{{ $m->name }} ({{ $m->email }})</option>
                        @endforeach
                    </select>
                </form>
                @if($membersWithFeedback->isEmpty())
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No feedback submissions yet. Guests submit feedback from the Feedback menu.</p>
                @endif
            @endif
        </div>

        @if($feedbackResults && $selectedMember)
            <div class="px-6 py-4">
                <p class="text-sm text-gray-600 dark:text-gray-300 mb-4">
                    <strong>{{ $selectedMember->name }}</strong> — {{ $feedbackResults['total_submissions'] }} submission(s) from guests.
                </p>
                @if($feedbackResults['total_submissions'] === 0)
                    <p class="text-gray-500 dark:text-gray-400">{{ $isAdmin ? 'No feedback submitted yet for this member.' : 'No feedback from guests yet.' }}</p>
                @else
                    <div class="space-y-6">
                        @foreach($feedbackResults['by_question'] as $qId => $data)
                            <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4">
                                <p class="font-medium text-gray-900 dark:text-white mb-2">{{ $data['question']->question_text }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $data['count'] }} response(s)</p>
                                @if($data['type'] === 'rating')
                                    <div class="flex flex-wrap gap-4 text-sm">
                                        <span class="text-gray-700 dark:text-gray-300">Average: <strong>{{ $data['average'] !== null ? number_format($data['average'], 1) : '—' }}</strong></span>
                                        @if($data['min'] !== null)
                                            <span class="text-gray-600 dark:text-gray-400">Min: {{ $data['min'] }}, Max: {{ $data['max'] }}</span>
                                        @endif
                                    </div>
                                @else
                                    <ul class="space-y-1 text-sm">
                                        @foreach($data['question']->options as $opt)
                                            @php $cnt = $data['option_counts'][$opt->id] ?? 0; @endphp
                                            <li class="flex justify-between text-gray-700 dark:text-gray-300">
                                                <span>{{ $opt->option_text }}</span>
                                                <span>{{ $cnt }} {{ $cnt === 1 ? 'vote' : 'votes' }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <!-- Feedback answers: each submission with actual answers (admin sees full list) -->
                    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Feedback answers</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Each submission with the answers given by the guest.</p>
                        <div class="space-y-4">
                            @foreach($feedbackResults['submissions'] as $sub)
                                <div class="border border-gray-200 dark:border-gray-600 rounded-lg p-4 bg-gray-50 dark:bg-gray-700/30">
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="font-medium text-gray-900 dark:text-white">{{ $sub->guest->name ?? 'Guest' }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $sub->submitted_at->format('M j, Y H:i') }}</span>
                                    </div>
                                    <ul class="space-y-2 text-sm">
                                        @foreach($sub->answers as $ans)
                                            <li class="flex flex-wrap gap-x-2">
                                                <span class="text-gray-700 dark:text-gray-300 font-medium">{{ $ans->question->question_text ?? 'Q' }}:</span>
                                                @if($ans->question && $ans->question->type === 'rating')
                                                    <span class="text-gray-900 dark:text-white">{{ $ans->rating_value ?? '—' }}/{{ $ans->question->rating_max ?? 5 }}</span>
                                                @else
                                                    <span class="text-gray-900 dark:text-white">{{ $ans->option->option_text ?? '—' }}</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        @elseif(!$isAdmin)
            {{-- Member view but no results yet --}}
            <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                <p>No feedback from guests yet. Guests you mentor can submit feedback from the <strong>Feedback</strong> menu.</p>
            </div>
        @elseif($isAdmin && $membersWithFeedback->isNotEmpty() && !$selectedMember)
            <div class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                <p>Select a member above to view their feedback results.</p>
            </div>
        @endif
    </div>
</div>
@endsection
