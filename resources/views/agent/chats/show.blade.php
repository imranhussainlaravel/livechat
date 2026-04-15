@extends('layouts.app')
@section('header_title', 'Chat #' . $chat->id)

@section('content')

<style>
    main { overflow: hidden !important; padding: 0 !important; }
</style>

<div class="flex h-[calc(100vh-3rem)] overflow-hidden bg-gray-50">
    <div class="flex-1 flex flex-col min-w-0 border-r border-gray-100">

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
        <div class="flex-1 relative overflow-hidden" style="position:relative;">
            <div id="messages-container" class="absolute inset-0 overflow-y-auto p-4 space-y-3 bg-white" style="z-index:1;">
                @forelse($messages as $msg)
                @php
                $senderType = $msg->sender_type->value ?? $msg->sender_type;
                $isAgent = $senderType === 'agent';
                $isSystem = in_array($senderType, ['system', 'bot']);
                @endphp

                @if($isSystem)
                <div class="flex items-center justify-center my-1.5">
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
                    :isMine="$isAgent"
                    :senderName="$isAgent ? ($msg->sender->name ?? 'User') : ($chat->visitor->name ?? 'Visitor')"
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

            {{-- Scroll to Bottom Arrow (WhatsApp style) --}}
            <button id="scroll-to-bottom" type="button"
                style="display:none; position:absolute; bottom:20px; right:20px; z-index:99; width:40px; height:40px; border-radius:50%; background:#3b82f6; color:#fff; border:2px solid #fff; box-shadow:0 4px 12px rgba(0,0,0,0.25); cursor:pointer; align-items:center; justify-content:center; transition:transform 0.2s ease;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 13l5 5 5-5M7 6l5 5 5-5"/>
                </svg>
            </button>
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
        <form method="POST" action="{{ route('agent.chats.message', $chat->id) }}" id="message-form" 
              class="bg-white border-t border-gray-100 p-4 sticky bottom-0 z-10 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
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
    <div class="w-80 h-full overflow-y-auto bg-white border-l border-gray-100 shadow-sm">
        <x-chat-sidebar :chat="$chat" :agents="$agents" />
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var container = document.getElementById('messages-container');
        var scrollBtn = document.getElementById('scroll-to-bottom');
        var form = document.getElementById('message-form');
        var textarea = document.getElementById('message-input');
        var tokenEl = form ? form.querySelector('input[name="_token"]') : null;
        var csrfToken = tokenEl ? tokenEl.value : '';

        // ---- SCROLL TO BOTTOM ----
        function scrollToBottom(smooth) {
            if (!container) return;
            container.scrollTo({
                top: container.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
        }

        // ---- SHOW / HIDE ARROW ----
        function updateArrow() {
            if (!container || !scrollBtn) return;
            var distFromBottom = container.scrollHeight - container.scrollTop - container.clientHeight;
            if (distFromBottom > 80) {
                scrollBtn.style.display = 'flex';
            } else {
                scrollBtn.style.display = 'none';
            }
        }

        // ---- FORMAT TIME ----
        function formatTime(date) {
            var h = date.getHours();
            var m = date.getMinutes();
            var ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12;
            m = m < 10 ? '0' + m : m;
            return h + ':' + m + ' ' + ampm;
        }

        // ---- APPEND AGENT MESSAGE BUBBLE ----
        function appendAgentMessage(text) {
            var escaped = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            escaped = escaped.replace(/\n/g, '<br>');
            var time = formatTime(new Date());
            var html = '<div class="flex items-start mb-3 flex-row-reverse">' +
                '<div class="flex flex-col items-end max-w-[85%]">' +
                '<span class="text-[10px] text-gray-400 mb-0.5 px-1">' + time + '</span>' +
                '<div class="px-3 py-1.5 rounded-2xl shadow-sm text-[13px] leading-relaxed bg-indigo-600 text-white rounded-tr-sm">' +
                escaped + '</div></div></div>';
            container.insertAdjacentHTML('beforeend', html);
            scrollToBottom(true);
        }

        // ---- SEND MESSAGE VIA AJAX ----
        function sendMessage() {
            if (!textarea || !form) return;
            var msg = textarea.value.trim();
            if (msg.length === 0) return;

            // Instantly show the bubble and clear input
            appendAgentMessage(msg);
            textarea.value = '';
            textarea.style.height = 'auto';

            // POST via fetch (no page reload)
            var url = form.getAttribute('action');
            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ message: msg })
            }).catch(function(err) {
                console.error('Send error:', err);
            });
        }

        // ---- 1) ON PAGE OPEN: scroll to latest message ----
        scrollToBottom(false);
        setTimeout(function() { scrollToBottom(false); }, 100);
        setTimeout(function() { scrollToBottom(false); }, 300);
        setTimeout(function() { scrollToBottom(false); }, 600);
        window.addEventListener('load', function() {
            scrollToBottom(false);
            setTimeout(function() { scrollToBottom(false); }, 100);
        });

        // ---- 2) SCROLL ARROW ----
        if (container) {
            container.addEventListener('scroll', updateArrow);
        }
        if (scrollBtn) {
            scrollBtn.addEventListener('click', function(e) {
                e.preventDefault();
                scrollToBottom(true);
            });
            scrollBtn.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.1)';
            });
            scrollBtn.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
            });
        }

        // ---- 3) FORM SUBMIT & ENTER KEY (AJAX, no refresh) ----
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                sendMessage();
            });
        }
        if (textarea) {
            textarea.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 120) + 'px';
            });
            textarea.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    sendMessage();
                }
            });
        }

        // ---- Laravel Echo / Real-Time Events ----
        if (typeof window.Echo !== 'undefined') {
            var chatId = {{ $chat->id }};
            var currentUserId = {{ auth()->id() }};

            window.Echo.join('chat-room.' + chatId)
                .here(function(users) {})
                .joining(function(user) {
                    if (user.id !== currentUserId && user.role === 'agent') {
                        appendSystemMessage('Agent ' + user.name + ' joined the chat.');
                    }
                })
                .leaving(function(user) {
                    if (user.id !== currentUserId && user.role === 'agent') {
                        appendSystemMessage('Agent ' + user.name + ' left the chat.');
                    }
                })
                .listen('AnotherAgentJoined', function(e) {
                    if (e.agentId !== currentUserId) {
                        appendSystemMessage('Agent ' + e.agentName + ' has joined to assist.');
                    }
                })
                .listen('QuotationSent', function(e) {
                    appendSystemMessage('Quotation of $' + e.amount + ' sent by ' + e.agentName + '.');
                })
                .listen('MessageSent', function(e) {
                    setTimeout(function() { scrollToBottom(true); }, 100);
                })
                .listen('FollowupScheduled', function(e) {
                    var date = new Date(e.scheduledAt).toLocaleString();
                    appendSystemMessage('Follow-up scheduled for ' + date + ' by ' + e.agentName + '.');
                });

            function appendSystemMessage(msg) {
                var sysHtml = '<div class="flex items-center justify-center my-1.5">' +
                    '<div class="flex items-center gap-4 w-full">' +
                    '<div class="flex-1 h-px bg-gray-100"></div>' +
                    '<span class="px-3 py-1 bg-gray-50 rounded-full text-[11px] font-medium text-gray-400 uppercase tracking-wider whitespace-nowrap">' +
                    msg + '</span>' +
                    '<div class="flex-1 h-px bg-gray-100"></div>' +
                    '</div></div>';
                container.insertAdjacentHTML('beforeend', sysHtml);
                scrollToBottom(true);
            }
        }
    });
</script>

@endsection
