<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\JsonResponse;

class WidgetController extends Controller
{
    /**
     * GET /api/widget/config
     * 
     * Returns the widget configuration and whether any agents are online.
     * This is the first request made by the Next.js widget.
     */
    public function config(): JsonResponse
    {
        // Check if any agent is currently set as 'online'
        $isOnline = User::where('role', UserRole::AGENT)
            ->where('status', 'online')
            ->exists();

        // Return a unified config object
        return response()->json([
            'success' => true,
            'data' => [
                'is_online' => $isOnline,
                'settings' => [
                    'primary_color'    => Setting::getValue('widget_primary_color', '#3b82f6'),
                    'name'             => Setting::getValue('widget_name', 'Live Support'),
                    'greeting'         => Setting::getValue('widget_greeting', 'Hello! How can we help you today?'),
                    'logo_url'         => Setting::getValue('widget_logo_url', null),
                    'position'         => Setting::getValue('widget_position', 'right'), // left or right
                    'show_agent_info'  => Setting::getValue('widget_show_agent_info', true),
                ]
            ]
        ]);
    }
}
