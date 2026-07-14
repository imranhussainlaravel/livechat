@php
    $pendingQueueCount = \App\Models\Chat::where('queue_status', \App\Enums\QueueStatus::QUEUED)
        ->whereNull('assigned_agent_id')
        ->where('status', \App\Enums\ChatStatus::PENDING)
        ->count();

    $totalUnreadInternal = \App\Models\InternalMessage::where('receiver_id', auth()->id())
        ->where('is_read', false)
        ->count();
@endphp
<div id="app-sidebar" class="fixed inset-y-0 left-0 -translate-x-full transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 w-72 max-w-[85vw] lg:w-64 lg:max-w-none flex-shrink-0 bg-slate-900 lg:bg-slate-900/40 border-r border-slate-700/50 backdrop-blur-xl text-slate-300 flex flex-col shadow-2xl z-40 lg:z-20">
    <div class="h-14 flex items-center justify-between px-5 border-b border-slate-700/50">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <img src="https://images.nexonpackaging.com/logo.webp" alt="Nexon" class="h-8 w-auto object-contain">
            <div class="flex flex-col">
                <span class="text-[10px] font-black text-[#6366F1] uppercase tracking-[0.2em] leading-none mb-0.5">Nexon</span>
                <span class="text-xs font-bold text-slate-100 uppercase tracking-wider leading-none">Live Chat</span>
            </div>
        </a>
        <button id="sidebar-close-btn" type="button" aria-label="Close menu" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800/80 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav class="flex-1 overflow-y-auto py-3">
        @if(auth()->user()->isAdmin())
        <!-- Admin Menu -->
        <ul class="space-y-0.5">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('admin.dashboard') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>
            </li>
            {{-- My Chats: the admin's own assigned conversations --}}
            <li class="relative">
                <a href="{{ route('agent.chats.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('agent.chats.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                    </svg>
                    <span class="text-sm font-semibold">My Chats</span>
                </a>
                <span id="unread-chat-counter" class="hidden absolute right-5 top-1/2 -translate-y-1/2 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6366F1] text-white leading-none shadow-lg"></span>
            </li>
            {{-- Monitor: oversight view of all agents' chats --}}
            <li class="relative">
                <a href="{{ route('admin.chats.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('admin.chats.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Monitor</span>
                </a>
                <span id="monitor-unread-counter" class="hidden absolute right-5 top-1/2 -translate-y-1/2 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-500 text-white leading-none shadow-lg"></span>
            </li>
            <li>
                <a href="{{ route('admin.queue.index') }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('admin.queue.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-semibold">Pending Queue</span>
                    </div>
                    <span id="sidebar-queue-count" class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white leading-none transition-transform {{ $pendingQueueCount > 0 ? '' : 'hidden' }}">
                        {{ $pendingQueueCount }}
                    </span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.activities.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('admin.activities.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Activities</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.tickets.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('admin.tickets.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    <span class="text-sm font-semibold">Tickets</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.agents.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('admin.agents.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Agents</span>
                </a>
            </li>
            <li>
                <a href="{{ route('agent.agents.index') }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('agent.agents.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="text-sm font-semibold">Other Agents</span>
                    </div>
                    @if($totalUnreadInternal > 0)
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6366F1] text-white leading-none shadow-md">
                        {{ $totalUnreadInternal }}
                    </span>
                    @endif
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('admin.settings.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Settings</span>
                </a>
            </li>
        </ul>
        @else
        <!-- Agent Menu -->
        <ul class="space-y-0.5">
            <li>
                <a href="{{ route('agent.dashboard') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('agent.dashboard') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Dashboard</span>
                </a>
            </li>
            <li class="relative">
                <a href="{{ route('agent.chats.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('agent.chats.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Chats</span>
                </a>
                <span id="unread-chat-counter" class="hidden absolute right-5 top-1/2 -translate-y-1/2 inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6366F1] text-white leading-none shadow-lg"></span>
            </li>
            <li class="relative">
                <a href="{{ route('agent.queue.index') }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('agent.queue.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="text-sm font-semibold">Pending Queue</span>
                    </div>
                    <span id="sidebar-queue-count" class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-500 text-white leading-none transition-transform {{ $pendingQueueCount > 0 ? '' : 'hidden' }}">
                        {{ $pendingQueueCount }}
                    </span>
                </a>
            </li>
            <li>
                <a href="{{ route('agent.followups.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('agent.followups.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Followups</span>
                </a>
            </li>
            <li>
                <a href="{{ route('agent.tickets.index') }}" class="flex items-center px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('agent.tickets.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    <span class="text-sm font-semibold">Tickets</span>
                </a>
            </li>
            <li>
                <a href="{{ route('agent.agents.index') }}" class="flex items-center justify-between px-4 py-2.5 hover:bg-slate-700/50 rounded-xl mx-2 transition-all duration-200 group {{ request()->routeIs('agent.agents.*') ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-400 hover:text-slate-200' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                    <span class="text-sm font-semibold">Other Agents</span>
                    </div>
                    @if($totalUnreadInternal > 0)
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 rounded-full text-[10px] font-bold bg-[#6366F1] text-white leading-none shadow-md">
                        {{ $totalUnreadInternal }}
                    </span>
                    @endif
                </a>
            </li>
        </ul>
        @endif
    </nav>

    {{-- User Profile Section --}}
    <div class="mt-auto p-4 border-t border-slate-700/50 bg-slate-900/40">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#6366F1] to-[#818CF8] flex items-center justify-center text-sm font-bold text-white shadow-lg shadow-[#6366F1]/20 shrink-0">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-slate-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-500 font-medium uppercase tracking-wider">{{ auth()->user()->role->value }}</p>
                </div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="p-2 rounded-lg text-slate-500 hover:text-rose-500 hover:bg-rose-500/10 transition-all duration-300 group" title="Sign Out">
                    <svg class="w-5 h-5 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>