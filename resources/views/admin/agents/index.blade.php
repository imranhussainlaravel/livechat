@extends('layouts.app')
@section('header_title', 'Agent Management')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Agent Details</h1>
        <p class="text-gray-500 mt-1">Add, remove, and monitor support agents.</p>
    </div>
</div>

{{-- Create Agent Form --}}
<div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6 mb-8">
    <h3 class="text-base font-semibold text-gray-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
        </svg>
        Add New Agent
    </h3>
    <form method="POST" action="{{ route('admin.agents.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium leading-6 text-gray-900 mb-1">Full Name</label>
            <input type="text" name="name" id="name" placeholder="e.g. Jane Doe" required
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium leading-6 text-gray-900 mb-1">Email Address</label>
            <input type="email" name="email" id="email" placeholder="jane@company.com" required
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium leading-6 text-gray-900 mb-1">Password</label>
            <input type="password" name="password" id="password" placeholder="Min 8 characters" required minlength="8"
                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
        </div>
        <div>
            <button type="submit" class="w-full flex justify-center items-center gap-2 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Agent
            </button>
        </div>
    </form>
</div>

{{-- Agents List --}}
<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
        <h3 class="text-sm font-semibold text-gray-900">Active Directory</h3>
        <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">
            Total: {{ $agents->total() ?? count($agents) }}
        </span>
    </div>

    <div class="divide-y divide-gray-100">
        @forelse($agents as $agent)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-gray-50 transition">

            {{-- Avatar & Info --}}
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-sm font-bold text-blue-700 shrink-0">
                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                    </div>
                    <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white
                                {{ $agent->status === 'online' ? 'bg-green-400' : ($agent->status === 'away' ? 'bg-yellow-400' : 'bg-gray-300') }}">
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ $agent->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $agent->email }}</p>
                </div>
            </div>

            {{-- Metrics & Actions --}}
            <div class="flex items-center gap-6 shrink-0 sm:ml-4">
                <div class="hidden sm:block text-right">
                    <p class="text-xs font-medium text-gray-500">Max Chats</p>
                    <p class="text-sm font-semibold text-gray-900">{{ $agent->max_chats ?? config('livechat.default_max_chats', 5) }}</p>
                </div>

                <div class="w-24">
                    @php
                    $statusColors = [
                    'online' => 'bg-green-100 text-green-800',
                    'away' => 'bg-yellow-100 text-yellow-800',
                    'offline' => 'bg-gray-100 text-gray-800'
                    ];
                    $statusVal = $agent->status ?? 'offline';
                    $statusClass = $statusColors[$statusVal] ?? $statusColors['offline'];
                    @endphp
                    <span class="inline-flex items-center justify-center w-full px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ ucfirst($statusVal) }}
                    </span>
                </div>

                <div class="border-l border-gray-200 pl-4">
                    @if($agent->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.agents.destroy', $agent->id) }}" onsubmit="return confirm('Remove this agent? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-600 transition p-1 rounded-md hover:bg-red-50" title="Remove Agent">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                    @else
                    <span class="text-xs text-gray-400 italic px-2">You</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="text-sm text-gray-500">No agents registered in the system.</p>
        </div>
        @endforelse
    </div>
</div>

@if(isset($agents) && $agents->hasPages())
<div class="mt-6 flex justify-center">
    {{ $agents->links() }}
</div>
@endif

@endsection