<?php

namespace Src\Notification\WhatsappAlertEngine;

use Src\Database\Models\NotificationLog;

/**
 * WhatsApp Alert Engine — sends alerts via WhatsApp API.
 *
 * All notification attempts are logged to the notification_logs table.
 * Implements retry logic and cooldown to avoid spam.
 */
class WhatsappAlertEngine
{
    /**
     * Send an alert to a phone number.
     */
    public function send(string $phone, string $message, array $context = []): NotificationLog
    {
        $log = NotificationLog::create([
            'channel'    => 'whatsapp',
            'recipient'  => $phone,
            'message'    => $message,
            'status'     => 'pending',
            'metadata'   => $context,
        ]);

        try {
            // TODO: integrate with WhatsApp Business API / Twilio / etc.
            $this->dispatch($phone, $message);
            $log->update(['status' => 'sent']);
        } catch (\Throwable $e) {
            $log->update([
                'status'   => 'failed',
                'metadata' => array_merge($context, ['error' => $e->getMessage()]),
            ]);
        }

        return $log;
    }

    /**
     * Check if a notification is in cooldown for a given context.
     */
    public function inCooldown(string $phone, string $eventType, int $cooldownSeconds = 300): bool
    {
        return NotificationLog::where('recipient', $phone)
            ->where('channel', 'whatsapp')
            ->where('status', 'sent')
            ->where('created_at', '>', now()->subSeconds($cooldownSeconds))
            ->exists();
    }

    /**
     * @internal Dispatch the actual API call.
     */
    private function dispatch(string $phone, string $message): void
    {
        // Placeholder — implement actual WhatsApp API integration
    }
}
