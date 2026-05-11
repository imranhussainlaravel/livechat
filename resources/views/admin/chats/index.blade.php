@extends('layouts.app')
@section('header_title', 'Monitor — All Chats')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div>
        <h1 class="text-xl font-bold text-white tracking-tight">Monitor</h1>
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
$colors = [
    'all'         => 'bg-slate-700 text-slate-200 border-slate-600',
    'pending'     => 'bg-amber-500/20 text-amber-300 border-amber-500/40',
    'assigned'    => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
    'active'      => 'bg-[#6366F1]/20 text-[#6366F1] border-[#6366F1]/40',
    'closed'      => 'bg-slate-700/50 text-slate-400 border-slate-600/50',
    'transferred' => 'bg-purple-500/20 text-purple-300 border-purple-500/40',
];
$defaultClass = 'bg-slate-800/50 text-slate-400 border-slate-700/50 hover:bg-slate-700/50 hover:text-slate-300';
@endphp
<div class="flex items-center gap-2 mb-5 flex-wrap">
    @foreach($filters as $filter)
    <a href="{{ route('admin.chats.index', ['status' => $filter]) }}"
       onclick="event.preventDefault(); Turbo.visit(this.href)"
       class="px-3.5 py-1.5 rounded-lg border text-xs font-semibold transition-all {{ $currentStatus === $filter ? ($colors[$filter] ?? $defaultClass) : $defaultClass }}">
        {{ ucfirst(str_replace('_', ' ', $filter)) }}
    </a>
    @endforeach
</div>

{{-- Chat list --}}
<div class="bg-gray-900 border border-gray-800 rounded-xl overflow-hidden">
    <div class="divide-y divide-gray-800">
        @forelse($chats as $chat)
        <div class="group flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-gray-800 transition cursor-pointer chat-row"
             data-chat-id="{{ $chat->id }}"
             onclick="Turbo.visit('{{ route('agent.chats.show', $chat->id) }}')">

            <div class="flex items-center gap-4 flex-1 min-w-0 pointer-events-none">
                <div class="relative flex-shrink-0">
                    <div class="w-10 h-10 rounded-full bg-[#6366F1] flex items-center justify-center text-sm font-bold text-white shadow-sm">
                        {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                    </div>
                    <span class="unread-dot-{{ $chat->id }} hidden absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full border-2 border-gray-900"></span>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <p class="text-sm font-semibold text-gray-100 truncate">{{ $chat->visitor->name ?? 'Visitor' }}</p>
                        <span class="text-[10px] text-gray-500 font-mono">#{{ $chat->id }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="truncate max-w-[160px]">{{ $chat->subject ?? 'General Inquiry' }}</span>
                        <span>&bull;</span>
                        <span class="{{ $chat->agent ? 'text-gray-400' : 'text-amber-500 italic' }}">
                            {{ $chat->agent ? $chat->agent->name : 'Unassigned' }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 shrink-0 sm:ml-4 z-20 relative w-full sm:w-auto mt-2 sm:mt-0 justify-between sm:justify-end">
                @php
                $statusVal = $chat->status->value;
                $statusClasses = [
                    'pending'     => 'bg-amber-500/20 text-amber-300',
                    'assigned'    => 'bg-emerald-500/20 text-emerald-300',
                    'active'      => 'bg-[#6366F1]/20 text-[#6366F1]',
                    'transferred' => 'bg-purple-500/20 text-purple-300',
                    'closed'      => 'bg-gray-700/50 text-gray-400',
                ];
                $sc = $statusClasses[$statusVal] ?? 'bg-gray-700/50 text-gray-400';
                @endphp
                <div class="flex flex-col items-start sm:items-end gap-1">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $sc }}">
                        {{ ucfirst(str_replace('_', ' ', $statusVal)) }}
                    </span>
                    <span class="text-[11px] text-gray-500">{{ $chat->created_at->diffForHumans() }}</span>
                </div>
                <div class="hidden group-hover:flex w-7 h-7 rounded-full bg-gray-800 items-center justify-center text-gray-500 ml-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-14 text-center">
            <svg class="mx-auto h-10 w-10 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <p class="text-sm text-gray-500">No conversations found.</p>
        </div>
        @endforelse
    </div>
</div>

@if(isset($chats) && $chats->hasPages())
<div class="mt-5 flex justify-center">{{ $chats->links() }}</div>
@endif

@endsection
