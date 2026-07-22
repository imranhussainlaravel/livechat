@props(['label' => '', 'name' => '', 'required' => false])
<div {{ $attributes->only('class') }}>
    @if($label)
    <label for="{{ $name }}" class="block text-xs font-semibold text-slate-300 mb-1.5">
        {{ $label }}@if($required)<span class="text-rose-400"> *</span>@endif
    </label>
    @endif
    {{ $slot }}
    @error($name)
    <p class="mt-1 text-[11px] text-rose-400">{{ $message }}</p>
    @enderror
</div>
