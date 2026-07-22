@extends('layouts.app')
@section('header_title', 'CRM · ' . $module)

@section('content')
<div class="max-w-2xl mx-auto py-16">
    <div class="bg-slate-800/40 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-xl shadow-black/10 p-10 text-center">
        <div class="w-14 h-14 mx-auto mb-6 rounded-2xl bg-[#6366F1]/10 border border-[#6366F1]/20 flex items-center justify-center">
            <svg class="w-7 h-7 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
        </div>
        <span class="inline-block text-[10px] font-black uppercase tracking-[0.2em] text-[#6366F1] mb-2">CRM Module</span>
        <h1 class="text-2xl font-bold text-white mb-3">{{ $module }}</h1>
        <p class="text-sm text-slate-400 leading-relaxed mb-6 max-w-md mx-auto">{{ $blurb }}</p>
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Coming soon — being built now
        </div>
    </div>
</div>
@endsection
