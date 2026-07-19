<?php

namespace App\Services;

use App\Enums\MessageSenderType;
use App\Models\Chat;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AiBotService — a lightweight "first responder" assistant.
 *
 * While a chat is still waiting for a human agent (status = pending), this
 * service answers simple questions (greetings, shipping, pricing, company
 * info, packages, …) using a knowledge base the admin edits in Settings.
 *
 * It intentionally does NOT try to be clever: it only helps with the basics,
 * always makes clear a live agent will take over, and gently invites the
 * visitor to leave an email so the team can follow up. The moment an agent
 * joins the chat, the bot stops (enforced by the caller, GenerateBotReply).
 *
 * Powered by Groq (free, very fast, OpenAI-compatible API).
 */
class AiBotService
{
    /** How many recent messages to feed the model for context. */
    private const HISTORY_LIMIT = 12;

    /**
     * Is the assistant switched on AND configured with an API key?
     */
    public function isEnabled(): bool
    {
        return (bool) Setting::getValue('ai_bot_enabled', false)
            && (string) config('services.groq.key') !== '';
    }

    /**
     * Generate a reply for the given chat, or null if the bot has nothing
     * useful to say / is disabled / the API failed. Never throws.
     */
    public function reply(Chat $chat): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $messages = array_merge(
            [['role' => 'system', 'content' => $this->systemPrompt($chat)]],
            $this->history($chat),
        );

        $url = rtrim((string) config('services.groq.base_url'), '/').'/chat/completions';
        $primary = (string) config('services.groq.model');

        // Try the configured model, then retry on transient failures (rate
        // limits / 5xx), then fall back to the fast 8B model if the primary is
        // rate-limited — so the bot keeps answering instead of going silent.
        $attempts = [
            ['model' => $primary, 'wait' => 0],
            ['model' => $primary, 'wait' => 2],
            ['model' => 'llama-3.1-8b-instant', 'wait' => 0],
        ];

        foreach ($attempts as $i => $attempt) {
            if ($attempt['wait'] > 0) {
                sleep($attempt['wait']);
            }

            try {
                $response = Http::timeout(20)
                    ->withToken((string) config('services.groq.key'))
                    ->post($url, [
                        'model' => $attempt['model'],
                        'messages' => $messages,
                        'temperature' => 0.2,
                        'max_tokens' => 160,
                    ]);

                if ($response->successful()) {
                    $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

                    return $text !== '' ? $text : null;
                }

                Log::warning('Groq AI reply failed', [
                    'attempt' => $i + 1,
                    'model' => $attempt['model'],
                    'status' => $response->status(),
                    'body' => \Illuminate\Support\Str::limit($response->body(), 500),
                ]);

                // Only worth retrying on rate-limit (429) or server errors (5xx).
                if ($response->status() !== 429 && $response->status() < 500) {
                    return null;
                }
            } catch (\Throwable $e) {
                Log::error('Groq AI reply exception: '.$e->getMessage());
            }
        }

        return null;
    }

    /**
     * Build the system prompt from the admin-editable knowledge base plus
     * the fixed behaviour rules that keep the bot "simple assist only".
     */
    private function systemPrompt(Chat $chat): string
    {
        $company = Setting::getValue('widget_name', config('app.name', 'our team'));
        $knowledge = trim((string) Setting::getValue('ai_bot_knowledge', ''));
        $hasEmail = ! empty($chat->visitor?->email);

        $rules = <<<TXT
        You are a support assistant for "{$company}" on a website live-chat widget, helping while a
        human agent connects.

        Answer the visitor's LAST message directly and stop. Follow every rule:
        - Reply with ONLY the answer to what they actually asked. 1–2 short sentences. Then stop.
        - Do NOT add marketing lines, company descriptions, or sales pitches unless they asked for that.
        - Do NOT ask counter-questions like "what product are you looking for?" or "what brings you here?".
          Only ask a question back if it is strictly required to answer them.
        - Use ONLY the facts in "Company information" below. If the answer is not there, or the request is
          complex, custom, a complaint, or about an order/account, reply briefly that a live agent will
          join shortly to help with that — nothing more.
        - Never invent prices, delivery times, stock, discounts, or policies not written below.
        - If the message is a greeting, just greet back briefly (e.g. "Hi! How can I help?"). Do not
          dump company info.
        - If the message is small talk, off-topic, or rude, stay calm and brief; do not lecture or pitch.
        - Do not claim to be human. If asked, say you are an automated assistant and an agent will join soon.
        - Plain chat text only — no markdown, headings, bullets, or emojis-heavy replies.
        - Never use a stored/system name — any name on file is an auto-generated placeholder, not real.
          Never greet them by name. Only use a name if the visitor types their own name in the chat.
        TXT;

        if ($hasEmail) {
            $rules .= "\n- The visitor has already shared their email, so do NOT ask for it again.";
        }

        $knowledgeBlock = $knowledge !== ''
            ? "Company information (your only source of truth):\n{$knowledge}"
            : "Company information: (none provided yet — only greet, be reassuring, and let them know a human agent will join shortly).";

        return $rules."\n\n".$knowledgeBlock;
    }

    /**
     * Map recent chat messages to OpenAI-style roles.
     *   visitor → user, bot/agent → assistant.
     * System messages (welcome text, "email received", "agent joined", …) are
     * SKIPPED entirely — feeding them to the model made it parrot the welcome
     * line back to the visitor.
     *
     * @return array<int, array{role: string, content: string}>
     */
    private function history(Chat $chat): array
    {
        $recent = $chat->messages()
            ->orderByDesc('id')
            ->limit(self::HISTORY_LIMIT)
            ->get()
            ->reverse();

        $mapped = [];
        foreach ($recent as $message) {
            $type = $message->sender_type instanceof \BackedEnum
                ? $message->sender_type->value
                : (string) $message->sender_type;

            $text = trim((string) $message->message);
            if ($text === '') {
                continue;
            }

            $role = match ($type) {
                MessageSenderType::VISITOR->value => 'user',
                MessageSenderType::BOT->value, MessageSenderType::AGENT->value => 'assistant',
                default => null, // system → skip
            };

            if ($role === null) {
                continue;
            }

            $mapped[] = ['role' => $role, 'content' => $text];
        }

        return $mapped;
    }
}
