@extends('layouts.app')

@section('title', 'Agent Dashboard')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Welcome, {{ session('agent_name') }}</h2>
        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-semibold">Online Status Active</span>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <a href="/agent/conversations" class="block p-6 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-green-300 transition shadow-sm">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Pending Queue</h5>
            <p class="font-normal text-gray-700">View and accept incoming visitor chats.</p>
        </a>
        <div class="block p-6 bg-white border border-gray-200 rounded-lg shadow-sm">
            <h5 class="mb-2 text-2xl font-bold tracking-tight text-gray-900">Heartbeat</h5>
            <p class="font-normal text-gray-700 mb-4">Your connection is being monitored.</p>
            <button onclick="pingHeartbeat()" class="px-4 py-2 bg-gray-800 text-white rounded hover:bg-gray-700 text-sm">Ping Manually</button>
            <span id="ping-status" class="ml-2 text-sm text-green-600 hidden">Pinged!</span>
        </div>
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

    function pingHeartbeat() {
        axios.post('/api/agent/heartbeat', {
                agent_id: agentId
            })
            .then(() => {
                document.getElementById('ping-status').classList.remove('hidden');
                setTimeout(() => document.getElementById('ping-status').classList.add('hidden'), 2000);
            })
            .catch(err => console.error("Heartbeat failed", err));
    }

    // Auto ping every 60 seconds
    setInterval(pingHeartbeat, 60000);
</script>
@endpush