<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'LiveChat' }} — LiveChat</title>
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js"></script>
</head>

<body class="bg-gray-950 text-gray-100 min-h-screen flex">

    {{-- Sidebar --}}
    <aside class="w-64 bg-gray-900 border-r border-gray-800 flex flex-col min-h-screen sticky top-0">
        <div class="p-5 border-b border-gray-800">
            <h1 class="text-xl font-bold bg-gradient-to-r from-indigo-400 to-purple-400 bg-clip-text text-transparent">
                💬 LiveChat
            </h1>
            <p class="text-xs text-gray-500 mt-1">{{ auth()->user()->isAdmin() ? 'Admin Panel' : 'Agent Panel' }}</p>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            @if(auth()->user()->isAdmin())
            <x-nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                📊 Dashboard
            </x-nav-link>
            <x-nav-link href="{{ route('admin.chats.index') }}" :active="request()->routeIs('admin.chats.*')">
                💬 All Chats
            </x-nav-link>
            <x-nav-link href="{{ route('admin.agents.index') }}" :active="request()->routeIs('admin.agents.*')">
                👥 Agents
            </x-nav-link>
            <x-nav-link href="{{ route('admin.activities.index') }}" :active="request()->routeIs('admin.activities.*')">
                📋 Activity Log
            </x-nav-link>
            <x-nav-link href="{{ route('admin.reports.index') }}" :active="request()->routeIs('admin.reports.*')">
                📈 Reports
            </x-nav-link>
            <x-nav-link href="{{ route('admin.settings.index') }}" :active="request()->routeIs('admin.settings.*')">
                ⚙️ Settings
            </x-nav-link>
            @else
            <x-nav-link href="{{ route('agent.dashboard') }}" :active="request()->routeIs('agent.dashboard')">
                📊 Dashboard
            </x-nav-link>
            <x-nav-link href="{{ route('agent.chats.index') }}" :active="request()->routeIs('agent.chats.*')">
                💬 My Chats
            </x-nav-link>
            <x-nav-link href="{{ route('agent.followups.index') }}" :active="request()->routeIs('agent.followups.*')">
                🔔 Follow-ups
            </x-nav-link>
            @endif
        </nav>

        {{-- User Info --}}
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-sm font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role->value) }}</p>
                </div>
            </div>

            {{-- Agent Status Toggle --}}
            @if(auth()->user()->isAgent())
            <div class="mt-3" x-data="{ status: '{{ auth()->user()->status }}' }">
                <select x-model="status"
                    @change="fetch('{{ route('agent.status.update') }}', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ status })
                        })"
                    class="w-full text-xs bg-gray-800 border border-gray-700 rounded px-2 py-1.5 text-gray-300 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="online">🟢 Online</option>
                    <option value="away">🟡 Away</option>
                    <option value="offline">🔴 Offline</option>
                </select>
            </div>
            @endif

            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="w-full text-xs text-gray-400 hover:text-white bg-gray-800 hover:bg-gray-700 rounded px-3 py-2 transition">
                    🚪 Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-screen">
        {{-- Top Bar --}}
        <header class="h-14 bg-gray-900/50 border-b border-gray-800 flex items-center px-6 backdrop-blur-sm sticky top-0 z-10">
            <h2 class="text-lg font-semibold text-gray-200">{{ $title ?? 'Dashboard' }}</h2>
            <div class="ml-auto flex items-center gap-4">
                @if(session('success'))
                <span class="text-sm text-emerald-400 animate-pulse">✓ {{ session('success') }}</span>
                @endif
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-6">
            @if($errors->any())
            <div class="mb-4 p-4 bg-red-900/30 border border-red-800 rounded-lg text-sm text-red-300">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>

</body>

</html>