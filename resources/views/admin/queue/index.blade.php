@extends('layouts.app')
@section('header_title', 'System Queue Monitor')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Queued Conversations</h1>
        <p class="text-gray-500 mt-1">Real-time overview of unassigned visitors waiting for support.</p>
    </div>
</div>

<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-100">
        @forelse($chats as $chat)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-5 hover:bg-gray-50 transition relative">
            <div class="flex-1 min-w-0 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-lg font-bold text-indigo-700 shrink-0">
                    {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ $chat->visitor->name ?? 'Visitor' }}
                        </p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium bg-yellow-100 text-yellow-800 uppercase tracking-wider">Waiting</span>
                    </div>
                    <p class="text-sm text-gray-600 truncate max-w-xl">{{ $chat->subject ?? 'General inquiry' }}</p>
                    <div class="flex items-center gap-3 mt-1.5">
                         <span class="inline-flex items-center text-xs text-gray-400">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Joined {{ $chat->created_at->diffForHumans() }}
                        </span>
                        <span class="text-gray-200 text-xs">&bull;</span>
                        <span class="text-xs text-gray-500">Visitor ID: #{{ $chat->visitor_id }}</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                 <!-- Admin can join directly too if needed -->
                <form method="POST" action="{{ route('agent.queue.join', $chat->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-blue-600 text-sm font-medium rounded-md text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Join as Admin
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-24 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-blue-50 mb-6">
                <svg class="h-10 w-10 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-gray-900">Zero Wait Time</h3>
            <p class="mt-2 text-gray-500 max-w-sm mx-auto">There are currently no visitors waiting in the queue. All conversations are either active or resolved.</p>
        </div>
        @endforelse
    </div>
</div>

@endsection
