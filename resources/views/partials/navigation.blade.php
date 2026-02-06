<nav class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" class="text-2xl font-bold text-indigo-600">
                        HumaClickup
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                    @auth
                        @if(session('current_workspace_id'))
                            <a href="{{ route('dashboard') }}" 
                               class="{{ request()->routeIs('dashboard') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('projects.index') }}" 
                               class="{{ request()->routeIs('projects.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Projects
                            </a>
                            {{-- Tasks tab hidden - access only through projects --}}
                            {{-- <a href="{{ route('tasks.index') }}" 
                               class="{{ request()->routeIs('tasks.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Tasks
                            </a> --}}
                            <a href="{{ route('bugs.index') }}" 
                               class="{{ request()->routeIs('bugs.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Bugs
                            </a>
                            {{-- Sprints tab hidden --}}
                            {{-- <a href="{{ route('sprints.index', ['workspace' => session('current_workspace_id')]) }}" 
                               class="{{ request()->routeIs('sprints.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Sprints
                            </a> --}}
                            @if(auth()->user()->isMemberInWorkspace(session('current_workspace_id')))
                            <a href="{{ route('workspaces.members', session('current_workspace_id')) }}" 
                               class="{{ request()->routeIs('workspaces.members*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Team
                            </a>
                            @endif
                            @if(auth()->user()->isAdminInWorkspace(session('current_workspace_id')) || auth()->user()->isOwnerInWorkspace(session('current_workspace_id')))
                            <a href="{{ route('workspaces.tracks.index', session('current_workspace_id')) }}" 
                               class="{{ request()->routeIs('workspaces.tracks*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Tracks
                            </a>
                            @endif
                            {{-- Time Tracking tab hidden --}}
                            {{-- <a href="{{ route('time-tracking.index') }}" 
                               class="{{ request()->routeIs('time-tracking.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Time Tracking
                            </a> --}}
                            <a href="{{ route('groups.index') }}" 
                               class="{{ request()->routeIs('groups.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Groups
                            </a>
                            <a href="{{ route('reports.index') }}" 
                               class="{{ request()->routeIs('reports.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Reports
                            </a>
                            <a href="{{ route('topics.index') }}" 
                               class="{{ request()->routeIs('topics.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Topics
                            </a>
                            <a href="{{ route('daily-statuses.index') }}" 
                               class="{{ request()->routeIs('daily-statuses.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Status
                            </a>
                            {{-- Attendance link hidden --}}
                            {{-- <a href="{{ route('attendance.index') }}" 
                               class="{{ request()->routeIs('attendance.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Attendance
                            </a> --}}
                        @endif
                    @endauth
                </div>
            </div>

            <!-- Right Side -->
            <div class="flex items-center">
                @auth
                    @php
                        $isGuest = session('current_workspace_id') && auth()->user()->isGuestInWorkspace(session('current_workspace_id'));
                    @endphp
                    
                    <!-- Active Timer Indicator -->
                    @php
                        $activeTimer = auth()->user()->getActiveTimer();
                    @endphp
                    @if($activeTimer)
                        <div class="mr-4">
                            <a href="{{ route('time-tracking.index') }}" 
                               class="flex items-center px-3 py-2 bg-red-100 text-red-700 rounded-lg hover:bg-red-200">
                                <svg class="w-4 h-4 mr-2 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium">Timer Running</span>
                            </a>
                        </div>
                    @endif

                    <!-- Notifications Bell -->
                    <div class="mr-4 relative" x-data="notificationBell()" x-init="init()">
                        <button @click="toggle()" 
                                class="relative flex items-center text-gray-500 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 rounded-full p-2"
                                title="Notifications">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <!-- Badge -->
                            <span x-show="unreadCount > 0" 
                                  x-text="unreadCount"
                                  class="absolute top-0 right-0 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-600 rounded-full min-w-[20px]"></span>
                        </button>

                        <!-- Dropdown -->
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 transform scale-95"
                             x-transition:enter-end="opacity-100 transform scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 transform scale-100"
                             x-transition:leave-end="opacity-0 transform scale-95"
                             class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-[500px] overflow-hidden flex flex-col"
                             style="display: none;">
                            <!-- Header -->
                            <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                <button @click="markAllAsRead()" 
                                        x-show="unreadCount > 0"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    Mark all as read
                                </button>
                            </div>

                            <!-- Notifications List -->
                            <div class="overflow-y-auto flex-1" style="max-height: 400px;">
                                <template x-if="notifications.length === 0">
                                    <div class="px-4 py-8 text-center text-gray-500 text-sm">
                                        <svg class="h-12 w-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                        </svg>
                                        <p>No notifications</p>
                                    </div>
                                </template>

                                <template x-for="notification in notifications" :key="notification.id">
                                    <a :href="notification.data.url || '#'" 
                                       @click="markAsRead(notification.id)"
                                       class="block px-4 py-3 hover:bg-gray-50 border-b border-gray-100 transition-colors"
                                       :class="{ 'bg-indigo-50': !notification.read_at }">
                                        <div class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <!-- Icon based on type -->
                                                <template x-if="notification.data.type === 'task_assigned'">
                                                    <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                        </svg>
                                                    </div>
                                                </template>
                                                <template x-if="notification.data.type === 'tester_assignment_request'">
                                                    <div class="h-10 w-10 rounded-full bg-green-100 flex items-center justify-center">
                                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                                        </svg>
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="ml-3 flex-1">
                                                <p class="text-sm font-medium text-gray-900" x-text="notification.data.message"></p>
                                                <p class="mt-1 text-xs text-gray-500" x-text="formatDate(notification.created_at)"></p>
                                            </div>
                                            <div x-show="!notification.read_at" class="ml-2">
                                                <span class="inline-block h-2 w-2 rounded-full bg-indigo-600"></span>
                                            </div>
                                        </div>
                                    </a>
                                </template>
                            </div>

                            <!-- Footer -->
                            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                                <a href="{{ route('notifications.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                                    View all notifications
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Workspace Switcher - Hidden for Guests (workspace name removed) -->
                    @if(session('current_workspace_id') && !$isGuest)
                        <div class="mr-4 relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900"
                                    title="Switch Workspace">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                </svg>
                            </button>
                            <div x-show="open" 
                                 @click.away="open = false"
                                 x-transition
                                 class="absolute mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 right-0">
                                @foreach(auth()->user()->workspaces as $workspace)
                                    <form method="POST" action="{{ route('workspaces.switch', $workspace) }}">
                                        @csrf
                                        <button type="submit" 
                                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 {{ $workspace->id == session('current_workspace_id') ? 'bg-indigo-50 text-indigo-700' : '' }}">
                                            {{ $workspace->name }}
                                        </button>
                                    </form>
                                @endforeach
                            </div>
                        </div>
                    @elseif(session('current_workspace_id') && $isGuest)
                        <!-- Guest badge only (workspace name removed) -->
                        <div class="mr-4 flex items-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Guest</span>
                        </div>
                    @endif

                    <!-- User Menu -->
                    <div class="ml-3 relative" x-data="{ open: false }">
                        <div>
                            <button @click="open = !open" 
                                    class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <span class="sr-only">Open user menu</span>
                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white font-medium">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                            </button>
                        </div>
                        <div x-show="open" 
                             @click.away="open = false"
                             x-transition
                             class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 z-50">
                            <a href="{{ route('profile.edit') }}" 
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your Profile</a>
                            @if(!$isGuest)
                                <a href="{{ route('workspaces.index') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Workspaces</a>
                            @endif
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" 
                                        class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Log out
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-700">Login</a>
                @endauth
            </div>
        </div>
    </div>
