@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="py-6">
        <!-- Header -->
        <div class="mb-8 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="mt-1 text-gray-500 dark:text-gray-400">Team overview and project management</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('projects.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                    <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Project
                </a>
            </div>
        </div>

        <!-- Mentor Instructions in Arabic -->
        <div class="mb-8 bg-gradient-to-r from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-2 border-purple-200 dark:border-purple-800 rounded-xl p-6 shadow-lg">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="h-8 w-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1" dir="rtl">
                    <h2 class="text-xl font-bold text-purple-900 dark:text-purple-100 mb-4"> (كل التعليمات دي ما تحسش انها قيود عليك ولكن هي بس نظام كلنا بنمشي عليه علشان نفيد فعلا الطلبه ❤️❤️❤️)</h2> 
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300"></span> نلتزم بمواعيد التسليمات المحددة
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300">لو الطلبة وقفت</span> وهتعطل التسليم، هتتدخل أنت بنفسك عشان تحل معاهم المشكلة عشان نسلم
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300">الطلبة طالعة مستفادة فعلاً</span> مش مجرد وقت بيعدي - ركز على جودة التدريب
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300">شجعهم على النشر</span> على السوشيال ميديا عن تجربتهم ومشاريعهم
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-orange-600 dark:text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-orange-900 dark:text-orange-100 leading-relaxed">
                                <span class="font-bold text-orange-700 dark:text-orange-300">أنت القدوة بتاعتهم</span> - ممنوع نلاقي منتور يقول للطلبة "زميلي" أو "صاحبي"
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300">يكون في روح حلوة</span> ما بينكم لكن ما حدش يتعدى الحدود - احنا في شركة
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-red-600 dark:text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-red-900 dark:text-red-100 leading-relaxed">
                                <span class="font-bold text-red-700 dark:text-red-300">غير مسموح للطلبة</span> إنهم ينادوا على بعض بألفاظ غير مناسبة - 
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300">متابعة دورية</span> - راجع على شغل الطلبة بشكل يومي وقدم لهم الدعم اللازم
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265l-4 12a1 1 0 11-1.898-.632l4-12a1 1 0 011.265-.633zM5.707 6.293a1 1 0 010 1.414L3.414 10l2.293 2.293a1 1 0 11-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0zm8.586 0a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 11-1.414-1.414L16.586 10l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-indigo-900 dark:text-indigo-100 leading-relaxed">
                                <span class="font-bold text-indigo-700 dark:text-indigo-300">مراجعة يومية على GitHub</span> - الطلبة بشكل يومي يرفعوا شغلهم وأنت تراجع عليه. <span class="font-bold text-red-700 dark:text-red-300">غير مسموح</span> بأن يوم يعدي من غير مراجعة للشغل وعمل Merge لو هتشتغلوا بـ GitHub
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300">تقارير أسبوعية</span> - اكتب تقرير أسبوعي لكل شخص من صفحة ال reports عن أدائه ونقاط القوة والضعف والتطوير
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300">التواصل الفعال</span> - كن متاح للطلبة وجاوب على أسئلتهم بسرعة
                            </p>
                        </div>
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="flex-shrink-0 mt-1">
                                <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-purple-900 dark:text-purple-100 leading-relaxed">
                                <span class="font-bold text-purple-700 dark:text-purple-300">كل اسبوع هتشرح موضوع معين للطلبة ونقدر نطبقة معاهم ف خلال  bootcamp
                                </span>
                            </p>
                        </div>
                    </div>
                    <div class="mt-4 pt-4 border-t border-purple-200 dark:border-purple-700">
                        <div class="flex items-start space-x-3 space-x-reverse bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-3">
                            <div class="flex-shrink-0 mt-0.5">
                                <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <p class="text-sm text-amber-900 dark:text-amber-100 leading-relaxed font-semibold">
                                💡 مسؤوليتك كمنتور هي تطوير الطلبة مهنياً وأخلاقياً - كن قدوة حسنة لهم في كل شيء
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts Section -->
        @if($overdueProjects->count() > 0 || $overdueTasks->count() > 0)
            <div class="mb-6 space-y-3">
                @if($overdueProjects->count() > 0)
                    <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-red-700">
                                <strong>{{ $overdueProjects->count() }} project(s) overdue:</strong>
                                {{ $overdueProjects->take(3)->pluck('name')->implode(', ') }}
                                @if($overdueProjects->count() > 3) and {{ $overdueProjects->count() - 3 }} more @endif
                            </p>
                        </div>
                    </div>
                @endif

                @if($projectsDueSoon->count() > 0)
                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-yellow-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <p class="text-sm text-yellow-700">
                                <strong>{{ $projectsDueSoon->count() }} project(s) due this week:</strong>
                                {{ $projectsDueSoon->take(3)->pluck('name')->implode(', ') }}
                            </p>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        <!-- Estimation Polling Overview -->
        @if($estimationPollingTasks->count() > 0)
        <div class="mb-6 bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-xl p-6" x-data="{ expanded: false, editingTask: null, editHours: 0, editMinutes: 0 }">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-amber-100 rounded-lg">
                        <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-amber-900">Estimation Polling</h2>
                        <p class="text-sm text-amber-700">Tasks with team estimation feedback</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        {{ $estimationPollingTasks->filter(fn($t) => $t['task']->estimation_status === 'polling')->count() }} in progress
                    </span>
                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full">
                        {{ $estimationPollingTasks->filter(fn($t) => $t['task']->estimation_status === 'completed')->count() }} completed
                    </span>
                </div>
            </div>

            <div class="space-y-3">
                @foreach($estimationPollingTasks->take(5) as $item)
                    <div class="bg-white rounded-lg border border-amber-100 p-4 shadow-sm">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('tasks.show', $item['task']) }}" class="font-medium text-gray-900 hover:text-indigo-600">
                                        {{ $item['task']->title }}
                                    </a>
                                    @if($item['task']->estimation_status === 'completed')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                            Completed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                            Polling
                                        </span>
                                    @endif
                                </div>
                                <p class="text-sm text-gray-500 mt-1">{{ $item['task']->project?->name }}</p>
                                
                                <!-- Progress -->
                                <div class="mt-2 flex items-center space-x-2">
                                    <div class="flex-1 bg-gray-200 rounded-full h-2 max-w-xs">
                                        <div class="h-2 rounded-full {{ $item['progress']['is_complete'] ? 'bg-green-500' : 'bg-amber-500' }}" 
                                             style="width: {{ $item['progress']['percentage'] }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $item['progress']['submitted'] }}/{{ $item['progress']['total'] }} submitted</span>
                                </div>

                                <!-- Estimations list -->
                                @if($item['estimations']->count() > 0)
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        @foreach($item['estimations'] as $est)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-700">
                                                {{ $est['user_name'] }}: <span class="font-medium ml-1">{{ $est['formatted'] }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="ml-4 text-right">
                                @if($item['task']->estimation_status === 'completed')
                                    <div>
                                        <p class="text-sm text-gray-500">Final Estimate</p>
                                        <p class="text-xl font-bold text-green-600">{{ $item['task']->getFormattedEstimation() }}</p>
                                        @if($item['task']->estimation_edited_by)
                                            <p class="text-xs text-gray-400">Edited by {{ $item['task']->estimationEditedBy?->name }}</p>
                                        @endif
                                        <button @click="editingTask = {{ $item['task']->id }}; editHours = {{ floor($item['task']->estimated_minutes / 60) }}; editMinutes = {{ $item['task']->estimated_minutes % 60 }}"
                                                class="mt-1 text-xs text-indigo-600 hover:text-indigo-800">
                                            Edit Estimate
                                        </button>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-400">Awaiting submissions</p>
                                @endif
                            </div>
                        </div>

                        <!-- Edit Estimation Inline Form -->
                        <div x-show="editingTask === {{ $item['task']->id }}" x-cloak class="mt-4 pt-4 border-t border-gray-200">
                            <form @submit.prevent="
                                fetch('/estimations/tasks/{{ $item['task']->id }}/final', {
                                    method: 'PUT',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ estimated_hours: editHours, estimated_minutes: editMinutes })
                                }).then(r => r.json()).then(data => {
                                    if (data.success) window.location.reload();
                                    else alert(data.message);
                                })
                            " class="flex items-end space-x-3">
                                <div>
                                    <label class="block text-xs text-gray-500">Hours</label>
                                    <input type="number" x-model="editHours" min="0" class="w-20 rounded border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500">Minutes</label>
                                    <input type="number" x-model="editMinutes" min="0" max="59" class="w-20 rounded border-gray-300 text-sm">
                                </div>
                                <button type="submit" class="px-3 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">
                                    Save
                                </button>
                                <button type="button" @click="editingTask = null" class="px-3 py-2 border border-gray-300 text-gray-700 text-sm rounded hover:bg-gray-50">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($estimationPollingTasks->count() > 5)
                <div class="mt-4 text-center">
                    <a href="{{ route('estimations.overview') }}" class="text-sm text-amber-700 hover:text-amber-900 font-medium">
                        View all {{ $estimationPollingTasks->count() }} polling tasks →
                    </a>
                </div>
            @endif
        </div>
        @endif

        <!-- My Time This Week -->
        <div class="mb-6 bg-gradient-to-r from-indigo-600 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100">My Time This Week</p>
                    <p class="text-3xl font-bold mt-1">{{ $myTimeSummary['total_formatted'] }}</p>
                </div>
                <a href="{{ route('time-tracking.index') }}" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    View Details →
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Top Time Trackers Leaderboard -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-amber-50 to-yellow-50">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <svg class="h-5 w-5 text-amber-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        Top Time Trackers
                    </h2>
                </div>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    @forelse($topTimeTrackers as $index => $tracker)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                                    {{ $index === 0 ? 'bg-amber-100 text-amber-800' : '' }}
                                    {{ $index === 1 ? 'bg-gray-100 text-gray-800' : '' }}
                                    {{ $index === 2 ? 'bg-orange-100 text-orange-800' : '' }}
                                    {{ $index > 2 ? 'bg-gray-50 text-gray-600' : '' }}">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $tracker['user']->name }}</p>
                                    <div class="flex items-center space-x-1">
                                        <span class="text-xs px-1.5 py-0.5 rounded 
                                            {{ $tracker['role'] === 'admin' ? 'bg-indigo-100 text-indigo-700' : '' }}
                                            {{ $tracker['role'] === 'member' ? 'bg-blue-100 text-blue-700' : '' }}
                                            {{ $tracker['role'] === 'guest' ? 'bg-gray-100 text-gray-700' : '' }}">
                                            {{ ucfirst($tracker['role']) }}
                                        </span>
                                        @if($tracker['track'])
                                            <span class="text-xs text-gray-400">{{ ucfirst(str_replace('_', '/', $tracker['track'])) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="text-sm font-semibold text-gray-900">{{ $tracker['total_formatted'] }}</span>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500 text-sm">
                            No time tracked this week yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- My Tasks -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">My Tasks</h2>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $myTasks->count() }}
                    </span>
                </div>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    @forelse($myTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-3 hover:bg-gray-50 transition-colors">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $task->title }}</p>
                            <div class="flex items-center justify-between mt-1">
                                <span class="text-xs text-gray-500">{{ $task->project?->name }}</span>
                                @if($task->due_date)
                                    <span class="text-xs {{ $task->due_date->isPast() ? 'text-red-600' : 'text-gray-400' }}">
                                        {{ $task->due_date->format('M d') }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500 text-sm">
                            No pending tasks assigned.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Team Guests -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Team Guests</h2>
                    <a href="{{ route('workspaces.members', session('current_workspace_id')) }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        Manage →
                    </a>
                </div>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    @forelse($guests as $guest)
                        <div class="px-6 py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="h-8 w-8 rounded-full bg-gray-500 flex items-center justify-center text-white text-sm font-medium">
                                    {{ strtoupper(substr($guest->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $guest->name }}</p>
                                    @if($guest->pivot->track)
                                        <span class="text-xs text-green-600">{{ ucfirst(str_replace('_', '/', $guest->pivot->track)) }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500 text-sm">
                            No guests in workspace.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Projects & Overdue Tasks -->
        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Projects -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Projects</h2>
                    <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:text-indigo-800">
                        View all →
                    </a>
                </div>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    @forelse($myProjects as $project)
                        <a href="{{ route('projects.show', $project) }}" class="block px-6 py-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center" 
                                         style="background-color: {{ $project->color ?? '#6366f1' }}20">
                                        <span class="text-lg" style="color: {{ $project->color ?? '#6366f1' }}">
                                            {{ $project->icon ?? '📁' }}
                                        </span>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $project->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $project->tasks_count }} tasks</p>
                                    </div>
                                </div>
                                @if($project->end_date)
                                    <span class="text-xs {{ $project->end_date->isPast() ? 'text-red-600 font-medium' : 'text-gray-400' }}">
                                        {{ $project->end_date->format('M d') }}
                                    </span>
                                @endif
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-gray-500 text-sm">
                            No projects yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Overdue Tasks -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-red-50">
                    <h2 class="text-lg font-semibold text-red-900 flex items-center">
                        <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                        Overdue Tasks
                    </h2>
                </div>
                <div class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                    @forelse($overdueTasks as $task)
                        <a href="{{ route('tasks.show', $task) }}" class="block px-6 py-3 hover:bg-red-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $task->title }}</p>
                                    <div class="flex items-center flex-wrap gap-1 mt-1">
                                        <span class="text-xs text-gray-500">{{ $task->project?->name }}</span>
                                        @foreach($task->assignees->take(2) as $assignee)
                                            @php
                                                $track = $assignee->getTrackInWorkspace(session('current_workspace_id'));
                                            @endphp
                                            <span class="text-xs text-gray-600 bg-gray-100 px-1.5 py-0.5 rounded">
                                                {{ explode(' ', $assignee->name)[0] }}@if($track) <span class="text-indigo-600">({{ Str::limit($track->name, 8, '') }})</span>@endif
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <span class="text-xs text-red-600 font-medium">
                                    {{ $task->due_date->diffForHumans() }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="px-6 py-8 text-center text-green-600 text-sm">
                            <svg class="mx-auto h-10 w-10 text-green-400 mb-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            No overdue tasks! Great job!
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="mt-6 bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
            </div>
            <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto">
                @forelse($recentActivity as $activity)
                    <div class="px-6 py-3 flex items-center space-x-3">
                        <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-sm font-medium">
                            {{ strtoupper(substr($activity->user?->name ?? 'S', 0, 1)) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">
                                <span class="font-medium">{{ $activity->user?->name ?? 'System' }}</span>
                                {{ $activity->description }}
                            </p>
                            <p class="text-xs text-gray-500">{{ $activity->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-500 text-sm">
                        No recent activity.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

