<header class="h-12 flex-shrink-0 bg-white border-b flex items-center justify-between px-4 shadow-sm z-10">
    <div class="flex items-center gap-4">
        <!-- Optional Mobile Menu Toggle -->
        <button class="md:hidden text-gray-500 hover:text-gray-700">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        <h2 class="text-lg font-semibold text-gray-800">@yield('header_title', 'Dashboard')</h2>
    </div>

    <div class="flex items-center gap-4">

        @if(auth()->check())
        <div class="flex items-center gap-3">
            {{-- Internal Messages Notification --}}
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="text-gray-500 hover:text-gray-700 focus:outline-none relative p-1.5 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                    @if($totalUnreadInternal > 0)
                    <span class="absolute top-0 right-0 inline-flex items-center justify-center px-1 py-0.5 rounded-full text-[9px] font-bold bg-rose-500 text-white leading-none border-2 border-white">
                        {{ $totalUnreadInternal }}
                    </span>
                    @endif
                </button>

                {{-- Dropdown --}}
                <div x-show="open" @click.away="open = false" 
                    class="absolute right-0 mt-3 w-64 bg-white rounded-lg shadow-xl ring-1 ring-black ring-opacity-5 focus:outline-none z-50 overflow-hidden"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="transform opacity-0 scale-95"
                    x-transition:enter-end="transform opacity-100 scale-100"
                    style="display: none;">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-100 flex justify-between items-center">
                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider">Colleague Messages</span>
                    </div>
                    <div class="max-h-60 overflow-y-auto underline-none">
                        @forelse($unreadAgents as $ua)
                        <a href="{{ route('agent.agents.show', $ua->id) }}" class="flex items-center gap-3 px-4 py-3 hover:bg-gray-50 transition border-b border-gray-50 last:border-0 no-underline">
                            <div class="w-8 h-8 rounded-full bg-blue-50 flex items-center justify-center text-xs font-bold text-blue-600 shrink-0">
                                {{ substr($ua->name, 0, 1) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate m-0">{{ $ua->name }}</p>
                                <p class="text-xs text-blue-600 font-medium m-0">{{ $ua->unread_count }} new message{{ $ua->unread_count > 1 ? 's' : '' }}</p>
                            </div>
                        </a>
                        @empty
                        <div class="px-4 py-8 text-center text-gray-400">
                            <svg class="mx-auto h-8 w-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                            </svg>
                            <p class="text-xs">No new messages from team.</p>
                        </div>
                        @endforelse
                    </div>
                    <a href="{{ route('agent.agents.index') }}" class="block text-center py-2.5 text-[11px] font-bold text-blue-600 bg-gray-50 hover:bg-blue-50 transition uppercase tracking-wider border-t border-gray-100 no-underline">
                        View All Agents
                    </a>
                </div>
            </div>

            {{-- Status Dot --}}
            <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800 shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span>
                Online
            </span>
        </div>
        @endif

        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center gap-1.5 focus:outline-none">
                <div class="w-7 h-7 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xs uppercase">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <span class="text-xs font-medium text-gray-700 hidden sm:block">{{ auth()->user()->name }}</span>
                <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>

            <!-- Dropdown menu -->
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                <div class="px-4 py-2 border-b">
                    <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                    <p class="text-sm text-gray-500 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 mt-1">
                        Sign out
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>