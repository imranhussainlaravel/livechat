@extends('layouts.app')
@section('header_title', 'Dashboard')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white tracking-tight">Dashboard</h1>
        <p class="text-xs text-slate-500 mt-0.5">{{ now()->format('l, F j Y') }} &middot; Real-time overview</p>
    </div>
    <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-medium bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 rounded-full">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0"></span>
        Live
    </span>
</div>

{{-- ── 5 KPI Cards ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">

    {{-- Active Chats --}}
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 hover:border-[#6366F1]/30 transition-colors group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Active</p>
            <div class="w-8 h-8 rounded-lg bg-[#6366F1]/10 border border-[#6366F1]/20 flex items-center justify-center group-hover:bg-[#6366F1]/20 transition-colors">
                <svg class="w-4 h-4 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-white leading-none mb-1">{{ $stats['active_chats'] ?? 0 }}</p>
        <p class="text-[11px] text-slate-500">Chats in progress</p>
    </div>

    {{-- Pending Queue --}}
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 hover:border-amber-500/30 transition-colors group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Queue</p>
            <div class="w-8 h-8 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center group-hover:bg-amber-500/20 transition-colors">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-white leading-none mb-1">{{ $stats['pending_queue'] ?? 0 }}</p>
        <p class="text-[11px] text-slate-500">Waiting for agent</p>
    </div>

    {{-- Agents Online --}}
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 hover:border-emerald-500/30 transition-colors group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Online</p>
            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center group-hover:bg-emerald-500/20 transition-colors">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-white leading-none mb-1">{{ $stats['agents_online'] ?? 0 }}</p>
        <p class="text-[11px] text-slate-500">Agents available</p>
    </div>

    {{-- Total Today --}}
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 hover:border-cyan-500/30 transition-colors group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Today</p>
            <div class="w-8 h-8 rounded-lg bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center group-hover:bg-cyan-500/20 transition-colors">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-white leading-none mb-1">{{ $stats['total_today'] ?? 0 }}</p>
        <p class="text-[11px] text-slate-500">Conversations started</p>
    </div>

    {{-- Resolution Rate --}}
    @php
        $resolved = $stats['closed_today'] ?? 0;
        $total    = $stats['total_today']  ?? 0;
        $rate     = $total > 0 ? round(($resolved / $total) * 100) : 0;
    @endphp
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl p-4 hover:border-violet-500/30 transition-colors group">
        <div class="flex items-center justify-between mb-3">
            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-widest">Resolved</p>
            <div class="w-8 h-8 rounded-lg bg-violet-500/10 border border-violet-500/20 flex items-center justify-center group-hover:bg-violet-500/20 transition-colors">
                <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-extrabold text-white leading-none mb-1">{{ $rate }}<span class="text-lg text-slate-400 font-bold">%</span></p>
        <p class="text-[11px] text-slate-500">{{ $resolved }} of {{ $total }} closed</p>
    </div>

</div>

{{-- ── Chart + Agents ───────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

    {{-- Chart --}}
    <div class="lg:col-span-2 bg-slate-800/50 border border-slate-700/50 rounded-xl p-5 flex flex-col">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-sm font-bold text-slate-200">Chat Volume</h3>
                <p class="text-[11px] text-slate-500 mt-0.5">Last 7 days</p>
            </div>
            <div class="flex items-center gap-1.5 text-[11px] text-[#6366F1] font-semibold bg-[#6366F1]/10 border border-[#6366F1]/20 px-2.5 py-1 rounded-full">
                <span class="w-1.5 h-1.5 rounded-full bg-[#6366F1]"></span>
                Chats
            </div>
        </div>
        <div class="relative flex-1 min-h-[260px]">
            <canvas id="chatVolumeChart"></canvas>
        </div>
    </div>

    {{-- Active Agents --}}
    <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden flex flex-col">
        <div class="px-5 py-3.5 border-b border-slate-700/50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-200">Agents</h3>
            <a href="{{ route('admin.agents.index') }}" class="text-xs font-semibold text-[#6366F1] hover:underline">
                View all →
            </a>
        </div>
        <div class="flex-1 overflow-y-auto divide-y divide-slate-700/40">
            @forelse($agents as $agent)
            <div class="flex items-center gap-3 px-5 py-3 hover:bg-slate-700/30 transition-colors">
                <div class="relative flex-shrink-0">
                    <div class="w-8 h-8 rounded-lg bg-slate-700 border border-slate-600 flex items-center justify-center text-xs font-bold text-slate-300">
                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full border-2 border-slate-800 {{ $agent->status === 'online' ? 'bg-emerald-400' : ($agent->status === 'away' ? 'bg-amber-400' : 'bg-slate-500') }}"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-200 truncate">{{ $agent->name }}</p>
                    <p class="text-[10px] text-slate-500">{{ $agent->assigned_chats_count ?? 0 }}/{{ $agent->max_chats ?? 5 }} chats</p>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wide {{ $agent->status === 'online' ? 'text-emerald-400' : ($agent->status === 'away' ? 'text-amber-400' : 'text-slate-500') }}">
                    {{ $agent->status ?? 'offline' }}
                </span>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-xs text-slate-500">No agents found</div>
            @endforelse
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chatVolumeChart').getContext('2d');

        let gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(99, 102, 241, 0.25)');
        gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

        const data = @json($graphData);

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [{
                    label: 'Chats',
                    data: data.values,
                    borderColor: '#6366F1',
                    backgroundColor: gradient,
                    borderWidth: 2,
                    pointBackgroundColor: '#6366F1',
                    pointBorderColor: '#0f172a',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#94a3b8',
                        borderColor: '#334155',
                        borderWidth: 1,
                        padding: 10,
                        usePointStyle: true,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(51,65,85,0.4)', drawBorder: false },
                        border: { display: false },
                        ticks: { color: '#475569', stepSize: 1, padding: 8, font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#475569', padding: 8, font: { size: 11 } }
                    }
                }
            }
        });
    });
</script>

@endsection
