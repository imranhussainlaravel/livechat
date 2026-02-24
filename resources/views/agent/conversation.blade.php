@extends('layouts.app')

@section('title', 'Active Chat - Agent View')

@section('content')
<div class="flex flex-col h-[calc(100vh-8rem)] bg-gray-100 border rounded-lg overflow-hidden shadow-sm">
    <!-- Header -->
    <div class="bg-white border-b px-6 py-4 flex justify-between items-center">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Chat #{{ $id }}</h2>
            <p class="text-xs text-green-600 font-medium">Session Active</p>
        </div>
        <button onclick="closeChat()" class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200 transition">End Chat</button>
    </div>

    <!-- Messages Window -->
    <div id="messages-window" class="flex-1 overflow-y-auto p-6 space-y-4">
        <!-- Messages injected here -->
        <div class="text-center text-gray-400 text-sm">System: Connected to chat session.</div>
    </div>

    <!-- Input Area -->
    <div class="bg-white border-t p-4 flex gap-2">
        <input type="text" id="message-input" class="flex-1 px-4 py-2 border rounded focus:outline-none focus:ring focus:border-indigo-300" placeholder="Type your reply..." onkeypress="if(event.key === 'Enter') sendMessage()">
        <button onclick="sendMessage()" class="bg-indigo-600 text-white px-6 py-2 rounded font-medium hover:bg-indigo-700 transition">Send</button>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const convId = {
        {
            $id
        }
    };
    const agentId = {
        {
            session('agent_id')
        }
    };
    const msgWindow = document.getElementById('messages-window');
    const input = document.getElementById('message-input');

    function appendMessage(text, isSelf) {
        const align = isSelf ? 'justify-end' : 'justify-start';
        const color = isSelf ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-800';

        msgWindow.innerHTML += `
            <div class="flex ${align}">
                <div class="${color} max-w-xs md:max-w-md rounded-lg px-4 py-2 text-sm">
                    ${text}
                </div>
            </div>
        `;
        msgWindow.scrollTop = msgWindow.scrollHeight;
    }

    window.sendMessage = function() {
        const text = input.value.trim();
        if (!text) return;

        input.value = '';
        input.disabled = true;

        axios.post(`/api/agent/conversation/${convId}/message`, {
                agent_id: agentId,
                content: text
            })
            .then(res => {
                appendMessage(text, true);
            })
            .catch(err => {
                alert("Send failed: " + (err.response?.data?.error || err.message));
            })
            .finally(() => {
                input.disabled = false;
                input.focus();
            });
    };

    window.closeChat = function() {
        if (!confirm("Are you sure you want to end this chat?")) return;

        axios.post(`/api/agent/conversation/${convId}/close`, {
                agent_id: agentId
            })
            .then(() => {
                alert("Chat closed.");
                window.location.href = '/agent/dashboard';
            })
            .catch(err => alert("Close failed: " + (err.response?.data?.error || err.message)));
    };
</script>
@endpush