@extends('layouts.app')
@section('header_title', 'Agent Management')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-100">Agent Details</h1>
        <p class="text-gray-500 mt-1">Add, remove, and monitor support agents.</p>
    </div>
</div>

{{-- New-agent credentials card — shown once, right after creation, so the
     admin can copy the login details + URL and send them to the agent. --}}
@if(session('new_agent'))
@php $na = session('new_agent'); @endphp
<div id="new-agent-card" class="bg-emerald-900/20 border border-emerald-700/40 rounded-lg shadow-sm p-6 mb-8">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <h3 class="text-base font-semibold text-gray-100">Agent created — share these login details</h3>
        </div>
        <button type="button" onclick="document.getElementById('new-agent-card').remove()" class="text-gray-400 hover:text-white shrink-0" title="Dismiss">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-2 text-sm mb-4">
        <div><span class="text-gray-500">Name:</span> <span class="text-gray-100 font-medium">{{ $na['name'] }}</span></div>
        <div><span class="text-gray-500">Login URL:</span> <a href="{{ $na['login_url'] }}" class="text-blue-400 font-medium break-all">{{ $na['login_url'] }}</a></div>
        <div><span class="text-gray-500">Email:</span> <span class="text-gray-100 font-medium">{{ $na['email'] }}</span></div>
        <div><span class="text-gray-500">Password:</span> <span class="text-gray-100 font-medium font-mono">{{ $na['password'] }}</span></div>
    </div>
    <div class="flex items-center gap-3">
        <button type="button" id="copy-agent-details"
            data-details="Hi {{ $na['name'] }}, your account is ready.&#10;Login URL: {{ $na['login_url'] }}&#10;Email: {{ $na['email'] }}&#10;Password: {{ $na['password'] }}&#10;Please change your password after first login."
            class="inline-flex items-center gap-2 py-2 px-4 rounded-md text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            Copy details
        </button>
        <span class="text-xs text-gray-500">These details won't be shown again after you leave this page.</span>
    </div>
</div>
@endif

