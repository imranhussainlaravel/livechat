@extends('layouts.app')
@section('header_title', 'All Chats')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Conversations</h1>
        <p class="text-gray-500 mt-1">Manage and respond to visitor inquiries.</p>
    </div>
</div>

{{-- Filters --}}
<div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 mb-6 w-full overflow-x-auto">
    <div class="flex items-center gap-2 min-w-max">
        @php $currentStatus = request('status', 'all'); @endphp
        @foreach(['all', 'pending', 'open', 'in_progress', 'solved', 'closed'] as $filter)
        <a href="{{ route('agent.chats.index', ['status' => $filter === 'all' ? null : $filter]) }}"
            class="px-4 py-2 rounded-md text-sm font-medium transition-colors
                  {{ $currentStatus === $filter
                      ? 'bg-blue-50 text-blue-700 bg-opacity-100'
                      : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            {{ ucfirst(str_replace('_', ' ', $filter)) }}
        </a>
        @endforeach
    </div>
</div>

{{-- Chat List --}}
<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-100">
        @forelse($chats ?? [] as $chat)
        <div class="group flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-gray-50 transition relative">
            <a href="{{ route('agent.chats.show', $chat->id) }}" class="absolute inset-0 z-0"></a>
            <div class="flex-1 min-w-0 flex items-center gap-4 relative z-10 pointer-events-none">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-lg font-bold text-blue-700 shrink-0">
                    {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-sm font-semibold text-gray-900 truncate">
                            {{ $chat->visitor->name ?? 'Visitor' }}
                        </p>
                        @if($chat->visitor->email ?? false)
                        <span class="hidden sm:inline text-xs text-gray-500 font-normal truncate max-w-[200px]">— {{ $chat->visitor->email }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 truncate max-w-md">{{ $chat->subject ?? 'General inquiry' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-6 sm:w-auto mt-3 sm:mt-0 relative z-10 w-full shrink-0">
                <div class="flex items-center gap-4">
                    @php
                    $statusColors = [
                    'pending' => 'bg-yellow-100 text-yellow-800',
                    'open' => 'bg-green-100 text-green-800',
                    'in_progress' => 'bg-blue-100 text-blue-800',
                    'solved' => 'bg-teal-100 text-teal-800',
                    'closed' => 'bg-gray-100 text-gray-800',
                    'followup' => 'bg-purple-100 text-purple-800',
                    ];
                    $statusBg = $statusColors[$chat->status->value] ?? 'bg-gray-100 text-gray-800';
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBg }}">
                        {{ ucfirst(str_replace('_', ' ', $chat->status->value)) }}
                    </span>

                    <span class="text-xs text-gray-400 whitespace-nowrap hidden sm:block">
                        {{ $chat->created_at->diffForHumans() }}
                    </span>
                </div>

                @if($chat->status->value === 'pending')
                <form method="POST" action="{{ route('agent.chats.accept', $chat->id) }}" class="shrink-0 relative z-20">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        Accept Chat
                    </button>
                </form>
                @else
                <a href="{{ route('agent.chats.show', $chat->id) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 relative z-20">
                    View
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
            </svg>
            <h3 class="text-base font-medium text-gray-900">No chats found</h3>
            <p class="mt-1 text-sm text-gray-500">There are no conversations matching your current filter.</p>
        </div>
        @endforelse
    </div>
</div>

{{-- Pagination --}}
@if(isset($chats) && $chats->hasPages())
<div class="mt-6 flex justify-center">
    {{ $chats->links() }}
</div>
@endif

@endsection