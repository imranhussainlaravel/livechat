<?php

namespace App\Services;

use App\Models\Chat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $instanceId;
    private string $token;
    private string $groupId;
    private bool $configured;

    public function __construct()
    {
        $this->instanceId = config('services.ultramsg.instance_id', '');
        $this->token      = config('services.ultramsg.token', '');
        $this->groupId    = config('services.ultramsg.group_id', '');
        $this->configured = $this->instanceId && $this->token && $this->groupId;
    }

    public function notifyNewChat(Chat $chat): void
    {
        if (!$this->configured) {
            return;
        }

        $visitor  = $chat->visitor;
        $name     = $visitor?->name  ?? 'Unknown';
        $email    = $visitor?->email ?? 'N/A';
        $subject  = $chat->subject   ?? 'General Inquiry';
        $time     = now()->format('M d, Y h:i A');
        $chatUrl  = config('app.url') . '/admin/chats/' . $chat->id;

        $body = implode("\n", [
            '🆕 *New Chat Started!*',
            '',
            "👤 *Visitor:* {$name}",
            "📧 *Email:* {$email}",
            "💬 *Subject:* {$subject}",
            "⏰ *Time:* {$time}",
            "🔗 *Open:* {$chatUrl}",
        ]);

        $this->send($body);
    }

    private function send(string $body): void
    {
        try {
            $response = Http::timeout(5)->asForm()->post(
                "https://api.ultramsg.com/{$this->instanceId}/messages/chat",
                [
                    'token' => $this->token,
                    'to'    => $this->groupId,
                    'body'  => $body,
                ]
            );

            if (!$response->successful()) {
                Log::warning('WhatsApp notification failed', [
                    'status'   => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp notification exception: ' . $e->getMessage());
        }
    }
}
