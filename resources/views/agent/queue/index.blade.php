@extends('layouts.app')
@section('header_title', 'Pending Queue')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Incoming Requests</h1>
        <p class="text-gray-500 mt-1">New visitors waiting for help. Join a conversation to start chatting.</p>
    </div>
</div>

<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-100">
        @forelse($chats as $chat)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-5 hover:bg-gray-50 transition relative">
            <div class="flex-1 min-w-0 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg font-bold text-blue-700 shrink-0">
                    {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ $chat->visitor->name ?? 'Visitor' }}
                        </p>
                        <span class="text-xs text-gray-500 font-normal">#{{ $chat->id }}</span>
                    </div>
                    <p class="text-sm text-gray-600 truncate max-w-xl">{{ $chat->subject ?? 'General inquiry' }}</p>
                    <div class="flex items-center gap-3 mt-1.5">
                         <span class="inline-flex items-center text-xs text-gray-400">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Waiting for {{ $chat->created_at->diffForHumans(null, true) }}
                        </span>
                        @if($chat->visitor->email)
                        <span class="inline-flex items-center text-xs text-blue-500">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            {{ $chat->visitor->email }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
                <form method="POST" action="{{ route('agent.queue.join', $chat->id) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all transform active:scale-95">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                        </svg>
                        Join Conversation
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-20 text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 mb-4 animate-pulse">
                <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900">Empty Queue</h3>
            <p class="mt-2 text-sm text-gray-500 max-w-xs mx-auto">Sit tight! New chat requests will appear here as soon as they come in.</p>
        </div>
        @endforelse
    </div>
</div>

@endsection
