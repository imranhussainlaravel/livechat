@props(['chat', 'agents', 'timeline' => []])

<div class="space-y-4 p-4">
    {{-- 1. Visitor Info Card (inline-editable) --}}
    <div class="bg-slate-800/50 rounded-xl border border-slate-700/50 p-4"
         x-data="{
            editing: false,
            saving:  false,
            name:    '{{ addslashes($chat->visitor->name ?? '') }}',
            email:   '{{ addslashes($chat->visitor->email ?? '') }}',
            error:   '',
            updateUrl: '{{ route('agent.visitor.update', $chat->visitor->id ?? 0) }}',
            csrfToken: '{{ csrf_token() }}',
            get initial() { return this.name.charAt(0).toUpperCase() || 'V'; },
            async save() {
                this.saving = true; this.error = '';
                try {
                    const res = await fetch(this.updateUrl, {
                        method: 'PATCH',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': this.csrfToken },
                        body: JSON.stringify({ name: this.name, email: this.email }),
                    });
                    const json = await res.json();
                    if (!res.ok) {
                        this.error = json.message || 'Failed to save.';
                        if (window.showToast) window.showToast(this.error, 'error');
                    } else {
                        this.editing = false;
                        if (window.showToast) window.showToast('Visitor details updated.');
                        const n = document.getElementById('visitor-header-name');
                        const i = document.getElementById('visitor-header-initial');
                        const e = document.getElementById('visitor-header-email');
                        if (n) n.textContent = this.name || 'Visitor';
                        if (i) i.textContent = this.initial;
                        if (e) e.textContent = this.email || '';
                    }
                } catch(e) { this.error = 'Network error.';
                } finally { this.saving = false; }
            },
            cancel() { this.editing = false; this.error = ''; }
         }">

        <div class="flex items-center gap-3 mb-3 pb-3 border-b border-slate-700/40">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#6366F1] to-[#818CF8] flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                 x-text="initial"></div>

            <div class="flex-1 min-w-0">
                <template x-if="!editing">
                    <div>
                        <h3 class="text-sm font-bold text-slate-100 truncate" x-text="name || 'Anonymous'"></h3>
                        <p class="text-[11px] text-slate-500 truncate" x-text="email || 'No email'"></p>
                    </div>
                </template>
                <template x-if="editing">
                    <div class="space-y-1.5">
                        <input type="text" x-model="name" placeholder="Visitor name"
                               @keydown.enter="save()" @keydown.escape="cancel()"
                               class="w-full bg-slate-900/70 border border-slate-600 focus:border-[#6366F1] rounded-lg px-2.5 py-1.5 text-xs text-slate-200 outline-none" />
                        <input type="email" x-model="email" placeholder="Email address"
                               @keydown.enter="save()" @keydown.escape="cancel()"
                               class="w-full bg-slate-900/70 border border-slate-600 focus:border-[#6366F1] rounded-lg px-2.5 py-1.5 text-xs text-slate-200 outline-none" />
                        <p x-show="error" x-text="error" class="text-[10px] text-red-400"></p>
                    </div>
                </template>
            </div>

            <div class="flex-shrink-0">
                <template x-if="!editing">
                    <button @click="editing = true" class="p-1.5 rounded-lg text-slate-500 hover:text-[#6366F1] hover:bg-slate-700/50 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                </template>
                <template x-if="editing">
                    <div class="flex gap-1">
                        <button @click="save()" :disabled="saving" class="p-1.5 rounded-lg text-green-400 hover:bg-slate-700/50 disabled:opacity-50">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button @click="cancel()" class="p-1.5 rounded-lg text-slate-500 hover:bg-slate-700/50">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
            </div>
        </div>

        <div class="space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Chat ID</span>
                <span class="text-xs font-semibold text-slate-300">#{{ $chat->id }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Status</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $chat->status->value === 'active' ? 'bg-[#6366F1]/10 text-[#6366F1] border-[#6366F1]/20' : 'bg-slate-700/30 text-slate-400 border-slate-600/30' }}">
                    {{ ucfirst($chat->status->value) }}
                </span>
            </div>
            @if($chat->assigned_agent_id)
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Agent</span>
                <span class="text-xs font-semibold text-slate-300">{{ $chat->agent->name ?? 'Unknown' }}</span>
            </div>
            @endif
        </div>
    </div>

    {{-- 2. Chat Metadata (if any) --}}
    @if(isset($chat->visitor->metadata['browser']))
    <div class="bg-slate-800/50 rounded-xl border border-slate-700/50 p-4">
        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
            <svg class="w-3 h-3 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            System Info
        </h4>
        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-medium text-slate-500">Browser</span>
                <span class="text-[11px] font-semibold text-slate-300">{{ $chat->visitor->metadata['browser'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-medium text-slate-500">Device</span>
                <span class="text-[11px] font-semibold text-slate-300">{{ $chat->visitor->metadata['device_type'] ?? 'Desktop' }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- 4. Transfer --}}
    @if(! in_array($chat->status->value, ['closed']))
    <div class="bg-slate-800/50 rounded-xl border border-slate-700/50 p-4">
        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4 pb-2 border-b border-slate-700/50 flex items-center gap-2">
            <svg class="w-3 h-3 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            Transfer Chat
        </h4>
        <form method="POST" action="{{ route('agent.chats.transfer', $chat->id) }}" class="space-y-4" data-ajax-form>
            @csrf
            <div>
                <select name="to_agent_id" required class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-300 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
                    <option value="">Select Agent...</option>
                    @foreach($agents as $agent)
                    @if($agent->id !== auth()->id())
                    <option value="{{ $agent->id }}" {{ (isset($previousAgentId) && $agent->id == $previousAgentId) ? 'selected' : '' }}>
                        {{ $agent->name }} ({{ $agent->role->label() }})
                    </option>
                    @endif
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-amber-600 hover:bg-amber-500 transition-all shadow-lg shadow-amber-600/20 uppercase tracking-widest">
                Transfer Now
            </button>
        </form>
    </div>
    @endif

    {{-- 5. Visitor Note --}}
    <div class="bg-slate-800/50 rounded-xl border border-slate-700/50 p-4">
        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-4 pb-2 border-b border-slate-700/50 flex items-center gap-2">
            <svg class="w-3 h-3 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Internal Notes
        </h4>
        <form method="POST" action="{{ route('agent.chats.addVisitorNote', $chat->id) }}" data-ajax-form>
            @csrf
            <textarea name="note" rows="4" placeholder="Add internal visitor notes..." class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-4 py-3 text-xs text-slate-300 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all mb-3">{{ $chat->visitor->metadata['internal_note'] ?? '' }}</textarea>
            <button type="submit" class="w-full py-2.5 rounded-xl text-[10px] font-bold text-slate-400 border border-slate-700 hover:bg-slate-700 transition-all uppercase tracking-[0.2em]">
                Save Note
            </button>
        </form>
    </div>

    {{-- 6. Create CRM Lead from this chat --}}
    @if(auth()->user()->canCreateLeads())
    <div class="bg-[#6366F1]/5 rounded-xl border border-[#6366F1]/20 p-4" x-data="{ open: false }">
        <div class="flex items-center justify-between">
            <h4 class="text-[10px] font-bold text-[#6366F1] uppercase tracking-[0.2em] flex items-center gap-2">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L14 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 018 21v-7.586L3.293 6.707A1 1 0 013 6V4z"></path></svg>
                Create CRM Lead
            </h4>
            <button type="button" @click="open = !open" class="text-[10px] font-bold text-[#6366F1] hover:text-[#818CF8] uppercase tracking-widest" x-text="open ? 'Close' : 'Open'"></button>
        </div>
        <div x-show="open" x-cloak class="mt-4">
            <form method="POST" action="{{ route('agent.chats.createLead', $chat->id) }}" class="space-y-3">
                @csrf
                <input type="text" name="company_name" required placeholder="Company name"
                    value="{{ old('company_name', $chat->visitor->metadata['company'] ?? '') }}"
                    class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none">
                <input type="text" name="contact_name" required placeholder="Contact name"
                    value="{{ old('contact_name', $chat->visitor->name ?? '') }}"
                    class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none">
                <input type="email" name="email" placeholder="Email"
                    value="{{ old('email', $chat->visitor->email ?? '') }}"
                    class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none">
                <input type="text" name="phone" placeholder="Phone (optional)"
                    value="{{ old('phone') }}"
                    class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-xs text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none">
                <div class="grid grid-cols-2 gap-2">
                    <select name="source" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-2 py-2 text-xs text-slate-200 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none">
                        @foreach(\App\Enums\LeadSource::cases() as $src)
                        <option value="{{ $src->value }}" {{ $src->value === 'website' ? 'selected' : '' }}>{{ $src->getLabel() }}</option>
                        @endforeach
                    </select>
                    <select name="product_interest" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-2 py-2 text-xs text-slate-200 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none">
                        @foreach(\App\Enums\ProductInterest::cases() as $pi)
                        <option value="{{ $pi->value }}">{{ $pi->getLabel() }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20 uppercase tracking-widest">
                    Create Lead
                </button>
            </form>
        </div>
    </div>
    @endif

    {{-- 7. Interaction Timeline --}}
    @if(isset($timeline) && count($timeline) > 0)
    <div class="bg-slate-800/40 backdrop-blur-xl rounded-2xl border border-slate-700/50 p-5 shadow-xl">
        <h4 class="text-[10px] font-bold text-slate-500 uppercase tracking-[0.2em] mb-5 pb-2 border-b border-slate-700/50 flex items-center gap-2">
            <svg class="w-3 h-3 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            Timeline
        </h4>
        <div class="relative space-y-6">
            @foreach($timeline as $activity)
            <div class="relative flex gap-4">
                @if(!$loop->last)
                <div class="absolute top-8 left-4 -ml-px w-px h-full bg-slate-700/50"></div>
                @endif
                <div class="w-8 h-8 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center shrink-0 z-10">
                    <svg class="h-3 h-3 text-[#6366F1]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
                <div class="pt-1 min-w-0">
                    <p class="text-xs font-semibold text-slate-300 truncate">{{ $activity->user->name ?? 'System' }}</p>
                    <p class="text-[10px] text-slate-500 font-medium">{{ str_replace('_', ' ', $activity->action) }}</p>
                    <p class="text-[9px] text-slate-600 mt-1">{{ $activity->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>