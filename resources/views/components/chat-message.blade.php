<div class="flex items-start mb-3 {{ $isMine ? 'flex-row-reverse' : '' }}">
    <div class="flex flex-col {{ $isMine ? 'items-end' : 'items-start' }} max-w-[85%]">
        <span class="text-[10px] text-gray-400 mb-0.5 px-1">{{ $time }}</span>
        <div class="px-3 py-1.5 rounded-2xl shadow-sm text-[13px] leading-relaxed {{ 
            $isMine ? 'bg-[#F0644B] text-white rounded-tr-sm' : 
            ($isBot ? 'bg-purple-50 text-purple-900 border border-purple-100 rounded-tl-sm' : 'bg-gray-800 text-gray-200 rounded-tl-sm') 
        }}">
            {!! nl2br(e($message)) !!}
        </div>
    </div>
</div>