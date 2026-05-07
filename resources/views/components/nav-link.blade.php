@props(['href', 'active' => false])

<a href="{{ $href }}"
    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
          {{ $active
              ? 'bg-[#F0644B]/15 text-[#F0644B] font-semibold'
              : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800' }}">
    {{ $slot }}
</a>