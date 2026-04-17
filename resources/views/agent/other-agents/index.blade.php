@extends('layouts.app')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Other Agents</h1>
    <p class="text-gray-500 mt-1">Chat with your colleagues and see their current status.</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($users as $user)
    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-5 flex items-center justify-between hover:shadow-md transition-shadow">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center text-lg font-bold text-gray-700">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 border-2 border-white rounded-full 
                    {{ $user->status === 'online' ? 'bg-emerald-500' : ($user->status === 'away' ? 'bg-amber-500' : 'bg-gray-400') }}">
                </div>
                
                @if($user->unread_count > 0)
                <div class="absolute -top-1 -right-1 w-5 h-5 bg-blue-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white ring-2 ring-blue-50">
                    {{ $user->unread_count }}
                </div>
                @endif
            </div>
            <div>
                <h3 class="font-semibold text-gray-900">{{ $user->name }}</h3>
                <p class="text-xs text-gray-500 capitalize">{{ $user->status }} • {{ $user->role->value ?? $user->role }}</p>
            </div>
        </div>
        
        <a href="{{ route('agent.agents.show', $user->id) }}" class="p-2 text-gray-400 hover:text-blue-600 transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
        </a>
    </div>
    @endforeach
</div>
@endsection
