@extends('layouts.app')
@section('header_title', 'Chat #' . $chat->id)

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-12 gap-6 h-[calc(100vh-8rem)]">

    {{-- Chat Conversation (Main Area) --}}
    <div class="lg:col-span-8 bg-white border border-gray-100 shadow-sm rounded-lg flex flex-col overflow-hidden">

        {{-- Chat Header --}}
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between bg-gray-50">
            <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-lg font-bold text-blue-700 shrink-0">
                    {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                </div>
                <div>
                    <h2 class="text-base font-semibold text-gray-900">{{ $chat->visitor->name ?? 'Visitor' }}</h2>
                    <p class="text-sm text-gray-500">
                        @if($chat->visitor->email)
                        {{ $chat->visitor->email }} <span class="mx-1">&middot;</span>
                        @endif
                        {{ $chat->subject ?? 'General Inquiry' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                @php
                $statusColors = [
                'pending' => 'bg-yellow-100 text-yellow-800',
                'assigned' => 'bg-green-100 text-green-800',
                'active' => 'bg-blue-100 text-blue-800',
                'transferred' => 'bg-purple-100 text-purple-800',
                'closed' => 'bg-gray-100 text-gray-800',
                ];
                $statusBg = $statusColors[$chat->status->value] ?? 'bg-gray-100 text-gray-800';
                @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBg }}">
                    {{ ucfirst(str_replace('_', ' ', $chat->status->value)) }}
                </span>

                {{-- Quick Actions --}}
                @if(! in_array($chat->status->value, ['closed']))
                <form method="POST" action="{{ route('agent.chats.close', $chat->id) }}" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-red-600 bg-white hover:bg-red-50 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors">
                        Close Chat
                    </button>
                </form>
                @endif
            </div>
        </div>

        {{-- Messages Area --}}
        <div id="messages-container" class="flex-1 overflow-y-auto p-6 space-y-6 bg-white">
            @forelse($messages as $msg)
            @php
            $senderType = $msg->sender_type->value ?? $msg->sender_type;
            $isAgent = $senderType === 'agent';
            $isSystem = in_array($senderType, ['system', 'bot']);
            @endphp

            @if($isSystem)
            <div class="flex items-center justify-center my-2">
                <div class="flex items-center gap-4 w-full">
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="px-3 py-1 bg-gray-50 rounded-full text-[11px] font-medium text-gray-400 uppercase tracking-wider whitespace-nowrap">
                        {{ $msg->message }}
                    </span>
                    <div class="flex-1 h-px bg-gray-100"></div>
                </div>
            </div>
            @else
            <x-chat-message
                :isAgent="$isAgent"
                :isBot="false"
                :isMine="$isAgent && $msg->sender_id === auth()->id()"
                :senderName="$isAgent ? ($msg->sender->name ?? 'Agent') : ($chat->visitor->name ?? 'Visitor')"
                :message="$msg->message"
                :time="$msg->created_at->format('g:i A')" />
            @endif
            @empty
            <div class="h-full flex flex-col items-center justify-center text-gray-400">
                <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <p class="text-sm">No messages yet. Start the conversation!</p>
            </div>
            @endforelse
        </div>

        {{-- Typing Indicator --}}
        <div id="typing-indicator" class="px-6 py-2 text-xs text-gray-400 bg-gray-50 border-t border-gray-100 hidden">
            <span class="flex items-center gap-1.5 animate-pulse">
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full"></span>
                <span class="ml-1">Visitor is typing...</span>
            </span>
        </div>

        {{-- Message Input --}}
        @if(! in_array($chat->status->value, ['closed']))
        <form method="POST" action="{{ route('agent.chats.message', $chat->id) }}" id="message-form" class="bg-white border-t border-gray-100 p-4">
            @csrf
            <div class="flex items-end gap-3 rounded-lg border border-gray-300 bg-white px-3 py-2 shadow-sm focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500">
                <textarea name="message" id="message-input" required autocomplete="off" rows="1"
                    placeholder="Type your message here..."
                    class="block w-full resize-none border-0 bg-transparent py-1.5 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"></textarea>

                <div class="flex shrink-0">
                    <button type="submit" class="inline-flex items-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600">
                        <span class="hidden sm:inline mr-2">Send</span>
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </form>
        @else
        <div class="bg-gray-50 border-t border-gray-100 p-4 text-center">
            <p class="text-sm text-gray-500">This conversation is {{ $chat->status->value }}. You cannot send new messages.</p>
        </div>
        @endif
    </div>

    {{-- Sidebar: Visitor Info & Actions --}}
    <div class="lg:col-span-4 h-full">
        <x-chat-sidebar :chat="$chat" :agents="$agents" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('messages-container');
        if (container) container.scrollTop = container.scrollHeight;

        // Auto-resize textarea
        const textarea = document.getElementById('message-input');
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = (this.scrollHeight) + 'px';
                if (this.scrollHeight > 100) {
                    this.style.overflowY = 'auto';
                } else {
                    this.style.overflowY = 'hidden';
                }
            });

            // Enter to submit (Shift+Enter for newline)
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.getElementById('message-form').submit();
                }
            });
        }

        // --- Laravel Echo / Real-Time Events ---
        if (typeof window.Echo !== 'undefined') {
            const chatId = {
                {
                    $chat - > id
                }
            };
            const currentUserId = {
                {
                    auth() - > id()
                }
            };

            window.Echo.join(`chat-room.${chatId}`)
                .here((users) => {
                    // console.log('Currently in room:', users);
                })
                .joining((user) => {
                    if (user.id !== currentUserId && user.role === 'agent') {
                        appendSystemMessage(`Agent ${user.name} joined the chat.`);
                    }
                })
                .leaving((user) => {
                    if (user.id !== currentUserId && user.role === 'agent') {
                        appendSystemMessage(`Agent ${user.name} left the chat.`);
                    }
                })
                .listen('AnotherAgentJoined', (e) => {
                    if (e.agentId !== currentUserId) {
                        appendSystemMessage(`Agent ${e.agentName} has joined to assist.`);
                    }
                })
                .listen('QuotationSent', (e) => {
                    appendSystemMessage(`Quotation of $${e.amount} sent by ${e.agentName}.`);
                })
                .listen('FollowupScheduled', (e) => {
                    const date = new Date(e.scheduledAt).toLocaleString();
                    appendSystemMessage(`Follow-up scheduled for ${date} by ${e.agentName}.`);
                });

            function appendSystemMessage(msg) {
                const sysHtml = `
                    <div class="flex justify-center my-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                            ${msg}
                        </span>
                    </div>
                `;
                container.insertAdjacentHTML('beforeend', sysHtml);
                container.scrollTop = container.scrollHeight;
            }
        }
    });
</script>

@endsection