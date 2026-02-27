@props(['href', 'active' => false])

<a href="{{ $href }}"
    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition
          {{ $active
              ? 'bg-indigo-500/15 text-indigo-300 font-medium'
              : 'text-gray-400 hover:text-gray-200 hover:bg-gray-800' }}">
    {{ $slot }}
</a>