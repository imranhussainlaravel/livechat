@props(['title', 'value', 'icon' => null, 'color' => 'blue'])

@php
$bgClasses = [
'blue' => 'bg-blue-100 text-blue-600',
'yellow' => 'bg-yellow-100 text-yellow-600',
'red' => 'bg-red-100 text-red-600',
'green' => 'bg-green-100 text-green-600',
][$color] ?? 'bg-gray-100 text-gray-600';
@endphp

<div class="bg-white rounded-lg shadow-sm p-6 border border-gray-100 flex items-center justify-between">
    <div>
        <p class="text-sm font-medium text-gray-500 mb-1">{{ $title }}</p>
        <h3 class="text-3xl font-bold text-gray-900">{{ $value }}</h3>
    </div>
    @if($icon)
    <div class="w-12 h-12 rounded-lg {{ $bgClasses }} flex items-center justify-center">
        {!! $icon !!}
    </div>
    @endif
</div>