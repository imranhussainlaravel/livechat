@extends('layouts.app')
@section('header_title', 'Dashboard')

@section('content')

{{-- Page Header --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <h1 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
            Dashboard <span class="animate-[wave_2.5s_infinite] origin-bottom-right">👋</span>
        </h1>
        <p class="text-xs text-slate-500 mt-0.5">Welcome back, {{ auth()->user()->name ?? 'User' }}! Here's what's happening with your business today.</p>
    </div>
    <span class="flex items-center gap-1.5 text-xs text-emerald-400 font-medium bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 rounded-full shadow-sm">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 flex-shrink-0 animate-pulse"></span>
        Live
    </span>
</div>

{{-- ── 5 KPI Cards ─────────────────────────────────────────── --}}
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    <div id="dashboard-active-card" class="h-full">
        <x-stat-card 
            title="Active" 
            value="{{ $stats['active_chats'] ?? 0 }}" 
            subtitle="Chats in progress"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>'
            color="indigo"
            trend="{{ $stats['chat_trend']['type'] ?? 'flat' }}"
            trendValue="{{ $stats['chat_trend']['value'] ?? '0%' }}"
            trendLabel="{{ $stats['trend_label'] ?? 'vs last 7d' }}"
            sparkline="{{ $stats['chat_sparkline'] ?? '' }}"
        />
    </div>
    
    <div id="dashboard-queue-card" class="h-full">
        <x-stat-card 
            title="Queue" 
            value="{{ $stats['pending_queue'] ?? 0 }}" 
            subtitle="Waiting for agent"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'
            color="amber"
            trend="flat"
            trendValue="—"
            trendLabel="current"
        />
    </div>
    
    <div id="dashboard-online-card" class="h-full">
        <x-stat-card 
            title="Online" 
            value="{{ $stats['agents_online'] ?? 0 }}" 
            subtitle="Agents available"
            icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'
            color="emerald"
            trend="flat"
            trendValue="—"
            trendLabel="current"
        />
    </div>
    
    <x-stat-card 
        title="Started" 
        value="{{ $stats['period_chats'] ?? 0 }}" 
        subtitle="Conversations started"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'
        color="cyan"
        trend="{{ $stats['chat_trend']['type'] ?? 'flat' }}"
        trendValue="{{ $stats['chat_trend']['value'] ?? '0%' }}"
        trendLabel="{{ $stats['trend_label'] ?? 'vs last 7d' }}"
        sparkline="{{ $stats['chat_sparkline'] ?? '' }}"
    />
    
    <x-stat-card 
        title="Resolved" 
        value="{{ $stats['resolved_percent'] ?? 0 }}%" 
        subtitle="{{ $stats['period_resolved'] ?? 0 }} of {{ $stats['period_chats'] ?? 0 }} closed"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        color="violet"
        trend="{{ $stats['resolved_percent_trend']['type'] ?? 'flat' }}"
        trendValue="{{ $stats['resolved_percent_trend']['value'] ?? '0%' }}"
        trendLabel="{{ $stats['trend_label'] ?? 'vs last 7d' }}"
        sparkline="{{ $stats['resolved_sparkline'] ?? '' }}"
    />
</div>

{{-- ── CRM Overview ─────────────────────────────────────────── --}}
<div class="flex items-center justify-between mb-3">
    <h2 class="text-xs font-black uppercase tracking-[0.2em] text-slate-500">CRM Overview</h2>
    <a href="{{ route('crm.leads.index') }}" class="text-xs font-semibold text-[#6366F1] hover:text-[#818CF8]">Open CRM &rarr;</a>
</div>
<div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-5 gap-4 mb-6">
    <x-stat-card 
        title="Open Leads" 
        value="{{ $crm['leads_open'] ?? 0 }}" 
        subtitle="of {{ $crm['leads_total'] ?? 0 }} total"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/>'
        color="indigo"
        href="{{ route('crm.leads.index') }}"
        trend="flat"
        trendValue="—"
        trendLabel="current"
    />

    <x-stat-card 
        title="Open Deals" 
        value="{{ $crm['deals_open'] ?? 0 }}" 
        subtitle="In pipeline"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'
        color="sky"
        href="{{ route('crm.deals.index') }}"
        trend="flat"
        trendValue="—"
        trendLabel="current"
    />

    <x-stat-card 
        title="Pipeline" 
        value="PKR {{ number_format($crm['pipeline_value'] ?? 0, 0) }}" 
        subtitle="Open deal value"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V6m0 12v-2m0 2c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        color="amber"
        href="{{ route('crm.deals.index') }}"
        trend="flat"
        trendValue="—"
        trendLabel="current"
    />

    <x-stat-card 
        title="Won" 
        value="{{ $crm['deals_won'] ?? 0 }}" 
        subtitle="PKR {{ number_format($crm['won_value'] ?? 0, 0) }}"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>'
        color="emerald"
        href="{{ route('crm.deals.index') }}"
        trend="flat"
        trendValue="—"
        trendLabel="period"
    />

    <x-stat-card 
        title="Active Orders" 
        value="{{ $crm['orders_active'] ?? 0 }}" 
        subtitle="Not yet delivered"
        icon='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'
        color="violet"
        href="{{ route('crm.orders.index') }}"
        trend="flat"
        trendValue="—"
        trendLabel="current"
    />
</div>

{{-- ── Analytics Widgets ───────────────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">
    <x-dashboard.line-chart :graphData="$graphData" />
    <x-dashboard.donut-chart :chatVolume="$chatVolume" />
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
    <x-dashboard.funnel-chart :funnelData="$funnelData" />
    <x-dashboard.agent-performance-list :agents="$agents" />
    <x-dashboard.recent-chats-list :recentChats="$recentChats" />
</div>

<style>
    @keyframes wave {
        0%, 100% { transform: rotate(0deg); }
        25% { transform: rotate(15deg); }
        50% { transform: rotate(-10deg); }
        75% { transform: rotate(15deg); }
    }
</style>

@endsection
