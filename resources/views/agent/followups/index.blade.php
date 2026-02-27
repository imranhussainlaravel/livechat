@extends('layouts.app')
@section('header_title', 'Follow-ups')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Your Follow-ups</h1>
        <p class="text-gray-500 mt-1">Manage scheduled follow-ups with visitors.</p>
    </div>
</div>

<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-100">
        @forelse($followups as $followup)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-gray-50 transition">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 mb-1">
                    <a href="{{ route('agent.chats.show', $followup->chat_id) }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800 hover:underline">
                        Chat #{{ $followup->chat_id }}
                    </a>
                    <span class="text-xs text-gray-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Due: {{ \Carbon\Carbon::parse($followup->followup_time)->format('M d, Y g:i A') }}
                    </span>
                </div>
                @if($followup->notes)
                <p class="text-sm text-gray-600 mt-1">{{ $followup->notes }}</p>
                @endif
            </div>

            <div class="flex items-center gap-4 shrink-0 mt-3 sm:mt-0">
                @php
                $fStatusColors = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'completed' => 'bg-green-100 text-green-800',
                'cancelled' => 'bg-gray-100 text-gray-800',
                ];
                $statusValue = $followup->status->value ?? $followup->status;
                $statusBg = $fStatusColors[$statusValue] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBg }}">
                    {{ ucfirst($statusValue) }}
                </span>

                @if($statusValue === 'pending')
                <div class="flex gap-2">
                    <form method="POST" action="{{ route('agent.followups.complete', $followup->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors">
                            Complete
                        </button>
                    </form>
                    <form method="POST" action="{{ route('agent.followups.cancel', $followup->id) }}">
                        @csrf @method('PATCH')
                        <button type="submit" class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            Cancel
                        </button>
                    </form>
                </div>
                @endif
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="text-sm text-gray-500">No scheduled follow-ups.</p>
        </div>
        @endforelse
    </div>
</div>

@if(isset($followups) && $followups->hasPages())
<div class="mt-6 flex justify-center">
    {{ $followups->links() }}
</div>
@endif

@endsection