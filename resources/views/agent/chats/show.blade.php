@extends('layouts.app')
@section('header_title', 'Chat #' . $chat->id)

@push('head')
<meta name="turbo-cache-control" content="no-cache">
@endpush

@section('content')

<style>
    main { overflow: hidden !important; padding: 0 !important; }
</style>

<div class="flex h-[calc(100vh-3rem)] overflow-hidden bg-gray-800">
    <div class="flex-1 flex flex-col min-w-0 border-r border-gray-800">

        {{-- Chat Header --}}
        <div class="px-3 sm:px-6 py-3 sm:py-4 border-b border-gray-800 flex items-center justify-between gap-2 bg-gray-800">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <a href="{{ route('agent.chats.index') }}" class="p-2 -ml-2 text-gray-400 hover:text-gray-400 transition-colors shrink-0" title="Back to Chats">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <div id="visitor-header-initial" class="w-9 h-9 sm:w-10 sm:h-10 rounded-full bg-[#6366F1]/30 flex items-center justify-center text-base sm:text-lg font-bold text-[#6366F1] shrink-0">
                    {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <h2 id="visitor-header-name" class="text-sm sm:text-base font-semibold text-gray-100 truncate">{{ $chat->visitor->name ?? 'Visitor' }}</h2>
                    <p class="text-xs sm:text-sm text-gray-500 truncate">
                        <span id="visitor-header-email">@if($chat->visitor->email){{ $chat->visitor->email }}<span class="mx-1">&middot;</span>@endif</span>
                        {{ $chat->subject ?? 'General Inquiry' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                @php
                $statusColors = [
                'pending' => 'bg-yellow-900/30 text-yellow-300',
                'assigned' => 'bg-green-900/30 text-green-300',
                'active' => 'bg-blue-900/30 text-blue-300',
                'transferred' => 'bg-purple-900/30 text-purple-300',
                'closed' => 'bg-gray-800 text-gray-200',
                ];
                $statusBg = $statusColors[$chat->status->value] ?? 'bg-gray-800 text-gray-200';
                @endphp
                <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusBg }}">
                    {{ ucfirst(str_replace('_', ' ', $chat->status->value)) }}
                </span>

                {{-- Quick Actions --}}
                @if(! in_array($chat->status->value, ['closed']))
                <form method="POST" action="{{ route('agent.chats.close', $chat->id) }}" class="inline" data-ajax-form>
                    @csrf
                    <button type="submit" class="inline-flex items-center px-2 sm:px-3 py-1.5 border border-gray-600 text-xs font-medium rounded-md text-red-600 bg-gray-900 hover:bg-red-50 hover:border-red-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors whitespace-nowrap">
                        Close Chat
                    </button>
                </form>
                @endif

                {{-- Visitor info drawer toggle (mobile/tablet only) --}}
                <button id="visitor-info-toggle" type="button" aria-label="Visitor info" class="lg:hidden p-2 rounded-md text-gray-400 hover:text-gray-200 hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Linked previous conversation (visitor messaged again after being resolved) --}}
        @if($chat->previousChat)
        <a href="{{ route('agent.chats.show', $chat->previousChat->id) }}"
           class="flex items-center gap-2 px-3 sm:px-6 py-2 bg-indigo-950/40 border-b border-indigo-800/50 text-xs text-indigo-300 hover:bg-indigo-950/60 transition-colors no-underline">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            This visitor's previous conversation was resolved — view chat #{{ $chat->previousChat->id }} for history
        </a>
        @endif

        {{-- Messages Area --}}
        <div class="flex-1 relative overflow-hidden" style="position:relative;">
            <div id="messages-container" class="absolute inset-0 overflow-y-auto overflow-x-hidden p-4 space-y-3 bg-gray-900" style="z-index:1;">
                @forelse($messages as $msg)
                @php
                $senderType = $msg->sender_type->value ?? $msg->sender_type;
                $isAgent = $senderType === 'agent';
                $isBot = $senderType === 'bot';
                $isSystem = $senderType === 'system';
                @endphp

                @if($isSystem)
                <div class="flex items-center justify-center my-1.5">
                    <div class="flex items-center gap-4 w-full">
                        <div class="flex-1 h-px bg-gray-800"></div>
                        <span class="px-3 py-1 bg-gray-800 rounded-full text-[11px] font-medium text-gray-400 uppercase tracking-wider whitespace-nowrap">
                            {{ $msg->message }}
                        </span>
                        <div class="flex-1 h-px bg-gray-800"></div>
                    </div>
                </div>
                @else
                <x-chat-message
                    :isAgent="$isAgent"
                    :isBot="$isBot"
                    :isMine="$isAgent"
                    :senderName="$isAgent ? ($msg->sender->name ?? 'User') : ($isBot ? \App\Models\Setting::getValue('ai_bot_name', 'Assistant') : ($chat->visitor->name ?? 'Visitor'))"
                    :message="$msg->message"
                    :time="$msg->created_at->format('g:i A')"
                    :createdAtIso="$msg->created_at->toIso8601String()" />
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

            {{-- Scroll to Bottom Arrow --}}
            <button id="scroll-to-bottom" type="button"
                style="display:none; position:absolute; bottom:20px; right:20px; z-index:99; width:40px; height:40px; border-radius:50%; background:#3b82f6; color:#fff; border:2px solid #fff; box-shadow:0 4px 12px rgba(0,0,0,0.25); cursor:pointer; align-items:center; justify-content:center; transition:transform 0.2s ease;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M7 13l5 5 5-5M7 6l5 5 5-5"/>
                </svg>
            </button>
        </div>

        {{-- Typing Indicator --}}
        <div id="typing-indicator" class="px-6 py-2 text-xs text-gray-400 bg-gray-800 border-t border-gray-800 hidden">
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
              class="bg-gray-900 border-t border-gray-800 p-4 sticky bottom-0 z-10">
            @csrf
            <div class="flex items-end gap-3 rounded-lg border border-gray-600 bg-gray-900 px-3 py-2 shadow-sm focus-within:ring-1 focus-within:ring-blue-500 focus-within:border-blue-500">
                <textarea name="message" id="message-input" required autocomplete="off" rows="1"
                    placeholder="Type your message here..."
                    class="block w-full resize-none border-0 bg-transparent py-1.5 text-gray-100 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"></textarea>

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
        <div class="bg-gray-800 border-t border-gray-800 p-4 text-center">
            <p class="text-sm text-gray-500">This conversation is {{ $chat->status->value }}. You cannot send new messages.</p>
        </div>
        @endif
    </div>

    {{-- Mobile backdrop for visitor info drawer --}}
    <div id="visitor-info-backdrop" class="fixed inset-0 bg-black/60 z-40 hidden lg:hidden"></div>

    {{-- Sidebar: Visitor Info & Actions --}}
    <div id="visitor-info-panel" class="fixed inset-y-0 right-0 translate-x-full transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 w-80 max-w-[85vw] h-full overflow-y-auto bg-gray-900 border-l border-gray-800 shadow-sm z-50 lg:z-auto">
        <x-chat-sidebar :chat="$chat" :agents="$agents" :previousAgentId="$previousAgentId ?? null" :timeline="$timeline ?? []" />
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

        // ---- VISITOR INFO DRAWER (mobile/tablet) ----
        var infoToggle   = document.getElementById('visitor-info-toggle');
        var infoPanel    = document.getElementById('visitor-info-panel');
        var infoBackdrop = document.getElementById('visitor-info-backdrop');
        function closeVisitorInfo() {
            if (infoPanel) infoPanel.classList.add('translate-x-full');
            if (infoBackdrop) infoBackdrop.classList.add('hidden');
        }
        function openVisitorInfo() {
            if (window.closeOtherDrawers) window.closeOtherDrawers('visitor-info');
            if (infoPanel) infoPanel.classList.remove('translate-x-full');
            if (infoBackdrop) infoBackdrop.classList.remove('hidden');
        }
        if (window.registerDrawer) window.registerDrawer('visitor-info', closeVisitorInfo);
        if (infoToggle) {
            infoToggle.addEventListener('click', function() {
                if (infoPanel && infoPanel.classList.contains('translate-x-full')) openVisitorInfo();
                else closeVisitorInfo();
            });
        }
        if (infoBackdrop) infoBackdrop.addEventListener('click', closeVisitorInfo);

        // ---- CLEAR UNREAD STATUS ----
        var chatId = {{ $chat->id }};
        // Remove from localStorage
        var storedUnreads = JSON.parse(localStorage.getItem('unreadChats') || '[]');
        var index = storedUnreads.indexOf(chatId);
        if (index !== -1) {
            storedUnreads.splice(index, 1);
            localStorage.setItem('unreadChats', JSON.stringify(storedUnreads));
        }
        // Also sync the in-memory array used by updateUnreadUI
        if (window.unreadChats) {
            var memIdx = window.unreadChats.indexOf(chatId);
            if (memIdx !== -1) window.unreadChats.splice(memIdx, 1);
        }
        // Refresh sidebar badges & dots
        if (typeof updateUnreadUI === 'function') updateUnreadUI();

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
            var nowIso = new Date().toISOString();
            var time = formatTime(new Date());
            var html = '<div class="flex items-start mb-3 flex-row-reverse" data-agent-msg="1" data-created-at="' + nowIso + '">' +
                '<div class="flex flex-col items-end max-w-[85%]">' +
                '<span class="text-[10px] text-gray-400 mb-0.5 px-1">' + time + '</span>' +
                '<div class="px-3 py-1.5 rounded-2xl shadow-sm text-[13px] leading-relaxed break-words bg-[#6366F1] text-white rounded-tr-sm">' +
                escaped + '</div>' +
                '<span class="seen-label hidden text-[10px] text-gray-500 mt-0.5 px-1">Seen</span>' +
                '</div></div>';
            container.insertAdjacentHTML('beforeend', html);
            scrollToBottom(true);
        }

        // ---- SEEN INDICATOR ----
        // Shows "Seen" only under the most recent agent bubble the visitor has read up to.
        function applySeenUpTo(seenAtIso) {
            if (!seenAtIso || !container) return;
            var seenAt = new Date(seenAtIso).getTime();
            var bubbles = container.querySelectorAll('[data-agent-msg="1"]');
            var lastSeenBubble = null;
            bubbles.forEach(function(bubble) {
                var label = bubble.querySelector('.seen-label');
                if (!label) return;
                var createdAt = new Date(bubble.getAttribute('data-created-at')).getTime();
                if (createdAt && createdAt <= seenAt) {
                    lastSeenBubble = bubble;
                }
                label.classList.add('hidden');
            });
            if (lastSeenBubble) {
                var label = lastSeenBubble.querySelector('.seen-label');
                if (label) label.classList.remove('hidden');
            }
        }
        @if($chat->visitor_last_read_at)
        applySeenUpTo(@json($chat->visitor_last_read_at->toIso8601String()));
        @endif

        // ---- APPEND VISITOR MESSAGE BUBBLE ----
        function appendVisitorMessage(text, createdAt) {
            var escaped = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            escaped = escaped.replace(/\n/g, '<br>');
            var time = createdAt ? formatTime(new Date(createdAt)) : formatTime(new Date());
            var html = '<div class="flex items-start mb-3">' +
                '<div class="flex flex-col items-start max-w-[85%]">' +
                '<span class="text-[10px] text-gray-400 mb-0.5 px-1">' + time + '</span>' +
                '<div class="px-3 py-1.5 rounded-2xl shadow-sm text-[13px] leading-relaxed break-words bg-gray-900 text-gray-200 border border-gray-800 rounded-tl-sm">' +
                escaped + '</div></div></div>';
            container.insertAdjacentHTML('beforeend', html);
            scrollToBottom(true);
        }

        // ---- APPEND AI ASSISTANT (BOT) MESSAGE BUBBLE ----
        function appendBotMessage(text, createdAt, senderName) {
            var escaped = text.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            escaped = escaped.replace(/\n/g, '<br>');
            var time = createdAt ? formatTime(new Date(createdAt)) : formatTime(new Date());
            var nameEsc = (senderName || 'Assistant').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
            var html = '<div class="flex items-start mb-3">' +
                '<div class="flex flex-col items-start max-w-[85%]">' +
                '<span class="text-[10px] text-gray-400 mb-0.5 px-1"><span class="font-semibold text-purple-400">' + nameEsc + '</span> &middot; ' + time + '</span>' +
                '<div class="px-3 py-1.5 rounded-2xl shadow-sm text-[13px] leading-relaxed break-words bg-purple-50 text-purple-900 border border-purple-100 rounded-tl-sm">' +
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
                body: JSON.stringify({ 
                    message: msg,
                    chat_id: {{ $chat->id }}
                })
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
        // Reuse the global Echo set up by the layout (same config).
        // Poll until it is ready, then attach chat-specific subscriptions.
        var chatId = {{ $chat->id }};
        var currentUserId = {{ auth()->id() }};
        window._chatChannelId = chatId; // Lets the global turbo:before-render handler clean up

        function setupChatEcho() {
            if (!window.Echo) { setTimeout(setupChatEcho, 80); return; }
            var currentUserId = {{ auth()->id() }};

            window.Echo.private('chat.' + chatId)
                .listen('.message.new', function(e) {
                    if (e.sender_type === 'visitor') {
                        appendVisitorMessage(e.message, e.created_at);
                        if (typeof playAlertSound === 'function') playAlertSound(false);
                    } else if (e.sender_type === 'bot') {
                        appendBotMessage(e.message, e.created_at, e.sender_name);
                        if (typeof playAlertSound === 'function') playAlertSound(false);
                    } else if (e.sender_type === 'system') {
                        appendSystemMessage(e.message);
                        if (typeof playAlertSound === 'function') playAlertSound(false);
                    } else if (e.sender_type === 'agent' && e.sender_id !== currentUserId) {
                        appendAgentMessage(e.message);
                        if (typeof playAlertSound === 'function') playAlertSound(false);
                    }
                })
                .listen('.chat.seen', function(e) {
                    applySeenUpTo(e.seen_at);
                });

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
                .listen('.typing.indicator', function(e) {
                    var typingDiv = document.getElementById('typing-indicator');
                    if (!typingDiv) return;
                    if (e.sender_type === 'visitor') {
                        if (e.is_typing) {
                            typingDiv.classList.remove('hidden');
                        } else {
                            typingDiv.classList.add('hidden');
                        }
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
                });

            function appendSystemMessage(msg) {
                var sysHtml = '<div class="flex items-center justify-center my-1.5">' +
                    '<div class="flex items-center gap-4 w-full">' +
                    '<div class="flex-1 h-px bg-gray-800"></div>' +
                    '<span class="px-3 py-1 bg-gray-800 rounded-full text-[11px] font-medium text-gray-400 uppercase tracking-wider whitespace-nowrap">' +
                    msg + '</span>' +
                    '<div class="flex-1 h-px bg-gray-800"></div>' +
                    '</div></div>';
                container.insertAdjacentHTML('beforeend', sysHtml);
                scrollToBottom(true);
            }
        } // end setupChatEcho
        setupChatEcho();
    });
</script>

@endsection

