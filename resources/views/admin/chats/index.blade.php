@extends('layouts.app')
@section('header_title', 'All Chats')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-100">System Activity</h1>
        <p class="text-gray-500 mt-1">Monitor all active and historical chat conversations.</p>
    </div>
</div>

<div class="bg-gray-900 p-4 rounded-lg shadow-sm border border-gray-800 mb-6 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <label for="status-filter" class="text-sm font-medium text-gray-300">Filter by status:</label>
        @php 
        $currentStatus = request('status', 'all'); 
        $filters = ['all', 'pending', 'assigned', 'active', 'closed', 'transferred'];
        @endphp
        <select id="status-filter" 
            onchange="window.location.href = this.value"
            class="block w-48 rounded-md border-gray-600 py-2 pl-3 pr-10 text-base focus:border-blue-500 focus:outline-none focus:ring-blue-500 sm:text-sm shadow-sm">
            @foreach($filters as $filter)
                <option value="{{ route('admin.chats.index', ['status' => $filter]) }}" {{ $currentStatus === $filter ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $filter)) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

<div class="bg-gray-900 border border-gray-800 rounded-lg shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-800">
        @forelse($chats as $chat)
        <div class="group flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-gray-800 transition relative chat-row cursor-pointer" data-chat-id="{{ $chat->id }}" onclick="window.location.href='{{ route('agent.chats.show', $chat->id) }}'">
            
            {{-- Avatar & Info --}}
            <div class="flex items-center gap-4 flex-1 min-w-0 pointer-events-none z-10 relative">
                <div class="relative">
                    <div class="w-12 h-12 rounded-full bg-[#F0644B] flex items-center justify-center text-lg font-bold text-white shadow-sm shrink-0">
                        {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                    </div>
                    <!-- Unread Indicator Dot -->
                    <span class="unread-dot-{{ $chat->id }} hidden absolute -top-1 -right-1 w-3.5 h-3.5 bg-red-500 rounded-full animate-pulse border-2 border-white shadow-sm"></span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-100 truncate flex items-center gap-2">
                        {{ $chat->visitor->name ?? 'Visitor' }}
                        <span class="text-xs font-normal text-gray-500">ID: #{{ $chat->id }}</span>
                    </p>
                    <div class="flex items-center gap-1.5 text-xs text-gray-400 mt-0.5 truncate">
                        <span class="font-medium truncate max-w-[150px]">{{ $chat->subject ?? 'General Inquiry' }}</span>
                        <span class="text-gray-300">&bull;</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Agent: <span class="{{ $chat->agent ? 'text-gray-200' : 'text-yellow-600 italic' }}">{{ $chat->agent->name ?? 'Unassigned' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Status & Time --}}
            <div class="flex items-center gap-4 shrink-0 sm:ml-4 z-20 relative w-full sm:w-auto mt-3 sm:mt-0 justify-between sm:justify-end">
                @php
                $colors = [
                'pending' => 'bg-yellow-900/30 text-yellow-300',
                'assigned' => 'bg-emerald-100 text-emerald-800',
                'active' => 'bg-blue-900/30 text-blue-300',
                'transferred' => 'bg-purple-900/30 text-purple-300',
                'closed' => 'bg-gray-800 text-gray-200',
                ];
                $statusVal = $chat->status->value;
                $statusClass = $colors[$statusVal] ?? 'bg-gray-800 text-gray-200';
                @endphp

                <div class="flex flex-col items-start sm:items-end gap-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] tracking-wider uppercase font-bold {{ $statusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $statusVal)) }}
                    </span>
                    <span class="text-xs text-gray-400 font-medium flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $chat->created_at->diffForHumans() }}
                    </span>
                </div>

                <div class="hidden group-hover:flex items-center justify-center w-8 h-8 rounded-full bg-gray-800 text-gray-500 transition-colors ml-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <p class="text-sm text-gray-500">No chats found in this category.</p>
        </div>
        @endforelse
    </div>
</div>

@if(isset($chats) && $chats->hasPages())
<div class="mt-6 flex justify-center">
    {{ $chats->links() }}
</div>
@endif

@endsection