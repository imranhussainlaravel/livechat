@extends('layouts.app')
@section('header_title', 'System Settings')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Configuration</h1>
        <p class="text-gray-500 mt-1">Manage global system settings and chat behaviors.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
    @csrf
    @method('PUT')

    @foreach($settings as $group => $items)
    <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">{{ ucfirst($group) }} Settings</h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                @foreach($items as $i => $setting)
                <div>
                    <label for="setting_{{ $setting->key }}" class="block text-sm font-medium leading-6 text-gray-900 mb-1">
                        {{ ucfirst(str_replace('_', ' ', $setting->key)) }}
                        @if(str_contains($setting->key, 'minutes') || str_contains($setting->key, 'timeout'))
                        <span class="text-gray-400 font-normal text-xs ml-1">(in minutes)</span>
                        @endif
                    </label>

                    <input type="hidden" name="settings[{{ $loop->parent->index }}_{{ $i }}][key]" value="{{ $setting->key }}">
                    <input type="hidden" name="settings[{{ $loop->parent->index }}_{{ $i }}][group]" value="{{ $group }}">

                    <div class="relative mt-1 rounded-md shadow-sm">
                        <input type="{{ str_contains($setting->key, 'color') ? 'color' : (is_numeric($setting->value) ? 'number' : 'text') }}"
                            name="settings[{{ $loop->parent->index }}_{{ $i }}][value]"
                            id="setting_{{ $setting->key }}"
                            value="{{ $setting->value }}"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-900 placeholder:text-gray-400 sm:text-sm sm:leading-6 {{ str_contains($setting->key, 'color') ? 'h-10 cursor-pointer p-0.5' : '' }}">
                    </div>
                    <p class="mt-2 text-xs text-gray-500" id="email-description">Sets the value for {{ str_replace('_', ' ', $setting->key) }}.</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    <div class="flex justify-end pt-4 mb-10">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            Save Settings
        </button>
    </div>
</form>

@endsection