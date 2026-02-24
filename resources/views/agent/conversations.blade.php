@extends('layouts.app')

@section('title', 'Agent - Pending Conversations')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Pending Conversations</h2>
        <a href="/agent/dashboard" class="text-gray-500 hover:text-gray-700 font-medium">&larr; Dashboard</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" id="conversations-grid">
        <div class="p-4 border rounded text-center text-gray-500 col-span-full">Loading pending queue...</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const agentId = {
        {
            session('agent_id')
        }
    };

    function loadPending() {
        axios.get('/api/agent/conversations?state=PENDING')
            .then(response => {
                const data = response.data.data;
                const grid = document.getElementById('conversations-grid');

                if (data.length === 0) {
                    grid.innerHTML = '<div class="p-4 border rounded text-center text-gray-500 col-span-full">Queue is empty.</div>';
                    return;
                }

                grid.innerHTML = data.map(conv => `
                    <div class="bg-white border rounded-lg p-5 shadow-sm flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">#${conv.id}</span>
                                <span class="text-xs text-gray-400">${new Date(conv.created_at).toLocaleTimeString()}</span>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mb-1">${conv.visitor_session?.visitor_name || 'Guest User'}</h3>
                            <p class="text-sm text-gray-600 mb-4">Queue: <span class="font-medium">${conv.queue}</span></p>
                        </div>
                        <button onclick="acceptConversation(${conv.id})" class="w-full bg-indigo-600 text-white font-medium py-2 px-4 rounded hover:bg-indigo-700 transition">
                            Accept Chat
                        </button>
                    </div>
                `).join('');
            })
            .catch(err => {
                console.error("Failed to load queue", err);
                document.getElementById('conversations-grid').innerHTML = '<div class="text-red-500 col-span-full">Error loading queue.</div>';
            });
    }

    window.acceptConversation = function(id) {
        axios.post(`/api/agent/conversation/${id}/accept`, {
                agent_id: agentId
            })
            .then(res => {
                window.location.href = `/agent/conversation/${id}`;
            })
            .catch(err => {
                alert(err.response?.data?.error || "Failed to accept conversation.");
                loadPending(); // reload queue in case it was snagged
            });
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadPending();
        setInterval(loadPending, 10000); // Polling every 10s for demo
    });
</script>
@endpush