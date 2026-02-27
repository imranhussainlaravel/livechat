@extends('layouts.app')
@section('header_title', 'Activity Log')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Activity Log</h1>
        <p class="text-gray-500 mt-1">Audit trail of system and agent actions.</p>
    </div>
</div>

<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-900">Recent Activity</h3>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($activities as $activity)
        <div class="flex items-center gap-4 px-6 py-3 hover:bg-gray-50 transition">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold text-gray-900">{{ $activity->user->name ?? 'System' }}</span>
                    {{ $activity->action }}
                    @if($activity->reference_type)
                    <span class="text-gray-500 font-medium ml-1 bg-gray-100 px-1.5 py-0.5 rounded text-xs">
                        {{ class_basename($activity->reference_type) }} #{{ $activity->reference_id }}
                    </span>
                    @endif
                </p>
            </div>

            <div class="shrink-0 text-right">
                <span class="text-xs text-gray-500 whitespace-nowrap">{{ $activity->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <p class="text-sm text-gray-500">No activity recorded yet.</p>
        </div>
        @endforelse
    </div>
</div>

@if(isset($activities) && $activities->hasPages())
<div class="mt-6 flex justify-center">
    {{ $activities->links() }}
</div>
@endif

@endsection