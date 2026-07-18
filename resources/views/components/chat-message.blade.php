<div class="flex items-start mb-3 {{ $isMine ? 'flex-row-reverse' : '' }}"
    @if($isMine) data-agent-msg="1" data-created-at="{{ $createdAtIso ?? '' }}" @endif>
    <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }} max-w-[85%]">
        <span class="text-[10px] text-gray-400 mb-0.5 px-1">
            @if($isBot)<span class="font-semibold text-purple-400">{{ $senderName ?? 'Assistant' }}</span> &middot; @endif{{ $time }}
        </span>
        <div class="px-3 py-1.5 rounded-2xl shadow-sm text-[13px] leading-relaxed {{
            $isMine ? 'bg-[#6366F1] text-white rounded-tr-sm' :
            ($isBot ? 'bg-purple-50 text-purple-900 border border-purple-100 rounded-tl-sm' : 'bg-gray-800 text-gray-200 rounded-tl-sm')
        }}">
            {!! nl2br(e($message)) !!}
        </div>
        @if($isMine)
        <span class="seen-label hidden text-[10px] text-gray-500 mt-0.5 px-1">Seen</span>
        @endif
    </div>
</div>