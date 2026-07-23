<header class="h-11 flex-shrink-0 bg-slate-950/60 border-b border-slate-800/60 flex items-center justify-between px-4 gap-3 backdrop-blur-xl sticky top-0 z-10">
    <div class="flex items-center gap-2 min-w-0">
        <button id="sidebar-toggle" type="button" onclick="window.toggleSidebar && window.toggleSidebar()" aria-label="Toggle menu" class="lg:hidden p-1.5 -ml-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors flex-shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <span class="text-sm font-semibold text-slate-200 truncate lg:hidden">@yield('header_title', 'Live Chat')</span>
    </div>

    <div class="flex items-center gap-3">
    {{-- PWA Install Button --}}
    <button type="button" onclick="window.triggerPwaInstall && window.triggerPwaInstall()" title="Install App" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-[#6366F1]/20 hover:bg-[#6366F1]/30 border border-[#6366F1]/40 text-[#818CF8] text-xs font-bold transition-all shadow-sm active:scale-95">
        <svg class="w-4 h-4 text-[#6366F1] animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
        </svg>
        <span class="hidden sm:inline">Install App</span>
    </button>

    {{-- Theme switcher: System / Light / Dark --}}
    <div class="relative" x-data="{ open: false, theme: (localStorage.getItem('theme') || 'system'), set(t) { this.theme = t; localStorage.setItem('theme', t); window.applyTheme && window.applyTheme(); this.open = false; } }">
        <button @click="open = !open" title="Theme" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </button>
        <div x-show="open" @click.away="open = false" style="display:none;"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
             class="absolute right-0 mt-2 w-36 bg-slate-800/95 backdrop-blur-xl border border-slate-700/50 rounded-xl shadow-2xl shadow-black/40 z-50 overflow-hidden py-1">
            <button @click="set('system')" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left hover:bg-slate-700/50 transition-colors" :class="theme === 'system' ? 'text-[#6366F1]' : 'text-slate-300'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17.25V21m6-3.75V21M4 4h16v10H4z"></path></svg> System
            </button>
            <button @click="set('light')" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left hover:bg-slate-700/50 transition-colors" :class="theme === 'light' ? 'text-[#6366F1]' : 'text-slate-300'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.4 6.4l-.7-.7M6.3 6.3l-.7-.7m12.8 0l-.7.7M6.3 17.7l-.7.7M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg> Light
            </button>
            <button @click="set('dark')" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left hover:bg-slate-700/50 transition-colors" :class="theme === 'dark' ? 'text-[#6366F1]' : 'text-slate-300'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg> Dark
            </button>
        </div>
    </div>
    @if(auth()->check())
    {{-- Status Indicator --}}
    @php
        $statusColor = auth()->user()->status === 'online' ? 'emerald' : (auth()->user()->status === 'away' ? 'amber' : 'slate');
        $statusText  = ucfirst(auth()->user()->status ?? 'offline');
    @endphp
    <div class="flex items-center gap-2 px-2.5 py-1 rounded-lg bg-slate-800/60 border border-slate-700/50 text-xs font-semibold text-slate-300">
        <span class="w-2 h-2 rounded-full bg-{{ $statusColor }}-500 flex-shrink-0"></span>
        {{ $statusText }}
    </div>

    {{-- Notifications Bell --}}
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open" class="relative p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-all">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <span id="header-unread-badge" class="absolute top-0.5 right-0.5 inline-flex items-center justify-center rounded-full h-3.5 w-3.5 bg-[#6366F1] text-[8px] font-bold text-white leading-none {{ $totalUnreadInternal > 0 ? '' : 'hidden' }}">
                {{ $totalUnreadInternal > 0 ? $totalUnreadInternal : '' }}
            </span>
        </button>

        <div x-show="open" @click.away="open = false"
             class="absolute right-0 mt-2 w-72 bg-slate-800/95 backdrop-blur-xl border border-slate-700/50 rounded-xl shadow-2xl shadow-black/40 z-50 overflow-hidden"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             style="display:none;">
            <div class="px-4 py-3 border-b border-slate-700/50 flex justify-between items-center">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Messages</span>
                <span class="text-[10px] bg-[#6366F1]/10 text-[#6366F1] px-2 py-0.5 rounded-full border border-[#6366F1]/20 font-bold">{{ $totalUnreadInternal }} New</span>
            </div>
            <div class="max-h-64 overflow-y-auto">
                @forelse($unreadAgents as $ua)
                <a href="{{ route('agent.agents.show', $ua->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-700/50 transition-colors border-b border-slate-700/30 last:border-0 no-underline group">
                    <div class="w-8 h-8 rounded-lg bg-[#6366F1]/10 border border-[#6366F1]/20 flex items-center justify-center text-sm font-bold text-[#6366F1] shrink-0">
                        {{ substr($ua->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-slate-100 truncate m-0">{{ $ua->name }}</p>
                        <p class="text-xs text-[#6366F1] font-medium m-0">{{ $ua->unread_count }} new</p>
                    </div>
                </a>
                @empty
                <div class="px-4 py-6 text-center">
                    <p class="text-xs font-medium text-slate-500">No new messages</p>
                </div>
                @endforelse
            </div>
            <a href="{{ route('agent.agents.index') }}" class="block text-center py-2.5 text-xs font-bold text-[#6366F1] bg-slate-800/80 hover:bg-slate-700 transition-colors border-t border-slate-700/50 no-underline">
                Open Messages &rarr;
            </a>
        </div>
    </div>
    @endif
    </div>
</header>
