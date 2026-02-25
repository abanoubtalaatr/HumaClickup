@extends('layouts.app')

@section('title', 'Mentor KPI')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="flex flex-wrap justify-between items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Mentor KPI</h1>
            <p class="mt-1 text-sm text-gray-500">Performance based on feedback, attendance, and project delivery.</p>
        </div>
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label for="period_end" class="text-sm text-gray-700">Period end</label>
            <input type="date" id="period_end" name="period_end" value="{{ ($periodEnd ?? today())->format('Y-m-d') }}" class="rounded-md border-gray-300 text-sm">
            <label for="weeks" class="text-sm text-gray-700 ml-2">Weeks</label>
            <select name="weeks" id="weeks" class="rounded-md border-gray-300 text-sm">
                @foreach([1,2,4,8,12] as $w)
                    <option value="{{ $w }}" {{ (int)request('weeks', $weeks) === $w ? 'selected' : '' }}>{{ $w }}</option>
                @endforeach
            </select>
            @if(count($mentors) > 1)
                <label for="mentor_id" class="text-sm text-gray-700 ml-2">Mentor</label>
                <select name="mentor_id" id="mentor_id" class="rounded-md border-gray-300 text-sm">
                    <option value="">All</option>
                    @foreach($mentors as $m)
                        <option value="{{ $m->id }}" {{ request('mentor_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                    @endforeach
                </select>
            @endif
            <button type="submit" class="ml-2 px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm">Apply</button>
        </form>
    </div>
    <div class="mb-4 rounded-lg bg-gray-50 border border-gray-200 px-4 py-3 text-sm text-gray-600">
        <strong>Column meanings:</strong> <strong>Feedback</strong> = total mentor feedback submissions; “guests (4+)” = how many guests submitted at least 4 times in the period. <strong>Attendance</strong> = guests with good attendance (≤3 absence days) / total guests. <strong>Projects</strong> = groups that have at least 2 projects (with group size ≥2) / total such groups.
    </div>
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mentor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guests</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">KPI</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" title="Total feedback submissions; how many guests have ≥4 submissions in the period">Feedback <span class="text-gray-400 font-normal">(subs · guests with 4+)</span></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" title="Guests with good attendance (≤3 absence days) / total guests">Attendance <span class="text-gray-400 font-normal">(good / total)</span></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase" title="Groups with ≥2 projects (group size ≥2) / total valid groups">Projects <span class="text-gray-400 font-normal">(groups 2+ proj)</span></th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Target</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($kpiList as $row)
                    @php $d = is_array($row) ? $row : (array) $row; @endphp
                    <tr class="{{ ($d['meets_target'] ?? false) ? 'bg-green-50' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-medium text-gray-900">{{ $d['mentor']->name }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $d['guests_count'] ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-sm font-semibold rounded-full
                                {{ ($d['kpi_total'] ?? 0) >= 70 ? 'bg-green-100 text-green-800' : (($d['kpi_total'] ?? 0) >= 40 ? 'bg-amber-100 text-amber-800' : 'bg-red-100 text-red-800') }}">
                                {{ $d['kpi_total'] ?? 0 }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600" title="Total submissions; guests with ≥4 in period">{{ $d['feedback']['total_submissions'] ?? 0 }} sub · {{ $d['feedback']['guests_with_at_least_4'] ?? 0 }}/{{ $d['guests_count'] ?? 0 }} guests (4+)</td>
                        <td class="px-6 py-4 text-sm text-gray-600" title="Guests with ≤3 absence days">{{ $d['attendance']['guests_with_good_attendance'] ?? 0 }}/{{ $d['guests_count'] ?? 0 }} good</td>
                        <td class="px-6 py-4 text-sm text-gray-600" title="Groups that have ≥2 projects (size ≥2)">{{ $d['projects']['groups_with_at_least_2_projects'] ?? 0 }}/{{ $d['projects']['total_valid_groups'] ?? 0 }} groups (2+ proj)</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($d['meets_target'] ?? false)
                                <span class="text-green-600 font-medium">✓ Met</span>
                            @else
                                <span class="text-amber-600">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('kpi.show', $d['mentor']->id) }}?weeks={{ $weeks }}&period_end={{ ($periodEnd ?? today())->format('Y-m-d') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">Details</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if(empty($kpiList) || (is_countable($kpiList) && count($kpiList) === 0))
            <div class="px-6 py-12 text-center text-gray-500">No mentor data for this period.</div>
        @endif
    </div>
</div>
@endsection
