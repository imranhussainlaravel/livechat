@extends('layouts.app')
@section('header_title', 'All Chats')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">Conversations</h1>
        <p class="text-xs text-slate-500 mt-0.5">Manage and respond to visitor inquiries.</p>
    </div>
</div>

{{-- Filters --}}
@php $currentStatus = request('status', 'all'); @endphp
<div class="flex items-center gap-1 mb-5 border-b border-slate-700/50 flex-wrap">
    @foreach(['all', 'pending', 'assigned', 'active', 'closed', 'transferred'] as $filter)
    @php
        $isActive = $currentStatus === $filter;
        $activeClass = 'border-[#6366F1] text-[#6366F1] bg-[#6366F1]/10 rounded-t-lg';
        $defaultClass = 'border-transparent text-slate-400 hover:text-slate-300 hover:bg-slate-800/50 rounded-t-lg';
    @endphp
    <a href="{{ route('agent.chats.index', ['status' => $filter]) }}"
       onclick="event.preventDefault(); Turbo.visit(this.href)"
       class="px-4 py-2.5 border-b-2 text-xs font-semibold transition-all -mb-px {{ $isActive ? $activeClass : $defaultClass }}">
        {{ ucfirst(str_replace('_', ' ', $filter)) }}
    </a>
    @endforeach
</div>

{{-- Chat List --}}
<div class="bg-slate-900/50 border border-slate-800 rounded-xl shadow-sm overflow-hidden pb-4">
    <div class="flex flex-col gap-1.5 p-2">
        @forelse($chats ?? [] as $index => $chat)
        @php
            $isRowActive = $chat->status->value === 'active';
            $avatarColors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500'];
            $avatarBg = $avatarColors[$index % count($avatarColors)];
        @endphp
        <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-5 py-4 rounded-lg hover:bg-slate-800/70 transition-colors relative chat-row cursor-pointer {{ $isRowActive ? 'bg-slate-800/40 border-l-2 border-l-[#6366F1]' : 'border-l-2 border-l-transparent' }}" data-chat-id="{{ $chat->id }}" onclick="Turbo.visit('{{ route('agent.chats.show', $chat->id) }}')"  >
            <div class="flex-1 min-w-0 flex items-center gap-4 relative z-10 pointer-events-none">
                <div class="relative">
                    <div class="w-11 h-11 rounded-full {{ $avatarBg }} flex items-center justify-center text-lg font-bold text-white shadow-sm shrink-0">
                        {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                    </div>
                    <!-- Unread Indicator Dot -->
                    <span class="unread-dot-{{ $chat->id }} hidden absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-500 rounded-full animate-pulse border-2 border-slate-900 shadow-sm"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <p class="text-sm font-semibold text-slate-100 truncate">
                            {{ $chat->visitor->name ?? 'Visitor' }}
                        </p>
                        @if($chat->visitor->email ?? false)
                        <span class="hidden sm:inline text-xs text-slate-500 font-normal truncate max-w-[200px]">— {{ $chat->visitor->email }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-400 truncate max-w-md">{{ $chat->subject ?? 'General inquiry' }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-6 sm:w-auto relative z-20 shrink-0">
                <div class="flex items-center gap-4 text-right">
                    <span class="text-[10px] text-slate-500 whitespace-nowrap hidden sm:block">
                        {{ $chat->created_at->diffForHumans() }}
                    </span>

                    @php
                    $statusColors = [
                    'pending' => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
                    'assigned' => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                    'active' => 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20',
                    'transferred' => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
                    'closed' => 'bg-slate-700/50 text-slate-400 border border-slate-600',
                    ];
                    $statusBg = $statusColors[$chat->status->value] ?? 'bg-slate-700/50 text-slate-400 border border-slate-600';
                    @endphp
                    <span class="inline-flex items-center justify-center w-24 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase {{ $statusBg }}">
                        {{ ucfirst(str_replace('_', ' ', $chat->status->value)) }}
                    </span>
                </div>

                @if($chat->status->value === 'pending')
                <form method="POST" action="{{ route('agent.chats.accept', $chat->id) }}" class="shrink-0 ml-2" onclick="event.stopPropagation();">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-xs font-semibold rounded-lg shadow-sm text-white bg-emerald-600 hover:bg-emerald-500 transition-colors">
                        Accept Chat
                    </button>
                </form>
                @endif
                <div class="hidden group-hover:flex items-center justify-center w-8 h-8 rounded-full bg-slate-800 text-slate-400 transition-colors">
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