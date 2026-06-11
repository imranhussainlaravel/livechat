<?php

namespace App\Services;

use App\Models\Chat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $phone;

    private string $apiKey;

    private bool $configured;

    public function __construct()
    {
        $this->phone = config('services.callmebot.phone', '');
        $this->apiKey = config('services.callmebot.apikey', '');
        $this->configured = $this->phone && $this->apiKey;
    }

    public function notifyNewChat(Chat $chat): void
    {
        if (! $this->configured) {
            return;
        }

        $visitor = $chat->visitor;
        $name = $visitor?->name ?? 'Unknown';
        $email = $visitor?->email ?? 'N/A';
        $subject = $chat->subject ?? 'General Inquiry';
        $time = now()->format('M d, Y h:i A');
        $chatUrl = rtrim(config('app.url'), '/').'/admin/chats/'.$chat->id;

        $body = implode("\n", [
            '*New Chat Started!*',
            '',
            "*Visitor:* {$name}",
            "*Email:* {$email}",
            "*Subject:* {$subject}",
            "*Time:* {$time}",
            "*Open:* {$chatUrl}",
        ]);

        $this->send($body);
    }

    public function notifyVisitorMessage(Chat $chat, string $message): void
    {
        if (! $this->configured) {
            return;
        }

        $visitor = $chat->visitor;
        $name = $visitor?->name ?? 'Visitor';
        $chatUrl = rtrim(config('app.url'), '/').'/admin/chats/'.$chat->id;

        $body = implode("\n", [
            '*New Visitor Message!*',
            '',
            "*Visitor:* {$name}",
            '*Message:* '.str($message)->limit(400),
            "*Open:* {$chatUrl}",
        ]);

        $this->send($body);
    }

    private function send(string $body): void
    {
        try {
            $response = Http::timeout(5)->get('https://api.callmebot.com/whatsapp.php', [
                'phone' => $this->phone,
                'text' => $body,
                'apikey' => $this->apiKey,
            ]);

            if (! $response->successful()) {
                Log::warning('CallMeBot WhatsApp notification failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('CallMeBot WhatsApp notification exception: '.$e->getMessage());
        }
    }
}
