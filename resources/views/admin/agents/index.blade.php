@extends('layouts.app')
@section('header_title', 'Agent Management')

@section('content')

<!-- Header -->
<div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-100 tracking-tight">Agent Details</h1>
    <p class="text-gray-400 mt-2 text-sm">Manage your support agents, permissions, and availability.</p>
</div>

<!-- New agent credentials card -->
@if(session('new_agent'))
@php $na = session('new_agent'); @endphp
<div id="new-agent-card" class="bg-emerald-900/20 border border-emerald-700/40 rounded-xl shadow-lg p-6 mb-8 relative overflow-hidden">
    <div class="absolute -top-10 -right-10 w-40 h-40 bg-emerald-500/10 blur-3xl rounded-full pointer-events-none"></div>
    <div class="relative">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center border border-emerald-500/30">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h3 class="text-base font-semibold text-emerald-400">Agent created — share these login details</h3>
            </div>
            <button type="button" onclick="document.getElementById('new-agent-card').remove()" class="text-gray-400 hover:text-white shrink-0 transition-colors" title="Dismiss">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm mb-5 p-4 rounded-lg bg-gray-800/50 border border-gray-700/50">
            <div><span class="text-gray-500 block text-xs mb-0.5">Name</span> <span class="text-gray-100 font-medium">{{ $na['name'] }}</span></div>
            <div><span class="text-gray-500 block text-xs mb-0.5">Login URL</span> <a href="{{ $na['login_url'] }}" class="text-indigo-400 font-medium break-all hover:text-indigo-300 transition-colors">{{ $na['login_url'] }}</a></div>
            <div><span class="text-gray-500 block text-xs mb-0.5">Email</span> <span class="text-gray-100 font-medium">{{ $na['email'] }}</span></div>
            <div><span class="text-gray-500 block text-xs mb-0.5">Password</span> <span class="text-gray-100 font-medium font-mono">{{ $na['password'] }}</span></div>
        </div>
        <div class="flex items-center gap-4">
            <button type="button" id="copy-agent-details"
                data-details="Hi {{ $na['name'] }}, your account is ready.&#10;Login URL: {{ $na['login_url'] }}&#10;Email: {{ $na['email'] }}&#10;Password: {{ $na['password'] }}&#10;Please change your password after first login."
                class="inline-flex items-center gap-2 py-2 px-4 rounded-lg text-sm font-medium text-emerald-400 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 hover:text-emerald-300 transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
                Copy details
            </button>
            <span class="text-xs text-gray-500">These details won't be shown again after you leave this page.</span>
        </div>
    </div>
</div>
@endif

