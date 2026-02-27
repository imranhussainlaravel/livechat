@extends('layouts.app')
@section('header_title', 'Admin Dashboard')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">System Overview</h1>
        <p class="text-gray-500 mt-1">Real-time metrics and agent load tracking.</p>
    </div>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
    <x-stat-card title="Active Chats" :value="$stats['active_chats'] ?? 0" icon="chat-alt" color="blue" />
    <x-stat-card title="Pending Queue" :value="$stats['pending_queue'] ?? 0" icon="clock" color="yellow" />
    <x-stat-card title="Agents Online" :value="$stats['agents_online'] ?? 0" icon="user-group" color="green" />
    <x-stat-card title="Total Today" :value="$stats['total_today'] ?? 0" icon="chart-bar" color="indigo" />
    <x-stat-card title="Solved Today" :value="$stats['solved_today'] ?? 0" icon="check-circle" color="teal" />
    <x-stat-card title="Closed Today" :value="$stats['closed_today'] ?? 0" icon="lock-closed" color="gray" />
</div>

{{-- Agent Load --}}
<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
        <h3 class="text-sm font-semibold text-gray-900">Agent Load</h3>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($agents as $agent)
        <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition">
            <div class="relative">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700">
                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                </div>
                <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white
                            {{ $agent->status === 'online' ? 'bg-green-400' : ($agent->status === 'away' ? 'bg-yellow-400' : 'bg-gray-300') }}">
                </span>
            </div>

            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ $agent->name }}</p>
                <p class="text-xs text-gray-500 truncate">{{ $agent->email }}</p>
            </div>

            <div class="flex items-center gap-4 shrink-0">
                @php
                $statusColors = [
                'online' => 'bg-green-100 text-green-800',
                'away' => 'bg-yellow-100 text-yellow-800',
                'offline' => 'bg-gray-100 text-gray-800'
                ];
                $statusVal = $agent->status ?? 'offline';
                $statusClass = $statusColors[$statusVal] ?? $statusColors['offline'];
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }} w-20 justify-center">
                    {{ ucfirst($statusVal) }}
                </span>

                <div class="w-24 text-right">
                    <span class="text-sm text-gray-900 font-medium">{{ $agent->assigned_chats_count ?? 0 }}</span>
                    <span class="text-xs text-gray-500">/ {{ $agent->max_chats ?? config('livechat.default_max_chats', 5) }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-8 text-center text-sm text-gray-500">
            No agents found.
        </div>
        @endforelse
    </div>
</div>

@endsection