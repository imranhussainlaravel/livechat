import './echo';
import axios from 'axios';
import Alpine from 'alpinejs';

// ─── Setup ──────────────────────────────────────────────────────
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const csrfToken = document.querySelector('meta[name="csrf-token"]');
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;
}

// ─── Alpine.js ──────────────────────────────────────────────────
window.Alpine = Alpine;
Alpine.start();

// ─── Real-time Chat (only on chat show pages) ───────────────────
const messagesContainer = document.getElementById('messages-container');
const messageForm = document.getElementById('message-form');

if (messagesContainer && messageForm) {
    // Extract chat ID from the URL: /agent/chats/{id}
    const pathParts = window.location.pathname.split('/');
    const chatId = pathParts[pathParts.indexOf('chats') + 1];

    if (chatId) {
        // Listen for new messages via WebSocket
        window.Echo.private(`chat.${chatId}`)
            .listen('MessageSent', (e) => {
                appendMessage(e.message || e);
            });

        // Listen for typing indicators
        window.Echo.private(`chat.${chatId}`)
            .listen('TypingIndicator', (e) => {
                const indicator = document.getElementById('typing-indicator');
                if (indicator && e.sender_type === 'visitor') {
                    indicator.classList.toggle('hidden', !e.is_typing);
                }
            });

        // Handle message form submission via AJAX
        messageForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const input = document.getElementById('message-input');
            const message = input.value.trim();
            if (!message) return;

            try {
                const response = await axios.post(messageForm.action, {
                    message: message,
                }, {
                    headers: { 'Accept': 'application/json' }
                });

                input.value = '';
                input.focus();

                // Append agent's own message immediately
                appendMessage({
                    message: message,
                    sender_type: 'agent',
                    created_at: new Date().toISOString(),
                });
            } catch (err) {
                console.error('Failed to send message:', err);
            }
        });

        // Send typing indicator on input
        let typingTimer = null;
        const messageInput = document.getElementById('message-input');
        if (messageInput) {
            messageInput.addEventListener('input', function () {
                if (typingTimer) clearTimeout(typingTimer);

                // Send typing = true
                axios.post(`/agent/chats/${chatId}/typing`, {
                    is_typing: true,
                }, { headers: { 'Accept': 'application/json' } }).catch(() => { });

                // Send typing = false after 2 seconds of no input
                typingTimer = setTimeout(() => {
                    axios.post(`/agent/chats/${chatId}/typing`, {
                        is_typing: false,
                    }, { headers: { 'Accept': 'application/json' } }).catch(() => { });
                }, 2000);
            });
        }
    }
}

function appendMessage(msg) {
    if (!messagesContainer) return;

    const isAgent = msg.sender_type === 'agent';
    const isSystem = msg.sender_type === 'system';
    const time = msg.created_at ? new Date(msg.created_at).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' }) : '';

    let html = '';
    if (isSystem) {
        html = `<div class="text-center"><span class="text-xs text-gray-500 bg-gray-800 px-3 py-1 rounded-full">${escapeHtml(msg.message)}</span></div>`;
    } else {
        const bubbleClasses = isAgent
            ? 'bg-indigo-600/30 border border-indigo-700/30 rounded-2xl rounded-br-md'
            : 'bg-gray-800 border border-gray-700 rounded-2xl rounded-bl-md';
        html = `
            <div class="flex ${isAgent ? 'justify-end' : 'justify-start'}">
                <div class="max-w-[70%] ${bubbleClasses} px-4 py-2.5">
                    <p class="text-sm text-gray-200 leading-relaxed">${escapeHtml(msg.message)}</p>
                    <p class="text-[10px] text-gray-500 mt-1 ${isAgent ? 'text-right' : ''}">${time}</p>
                </div>
            </div>`;
    }

    messagesContainer.insertAdjacentHTML('beforeend', html);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
