@extends('layouts.app')

@section('title', 'Global Settings')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Global Settings</h1>
        <p class="mt-1 text-sm text-gray-500">System-wide configurations for this workspace. Changes affect KPI, attendance, and reports.</p>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-4 rounded-md bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg overflow-hidden mb-8">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Round &amp; period settings</h2>
            <p class="text-sm text-gray-500 mt-0.5">Used for KPI calculations, attendance tracking, and weekly/monthly reports.</p>
        </div>
        <form action="{{ route('settings.update') }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label for="round_start_date" class="block text-sm font-medium text-gray-700">Round start date</label>
                    <input type="date"
                           id="round_start_date"
                           name="round_start_date"
                           value="{{ old('round_start_date', $roundStartDate) }}"
                           min="2000-01-01"
                           class="mt-1 block w-full max-w-xs rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <p class="mt-1 text-xs text-gray-500">Official start of the current round. Leave empty if not used.</p>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-sm font-medium">
                    Save settings
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-lg font-semibold text-gray-900">Recent changes</h2>
            <p class="text-sm text-gray-500 mt-0.5">Who changed what and when (last 20 entries).</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Setting</th>
                        <th scope="col" class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Previous</th>
                        <th scope="col" class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">New</th>
                        <th scope="col" class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">Changed by</th>
                        <th scope="col" class="px-6 py-2 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($auditLogs as $log)
                        <tr>
                            <td class="px-6 py-3 text-sm text-gray-900">{{ str_replace('_', ' ', ucfirst($log->setting_key)) }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $log->old_value ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $log->new_value ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $log->changedBy?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-sm text-gray-500">{{ $log->changed_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No changes recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
