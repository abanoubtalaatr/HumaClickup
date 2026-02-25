@extends('layouts.app')

@section('title', 'Edit Pull Request')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Pull Request</h1>
            <p class="mt-1 text-sm text-gray-500">Update the pull request link, project, or date.</p>
        </div>

        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('pull-requests.update', $pullRequest) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label for="project_id" class="block text-sm font-medium text-gray-700 mb-2">Project *</label>
                    <select id="project_id" name="project_id" required
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                        @foreach($assignedProjects as $p)
                            <option value="{{ $p->id }}" {{ old('project_id', $pullRequest->project_id) == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                    @error('project_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="link" class="block text-sm font-medium text-gray-700 mb-2">Pull request link (URL) *</label>
                    <input type="url" id="link" name="link" value="{{ old('link', $pullRequest->link) }}" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('link')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Date *</label>
                    <input type="date" id="date" name="date" value="{{ old('date', $pullRequest->date->format('Y-m-d')) }}" required
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    @error('date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('pull-requests.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