<!-- Agents Directory Card -->
<div class="bg-gray-900 border border-gray-800 rounded-xl shadow-lg overflow-hidden flex flex-col mb-8">
    
    <!-- Card Toolbar -->
    <div class="px-6 py-5 border-b border-gray-800 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-100">Agents</h2>
            <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full border border-gray-700 bg-gray-800 text-gray-300 text-xs font-medium">
                {{ $agents->total() ?? count($agents) }} Agents
            </span>
        </div>
        <div class="flex items-center gap-3 w-full sm:w-auto">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" placeholder="Search agents..." class="block w-full pl-9 pr-3 py-2 border border-gray-700 rounded-lg leading-5 bg-gray-800 text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm transition-colors">
            </div>
            <button class="inline-flex items-center gap-2 px-3 py-2 border border-gray-700 shadow-sm text-sm font-medium rounded-lg text-gray-300 bg-gray-800 hover:bg-gray-700 focus:outline-none transition-colors">
                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filter
            </button>
            <button type="button" onclick="document.getElementById('add-agent-modal').classList.remove('hidden')" class="inline-flex items-center gap-2 px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors whitespace-nowrap">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Agent
            </button>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-800 text-left">
            <thead class="bg-gray-800/50">
                <tr>
                    <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Agent</th>
                    <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">Role</th>
                    <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider">CRM Access / Scope</th>
                    <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Max Chats</th>
                    <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Status</th>
                    <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-center">Live Chat Access</th>
                    <th scope="col" class="px-6 py-4 text-xs font-semibold text-gray-400 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800 bg-gray-900">
                @forelse($agents as $agent)
                @php
                    $roleRaw = $agent->role;
                    if ($roleRaw instanceof \UnitEnum) {
                        $roleRaw = $roleRaw instanceof \BackedEnum ? $roleRaw->value : $roleRaw->name;
                    }
                    $role = ucfirst((string) ($roleRaw ?? 'Agent'));
                    
                    $scopeLabel = 'Full Cycle';
                    if ($agent->work_scope) {
                        if (is_string($agent->work_scope)) {
                            $scopeEnum = \App\Enums\WorkScope::tryFrom($agent->work_scope);
                            if ($scopeEnum) $scopeLabel = $scopeEnum->getLabel();
                        } elseif (is_object($agent->work_scope) && method_exists($agent->work_scope, 'getLabel')) {
                            $scopeLabel = $agent->work_scope->getLabel();
                        }
                    }
                @endphp
                <tr class="hover:bg-gray-800/50 transition-colors">
                    
                    <!-- Agent Info -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-indigo-900/30 flex items-center justify-center text-sm font-bold text-indigo-400 shrink-0 border border-indigo-500/20">
                                {{ strtoupper(substr($agent->name, 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="text-sm font-medium text-gray-100">{{ $agent->name }}</span>
                                <span class="text-xs text-gray-500 mt-0.5">{{ $agent->email }}</span>
                            </div>
                        </div>
                    </td>
                    
                    <!-- Role -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-300 font-medium">{{ $role }}</span>
                    </td>
                    
                    <!-- Work Scope -->
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm text-gray-300 font-medium">{{ $scopeLabel }}</span>
                    </td>
                    
                    <!-- Max Chats -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <span class="text-sm font-medium text-gray-200 bg-gray-800 px-2.5 py-1 rounded-md border border-gray-700 shadow-sm">
                            {{ $agent->max_chats ?? config('livechat.default_max_chats', 5) }}
                        </span>
                    </td>
                    
                    <!-- Status -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        @php
                        $statusColors = [
                            'online' => 'bg-emerald-900/30 text-emerald-400 border-emerald-500/20',
                            'away' => 'bg-amber-900/30 text-amber-400 border-amber-500/20',
                            'offline' => 'bg-gray-800/50 text-gray-400 border-gray-700'
                        ];
                        $statusVal = $agent->status ?? 'offline';
                        $statusClass = $statusColors[$statusVal] ?? $statusColors['offline'];
                        $dotClass = $statusVal === 'online' ? 'bg-emerald-400 shadow-[0_0_8px_rgba(52,211,153,0.8)]' : ($statusVal === 'away' ? 'bg-amber-400' : 'bg-gray-500');
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border {{ $statusClass }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $dotClass }}"></span>
                            {{ ucfirst($statusVal) }}
                        </span>
                    </td>
                    
                    <!-- Live Chat Toggle -->
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <form method="POST" action="{{ route('admin.agents.toggleLiveChat', $agent->id) }}" class="inline-flex items-center justify-center">
                            @csrf
                            @method('PATCH')
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="can_live_chat" class="sr-only peer" onchange="this.form.submit()" {{ $agent->can_live_chat ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-700 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-emerald-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-emerald-500"></div>
                                <span class="ml-3 w-6 text-left text-sm font-medium {{ $agent->can_live_chat ? 'text-gray-100' : 'text-gray-500' }}">
                                    {{ $agent->can_live_chat ? 'On' : 'Off' }}
                                </span>
                            </label>
                        </form>
                    </td>
                    
                    <!-- Actions -->
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end gap-2">
                            <button class="text-gray-400 hover:text-white transition-colors p-1.5 rounded-md hover:bg-gray-800" title="Edit Agent">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            @if($agent->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.agents.destroy', $agent->id) }}" onsubmit="return confirm('Remove this agent? This action cannot be undone.')" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-400 hover:text-red-400 transition-colors p-1.5 rounded-md hover:bg-red-900/20" title="Delete Agent">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                            @else
                            <span class="text-xs text-gray-500 italic px-2">You</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-sm text-gray-400">No agents registered in the system.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if(isset($agents) && $agents->hasPages())
<div class="mt-6 flex justify-center">
    {{ $agents->links() }}
</div>
@endif

<!-- Add Agent Modal overlay -->
<div id="add-agent-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm transition-opacity" onclick="document.getElementById('add-agent-modal').classList.add('hidden')"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel -->
            <div class="relative transform overflow-hidden rounded-2xl bg-gray-900 border border-gray-800 text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                
                <div class="px-6 py-4 border-b border-gray-800 flex justify-between items-center bg-gray-900">
                    <h3 class="text-lg font-semibold text-gray-100 flex items-center gap-2" id="modal-title">
                        <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        Add New Agent
                    </h3>
                    <button type="button" onclick="document.getElementById('add-agent-modal').classList.add('hidden')" class="text-gray-400 hover:text-white transition-colors bg-gray-800 hover:bg-gray-700 p-1.5 rounded-md">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <form method="POST" action="{{ route('admin.agents.store') }}" class="flex flex-col">
                    @csrf
                    <div class="px-6 py-6 space-y-5">
                        
                        <!-- Name & Email -->
                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-300 mb-1.5">Full Name</label>
                                <input type="text" name="name" id="name" placeholder="e.g. Jane Doe" required
                                    class="block w-full rounded-lg border border-gray-700 bg-gray-800 py-2 px-3 text-gray-100 placeholder-gray-500 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-colors outline-none">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-300 mb-1.5">Email Address</label>
                                <input type="email" name="email" id="email" placeholder="jane@company.com" required
                                    class="block w-full rounded-lg border border-gray-700 bg-gray-800 py-2 px-3 text-gray-100 placeholder-gray-500 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-colors outline-none">
                            </div>
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-300 mb-1.5">Password</label>
                            <div class="relative">
                                <input type="password" name="password" id="password" placeholder="Min 8 characters" required minlength="8"
                                    class="block w-full rounded-lg border border-gray-700 bg-gray-800 py-2 pl-3 pr-10 text-gray-100 placeholder-gray-500 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-colors outline-none">
                                <button type="button" id="toggle-password" tabindex="-1" title="Show password"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-white transition-colors">
                                    <svg id="pw-eye" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <svg id="pw-eye-off" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Role & Scope -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="role" class="block text-sm font-medium text-gray-300 mb-1.5">Role</label>
                                <select name="role" id="role"
                                    class="block w-full rounded-lg border border-gray-700 bg-gray-800 py-2 px-3 text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-colors outline-none">
                                    <option value="agent">Agent</option>
                                    <option value="production">Production</option>
                                </select>
                            </div>
                            <div>
                                <label for="work_scope" class="block text-sm font-medium text-gray-300 mb-1.5">CRM Work Scope</label>
                                <select name="work_scope" id="work_scope"
                                    class="block w-full rounded-lg border border-gray-700 bg-gray-800 py-2 px-3 text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 sm:text-sm transition-colors outline-none">
                                    @foreach(\App\Enums\WorkScope::cases() as $ws)
                                    <option value="{{ $ws->value }}" {{ $ws->value === 'full_cycle' ? 'selected' : '' }}>{{ $ws->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Permissions Toggle -->
                        <div class="bg-gray-800 border border-gray-700 rounded-xl p-4 mt-2 flex items-start gap-3 shadow-inner">
                            <div class="flex items-center h-5 mt-0.5">
                                <input type="hidden" name="can_live_chat" value="0">
                                <input type="checkbox" name="can_live_chat" id="can_live_chat" value="1" checked
                                    class="h-4 w-4 rounded border-gray-600 bg-gray-900 text-indigo-600 focus:ring-indigo-500 focus:ring-offset-gray-800">
                            </div>
                            <div>
                                <label for="can_live_chat" class="text-sm font-medium text-gray-100 block">Can access Live Chat</label>
                                <p class="text-xs text-gray-500 mt-1">Allow this user to accept and respond to live customer chats.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="px-6 py-4 bg-gray-800/50 border-t border-gray-800 flex justify-end gap-3 rounded-b-xl">
                        <button type="button" onclick="document.getElementById('add-agent-modal').classList.add('hidden')" class="inline-flex justify-center rounded-lg border border-gray-700 bg-gray-900 px-4 py-2.5 text-sm font-medium text-gray-300 shadow-sm hover:bg-gray-800 focus:outline-none transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="inline-flex justify-center rounded-lg border border-transparent bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-gray-900 transition-colors">
                            Create Agent
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Document-level delegation so it survives Turbo body swaps
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

            // Copy new-agent credentials
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