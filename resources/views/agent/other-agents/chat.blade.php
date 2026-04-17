@extends('layouts.app')

@section('content')
<div class="flex flex-col h-[calc(100vh-8rem)] bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    {{-- Header --}}
    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
        <div class="flex items-center gap-4">
            <a href="{{ route('agent.agents.index') }}" class="p-2 -ml-2 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <div class="relative">
                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-lg font-bold text-gray-600">
                    {{ strtoupper(substr($otherUser->name, 0, 1)) }}
                </div>
                <div class="absolute -bottom-0.5 -right-0.5 w-3 h-3 border-2 border-white rounded-full 
                    {{ $otherUser->status === 'online' ? 'bg-emerald-500' : ($otherUser->status === 'away' ? 'bg-amber-500' : 'bg-gray-400') }}">
                </div>
            </div>
            <div>
                <h2 class="text-sm font-semibold text-gray-900">{{ $otherUser->name }}</h2>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">{{ $otherUser->status }}</p>
            </div>
        </div>
    </div>

    {{-- Messages Area --}}
    <div id="internal-chat-container" class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50/50">
        @foreach($messages as $msg)
            <div class="flex {{ $msg->sender_id === auth()->id() ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-[70%] rounded-2xl px-4 py-2.5 text-sm shadow-sm 
                    {{ $msg->sender_id === auth()->id() ? 'bg-blue-600 text-white rounded-tr-none' : 'bg-white text-gray-900 border border-gray-100 rounded-tl-none' }}">
                    <p class="leading-relaxed">{{ $msg->message }}</p>
                    <div class="mt-1 text-[10px] {{ $msg->sender_id === auth()->id() ? 'text-blue-100' : 'text-gray-400' }}">
                        {{ $msg->created_at->format('H:i') }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Input Area --}}
    <div class="p-4 border-t border-gray-100 bg-white">
        <form action="{{ route('agent.agents.message', $otherUser->id) }}" method="POST" data-ajax-form id="internal-chat-form">
            @csrf
            <div class="flex items-center gap-2">
                <input type="text" name="message" required placeholder="Type a message..." autocomplete="off"
                    class="flex-1 rounded-full border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm py-2 px-4 shadow-sm bg-gray-50">
                <button type="submit" class="bg-blue-600 text-white p-2 rounded-full hover:bg-blue-700 transition shadow-sm transform active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('internal-chat-container');
        if (container) container.scrollTop = container.scrollHeight;

        // Custom AJAX handling for immediate message display
        const form = document.getElementById('internal-chat-form');
        const originalHandler = document.querySelector('[data-ajax-form]').onsubmit;
        
        // The global AJAX handler will handle the submission, but we want to clear input
        form.addEventListener('submit', function(e) {
            const input = form.querySelector('input[name="message"]');
            const message = input.value;
            
            // Wait for success and append locally (simplistic for now)
            setTimeout(() => {
                if (input.value === message) { // If it wasn't cleared by something else
                    input.value = '';
                    window.location.reload(); // Refresh to see new message for now, can be optimized later
                }
            }, 500);
        });
    });
</script>
@endpush
@endsection
