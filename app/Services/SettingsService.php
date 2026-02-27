<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'system_settings';
    private const CACHE_TTL = 3600; // 1 hour

    /**
     * Retrieve all settings, utilizing Redis cache.
     *
     * @return SystemSetting
     */
    public function getSettings(): SystemSetting
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            // Retrieve the first record or create a default one if none exists
            return SystemSetting::firstOrCreate([], [
                'max_chats_per_agent' => 5,
                'queue_timeout_minutes' => 10,
                'auto_close_minutes' => 30,
                'followup_reminder_minutes' => 15,
                'working_hours_start' => '09:00:00',
                'working_hours_end' => '17:00:00',
                'widget_primary_color' => '#000000',
                'widget_name' => 'LiveChat',
            ]);
        });
    }

    /**
     * Update settings and invalidate the cache.
     *
     * @param array $data
     * @return SystemSetting
     */
    public function updateSettings(array $data): SystemSetting
    {
        $settings = $this->getSettings();
        $settings->update($data);

        // Invalidate cache after update
        Cache::forget(self::CACHE_KEY);

        return $settings->refresh();
    }

    /**
     * Get a specific setting value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $settings = $this->getSettings();
        return $settings->getAttribute($key) ?? $default;
    }
}
