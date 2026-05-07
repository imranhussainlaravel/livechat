@extends('layouts.app')
@section('header_title', 'Agent Dashboard')

@section('content')

<div class="mb-8">
    <h1 class="text-3xl font-bold tracking-tight text-white mb-2">Welcome back, {{ auth()->user()->name }}</h1>
    <p class="text-sm font-medium text-slate-400">Here is the overview of your activity today.</p>
</div>

{{-- Personal Stats Grid --}}
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <x-stat-card title="Active Chats" :value="$metrics['active_chats'] ?? 0" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'></path></svg>" color="blue" />
    <x-stat-card title="Pending Queue" :value="$metrics['pending_queue'] ?? 0" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'></path></svg>" color="yellow" />
    <x-stat-card title="Followups Due" :value="$metrics['followups_due'] ?? 0" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'></path></svg>" color="red" />
    <x-circular-progress 
        title="Resolution Rate" 
        subtitle="Solved vs Assigned" 
        :current="$metrics['total_resolved'] ?? 0" 
        :total="$metrics['total_assigned'] ?? 0" 
        color="teal" 
    />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <div class="lg:col-span-2 space-y-6">
        {{-- Chart Section --}}
        <div class="bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl shadow-black/10 overflow-hidden p-6 group">
            <h3 class="text-base font-semibold tracking-wide text-slate-300 mb-6 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-cyan-500 shadow-[0_0_8px_#06b6d4]"></span>
                Messages Sent (Last 7 Days)
            </h3>
            <div class="relative h-64 w-full">
                <canvas id="agentMessagesChart"></canvas>
            </div>
        </div>

        {{-- Recent Active Chats --}}
        <div class="bg-slate-800/40 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-xl shadow-black/10 overflow-hidden flex flex-col">
            <div class="px-6 py-5 border-b border-slate-700/50 bg-slate-800/30 flex items-center justify-between">
                <h3 class="font-semibold text-slate-200">Your Active Chats</h3>
                <a href="{{ route('agent.chats.index') }}" class="text-sm font-semibold text-[#F0644B] hover:text-[#F0644B]/80 transition-colors">
                    View All &rarr;
                </a>
            </div>

            <div class="divide-y divide-slate-700/50 flex-1 overflow-y-auto max-h-72">
                @forelse($recentChats ?? [] as $chat)
                <a href="{{ route('agent.chats.show', $chat->id) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-slate-700/30 transition-colors block group">
                    <div class="flex-1 min-w-0 flex items-center gap-4">
                        <div class="w-10 h-10 rounded-xl bg-slate-700/50 border border-slate-600/50 flex items-center justify-center text-sm font-bold text-slate-300 shrink-0 group-hover:bg-slate-600/50 transition-colors">
                            {{ strtoupper(substr($chat->visitor->name ?? 'V', 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-200 truncate group-hover:text-white transition-colors">{{ $chat->visitor->name ?? 'Visitor' }}</p>
                            <p class="text-xs text-slate-400 truncate mt-0.5">{{ $chat->subject ?? 'General inquiry' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-semibold px-3 py-1 rounded-full border
                                    {{ in_array($chat->status->value, ['active', 'assigned']) ? 'bg-[#F0644B]/10 text-[#F0644B] border-[#F0644B]/20' : 'bg-slate-700/30 text-slate-400 border-slate-600/30' }}">
                            {{ ucfirst(str_replace('_', ' ', $chat->status->value)) }}
                        </span>
                        <span class="text-xs text-slate-500 font-medium whitespace-nowrap">{{ $chat->updated_at->diffForHumans() }}</span>
                    </div>
                </a>
                @empty
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    <p class="text-sm text-gray-500">No active chats assigned to you right now.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

    <div>
        {{-- Quick Actions or mini queue --}}
        <div class="bg-slate-800/40 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-xl shadow-black/10 overflow-hidden mb-6">
            <div class="px-6 py-5 border-b border-slate-700/50 bg-slate-800/30">
                <h3 class="font-semibold text-slate-200">Pending Queue</h3>
            </div>
            <div class="p-6">
                @if(($metrics['pending_queue'] ?? 0) > 0)
                <div class="text-center py-4">
                    <span class="text-5xl font-extrabold text-white block mb-2">{{ $metrics['pending_queue'] }}</span>
                    <p class="text-sm font-medium text-slate-400 mb-6">Visitors waiting for an agent</p>
                    <a href="{{ route('agent.chats.index') }}#queue" class="inline-flex justify-center items-center px-4 py-2.5 border border-transparent text-sm font-semibold rounded-xl text-white bg-[#F0644B] hover:bg-[#d9533a] transition-all w-full shadow-lg shadow-[#F0644B]/30">
                        Go to Queue
                    </a>
                </div>
                @else
                <div class="px-2 py-6 text-center text-sm text-gray-500">
                    Queue is empty.
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('agentMessagesChart').getContext('2d');
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(240, 100, 75, 0.4)');
        gradient.addColorStop(1, 'rgba(240, 100, 75, 0)');
        
        const data = @json($graphData);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Messages Sent',
                    data: data.values,
                    borderColor: '#F0644B',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    pointBackgroundColor: '#0f172a',
                    pointBorderColor: '#F0644B',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4 // ultra smooth curve
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)', // slate-900
                        titleColor: '#f8fafc',
                        bodyColor: '#cbd5e1',
                        borderColor: 'rgba(51, 65, 85, 0.5)',
                        borderWidth: 1,
                        padding: 12,
                        boxPadding: 4,
                        usePointStyle: true,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#64748b', stepSize: 1, padding: 10 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#64748b', padding: 10 }
                    }
                }
            }
        });
    });
</script>

@endsection