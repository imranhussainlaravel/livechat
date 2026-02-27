@extends('layouts.app')
@section('header_title', 'Other Agents')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Team Status</h1>
        <p class="text-gray-500 mt-1">Check availability of your fellow agents.</p>
    </div>
</div>

<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-gray-100">
        @forelse($agents ?? [] as $agent)
        @if($agent->id !== auth()->id())
        <div class="p-6 flex flex-col items-center text-center hover:bg-gray-50 transition border-b sm:border-b-0 border-gray-100">
            <div class="relative mb-4">
                <div class="w-16 h-16 rounded-full bg-blue-100 flex items-center justify-center text-xl font-bold text-blue-700">
                    {{ strtoupper(substr($agent->name, 0, 1)) }}
                </div>
                {{-- Status Dot --}}
                <span class="absolute bottom-0 right-0 block h-3.5 w-3.5 rounded-full ring-2 ring-white
                            {{ $agent->status === 'online' ? 'bg-green-400' : ($agent->status === 'away' ? 'bg-yellow-400' : 'bg-gray-300') }}">
                </span>
            </div>
            <h3 class="text-sm font-medium text-gray-900">{{ $agent->name }}</h3>
            <p class="text-xs text-gray-500 mb-4">{{ ucfirst($agent->status ?? 'Offline') }}</p>

            @php
            // Optional: show current chat load if available in metric relation or appended attribute
            $activeLoad = $agent->active_chats_count ?? 0;
            @endphp
            <div class="mt-auto w-full pt-4 border-t border-gray-100 flex items-center justify-between">
                <span class="text-xs text-gray-500">Current Load</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $activeLoad > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600' }}">
                    {{ $activeLoad }} chat(s)
                </span>
            </div>
        </div>
        @endif
        @empty
        <div class="col-span-full p-12 text-center text-gray-500">
            No other agents found.
        </div>
        @endforelse
    </div>
</div>

@endsection