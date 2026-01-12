@if(session('impersonating_user_id') && auth()->user()->isAdminInWorkspace(session('current_workspace_id')))
    @php
        $impersonatedUser = \App\Models\User::find(session('impersonating_user_id'));
        $impersonatedRole = $impersonatedUser?->getRoleInWorkspace(session('current_workspace_id'));
    @endphp
    @if($impersonatedUser)
        <div class="bg-gradient-to-r from-amber-500 via-orange-500 to-red-500 text-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="py-3 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <svg class="h-5 w-5 text-white/80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <span class="text-sm font-medium">Viewing as:</span>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="h-8 w-8 rounded-full bg-white/20 flex items-center justify-center text-white text-sm font-bold">
                                {{ strtoupper(substr($impersonatedUser->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold">{{ $impersonatedUser->name }}</p>
                                <div class="flex items-center space-x-2 text-xs text-white/80">
                                    <span class="px-1.5 py-0.5 bg-white/20 rounded">{{ ucfirst($impersonatedRole) }}</span>
                                    <span>{{ $impersonatedUser->email }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <form action="{{ route('dashboard.stop-impersonating') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-sm font-medium transition-colors border border-white/30">
                            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Return to Admin
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endif

