@extends('layouts.app')

@section('title', 'Edit Daily Status')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Edit Daily Status</h1>
            <p class="mt-1 text-sm text-gray-500">
                Status for {{ $dailyStatus->date->format('l, F d, Y') }}
            </p>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('daily-statuses.update', $dailyStatus) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')

                <!-- Date (read-only) -->
                <div class="mb-4">
                    <label for="date" class="block text-sm font-medium text-gray-700 mb-2">
                        Date
                    </label>
                    <input type="text" 
                           id="date" 
                           value="{{ $dailyStatus->date->format('l, F d, Y') }}"
                           disabled
                           class="block w-full border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500">
                    <p class="mt-1 text-xs text-gray-500">Date cannot be changed after creation</p>
                </div>

                <!-- Status -->
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        What did you do? *
                    </label>
                    <!-- Hidden textarea for form submission -->
                    <textarea name="status" 
                              id="status" 
                              style="display: none;">{{ old('status', $dailyStatus->status) }}</textarea>
                    <!-- TinyMCE editor container -->
                    <div id="status_editor" 
                         class="bg-white border border-gray-300 rounded-md shadow-sm focus-within:ring-indigo-500 focus-within:border-indigo-500"
                         style="min-height: 300px;">
                        {!! old('status', $dailyStatus->status) !!}
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimum 10 characters required</p>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('daily-statuses.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#status_editor',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 400,
            content_style: 'body { font-family:Inter,Helvetica,Arial,sans-serif; font-size:14px }',
            setup: function(editor) {
                editor.on('change', function() {
                    // Update hidden textarea with HTML content
                    document.getElementById('status').value = editor.getContent();
                });
                
                // Also update on keyup for real-time updates
                editor.on('keyup', function() {
                    document.getElementById('status').value = editor.getContent();
                });
            }
        });
    }
});
</script>
@endpush
@endsection
