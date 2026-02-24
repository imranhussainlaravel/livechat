@extends('layouts.app')

@section('title', 'LiveChat Visitor Interface')

@section('content')
<div class="max-w-xl mx-auto flex flex-col h-[calc(100vh-8rem)] bg-white border rounded-xl overflow-hidden shadow-lg mt-4">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-6 py-4 flex items-center justify-between text-white shadow-md z-10">
        <div>
            <h2 class="text-lg font-bold">Support Chat</h2>
            <p id="chat-status" class="text-xs text-blue-100 italic">Initializing...</p>
        </div>
        <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
        </div>
    </div>

    <!-- Messages Container -->
    <div id="messages-container" class="flex-1 overflow-y-auto p-4 space-y-4 bg-gray-50 flex flex-col">
        <!-- Messages -->
    </div>

    <!-- Form Area -->
    <div class="bg-white border-t p-3 relative" id="chat-form">
        <!-- Start Context Overlay -->
        <div id="start-overlay" class="absolute inset-0 bg-white/95 backdrop-blur-sm flex flex-col items-center justify-center z-20">
            <p class="text-sm text-gray-600 mb-3 text-center px-4">Hello! Please start the session to chat with us.</p>
            <button onclick="startSession()" id="btn-start" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded-full shadow-md transition transform active:scale-95">Start Conversation</button>
        </div>

        <form onsubmit="sendMessage(event)" class="flex items-end gap-2">
            <input type="text" id="msg-input" class="w-full bg-gray-100 text-gray-800 text-sm rounded-2xl px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:bg-white transition" placeholder="Type your message..." autocomplete="off" disabled>
            <button type="submit" id="btn-send" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-full p-3 shadow-md transition disabled:opacity-50 transform active:scale-95 flex-shrink-0" disabled>
                <svg class="w-5 h-5 rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                </svg>
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let sessionId = null;
    let expectedConvId = '{{ $conversation_id }}';
    const container = document.getElementById('messages-container');
    const input = document.getElementById('msg-input');
    const btnSend = document.getElementById('btn-send');
    const statusText = document.getElementById('chat-status');
    const overlay = document.getElementById('start-overlay');

    // On Load check existing mock session logic
    if (expectedConvId !== 'new') {
        // If they navigate to /chat/123 manually
        sessionId = localStorage.getItem('visitor_session_' + expectedConvId);
        if (sessionId) {
            activateChatInterface();
        } else {
            statusText.innerText = "Session Not Found";
            appendSysMsg("Invalid session. Wait for an agent or start anew.");
        }
    } else {
        statusText.innerText = "Ready to connect";
    }

    window.startSession = function() {
        const btn = document.getElementById('btn-start');
        btn.innerText = 'Connecting...';
        btn.disabled = true;

        axios.post('/api/chat/start', {
                visitor_name: 'Visitor ' + Math.floor(Math.random() * 1000)
            })
            .then(res => {
                sessionId = res.data.session_id;
                const convId = res.data.conversation_id;
                localStorage.setItem('visitor_session_' + convId, sessionId);

                // Rewrite URL context 
                window.history.replaceState({}, '', `/chat/${convId}`);

                activateChatInterface();
                appendSysMsg(`Connected. Transferring to queue... (Session #${convId})`);
            })
            .catch(err => {
                btn.innerText = 'Failed. Try Again';
                btn.disabled = false;
            });
    }

    function activateChatInterface() {
        overlay.classList.add('hidden');
        input.disabled = false;
        btnSend.disabled = false;
        statusText.innerText = "Queueing (Simulated)";
        input.focus();
    }

    function appendMsg(text, isUser) {
        const wrapper = document.createElement('div');
        wrapper.className = `flex ${isUser ? 'justify-end' : 'justify-start'} w-full`;

        const bubbleStyles = isUser ?
            'bg-indigo-600 text-white rounded-l-2xl rounded-tr-2xl' :
            'bg-white border text-gray-800 rounded-r-2xl rounded-tl-2xl shadow-sm';

        wrapper.innerHTML = `<div class="px-4 py-2 text-sm max-w-[80%] break-words ${bubbleStyles}">${text}</div>`;
        container.appendChild(wrapper);
        container.scrollTop = container.scrollHeight;
    }

    function appendSysMsg(text) {
        container.innerHTML += `<div class="text-center text-xs text-gray-400 my-2">${text}</div>`;
        container.scrollTop = container.scrollHeight;
    }

    window.sendMessage = function(e) {
        e.preventDefault();
        const content = input.value.trim();
        if (!content || !sessionId) return;

        input.value = '';
        input.disabled = true;
        btnSend.disabled = true;

        // Current URL id parsing
        const splits = window.location.pathname.split('/');
        const runningConvId = splits[splits.length - 1];

        axios.post(`/api/chat/${runningConvId}/message`, {
                session_id: sessionId,
                content: content
            })
            .then(res => {
                appendMsg(content, true);
            })
            .catch(err => {
                appendSysMsg("Failed to send: " + (err.response?.data?.error || err.message));
            })
            .finally(() => {
                input.disabled = false;
                btnSend.disabled = false;
                input.focus();
            });
    }
</script>
@endpush