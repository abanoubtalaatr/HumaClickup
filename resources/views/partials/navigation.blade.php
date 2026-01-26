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
                            <a href="{{ route('tasks.index') }}" 
                               class="{{ request()->routeIs('tasks.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Tasks
                            </a>
                            <a href="{{ route('bugs.index') }}" 
                               class="{{ request()->routeIs('bugs.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Bugs
                            </a>
                            <a href="{{ route('sprints.index', ['workspace' => session('current_workspace_id')]) }}" 
                               class="{{ request()->routeIs('sprints.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Sprints
                            </a>
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
                            <a href="{{ route('time-tracking.index') }}" 
                               class="{{ request()->routeIs('time-tracking.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Time Tracking
                            </a>
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

                    <!-- Workspace Switcher - Hidden for Guests -->
                    @if(session('current_workspace_id') && !$isGuest)
                        <div class="mr-4 relative" x-data="{ open: false }">
                            <button @click="open = !open" 
                                    class="flex items-center text-sm font-medium text-gray-700 hover:text-gray-900">
                                <span>{{ $currentWorkspace->name ?? 'Workspace' }}</span>
                                <svg class="ml-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
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
                        <!-- Show workspace name only for guests (no dropdown) -->
                        <div class="mr-4 flex items-center text-sm font-medium text-gray-500">
                            <span>{{ $currentWorkspace->name ?? 'Workspace' }}</span>
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Guest</span>
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

