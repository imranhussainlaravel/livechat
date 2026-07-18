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

        try {
            $response = Http::timeout(20)
                ->withToken((string) config('services.groq.key'))
                ->post(rtrim((string) config('services.groq.base_url'), '/').'/chat/completions', [
                    'model' => config('services.groq.model'),
                    'messages' => $messages,
                    'temperature' => 0.4,
                    'max_tokens' => 300,
                ]);

            if (! $response->successful()) {
                Log::warning('Groq AI reply failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $text = trim((string) data_get($response->json(), 'choices.0.message.content', ''));

            return $text !== '' ? $text : null;
        } catch (\Throwable $e) {
            Log::error('Groq AI reply exception: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Build the system prompt from the admin-editable knowledge base plus
     * the fixed behaviour rules that keep the bot "simple assist only".
     */
    private function systemPrompt(Chat $chat): string
    {
        $company = Setting::getValue('widget_name', config('app.name', 'our team'));
        $knowledge = trim((string) Setting::getValue('ai_bot_knowledge', ''));
        $visitorName = $chat->visitor?->name;
        $hasEmail = ! empty($chat->visitor?->email);

        $rules = <<<TXT
        You are a friendly first-response assistant for "{$company}" on a website live-chat widget.
        Your job is ONLY to give quick, simple help while the visitor waits for a human agent to join.

        Rules — follow every one:
        - Keep replies short, warm and easy to read (1–3 short sentences). Plain language, no jargon.
        - Answer ONLY from the "Company information" below. If the answer is not there, or the question
          is complex, custom, a complaint, or needs account/order details, do NOT guess. Say a live agent
          will join shortly to help with that.
        - Never invent prices, delivery times, stock, discounts, or policies that are not written below.
        - A human agent is being connected to this chat. Reassure the visitor of this when it fits.
        - Do not claim to be human. If asked, say you are an automated assistant and an agent will join soon.
        - After a message or two, if it feels natural, you may gently invite the visitor to leave their
          email so the team can follow up — but do NOT insist or repeat it, and never block them.
        - Do not use markdown, headings, or bullet symbols. Write like a normal chat message.
        TXT;

        if ($hasEmail) {
            $rules .= "\n- The visitor has already shared their email, so do NOT ask for it again.";
        }
        if ($visitorName) {
            $rules .= "\n- The visitor's name is {$visitorName}; you may address them by it once, naturally.";
        }

        $knowledgeBlock = $knowledge !== ''
            ? "Company information (your only source of truth):\n{$knowledge}"
            : "Company information: (none provided yet — only greet, be reassuring, and let them know a human agent will join shortly).";

        return $rules."\n\n".$knowledgeBlock;
    }

    /**
     * Map recent chat messages to OpenAI-style roles. Visitor → user,
     * bot → assistant. Agent/system lines are folded in as context so the
     * bot doesn't repeat what a system notice already said.
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

            $mapped[] = match ($type) {
                MessageSenderType::VISITOR->value => ['role' => 'user', 'content' => $text],
                MessageSenderType::BOT->value => ['role' => 'assistant', 'content' => $text],
                // agent + system notices given to the model as context
                default => ['role' => 'system', 'content' => "[note] {$text}"],
            };
        }

        // The model needs at least one user turn to respond to.
        return $mapped;
    }
}
