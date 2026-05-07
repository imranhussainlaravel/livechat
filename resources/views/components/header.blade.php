<header class="h-12 flex-shrink-0 bg-slate-950/40 border-b border-slate-800/50 flex items-center justify-between px-6 backdrop-blur-xl sticky top-0 z-10 shadow-sm">
    <div class="flex items-center gap-4">
        <!-- Optional Mobile Menu Toggle -->
        <button class="md:hidden text-slate-400 hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
    </div>

    <div class="flex items-center gap-6">
        @if(auth()->check())
        <div class="flex items-center gap-4">
            {{-- Status Indicator --}}
            @php
                $statusColor = auth()->user()->status === 'online' ? 'emerald' : (auth()->user()->status === 'away' ? 'amber' : 'slate');
                $statusText = ucfirst(auth()->user()->status ?? 'offline');
            @endphp
            <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-800/50 border border-slate-700/50 shadow-sm">
                <span class="relative flex h-2 w-2">
                    @if(auth()->user()->status === 'online')
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    @endif
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-{{ $statusColor }}-500"></span>
                </span>
                <span class="text-xs font-semibold text-slate-300">{{ $statusText }}</span>
            </div>

            {{-- Notifications / Messages --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800/80 transition-all relative group">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    @if($totalUnreadInternal > 0)
                    <span class="absolute top-1.5 right-1.5 flex h-4 w-4">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#F0644B] opacity-75"></span>
                        <span class="relative inline-flex items-center justify-center rounded-full h-4 w-4 bg-[#F0644B] text-[9px] font-bold text-white leading-none">
                            {{ $totalUnreadInternal }}
                        </span>
                    </span>
                    @endif
                </button>

                {{-- Dropdown --}}
                <div x-show="open" @click.away="open = false" 
                    class="absolute right-0 mt-3 w-80 bg-slate-800/95 backdrop-blur-xl border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/40 ring-1 ring-black ring-opacity-5 focus:outline-none z-50 overflow-hidden"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    style="display: none;">
                    <div class="px-5 py-4 border-b border-slate-700/50 bg-slate-800/50 flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Internal Messages</span>
                        <span class="text-[10px] bg-[#F0644B]/10 text-[#F0644B] px-2 py-0.5 rounded-full border border-[#F0644B]/20 font-bold">{{ $totalUnreadInternal }} New</span>
                    </div>
                    <div class="max-h-80 overflow-y-auto">
                        @forelse($unreadAgents as $ua)
                        <a href="{{ route('agent.agents.show', $ua->id) }}" class="flex items-center gap-4 px-5 py-4 hover:bg-slate-700/50 transition-colors border-b border-slate-700/30 last:border-0 no-underline group">
                            <div class="w-10 h-10 rounded-xl bg-[#F0644B]/10 border border-[#F0644B]/20 flex items-center justify-center text-sm font-bold text-[#F0644B] shrink-0 group-hover:scale-110 transition-transform">
                                {{ substr($ua->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-slate-100 truncate m-0">{{ $ua->name }}</p>
                                <p class="text-xs text-[#F0644B] font-medium m-0 mt-0.5">{{ $ua->unread_count }} new message{{ $ua->unread_count > 1 ? 's' : '' }}</p>
                            </div>
                        </a>
                        @empty
                        <div class="px-5 py-10 text-center">
                            <div class="w-12 h-12 bg-slate-700/30 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                            </div>
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wider">No new team messages</p>
                        </div>
                        @endforelse
                    </div>
                    <a href="{{ route('agent.agents.index') }}" class="block text-center py-3 text-xs font-bold text-[#F0644B] bg-slate-800/80 hover:bg-slate-700 transition-colors border-t border-slate-700/50 no-underline uppercase tracking-widest">
                        Open Messages &rarr;
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</header>