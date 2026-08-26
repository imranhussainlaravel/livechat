@props(['agents'])
<div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden flex flex-col h-full lg:col-span-1">
    <div class="px-5 py-4 border-b border-slate-700/50 flex items-center justify-between bg-slate-900/40">
        <h3 class="text-sm font-bold text-slate-200">Top Agents Performance</h3>
        <a href="{{ route('admin.agents.index') }}" class="text-xs font-semibold text-[#6366F1] hover:text-[#818CF8] transition-colors">
            View all &rarr;
        </a>
    </div>
    <div class="flex-1 overflow-y-auto divide-y divide-slate-700/40">
        @forelse($agents as $agent)
        @php
            $maxChats = $agents->max('assigned_chats_count') ?: 1;
            $percent = round(($agent->assigned_chats_count / $maxChats) * 100);
        @endphp
        <div class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-700/30 transition-colors group">
            <div class="w-9 h-9 rounded-full bg-slate-700 border border-slate-600 flex items-center justify-center text-sm font-bold text-white shrink-0 group-hover:border-[#6366F1]/50 transition-colors">
                {{ substr($agent->name, 0, 1) }}
            </div>
            
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs font-semibold text-slate-200 truncate">{{ $agent->name }}</p>
                    <span class="text-[10px] font-bold text-slate-400">{{ $agent->assigned_chats_count }} chats</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="flex-1 h-1.5 bg-slate-700/50 rounded-full overflow-hidden">
                        <div class="h-full bg-[#6366F1] rounded-full transition-all" style="width: {{ $percent }}%"></div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="p-5 text-center text-xs text-slate-500">
            No agent data available.
        </div>
        @endforelse
    </div>
</div>