</nav>

@auth
<script>
function notificationBell() {
    return {
        open: false,
        notifications: [],
        unreadCount: 0,

        init() {
            this.fetchNotifications();
            this.listenForNotifications();
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.fetchNotifications();
            }
        },

        async fetchNotifications() {
            try {
                const response = await fetch('/notifications', {
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    console.error('Failed to fetch notifications:', response.status);
                    return;
                }
                
                const data = await response.json();
                this.notifications = data.notifications || [];
                this.unreadCount = data.unread_count || 0;
            } catch (error) {
                console.error('Error fetching notifications:', error);
            }
        },

        async markAsRead(notificationId) {
            try {
                await fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                this.fetchNotifications();
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                await fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json'
                    }
                });
                this.fetchNotifications();
            } catch (error) {
                console.error('Error marking all as read:', error);
            }
        },

        listenForNotifications() {
            if (typeof window.Echo !== 'undefined') {
                window.Echo.private('App.Models.User.{{ auth()->id() }}')
                    .notification((notification) => {
                        // Add new notification to the list
                        this.notifications.unshift({
                            id: notification.id,
                            type: notification.type,
                            data: notification,
                            created_at: new Date().toISOString(),
                            read_at: null
                        });
                        this.unreadCount++;

                        // Show browser notification if permitted
                        if ('Notification' in window && Notification.permission === 'granted') {
                            new Notification(notification.message || 'New notification', {
                                icon: '/favicon.ico'
                            });
                        }
                    });
            }
        },

        formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = Math.floor((now - date) / 1000); // seconds

            if (diff < 60) return 'Just now';
            if (diff < 3600) return Math.floor(diff / 60) + ' min ago';
            if (diff < 86400) return Math.floor(diff / 3600) + ' hr ago';
            if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
            return date.toLocaleDateString();
        }
    };
}

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
</script>
@endauth

