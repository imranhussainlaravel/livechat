@extends('layouts.app')
@section('header_title', 'Team Chat')

@section('content')
@php $lastId = $messages->max('id') ?? 0; @endphp
<div class="h-full flex flex-col sm:flex-row gap-4">

    {{-- ── Left: channels + direct messages ── --}}
    <div class="w-full sm:w-64 shrink-0 flex flex-col gap-4 max-h-52 sm:max-h-none sm:h-full overflow-y-auto">
        <div>
            <h3 class="px-1 mb-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600">Group Channels</h3>
            <ul class="space-y-1">
                @foreach($channels as $key => $label)
                @php $on = $activeType === 'group' && $activeId === $key; $unread = $channelUnread[$key] ?? 0; @endphp
                <li>
                    <a href="{{ route('agent.agents.index', ['channel' => $key]) }}"
                       class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-sm font-medium transition-colors {{ $on ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-300 hover:bg-slate-800/60' }}">
                        <span class="flex items-center gap-2 min-w-0"><span class="text-slate-500">#</span> <span class="truncate">{{ $label }}</span></span>
                        @if($unread > 0)
                        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full text-[10px] font-bold bg-[#6366F1] text-white">{{ $unread }}</span>
                        @endif
                    </a>
                </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="px-1 mb-2 text-[10px] font-black uppercase tracking-[0.2em] text-slate-600">Direct Messages</h3>
            <ul class="space-y-1">
                @forelse($users as $u)
                @php $on = $activeType === 'dm' && $activeId === (string) $u->id; @endphp
                <li>
                    <a href="{{ route('agent.agents.index', ['dm' => $u->id]) }}"
                       class="flex items-center justify-between gap-2 px-3 py-2 rounded-xl text-sm transition-colors {{ $on ? 'bg-[#6366F1]/10 text-[#6366F1] border border-[#6366F1]/20' : 'text-slate-300 hover:bg-slate-800/60' }}">
                        <span class="flex items-center gap-2 min-w-0">
                            <span class="w-2 h-2 rounded-full shrink-0 {{ $u->status === 'online' ? 'bg-emerald-500' : 'bg-slate-600' }}"></span>
                            <span class="truncate">{{ $u->name }}</span>
                        </span>
                        @if($u->unread_count > 0)
                        <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full text-[10px] font-bold bg-[#6366F1] text-white">{{ $u->unread_count }}</span>
                        @endif
                    </a>
                </li>
                @empty
                <li class="px-3 py-2 text-xs text-slate-600">No other users yet.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- ── Right: conversation ── --}}
    <div class="flex-1 min-h-0 flex flex-col rounded-2xl border border-slate-700/50 bg-slate-800/40 backdrop-blur-xl overflow-hidden">
        @if(! $activeType)
        <div class="flex-1 flex items-center justify-center text-slate-500 text-sm">Select a conversation to start chatting.</div>
        @else
        <div class="px-5 py-3 border-b border-slate-700/50 bg-slate-800/40 flex items-center gap-2">
            <span class="text-sm font-bold text-slate-100">{{ $activeType === 'group' ? '# ' . $activeTitle : $activeTitle }}</span>
        </div>

        <div id="tc-messages" data-last-id="{{ $lastId }}" class="flex-1 overflow-y-auto p-4 space-y-3">
            @forelse($messages as $m)
            @php $mine = $m->sender_id === auth()->id(); @endphp
            <div class="flex flex-col {{ $mine ? 'items-end' : 'items-start' }}">
                <div class="max-w-[75%] px-3 py-2 rounded-2xl text-sm {{ $mine ? 'bg-[#6366F1] text-white rounded-br-sm' : 'bg-slate-700/60 text-slate-100 rounded-bl-sm' }}">
                    @unless($mine)<div class="text-[11px] font-semibold text-[#818CF8] mb-0.5">{{ $m->sender?->name ?? 'User' }}</div>@endunless
                    <div class="whitespace-pre-wrap break-words">{{ $m->message }}</div>
                </div>
                <span class="text-[10px] text-slate-500 mt-0.5">{{ $m->created_at?->format('M d, g:i A') }}</span>
            </div>
            @empty
            <div class="h-full flex items-center justify-center text-slate-500 text-sm">No messages yet — say hello.</div>
            @endforelse
        </div>

        <form id="tc-form"
              data-url="{{ $activeType === 'group' ? route('agent.agents.channelMessage', $activeId) : route('agent.agents.message', $activeId) }}"
              class="flex items-center gap-2 border-t border-slate-700/50 p-3">
            @csrf
            <input type="text" name="message" autocomplete="off" required placeholder="Type a message…"
                   class="flex-1 bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Send</button>
        </form>
        @endif
    </div>
</div>

@push('scripts')
<script>
(function () {
    var box = document.getElementById('tc-messages');
    var form = document.getElementById('tc-form');
    if (!box || !form) return;

    var CSRF = '{{ csrf_token() }}';
    var lastId = parseInt(box.getAttribute('data-last-id') || '0', 10);
    var pollUrl = @json(($activeType === 'dm')
        ? route('agent.agents.fetch', ['dm' => $activeId])
        : route('agent.agents.fetch', ['channel' => $activeId]));

    function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }

    function appendMessage(m) {
        if (m.id && m.id <= lastId) return;
        if (m.id) lastId = m.id;
        var wrap = document.createElement('div');
        wrap.className = 'flex flex-col ' + (m.mine ? 'items-end' : 'items-start');
        var name = m.mine ? '' : '<div class="text-[11px] font-semibold text-[#818CF8] mb-0.5">' + esc(m.sender_name) + '</div>';
        wrap.innerHTML =
            '<div class="max-w-[75%] px-3 py-2 rounded-2xl text-sm ' +
            (m.mine ? 'bg-[#6366F1] text-white rounded-br-sm' : 'bg-slate-700/60 text-slate-100 rounded-bl-sm') + '">' +
            name + '<div class="whitespace-pre-wrap break-words">' + esc(m.content) + '</div></div>' +
            '<span class="text-[10px] text-slate-500 mt-0.5">' + esc(m.time) + '</span>';
        var placeholder = box.querySelector('.h-full');
        if (placeholder) placeholder.remove();
        box.appendChild(wrap);
        box.scrollTop = box.scrollHeight;
    }

    box.scrollTop = box.scrollHeight;

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var input = form.querySelector('input[name="message"]');
        var text = input.value.trim();
        if (!text) return;
        input.value = '';
        fetch(form.getAttribute('data-url'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ message: text })
        })
        .then(function (r) { if (!r.ok) throw r; return r.json(); })
        .then(function (res) { if (res.data) appendMessage(res.data); })
        .catch(function () { if (window.showToast) showToast('Message failed to send.', 'error'); input.value = text; });
    });

    function poll() {
        fetch(pollUrl + '&after=' + lastId, { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (res) { (res.data || []).forEach(appendMessage); })
            .catch(function () {});
    }

    // Turbo re-runs this script on navigation — keep only one interval alive.
    if (window._teamChatPoll) clearInterval(window._teamChatPoll);
    window._teamChatPoll = setInterval(poll, 7000);
    document.addEventListener('turbo:before-render', function () {
        if (window._teamChatPoll) { clearInterval(window._teamChatPoll); window._teamChatPoll = null; }
    }, { once: true });
})();
</script>
@endpush
@endsection
