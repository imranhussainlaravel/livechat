<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private SettingsService $settingsService) {}

    /**
     * GET /admin/settings — Settings page.
     */
    public function index()
    {
        // Settings are defined in our new system_settings architecture
        // Group them for the view
        $allSettings = [
            'chats' => [
                (object) ['key' => 'max_chats_per_agent', 'value' => $this->settingsService->get('max_chats_per_agent', 5)],
                (object) ['key' => 'queue_timeout_minutes', 'value' => $this->settingsService->get('queue_timeout_minutes', 15)],
                (object) ['key' => 'auto_close_minutes', 'value' => $this->settingsService->get('auto_close_minutes', 60)],
                (object) ['key' => 'followup_reminder_minutes', 'value' => $this->settingsService->get('followup_reminder_minutes', 1440)],
            ],
            'widget' => [
                (object) ['key' => 'widget_name', 'value' => $this->settingsService->get('widget_name', 'Live Support')],
                (object) ['key' => 'widget_primary_color', 'value' => $this->settingsService->get('widget_primary_color', '#4F46E5')],
            ],
            'system' => [
                (object) ['key' => 'working_hours_start', 'value' => $this->settingsService->get('working_hours_start', '09:00')],
                (object) ['key' => 'working_hours_end', 'value' => $this->settingsService->get('working_hours_end', '17:00')],
            ],
        ];

        // AI assistant settings (stored in the key-value Setting store, since
        // the knowledge base is free-form text, not a fixed system column).
        $ai = [
            'enabled' => (bool) Setting::getValue('ai_bot_enabled', false),
            'knowledge' => (string) Setting::getValue('ai_bot_knowledge', ''),
            'name' => (string) Setting::getValue('ai_bot_name', 'Assistant'),
            'avatar_url' => (string) Setting::getValue('ai_bot_avatar_url', ''),
            'configured' => (string) config('services.groq.key') !== '',
        ];

        return view('admin.settings.index', ['settings' => $allSettings, 'ai' => $ai]);
    }

    /**
     * PUT /admin/settings/ai — Update AI assistant settings.
     */
    public function updateAi(Request $request)
    {
        $validated = $request->validate([
            'ai_bot_enabled' => 'nullable|boolean',
            'ai_bot_knowledge' => 'nullable|string|max:20000',
            'ai_bot_name' => 'nullable|string|max:60',
            'ai_bot_avatar_url' => 'nullable|url|max:500',
        ]);

        Setting::updateOrCreate(
            ['key' => 'ai_bot_enabled'],
            ['value' => $request->boolean('ai_bot_enabled') ? '1' : '', 'group' => 'ai']
        );

        Setting::updateOrCreate(
            ['key' => 'ai_bot_knowledge'],
            ['value' => $validated['ai_bot_knowledge'] ?? '', 'group' => 'ai']
        );

        Setting::updateOrCreate(
            ['key' => 'ai_bot_name'],
            ['value' => $validated['ai_bot_name'] ?: 'Assistant', 'group' => 'ai']
        );

        Setting::updateOrCreate(
            ['key' => 'ai_bot_avatar_url'],
            ['value' => $validated['ai_bot_avatar_url'] ?? '', 'group' => 'ai']
        );

        return back()->with('success', 'AI assistant settings updated successfully.');
    }

    /**
     * PUT /admin/settings — Bulk update settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);

        $settingsData = [];
        foreach ($request->settings as $item) {
            $settingsData[$item['key']] = $item['value'];
        }

        $this->settingsService->updateSettings($settingsData);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Settings updated successfully.']);
        }

        return back()->with('success', 'System settings updated successfully.');
    }
}
