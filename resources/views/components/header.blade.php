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

        @if(auth()->user()->isAgent())
        <div>
            <span class="inline-flex items-center gap-1 py-0.5 px-2.5 rounded-full text-[10px] font-medium bg-green-100 text-green-800">
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