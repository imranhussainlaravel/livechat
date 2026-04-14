<div class="w-64 flex-shrink-0 bg-gray-900 border-r text-gray-200 border-gray-800 flex flex-col">
    <div class="h-16 flex items-center justify-center border-b border-gray-800">
        <span class="text-xl font-bold italic tracking-wide text-white">{{ app(\App\Services\SettingsService::class)->get('widget_name', 'LiveChat') }}</span>
    </div>

    <nav class="flex-1 overflow-y-auto py-4">
        @if(auth()->user()->isAdmin())
        <!-- Admin Menu -->
        <ul class="space-y-1">
            <li>
                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.chats.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('admin.chats.*') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                    </svg>
                    Conversations
                </a>
            </li>
            <li>
                <a href="{{ route('admin.queue.index') }}" class="flex items-center justify-between px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('admin.queue.*') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pending Queue
                    </div>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.activities.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('admin.activities.*') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Activities
                </a>
            </li>
            <li>
                <!-- Placeholder Route for Records, needs mapping -->
                <a href="#" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                    </svg>
                    Records
                </a>
            </li>
            <li>
                <a href="{{ route('admin.agents.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('admin.agents.*') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Agents
                </a>
            </li>
            <li>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('admin.settings.*') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Settings
                </a>
            </li>
        </ul>
        @else
        <!-- Agent Menu -->
        <ul class="space-y-1">
            <li>
                <a href="{{ route('agent.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('agent.dashboard') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('agent.chats.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('agent.chats.*') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                    </svg>
                    Chats
                </a>
            </li>
            <li>
                <a href="{{ route('agent.queue.index') }}" class="flex items-center justify-between px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('agent.queue.*') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Pending Queue
                    </div>
                </a>
            </li>
            <li>
                <a href="{{ route('agent.followups.index') }}" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2 {{ request()->routeIs('agent.followups.*') ? 'bg-gray-800 text-blue-400' : '' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Followups
                </a>
            </li>
            <li>
                <!-- Placeholder for Tickets if applicable -->
                <a href="#" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    Tickets
                </a>
            </li>
            <li>
                <!-- Placeholder for Other Agents -->
                <a href="#" class="flex items-center px-6 py-3 hover:bg-gray-800 rounded mx-2">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    Other Agents
                </a>
            </li>
        </ul>
        @endif
    </nav>
</div>