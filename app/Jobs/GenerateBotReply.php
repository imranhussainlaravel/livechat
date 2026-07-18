<?php

namespace App\Jobs;

use App\Enums\ChatStatus;
use App\Models\Chat;
use App\Services\AiBotService;
use App\Services\ChatService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Generates and posts an AI assistant reply for a waiting chat.
 *
 * Dispatched by ChatService::sendMessage() whenever a visitor sends a message
 * and no human agent has joined yet. Runs on the queue so the visitor's own
 * request returns instantly and the bot reply arrives a moment later over the
 * existing broadcast channel — exactly like a real agent replying.
 */
class GenerateBotReply implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Don't pile up retries — a failed AI call just means no bot reply. */
    public int $tries = 1;

    public int $timeout = 30;

    public function __construct(public int $chatId) {}

    public function handle(AiBotService $bot, ChatService $chats): void
    {
        if (! $bot->isEnabled()) {
            return;
        }

        $chat = Chat::with('visitor')->find($this->chatId);

        // If an agent has joined (or the chat closed) between dispatch and now,
        // the bot must stay silent — the human is in control.
        if (! $chat || $chat->status !== ChatStatus::PENDING) {
            return;
        }

        $reply = $bot->reply($chat);

        if ($reply === null) {
            return;
        }

        // Re-check status right before posting, to avoid the rare race where an
        // agent joined while the model was thinking.
        if ($chat->fresh()->status !== ChatStatus::PENDING) {
            return;
        }

        $chats->postBotMessage($this->chatId, $reply);
    }
}
