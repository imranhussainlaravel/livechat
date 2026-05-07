@extends('layouts.app')
@section('header_title', 'Admin Dashboard')

@section('content')

<div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold tracking-tight text-white">System Overview</h1>
        <p class="text-sm font-medium text-slate-400 mt-2">Real-time metrics and agent load tracking.</p>
    </div>
</div>

{{-- Stats Grid --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6 mb-8">
    <x-stat-card title="Active Chats" :value="$stats['active_chats'] ?? 0" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'></path></svg>" color="blue" />
    <x-circular-progress 
        title="Pending Queue" 
        subtitle="Queue vs Total Ongoing" 
        :current="$stats['pending_queue'] ?? 0" 
        :total="$stats['total_ongoing'] ?? 0" 
        color="yellow" 
    />
    <x-stat-card title="Agents Online" :value="$stats['agents_online'] ?? 0" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'></path></svg>" color="green" />
    <x-stat-card title="Total Today" :value="$stats['total_today'] ?? 0" icon="<svg class='w-6 h-6' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'></path></svg>" color="indigo" />
    <x-circular-progress 
        title="Resolution Rate" 
        subtitle="Solved vs Total Today" 
        :current="$stats['closed_today'] ?? 0" 
        :total="$stats['total_today'] ?? 0" 
        color="teal" 
    />
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    {{-- Chart Section --}}
    <div class="lg:col-span-2 bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl shadow-black/10 overflow-hidden p-6 group h-full flex flex-col">
        <h3 class="text-base font-semibold tracking-wide text-slate-300 mb-6 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#F0644B] shadow-[0_0_8px_#F0644B]"></span>
            Chat Volume (Last 7 Days)
        </h3>
        <div class="relative flex-1 min-h-[300px]">
            <canvas id="chatVolumeChart"></canvas>
        </div>
    </div>

    {{-- Active Agents --}}
    <div class="bg-slate-800/40 backdrop-blur-xl rounded-2xl border border-slate-700/50 shadow-xl shadow-black/10 overflow-hidden flex flex-col h-full">
        <div class="px-6 py-5 border-b border-slate-700/50 bg-slate-800/30 flex items-center justify-between">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-[#F0644B] shadow-[0_0_8px_#F0644B]"></span>
                Recent Online
            </h3>
            <a href="{{ route('admin.agents.index') }}" class="text-sm font-semibold text-[#F0644B] hover:text-[#F0644B]/80 transition-colors">
                View All &rarr;
            </a>
        </div>

        <div class="divide-y divide-slate-700/50 flex-1 overflow-y-auto">
            @forelse($agents as $agent)
            <div class="flex items-center gap-3 px-5 py-4 hover:bg-slate-700/30 transition-colors group">
                <div class="relative">
                    <div class="w-10 h-10 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-sm font-bold text-slate-300 group-hover:bg-[#F0644B]/10 group-hover:text-[#F0644B] transition-all">
                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 block h-3 w-3 rounded-full ring-2 ring-slate-800
                                {{ $agent->status === 'online' ? 'bg-emerald-500 shadow-[0_0_8px_#10b981]' : ($agent->status === 'away' ? 'bg-amber-500 shadow-[0_0_8px_#f59e0b]' : 'bg-slate-500') }}">
                    </span>
                </div>

                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-100 truncate group-hover:text-[#F0644B] transition-colors leading-tight">{{ $agent->name }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Load:</span>
                        <span class="text-[10px] font-bold text-slate-300">{{ $agent->assigned_chats_count ?? 0 }} / {{ $agent->max_chats ?? 5 }}</span>
                    </div>
                </div>

                <div class="shrink-0 text-right">
                    <span class="text-[10px] font-bold {{ $agent->status === 'online' ? 'text-emerald-400' : ($agent->status === 'away' ? 'text-amber-400' : 'text-slate-500') }} uppercase tracking-wider">
                        {{ ucfirst($agent->status ?? 'Offline') }}
                    </span>
                </div>
            </div>
            @empty
            <div class="px-6 py-10 text-center">
                <p class="text-xs font-medium text-slate-500 uppercase tracking-widest">No agents found</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chatVolumeChart').getContext('2d');
        
        let gradient = ctx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(240, 100, 75, 0.4)');
        gradient.addColorStop(1, 'rgba(240, 100, 75, 0)');
        
        const data = @json($graphData);
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Chats Created',
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