@extends('layouts.app')
@section('header_title', 'All Chats')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-100">Conversations</h1>
        <p class="text-gray-500 mt-1">Manage and respond to visitor inquiries.</p>
    </div>
</div>

{{-- Filters --}}
@php $currentStatus = request('status', 'all'); @endphp
<div class="flex items-center gap-3 mb-5 flex-wrap">
    @foreach(['all', 'pending', 'assigned', 'active', 'closed', 'transferred'] as $filter)
    @php
        $isActive = $currentStatus === $filter;
        $colors = [
            'all'         => 'bg-slate-700 text-slate-200 border-slate-600',
            'pending'     => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
            'assigned'    => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
            'active'      => 'bg-[#6366F1]/20 text-[#6366F1] border-[#6366F1]/40',
            'closed'      => 'bg-slate-700/50 text-slate-400 border-slate-600/50',
            'transferred' => 'bg-purple-500/20 text-purple-300 border-purple-500/40',
        ];
        $activeClass  = $colors[$filter] ?? 'bg-slate-700 text-slate-200 border-slate-600';
        $defaultClass = 'bg-slate-800/50 text-slate-400 border-slate-700/50 hover:bg-slate-700/50 hover:text-slate-300';
    @endphp
    <a href="{{ route('agent.chats.index', ['status' => $filter]) }}"
       onclick="event.preventDefault(); Turbo.visit(this.href)"
       class="px-3.5 py-1.5 rounded-lg border text-xs font-semibold transition-all {{ $isActive ? $activeClass : $defaultClass }}">
        {{ ucfirst(str_replace('_', ' ', $filter)) }}
    </a>
    @endforeach
</div>

{{-- Chat List --}}
<div class="bg-gray-900 border border-gray-800 rounded-lg shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-800">
        @forelse($chats ?? [] as $chat)
        <div class="group flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-gray-800 transition relative chat-row cursor-pointer" data-chat-id="{{ $chat->id }}" onclick="Turbo.visit('{{ route('agent.chats.show', $chat->id) }}')"  >
            <div class="flex-1 min-w-0 flex items-center gap-4 relative z-10 pointer-events-none">
                <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-[#6366F1] flex items-center justify-center text-lg font-bold text-white shadow-sm shrink-0">
                        {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                    </div>
                    <!-- Unread Indicator Dot -->
                    <span class="unread-dot-{{ $chat->id }} hidden absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-500 rounded-full animate-pulse border-2 border-white shadow-sm"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-1">
                        <p class="text-sm font-semibold text-gray-100 truncate">
                            {{ $chat->visitor->name ?? 'Visitor' }}
                        </p>
                        @if($chat->visitor->email ?? false)
                        <span class="hidden sm:inline text-xs text-gray-500 font-normal truncate max-w-[200px]">— {{ $chat->visitor->email }}</span>
                        @endif
                    </div>
                    <p class="text-sm text-gray-500 truncate max-w-md">{{ $chat->subject ?? 'General inquiry' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-6 sm:w-auto mt-3 sm:mt-0 relative z-20 w-full shrink-0">
                <div class="flex items-center gap-4">
                    @php
                    $statusColors = [
                    'pending' => 'bg-yellow-900/30 text-yellow-300',
                    'assigned' => 'bg-emerald-100 text-emerald-800',
                    'active' => 'bg-blue-900/30 text-blue-300',
                    'transferred' => 'bg-purple-900/30 text-purple-300',
                    'closed' => 'bg-gray-800 text-gray-200',
                    ];
                    $statusBg = $statusColors[$chat->status->value] ?? 'bg-gray-800 text-gray-200';
                    @endphp
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold tracking-wider uppercase {{ $statusBg }}">
                        {{ ucfirst(str_replace('_', ' ', $chat->status->value)) }}
                    </span>

                    <span class="text-xs text-gray-400 whitespace-nowrap hidden sm:block font-medium">
                        {{ $chat->created_at->diffForHumans() }}
                    </span>
                </div>

                @if($chat->status->value === 'pending')
                <form method="POST" action="{{ route('agent.chats.accept', $chat->id) }}" class="shrink-0" onclick="event.stopPropagation();">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-semibold rounded-lg shadow-sm text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors">
                        Accept Chat
                    </button>
                </form>
                @endif
                <div class="hidden group-hover:flex items-center justify-center w-8 h-8 rounded-full bg-gray-800 text-gray-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-16 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
            </svg>
            <h3 class="text-base font-medium text-gray-100">No chats found</h3>
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