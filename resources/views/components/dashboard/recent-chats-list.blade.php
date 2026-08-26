@props(['recentChats'])
<div class="bg-slate-800/50 border border-slate-700/50 rounded-xl overflow-hidden flex flex-col h-full lg:col-span-2">
    <div class="px-5 py-4 border-b border-slate-700/50 flex items-center justify-between bg-slate-900/40">
        <h3 class="text-sm font-bold text-slate-200">Recent Chats</h3>
        <a href="{{ route('admin.chats.index') }}" class="text-xs font-semibold text-[#6366F1] hover:text-[#818CF8] transition-colors">
            View all &rarr;
        </a>
    </div>
    <div class="flex-1 overflow-y-auto divide-y divide-slate-700/40">
        @forelse($recentChats as $chat)
        @php
            $metadata = is_array($chat->metadata) ? $chat->metadata : json_decode($chat->metadata, true) ?? [];
            $channel = $metadata['source'] ?? 'Web Chat';
            $channelIcons = [
                'WhatsApp' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>',
                'Web Chat' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>',
                'Instagram' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>'
            ];
            $icon = $channelIcons[$channel] ?? $channelIcons['Web Chat'];
            $color = match($channel) {
                'WhatsApp' => 'emerald',
                'Instagram' => 'rose',
                default => 'indigo'
            };
            $name = $chat->visitor->name ?? 'Visitor';
            $email = $chat->visitor->email ?? 'Unknown contact';
            $message = $chat->subject ?? 'No subject';
        @endphp
        <div class="flex items-start gap-4 px-5 py-4 hover:bg-slate-700/30 transition-colors group cursor-pointer" onclick="window.location='{{ route('agent.chats.show', $chat->id) }}'">
            <div class="w-10 h-10 rounded-xl bg-{{ $color }}-500/10 border border-{{ $color }}-500/20 flex items-center justify-center text-{{ $color }}-400 shrink-0 mt-0.5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    {!! $icon !!}
                </svg>
            </div>
            
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between mb-0.5">
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-slate-200 truncate">{{ $name }}</p>
                    </div>
                    <span class="text-[10px] font-medium text-slate-500 whitespace-nowrap ml-2">{{ $chat->created_at->diffForHumans() }}</span>
                </div>
                <p class="text-[11px] text-slate-400 mb-1 truncate">{{ $email }}</p>
                <p class="text-xs text-slate-500 truncate">
                    {{ $message }}
                </p>
            </div>
        </div>
        @empty
        <div class="p-5 text-center text-xs text-slate-500">
            No recent chats available.
        </div>
        @endforelse
    </div>
</div>
