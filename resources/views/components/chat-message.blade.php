<div class="flex items-start gap-2.5 mb-3 {{ $isMine ? 'flex-row-reverse' : '' }}">
    <div class="flex-shrink-0">
        @if ($isBot)
        <div class="w-7 h-7 rounded-full bg-purple-100 flex items-center justify-center text-purple-600">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
        @elseif ($isMine)
        <div class="w-7 h-7 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[10px] font-bold shadow-sm">
            {{ strtoupper(substr($senderName, 0, 1)) }}
        </div>
        @else
        <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white text-[10px] font-bold shadow-sm">
            {{ strtoupper(substr($senderName, 0, 1)) }}
        </div>
        @endif
    </div>

    <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }} max-w-[80%]">
        <span class="text-[10px] text-gray-400 mb-0.5 px-1">{{ $senderName }} • {{ $time }}</span>
        <div class="px-3 py-1.5 rounded-2xl shadow-sm text-[13px] leading-relaxed {{ 
            $isMine ? 'bg-indigo-600 text-white rounded-tr-sm' : 
            ($isBot ? 'bg-purple-50 text-purple-900 border border-purple-100 rounded-tl-sm' : 'bg-gray-100 text-gray-800 rounded-tl-sm') 
        }}">
            {!! nl2br(e($message)) !!}
        </div>
    </div>
</div>