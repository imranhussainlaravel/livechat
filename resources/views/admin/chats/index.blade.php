@extends('layouts.app')
@section('header_title', 'Monitor — All Chats')

@section('content')

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">Monitor</h1>
        <p class="text-xs text-slate-500 mt-0.5">All conversations across all agents</p>
    </div>
    <span class="text-[11px] font-bold text-slate-400 bg-slate-800/60 border border-slate-700/50 px-3 py-1.5 rounded-full">
        {{ $chats->total() ?? 0 }} total
    </span>
</div>

{{-- Status filter pills --}}
@php
$currentStatus = request('status', 'all');
$filters = ['all', 'pending', 'assigned', 'active', 'closed', 'transferred'];
$activeClass = 'border-[#6366F1] text-[#6366F1] bg-[#6366F1]/10 rounded-t-lg';
$defaultClass = 'border-transparent text-slate-400 hover:text-slate-300 hover:bg-slate-800/50 rounded-t-lg';
@endphp
<div class="flex items-center gap-1 mb-5 border-b border-slate-700/50 flex-wrap">
    @foreach($filters as $filter)
    @php $isActive = $currentStatus === $filter; @endphp
    <a href="{{ route('admin.chats.index', ['status' => $filter]) }}"
       onclick="event.preventDefault(); Turbo.visit(this.href)"
       class="px-4 py-2.5 border-b-2 text-xs font-semibold transition-all -mb-px {{ $isActive ? $activeClass : $defaultClass }}">
        {{ ucfirst(str_replace('_', ' ', $filter)) }}
    </a>
    @endforeach
</div>

{{-- Chat list --}}
<div class="bg-slate-900/50 border border-slate-800 rounded-xl shadow-sm overflow-hidden pb-4">
    <div class="flex flex-col gap-1.5 p-2">
        @forelse($chats as $index => $chat)
        @php
            $isRowActive = $chat->status->value === 'active';
            $avatarColors = ['bg-indigo-500', 'bg-emerald-500', 'bg-amber-500', 'bg-rose-500', 'bg-cyan-500'];
            $avatarBg = $avatarColors[$index % count($avatarColors)];
        @endphp
        <div class="group flex flex-col sm:flex-row sm:items-center justify-between gap-4 px-5 py-4 rounded-lg hover:bg-slate-800/70 transition-colors relative chat-row cursor-pointer {{ $isRowActive ? 'bg-slate-800/40 border-l-2 border-l-[#6366F1]' : 'border-l-2 border-l-transparent' }}"
             data-chat-id="{{ $chat->id }}"
             onclick="Turbo.visit('{{ route('agent.chats.show', $chat->id) }}')">

            <div class="flex-1 min-w-0 flex items-center gap-4 relative z-10 pointer-events-none">
                <div class="relative">
                    <div class="w-11 h-11 rounded-full {{ $avatarBg }} flex items-center justify-center text-lg font-bold text-white shadow-sm shrink-0">
                        {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                    </div>
                    <span class="unread-dot-{{ $chat->id }} hidden absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-500 rounded-full animate-pulse border-2 border-slate-900 shadow-sm"></span>
                </div>
                <div>
                    <div class="flex items-center gap-2 mb-0.5">
                        <p class="text-sm font-semibold text-slate-100 truncate">{{ $chat->visitor->name ?? 'Visitor' }}</p>
                        <span class="text-[10px] text-slate-500 font-mono">#{{ $chat->id }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-slate-400">
                        <span class="truncate max-w-[160px]">{{ $chat->subject ?? 'General Inquiry' }}</span>
                        <span class="text-slate-600">&bull;</span>
                        <span class="{{ $chat->agent ? 'text-slate-500' : 'text-amber-500 italic' }}">
                            {{ $chat->agent ? $chat->agent->name : 'Unassigned' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between sm:justify-end gap-6 sm:w-auto relative z-20 shrink-0">
                <div class="flex items-center gap-4 text-right">
                    <span class="text-[10px] text-slate-500 whitespace-nowrap hidden sm:block">
                        {{ $chat->created_at->diffForHumans() }}
                    </span>

                    @php
                    $statusVal = $chat->status->value;
                    $statusClasses = [
                        'pending'     => 'bg-amber-500/10 text-amber-400 border border-amber-500/20',
                        'assigned'    => 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20',
                        'active'      => 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20',
                        'transferred' => 'bg-purple-500/10 text-purple-400 border border-purple-500/20',
                        'closed'      => 'bg-slate-700/50 text-slate-400 border border-slate-600',
                    ];
                    $sc = $statusClasses[$statusVal] ?? 'bg-slate-700/50 text-slate-400 border border-slate-600';
                    @endphp
                    <span class="inline-flex items-center justify-center w-24 py-1 rounded-full text-[10px] font-bold tracking-wider uppercase {{ $sc }}">
                        {{ ucfirst(str_replace('_', ' ', $statusVal)) }}
                    </span>
                </div>
                <div class="hidden group-hover:flex w-8 h-8 rounded-full bg-slate-800 items-center justify-center text-slate-400 ml-1 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-14 text-center">
            <svg class="mx-auto h-12 w-12 text-slate-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-sm font-medium text-slate-100">No conversations found.</p>
            <p class="text-sm text-slate-500 mt-1">There are no conversations matching your current filter.</p>
        </div>
        @endforelse
    </div>
</div>

@if(isset($chats) && $chats->hasPages())
<div class="mt-5 flex justify-center">{{ $chats->links() }}</div>
@endif

@endsection
