<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nexon Live Chat</title>
    <link rel="icon" type="image/webp" href="https://images.nexonpackaging.com/logo.webp">
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="/css/app.css">
    <script src="/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --brand-primary: #F0644B;
            --brand-primary-soft: rgba(240, 100, 75, 0.1);
        }
        body { font-family: 'Inter', sans-serif; }
        .text-brand { color: var(--brand-primary); }
        .bg-brand { background-color: var(--brand-primary); }
        .border-brand { border-color: var(--brand-primary); }
        .selection\:bg-brand *::selection { background-color: var(--brand-primary); color: white; }
    </style>
</head>

<body class="bg-slate-950 text-slate-100 antialiased min-h-screen flex selection:bg-[#F0644B] selection:text-white relative">
    <!-- Background Decor (Matches Login Page) -->
    <div class="fixed top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] right-[-10%] w-[60%] h-[60%] bg-[#F0644B]/10 blur-[150px] rounded-full"></div>
        <div class="absolute bottom-[-10%] left-[-10%] w-[60%] h-[60%] bg-indigo-500/5 blur-[150px] rounded-full"></div>
    </div>

    {{-- Sidebar --}}
    <aside class="w-64 bg-slate-800/50 border-r border-slate-700/50 backdrop-blur-xl flex flex-col min-h-screen sticky top-0 shadow-2xl z-20">
        <div class="h-14 flex items-center px-6 border-b border-slate-700/50">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                <img src="https://images.nexonpackaging.com/logo.webp" alt="Nexon" class="h-8 w-auto object-contain">
                <div class="flex flex-col">
                    <span class="text-[10px] font-black text-[#F0644B] uppercase tracking-[0.2em] leading-none mb-0.5">Nexon</span>
                    <span class="text-xs font-bold text-slate-100 uppercase tracking-wider leading-none">Live Chat</span>
                </div>
            </a>
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
        <div class="p-5 border-t border-slate-700/50 bg-slate-800/30">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#F0644B] to-[#ff8c7a] flex items-center justify-center text-sm font-bold text-white shadow-lg shadow-[#F0644B]/20">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-slate-200 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 font-medium">{{ ucfirst(auth()->user()->role->value) }}</p>
                </div>
            </div>

            {{-- Agent Status Toggle --}}
            @if(auth()->user()->isAgent())
            <div class="mb-3" x-data="{ status: '{{ auth()->user()->status }}' }">
                <select x-model="status"
                    @change="fetch('{{ route('agent.status.update') }}', {
                            method: 'PATCH',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
                            body: JSON.stringify({ status })
                        })"
                    class="w-full text-xs font-medium bg-slate-900/50 border border-slate-700 rounded-lg px-3 py-2 text-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                    <option value="online">🟢 Online</option>
                    <option value="away">🟡 Away</option>
                    <option value="offline">🔴 Offline</option>
                </select>
            </div>
            @endif

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-xs font-semibold text-slate-400 hover:text-white bg-slate-900/40 hover:bg-slate-700 border border-slate-700/50 rounded-lg px-3 py-2.5 transition-all shadow-sm">
                    🚪 Sign Out
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="flex-1 flex flex-col min-h-screen relative z-10">
        {{-- Top Bar --}}
        <header class="h-16 bg-slate-900/80 border-b border-slate-800 flex items-center px-8 backdrop-blur-xl sticky top-0 z-10 shadow-sm">
            <h2 class="text-xl font-semibold text-slate-100 tracking-tight">{{ $title ?? 'Dashboard' }}</h2>
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