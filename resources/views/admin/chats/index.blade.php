@extends('layouts.app')
@section('header_title', 'All Chats')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">System Activity</h1>
        <p class="text-gray-500 mt-1">Monitor all active and historical chat conversations.</p>
    </div>
</div>

{{-- Status Filters --}}
<div class="flex flex-wrap items-center gap-2 mb-6 bg-white p-2 rounded-lg border border-gray-100 shadow-sm inline-flex">
    @php
    $currentStatus = request('status', 'pending');
    $filters = ['pending', 'assigned', 'active', 'closed', 'transferred'];
    @endphp

    @foreach($filters as $filter)
    @php
    $isActive = $currentStatus === $filter;
    $activeClass = 'bg-blue-50 text-blue-700 font-medium border-blue-200';
    $inactiveClass = 'text-gray-600 hover:text-gray-900 hover:bg-gray-50 border-transparent';
    @endphp
    <a href="{{ route('admin.chats.index', ['status' => $filter]) }}"
        class="px-4 py-2 rounded-md text-sm transition border {{ $isActive ? $activeClass : $inactiveClass }}">
        {{ ucfirst(str_replace('_', ' ', $filter)) }}
    </a>
    @endforeach
</div>

<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="divide-y divide-gray-100">
        @forelse($chats as $chat)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-gray-50 transition">

            {{-- Avatar & Info --}}
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 shrink-0">
                    {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate flex items-center gap-2">
                        {{ $chat->visitor->name ?? 'Visitor' }}
                        <span class="text-xs font-normal text-gray-500">ID: #{{ $chat->id }}</span>
                    </p>
                    <div class="flex items-center gap-1.5 text-xs text-gray-600 mt-0.5 truncate">
                        <span class="font-medium truncate max-w-[150px]">{{ $chat->subject ?? 'General Inquiry' }}</span>
                        <span class="text-gray-300">&bull;</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Agent: <span class="{{ $chat->agent ? 'text-gray-800' : 'text-yellow-600 italic' }}">{{ $chat->agent->name ?? 'Unassigned' }}</span>
                        </span>
                    </div>
                </div>
            </div>

            {{-- Status & Time --}}
            <div class="flex items-center gap-4 shrink-0 sm:ml-4">
                @php
                $colors = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'assigned' => 'bg-green-100 text-green-800',
                'active' => 'bg-blue-100 text-blue-800',
                'transferred' => 'bg-purple-100 text-purple-800',
                'closed' => 'bg-gray-100 text-gray-800',
                ];
                $statusVal = $chat->status->value;
                $statusClass = $colors[$statusVal] ?? 'bg-gray-100 text-gray-800';
                @endphp

                <div class="flex flex-col items-end gap-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ ucfirst(str_replace('_', ' ', $statusVal)) }}
                    </span>
                    <span class="text-xs text-gray-400 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $chat->created_at->diffForHumans() }}
                    </span>
                </div>

                <a href="{{ route('agent.chats.show', $chat->id) }}" class="p-2 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-full transition" title="View Chat">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
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