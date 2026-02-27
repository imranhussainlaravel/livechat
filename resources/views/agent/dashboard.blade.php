@extends('layouts.app')
@section('header_title', 'Agent Dashboard')

@section('content')

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}</h1>
    <p class="text-gray-500 mt-1">Here is the overview of your activity today.</p>
</div>

{{-- Personal Stats Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card title="Active Chats" :value="$metrics['active_chats'] ?? 0" icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>' color="blue" />
    <x-stat-card title="Pending Queue" :value="$metrics['pending_queue'] ?? 0" icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>' color="yellow" />
    <x-stat-card title="Followups Due" :value="$metrics['followups_due'] ?? 0" icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>' color="red" />
    <x-stat-card title="Conversion Rate" :value="($metrics['conversion_rate'] ?? 0) . '%'" icon='<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>' color="green" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        {{-- Recent Active Chats --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Your Active Chats</h3>
                <a href="{{ route('agent.chats.index') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">
                    View All
                </a>
            </div>

            <div class="divide-y divide-gray-100">
                @forelse($recentChats ?? [] as $chat)
                <a href="{{ route('agent.chats.show', $chat->id) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition block">
                    <div class="flex-1 min-w-0 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 shrink-0">
                            {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $chat->visitor->name ?? 'Visitor' }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $chat->subject ?? 'General inquiry' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium px-2.5 py-0.5 rounded-full
                                    {{ in_array($chat->status->value, ['active', 'assigned']) ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst(str_replace('_', ' ', $chat->status->value)) }}
                        </span>
                        <span class="text-xs text-gray-400 whitespace-nowrap">{{ $chat->updated_at->diffForHumans() }}</span>
                    </div>
                </a>
                @empty
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p class="text-sm text-gray-500">No active chats assigned to you right now.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        {{-- Quick Actions or mini queue --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Pending Queue</h3>
            </div>
            <div class="p-4">
                @if(($metrics['pending_queue'] ?? 0) > 0)
                <div class="text-center py-4">
                    <span class="text-3xl font-bold text-gray-900 block mb-2">{{ $metrics['pending_queue'] }}</span>
                    <p class="text-sm text-gray-500 mb-4">Visitors waiting for an agent</p>
                    <a href="{{ route('agent.chats.index') }}#queue" class="inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 w-full">
                        Go to Queue
                    </a>
                </div>
                @else
                <div class="px-2 py-6 text-center text-sm text-gray-500">
                    Queue is empty.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection