<?php

namespace App\Services;

use App\Models\Chat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $topicUrl;

    private bool $configured;

    public function __construct()
    {
        $this->topicUrl = config('services.ntfy.topic_url', '') ?: $this->envFileValue('NTFY_TOPIC_URL');
        $this->configured = (bool) $this->topicUrl;
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
            $response = Http::timeout(5)
                ->withHeaders(['Title' => 'Website Alert'])
                ->withBody($body, 'text/plain')
                ->post($this->topicUrl);

            if (! $response->successful()) {
                Log::warning('ntfy push notification failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('ntfy push notification exception: '.$e->getMessage());
        }
    }

    private function envFileValue(string $key): string
    {
        $path = base_path('.env');

        if (! is_readable($path)) {
            return '';
        }

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);

            if (trim($name) === $key) {
                return trim($value, " \t\n\r\0\x0B\"'");
            }
        }

        return '';
    }
}
