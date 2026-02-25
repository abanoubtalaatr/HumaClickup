@extends('layouts.app')

@section('title', 'Mentor KPI – ' . $data['mentor']->name)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('kpi.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 mb-2 inline-block">← KPI list</a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $data['mentor']->name }}</h1>
            <p class="text-sm text-gray-500">Period: {{ $data['feedback']['period_start']->format('M j, Y') }} – {{ $data['feedback']['period_end']->format('M j, Y') }} ({{ $weeks }} weeks)</p>
        </div>
        <div class="text-right">
            <span class="inline-flex px-3 py-1 text-lg font-semibold rounded-full {{ $data['kpi_total'] >= 70 ? 'bg-green-100 text-green-800' : ($data['kpi_total'] >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                KPI: {{ $data['kpi_total'] }}
            </span>
            @if($data['meets_target'])
                <span class="ml-2 text-green-600 font-medium">Target met</span>
            @endif
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Feedback</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $data['feedback']['total_submissions'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Guests with 4+ submissions: {{ $data['feedback']['guests_with_at_least_4'] ?? 0 }}/{{ $data['guests_count'] }}</p>
            <p class="text-xs text-gray-500">Avg score: {{ $data['feedback']['average_score'] ? number_format($data['feedback']['average_score'], 1) : '—' }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Attendance</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $data['attendance']['guests_with_good_attendance'] ?? 0 }}/{{ $data['guests_count'] }}</p>
            <p class="text-xs text-gray-500">Guests with good attendance (≤3 absence days)</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-sm font-medium text-gray-500">Project delivery</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $data['projects']['groups_with_at_least_2_projects'] ?? 0 }}/{{ $data['projects']['total_valid_groups'] ?? 0 }}</p>
            <p class="text-xs text-gray-500">Groups with 2+ projects (group size ≥2)</p>
        </div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <h3 class="font-medium text-gray-900 mb-2">Weekly KPI progression</h3>
        <div class="flex flex-wrap gap-2 items-baseline">
            @foreach($weeklyProgression as $w)
                <div class="text-center">
                    <p class="text-lg font-semibold {{ $w['kpi'] >= 70 ? 'text-green-600' : ($w['kpi'] >= 40 ? 'text-amber-600' : 'text-red-600') }}">{{ $w['kpi'] }}</p>
                    <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($w['week_end'])->format('M j') }}</p>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