{{-- Create Agent Form --}}
<div class="bg-gray-900 border border-gray-800 rounded-lg shadow-sm p-6 mb-8">
    <h3 class="text-base font-semibold text-gray-100 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
        </svg>
        Add New Agent
    </h3>
    <form method="POST" action="{{ route('admin.agents.store') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium leading-6 text-gray-100 mb-1">Full Name</label>
            <input type="text" name="name" id="name" placeholder="e.g. Jane Doe" required
                class="block w-full rounded-md border-0 py-1.5 bg-gray-800 text-gray-100 shadow-sm ring-1 ring-inset ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
        </div>
        <div>
            <label for="email" class="block text-sm font-medium leading-6 text-gray-100 mb-1">Email Address</label>
            <input type="email" name="email" id="email" placeholder="jane@company.com" required
                class="block w-full rounded-md border-0 py-1.5 bg-gray-800 text-gray-100 shadow-sm ring-1 ring-inset ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
        </div>
        <div>
            <label for="password" class="block text-sm font-medium leading-6 text-gray-100 mb-1">Password</label>
            <div class="relative">
                <input type="password" name="password" id="password" placeholder="Min 8 characters" required minlength="8"
                    class="block w-full rounded-md border-0 py-1.5 pr-10 bg-gray-800 text-gray-100 shadow-sm ring-1 ring-inset ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                <button type="button" id="toggle-password" tabindex="-1" title="Show password"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-100">
                    <svg id="pw-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <svg id="pw-eye-off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                </button>
            </div>
        </div>
        <div>
            <label for="role" class="block text-sm font-medium leading-6 text-gray-100 mb-1">Role</label>
            <select name="role" id="role"
                class="block w-full rounded-md border-0 py-2 bg-gray-800 text-gray-100 shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                <option value="agent">Agent</option>
                <option value="production">Production</option>
            </select>
        </div>
        <div>
            <label for="work_scope" class="block text-sm font-medium leading-6 text-gray-100 mb-1">CRM Work Scope</label>
            <select name="work_scope" id="work_scope"
                class="block w-full rounded-md border-0 py-2 bg-gray-800 text-gray-100 shadow-sm ring-1 ring-inset ring-gray-700 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm">
                @foreach(\App\Enums\WorkScope::cases() as $ws)
                <option value="{{ $ws->value }}" {{ $ws->value === 'full_cycle' ? 'selected' : '' }}>{{ $ws->getLabel() }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2 pt-6">
            <input type="hidden" name="can_live_chat" value="0">
            <input type="checkbox" name="can_live_chat" id="can_live_chat" value="1" checked
                class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-blue-600 focus:ring-blue-500">
            <label for="can_live_chat" class="text-sm font-medium text-gray-100">Can access Live Chat</label>
        </div>
        <div>
            <button type="submit" class="w-full flex justify-center items-center gap-2 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Create Agent
            </button>
        </div>
    </form>
</div>

{{-- Agents List --}}
<div class="bg-gray-900 border border-gray-800 rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-800 bg-gray-800 flex justify-between items-center">
        <h3 class="text-sm font-semibold text-gray-100">Active Directory</h3>
        <span class="inline-flex items-center rounded-full bg-blue-900/30 px-2.5 py-0.5 text-xs font-medium text-blue-400 ring-1 ring-inset ring-blue-700/10">
            Total: {{ $agents->total() ?? count($agents) }}
        </span>
    </div>

    <div class="divide-y divide-gray-800">
        @forelse($agents as $agent)
        <div class="flex flex-col sm:flex-row sm:items-center gap-4 px-6 py-4 hover:bg-gray-800 transition">

            {{-- Avatar & Info --}}
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <div class="relative">
                    <div class="w-10 h-10 rounded-full bg-blue-900/30 flex items-center justify-center text-sm font-bold text-blue-400 shrink-0">
                        {{ strtoupper(substr($agent->name, 0, 1)) }}
                    </div>
                    <span class="absolute bottom-0 right-0 block h-2.5 w-2.5 rounded-full ring-2 ring-white
                                {{ $agent->status === 'online' ? 'bg-green-400' : ($agent->status === 'away' ? 'bg-yellow-400' : 'bg-gray-300') }}">
                    </span>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-100 truncate">{{ $agent->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ $agent->email }}</p>
                </div>
            </div>

            {{-- Metrics & Actions --}}
            <div class="flex items-center gap-6 shrink-0 sm:ml-4">
                <div class="hidden sm:block text-right">
                    <p class="text-xs font-medium text-gray-500">Max Chats</p>
                    <p class="text-sm font-semibold text-gray-100">{{ $agent->max_chats ?? config('livechat.default_max_chats', 5) }}</p>
                </div>

                <div class="w-24">
                    @php
                    $statusColors = [
                    'online' => 'bg-green-900/30 text-green-300',
                    'away' => 'bg-yellow-900/30 text-yellow-300',
                    'offline' => 'bg-gray-800 text-gray-200'
                    ];
                    $statusVal = $agent->status ?? 'offline';
                    $statusClass = $statusColors[$statusVal] ?? $statusColors['offline'];
                    @endphp
                    <span class="inline-flex items-center justify-center w-full px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                        {{ ucfirst($statusVal) }}
                    </span>
                </div>

                {{-- Live Chat access toggle --}}
                <div class="w-28 text-center">
                    <form method="POST" action="{{ route('admin.agents.toggleLiveChat', $agent->id) }}">
                        @csrf
                        @method('PATCH')
                        @if($agent->can_live_chat)
                        <button type="submit" title="Click to disable Live Chat" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-900/30 text-green-300 hover:bg-green-900/50 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-400"></span> Live Chat on
                        </button>
                        @else
                        <button type="submit" title="Click to enable Live Chat" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-gray-800 text-gray-400 hover:bg-gray-700 transition">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> CRM only
                        </button>
                        @endif
                    </form>
                </div>

                <div class="border-l border-gray-700 pl-4">
                    @if($agent->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.agents.destroy', $agent->id) }}" onsubmit="return confirm('Remove this agent? This action cannot be undone.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-red-600 transition p-1 rounded-md hover:bg-red-50" title="Remove Agent">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                    @else
                    <span class="text-xs text-gray-400 italic px-2">You</span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <p class="text-sm text-gray-500">No agents registered in the system.</p>
        </div>
        @endforelse
    </div>
</div>

@if(isset($agents) && $agents->hasPages())
<div class="mt-6 flex justify-center">
    {{ $agents->links() }}
</div>
@endif

@push('scripts')
<script>
    // Document-level delegation so it survives Turbo body swaps (bind once).
    if (!window._agentFormBound) {
        window._agentFormBound = true;

        document.addEventListener('click', function (e) {
            // Show / hide password
            if (e.target.closest('#toggle-password')) {
                var input = document.getElementById('password');
                var eye = document.getElementById('pw-eye');
                var eyeOff = document.getElementById('pw-eye-off');
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                if (eye) eye.classList.toggle('hidden', show);
                if (eyeOff) eyeOff.classList.toggle('hidden', !show);
                e.target.closest('#toggle-password').title = show ? 'Hide password' : 'Show password';
                return;
            }

            // Copy new-agent credentials + login URL
            var copyBtn = e.target.closest('#copy-agent-details');
            if (copyBtn) {
                var text = copyBtn.getAttribute('data-details') || '';
                var done = function () { if (window.showToast) showToast('Details copied — paste to send to the agent.'); };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
                } else {
                    fallbackCopy(text, done);
                }
            }
        });

        function fallbackCopy(text, cb) {
            var ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); cb(); } catch (err) {}
            document.body.removeChild(ta);
        }
    }
</script>
@endpush

@endsection