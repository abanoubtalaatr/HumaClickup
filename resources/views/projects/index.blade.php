@extends('layouts.app')

@section('title', 'Projects')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Projects</h1>
        @if(auth()->user()->canCreateInWorkspace(session('current_workspace_id')))
        <a href="{{ route('projects.create') }}"
           class="flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            New Project
        </a>
        @endif
    </div>

    {{-- Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        @forelse($projects ?? [] as $project)
        @php
            $progress = round(min($project->progress ?? 0, 100));
            $tasksCount = $project->tasks_count ?? 0;
            $color = $project->color ?? '#6366f1';
        @endphp
        <div class="bg-white rounded-2xl shadow-[0_1px_3px_rgba(0,0,0,0.08),0_8px_24px_rgba(0,0,0,0.04)] hover:shadow-[0_1px_3px_rgba(0,0,0,0.08),0_12px_32px_rgba(0,0,0,0.1)] transition-shadow duration-300">
            <a href="{{ route('projects.show', $project) }}" class="block p-6">

                {{-- Top row: icon + name --}}
                <div class="flex items-center gap-4 mb-5">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl" style="background: {{ $color }}1A;">
                        {{ $project->icon ?? '📁' }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-base font-semibold text-gray-900 truncate">{{ $project->name }}</h2>
                        @if($project->description)
                            <p class="text-sm text-gray-500 truncate">{{ Str::limit($project->description, 60) }}</p>
                        @endif
                    </div>
                </div>

                {{-- Progress --}}
                <div class="mb-5">
                    <div class="flex justify-between items-center text-sm mb-2">
                        <span class="text-gray-500 font-medium">Progress</span>
                        <span class="font-semibold text-gray-800">{{ $progress }}%</span>
                    </div>
                    <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full" style="width:{{ $progress }}%; background:{{ $color }};"></div>
                    </div>
                </div>

                {{-- Info pills --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ $tasksCount }} tasks
                    </span>
                    @if($project->due_date)
                        @if($project->isOverdue())
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 bg-red-50 rounded-full px-2.5 py-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Overdue · {{ $project->due_date->format('M j') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full px-2.5 py-1">
                                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Due {{ $project->due_date->format('M j') }}
                            </span>
                        @endif
                    @endif
                    <span class="text-xs text-gray-400 ml-auto">{{ $project->updated_at->diffForHumans() }}</span>
                </div>

                {{-- Creator --}}
                @if(!$isGuest && $project->createdBy)
                <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold text-white" style="background:{{ $color }}">
                        {{ strtoupper(substr($project->createdBy->name, 0, 1)) }}
                    </div>
                    <span class="text-sm text-gray-600">{{ $project->createdBy->name }}</span>
                    @if($project->createdBy->whatsapp_number)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $project->createdBy->whatsapp_number) }}"
                       target="_blank"
                       onclick="event.stopPropagation(); event.preventDefault();"
                       class="ml-auto flex items-center gap-1 text-xs font-medium text-green-600 hover:text-green-700 bg-green-50 hover:bg-green-100 rounded-full px-2.5 py-1 transition">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        WhatsApp
                    </a>
                    @endif
                </div>
                @endif

            </a>
        </div>
        @empty
        <div class="col-span-full text-center py-20">
            <svg class="mx-auto w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No projects yet</h3>
            @if(auth()->user()->canCreateInWorkspace(session('current_workspace_id')))
            <p class="text-sm text-gray-500 mb-6">Get started by creating your first project.</p>
            <a href="{{ route('projects.create') }}"
               class="inline-flex items-center gap-2 bg-indigo-600 text-white text-sm font-medium px-5 py-2.5 rounded-lg hover:bg-indigo-700 transition">
                New Project
            </a>
            @else
            <p class="text-sm text-gray-500">No projects have been shared with you yet.</p>
            @endif
        </div>
        @endforelse
    </div>
</div>
@endsection
