@extends('layouts.app')
@section('header_title', 'System Settings')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-100">Configuration</h1>
        <p class="text-gray-500 mt-1">Manage global system settings and chat behaviors.</p>
    </div>
</div>

<form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-6">
    @csrf
    @method('PUT')

    @foreach($settings as $group => $items)
    <div class="bg-gray-900 border border-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 bg-gray-800">
            <h3 class="text-sm font-semibold text-gray-100 uppercase tracking-wider">{{ ucfirst($group) }} Settings</h3>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                @foreach($items as $i => $setting)
                <div>
                    <label for="setting_{{ $setting->key }}" class="block text-sm font-medium leading-6 text-gray-100 mb-1">
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
                            class="block w-full px-3 py-2 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-100 placeholder:text-gray-400 sm:text-sm sm:leading-6 {{ str_contains($setting->key, 'color') ? 'h-10 cursor-pointer p-0.5' : '' }}">
                    </div>
                    <p class="mt-2 text-xs text-gray-500" id="email-description">Sets the value for {{ str_replace('_', ' ', $setting->key) }}.</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

    <div class="flex justify-end pt-4">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            Save Settings
        </button>
    </div>
</form>

{{-- AI Assistant --}}
<form method="POST" action="{{ route('admin.settings.updateAi') }}" class="space-y-6 mt-6 mb-10">
    @csrf
    @method('PUT')

    <div class="bg-gray-900 border border-gray-800 rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-800 bg-gray-800 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-100 uppercase tracking-wider">AI Assistant</h3>
            @unless($ai['configured'])
                <span class="text-xs text-amber-400">GROQ_API_KEY not set in .env — bot stays off until added</span>
            @endunless
        </div>

        <div class="p-6 space-y-6">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="checkbox" name="ai_bot_enabled" value="1" @checked($ai['enabled'])
                    class="h-4 w-4 rounded border-gray-600 bg-gray-800 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-medium text-gray-100">Enable AI assistant while visitors wait for an agent</span>
            </label>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                <div>
                    <label for="ai_bot_name" class="block text-sm font-medium leading-6 text-gray-100 mb-1">Bot display name</label>
                    <input type="text" name="ai_bot_name" id="ai_bot_name" value="{{ $ai['name'] }}"
                        placeholder="Assistant"
                        class="block w-full px-3 py-2 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-100 placeholder:text-gray-400 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Shown next to the bot's messages in the widget.</p>
                </div>
                <div>
                    <label for="ai_bot_avatar_url" class="block text-sm font-medium leading-6 text-gray-100 mb-1">Bot avatar image URL</label>
                    <input type="url" name="ai_bot_avatar_url" id="ai_bot_avatar_url" value="{{ $ai['avatar_url'] }}"
                        placeholder="https://nexonpackaging.com/bot-avatar.png"
                        class="block w-full px-3 py-2 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-100 placeholder:text-gray-400 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Public image URL for the bot's profile picture (optional).</p>
                </div>
            </div>

            <div>
                <label for="ai_bot_knowledge" class="block text-sm font-medium leading-6 text-gray-100 mb-1">
                    Knowledge base
                </label>
                <p class="text-xs text-gray-500 mb-2">
                    Everything the bot is allowed to say — company info, greetings, shipping, pricing, packages, FAQs.
                    The bot only answers from this; if something isn't here it tells the visitor a live agent will help.
                </p>
                <textarea name="ai_bot_knowledge" id="ai_bot_knowledge" rows="14"
                    class="block w-full px-3 py-2 border border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-gray-100 placeholder:text-gray-400 sm:text-sm font-mono"
                    placeholder="e.g.&#10;About us: We sell handmade candles across the UK.&#10;Shipping: Free UK shipping over £30, 2–4 working days.&#10;Packages: Starter £15, Premium £29, Gift set £45.&#10;Returns: 14-day returns on unused items.&#10;Hours: Mon–Fri 9am–5pm.">{{ $ai['knowledge'] }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex justify-end pt-4">
        <button type="submit" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            Save AI Settings
        </button>
    </div>
</form>

@endsection