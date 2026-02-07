@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
            <p class="text-sm text-gray-500 mt-1">{{ ($projects ?? collect())->count() }} projects in this workspace</p>
        </div>
        @if(auth()->user()->canCreateInWorkspace(session('current_workspace_id')))
        <a href="{{ route('projects.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-[#7b68ee] hover:bg-[#6c5ce7] shadow-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            New Project
        </a>
        @endif
    </div>

    {{-- Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($projects ?? [] as $project)
            @php
                $color = $project->color ?? '#7b68ee';
                $progress = min($project->progress ?? 0, 100);
                $tasksCount = $project->tasks_count ?? 0;
            @endphp
            <a href="{{ route('projects.show', $project) }}"
               class="group relative bg-white rounded-xl border border-gray-200 hover:border-[#7b68ee]/40 hover:shadow-lg transition-all duration-200 overflow-hidden">
                {{-- Color strip --}}
                <div class="h-1" style="background: {{ $color }};"></div>

                <div class="p-5">
                    {{-- Header --}}
                    <div class="flex items-start gap-3 mb-4">
                        <div class="flex-shrink-0 w-11 h-11 rounded-xl flex items-center justify-center text-xl shadow-sm"
                             style="background: {{ $color }}15;">
                            {{ $project->icon ?? '📁' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-[15px] font-bold text-gray-900 group-hover:text-[#7b68ee] truncate transition-colors">
                                {{ $project->name }}
                            </h3>
                            @if($project->description)
                                <p class="text-[13px] text-gray-500 truncate mt-0.5">{{ Str::limit($project->description, 50) }}</p>
                            @elseif($project->space)
                                <p class="text-[13px] text-gray-500 truncate mt-0.5">{{ $project->space->name }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Progress --}}
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-xs font-semibold text-gray-700">Progress</span>
                            <span class="text-xs font-bold" style="color: {{ $color }}">{{ number_format($progress, 0) }}%</span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" style="width: {{ $progress }}%; background: {{ $color }};"></div>
                        </div>
                    </div>

                    {{-- Stats row --}}
                    <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                        <span class="inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            {{ $tasksCount }} tasks
                        </span>
                        @if($project->due_date)
                            <span class="inline-flex items-center gap-1 {{ $project->isOverdue() ? 'text-red-500 font-semibold' : '' }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $project->due_date->format('M j, Y') }}
                                @if($project->isOverdue())
                                    <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-600 rounded text-[10px] font-bold uppercase">Overdue</span>
                                @endif
                            </span>
                        @endif
                        <span class="ml-auto text-gray-400">{{ $project->updated_at->diffForHumans() }}</span>
                    </div>

                    {{-- Creator --}}
                    @if(!$isGuest && $project->createdBy)
                        <div class="flex items-center gap-2 pt-3 border-t border-gray-100">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold text-white" style="background: {{ $color }};">
                                {{ strtoupper(substr($project->createdBy->name, 0, 1)) }}
                            </div>
                            <span class="text-xs text-gray-500">{{ $project->createdBy->name }}</span>
                            @if($project->createdBy->whatsapp_number)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $project->createdBy->whatsapp_number) }}"
                                   target="_blank"
                                   onclick="event.stopPropagation(); event.preventDefault();"
                                   class="ml-auto inline-flex items-center gap-1 text-[11px] text-[#25d366] hover:text-[#1da851] font-medium">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                                    </svg>
                                    Chat
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </a>
        @empty
            {{-- Empty state --}}
            <div class="col-span-full">
                <div class="flex flex-col items-center justify-center py-20 px-6 bg-white rounded-xl border-2 border-dashed border-gray-200">
                    <div class="w-20 h-20 rounded-2xl bg-[#7b68ee]/10 flex items-center justify-center mb-5">
                        <svg class="w-10 h-10 text-[#7b68ee]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">No projects yet</h3>
                    @if(auth()->user()->canCreateInWorkspace(session('current_workspace_id')))
                        <p class="text-sm text-gray-500 mb-6 max-w-sm text-center">Create your first project to start organizing tasks and tracking progress.</p>
                        <a href="{{ route('projects.create') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold text-white bg-[#7b68ee] hover:bg-[#6c5ce7] shadow-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            New Project
                        </a>
                    @else
                        <p class="text-sm text-gray-500">No projects have been shared with you yet.</p>
                    @endif
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
