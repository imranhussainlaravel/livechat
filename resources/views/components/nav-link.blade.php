@props(['href', 'active' => false])

<a href="{{ $href }}"
    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
          {{ $active
              ? 'bg-[#6366F1]/15 text-[#6366F1] font-semibold'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
    {{ $slot }}
</a>