@extends('layouts.app')

@section('title', 'All Notifications')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
        @if($notifications->where('read_at', null)->count() > 0)
        <form method="POST" action="{{ route('notifications.mark-all-read') }}">
            @csrf
            <button type="submit" 
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 text-sm font-medium">
                Mark all as read
            </button>
        </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notification)
        <a href="{{ $notification->data['url'] ?? '#' }}" 
           class="block bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition-all p-5
                  {{ $notification->read_at ? 'opacity-75' : 'border-l-4 border-l-indigo-500' }}">
            <div class="flex items-start">
                <!-- Icon -->
                <div class="flex-shrink-0 mr-4">
                    @if($notification->data['type'] === 'task_assigned')
                    <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    @elseif($notification->data['type'] === 'tester_assignment_request')
                    <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    @else
                    <div class="h-12 w-12 rounded-full bg-gray-100 flex items-center justify-center">
                        <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1">
                    <p class="text-base font-medium text-gray-900">{{ $notification->data['message'] ?? 'Notification' }}</p>
                    <p class="text-sm text-gray-600 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    
                    @if(!$notification->read_at)
                    <span class="inline-block mt-2 px-2 py-1 bg-indigo-100 text-indigo-800 text-xs font-medium rounded-full">
                        Unread
                    </span>
                    @endif
                </div>

                <!-- Arrow -->
                <div class="flex-shrink-0 ml-4">
                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </div>
        </a>
        @empty
        <div class="bg-white rounded-lg shadow p-12 text-center">
            <svg class="h-16 w-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No Notifications</h3>
            <p class="text-gray-600">You're all caught up!</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($notifications->hasPages())
    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
