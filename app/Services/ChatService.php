<?php

namespace App\Services;

use App\DTOs\SendMessageDTO;
use App\DTOs\StartChatDTO;
use App\DTOs\TransferChatDTO;
use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Enums\QueueStatus;
use App\Events\ChatAssigned;
use App\Events\ChatClosed;
use App\Events\ChatQueueUpdated;
use App\Events\ChatStarted;
use App\Events\ChatStatusUpdated;
use App\Events\ChatTransferred;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Visitor;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ChatService
{
    public function __construct(
        private ChatRepositoryInterface $chats,
        private MessageRepositoryInterface $messages,
        private QueueService $queue,
        private ActivityService $activity,
        private WhatsAppService $whatsApp,
    ) {}

    /**
     * Retrieve a chat by ID.
     */
    public function getChat(int $id): \App\Models\Chat
    {
        return $this->chats->findById($id);
    }

    /**
     * Find the visitor's resumable chat for a session token.
     * An ongoing (non-closed) chat always resumes. A closed chat never
     * auto-resumes — once an agent has resolved a conversation, the visitor
     * starts a fresh one (linked to the closed one via previous_chat_id in
     * startChat()) instead of silently reopening old, possibly stale context.
     */
    public function recoverSession(string $token): ?Chat
    {
        $visitor = Visitor::where('session_token', $token)->first();
        if (! $visitor) {
            return null;
        }

        $chat = $visitor->chats()->orderBy('created_at', 'desc')->first();
        if (! $chat || $chat->status === ChatStatus::CLOSED) {
            return null;
        }

        return $chat;
    }

    /* ================================================================== */
    /*  1. START CHAT */
    /* ================================================================== */

    /**
     * Visitor starts a new chat from the website widget.
     * Creates visitor (if new), creates chat, attempts auto-assignment.
     */
    public function startChat(StartChatDTO $dto): Chat
    {
        $chat = DB::transaction(function () use ($dto) {
            // Find or create visitor by session token
            $visitor = Visitor::firstOrCreate(
                ['session_token' => $dto->sessionToken],
                [
                    'name' => $dto->visitorName,
                    'email' => $dto->visitorEmail,
                    'metadata' => $dto->metadata,
                ]
            );

            // If the visitor's last chat was closed, link this new one to it
            // so the agent who picks this up can jump back to prior context.
            $lastChat = $visitor->chats()->orderBy('created_at', 'desc')->first();
            $previousChatId = $lastChat && $lastChat->status === ChatStatus::CLOSED ? $lastChat->id : null;

            // Create chat in PENDING status, QUEUED queue_status
            $chat = $this->chats->create([
                'visitor_id' => $visitor->id,
                'previous_chat_id' => $previousChatId,
                'status' => ChatStatus::PENDING->value,
                'queue_status' => QueueStatus::QUEUED->value,
                'priority' => 'normal',
                'subject' => $dto->subject,
                'metadata' => $dto->metadata,
                'started_at' => now(),
            ]);

            // System welcome message — this is visible to the visitor too, so
            // keep it customer-facing; the previous_chat_id link (surfaced to
            // agents separately in the dashboard) carries the internal context.
            // If the AI assistant is on, tell the visitor it will help right
            // away while a human agent is being connected; otherwise keep the
            // original "please wait" wording.
            $botOn = (bool) \App\Models\Setting::getValue('ai_bot_enabled', false)
                && (string) config('services.groq.key') !== '';

            if ($botOn) {
                $welcomeMessage = $previousChatId
                    ? "Welcome back! Our assistant can help you right away. Everything you send is also passed to our team, and a live agent will join shortly for the best help."
                    : "Hi! Our assistant can help you right away. Everything you send is also passed to our team, and a live agent will join shortly for the best help.";
            } else {
                $welcomeMessage = $previousChatId
                    ? 'Welcome back! Please wait while we connect you to an agent...'
                    : 'Chat started. Please wait while we connect you to an agent...';
            }
            $this->systemMessage($chat->id, $welcomeMessage);

            $chat = $chat->fresh(['visitor', 'agent']);

            event(new ChatStarted($chat));

            return $chat;
        });

        // Send WhatsApp group notification after DB commit
        $this->whatsApp->notifyNewChat($chat);

        return $chat;
    }

    /* ================================================================== */
    /*  2. ASSIGN AGENT */
    /* ================================================================== */

    /**
     * Manually assign an agent to a chat (admin or agent acceptance).
     * Transitions: pending → open.
     */
    public function assignAgent(int $chatId, int $agentId): Chat
    {
        return DB::transaction(function () use ($chatId, $agentId) {
            $chat = $this->chats->findById($chatId);

            // Transition to active from queue (assigned isn't used for queue logic but we can jump to ACTIVE directly)
            $chat->status->transitionTo(ChatStatus::ACTIVE);

            $chat = $this->chats->update($chatId, [
                'assigned_agent_id' => $agentId,
                'queue_status' => QueueStatus::PICKED->value,
                'status' => ChatStatus::ACTIVE->value,
            ]);

            $agent = \App\Models\User::find($agentId);
            $this->systemMessage($chatId, "{$agent->name} has joined the conversation.");
            $this->activity->log($agentId, 'chat.assigned', 'Chat', $chatId);

            $chat = $chat->fresh(['visitor', 'agent']);

            event(new ChatAssigned($chat, $chat->agent));

            // Notify all agents/admins that the pending queue count has dropped
            event(new ChatQueueUpdated(Chat::queued()->count()));

            return $chat;
        });
    }

    /* ================================================================== */
    /*  3. SEND MESSAGE */
    /* ================================================================== */

    /**
     * Send a message within an existing chat.
     * Automatically transitions assigned → active on first agent reply.
     * Events are fired AFTER the transaction commits to prevent race conditions
     * where Pusher delivers the event before the row is visible to other queries.
     */
    public function sendMessage(SendMessageDTO $dto)
    {
        $message = DB::transaction(function () use ($dto) {
            $chat = $this->chats->findById($dto->chatId);

            if (
                $dto->senderType === MessageSenderType::AGENT->value
                && $chat->status === ChatStatus::ASSIGNED
            ) {
                $this->transitionStatus($chat, ChatStatus::ACTIVE, $dto->senderId);
            }

            return $this->messages->create([
                'chat_id' => $dto->chatId,
                'sender_type' => $dto->senderType,
                'sender_id' => $dto->senderId,
                'message' => $dto->message,
                'metadata' => $dto->metadata,
            ]);
        });

        // Broadcast after commit so clients can safely re-fetch the message from DB
        $message->load(['chat', 'sender']);
        event(new MessageSent($message));
        event(new \App\Events\NewMessage($message));

        if ($dto->senderType === MessageSenderType::VISITOR->value) {
            $this->whatsApp->notifyVisitorMessage($message->chat, $message->message);

            // While no human agent has joined yet, let the AI assistant give a
            // quick first-response. The job re-checks status before posting, so
            // the moment an agent joins (status leaves PENDING) the bot goes quiet.
            if ($message->chat->status === ChatStatus::PENDING) {
                \App\Jobs\GenerateBotReply::dispatch($dto->chatId);
            }
        }

        return $message;
    }

    /* ================================================================== */
    /*  4. UPDATE STATUS */
    /* ================================================================== */

    /**
     * Transition chat to a new status with full flow validation.
     *
     * Status flow:
     *   pending → assigned → active → solved → closed
     *   assigned/active ↔ followup
     *
     * @throws InvalidArgumentException on invalid transition
     */
    public function updateStatus(int $chatId, ChatStatus $newStatus, int $agentId): Chat
    {
        return DB::transaction(function () use ($chatId, $newStatus, $agentId) {
            $chat = $this->chats->findById($chatId);
            $oldStatus = $chat->status;
            $assignedAgentId = $chat->assigned_agent_id;

            // Validate the transition is allowed
            $chat->status->transitionTo($newStatus);

            $updateData = ['status' => $newStatus->value];

            // Set timestamps on terminal states
            if ($newStatus === ChatStatus::CLOSED) {
                $updateData['ended_at'] = now();
            }

            if ($newStatus === ChatStatus::CLOSED) {
                $updateData['queue_status'] = QueueStatus::NONE->value;
            }

            $chat = $this->chats->update($chatId, $updateData);

            $this->systemMessage($chatId, "Status changed to {$newStatus->label()}.");
            $this->activity->log($agentId, 'chat.status_updated', 'Chat', $chatId, [
                'from' => $oldStatus->value,
                'to' => $newStatus->value,
            ]);

            $chat = $chat->fresh(['visitor', 'agent']);

            // Fire specific event for closed, generic for others
            if ($newStatus === ChatStatus::CLOSED) {
                if ($newStatus === ChatStatus::CLOSED) {
                    event(new ChatClosed($chat));
                }

                // Free up the agent's capacity
                if ($assignedAgentId) {
                    $this->queue->releaseChatFromAgent($chat);
                }
            } else {
                event(new ChatStatusUpdated($chat, $oldStatus, $newStatus));
            }

            return $chat;
        });
    }

    /* ================================================================== */
    /*  5. CLOSE CHAT */
    /* ================================================================== */

    /**
     * Close a chat. Validates the transition and sets ended_at.
     */
    public function closeChat(int $chatId, int $agentId): Chat
    {
        return $this->updateStatus($chatId, ChatStatus::CLOSED, $agentId);
    }

    /* ================================================================== */
    /*  6. TRANSFER CHAT */
    /* ================================================================== */

    /**
     * Transfer a chat to another agent. Creates transfer record.
     */
    public function transferChat(TransferChatDTO $dto): Chat
    {
        return DB::transaction(function () use ($dto) {
            $chat = $this->chats->update($dto->chatId, [
                'assigned_agent_id' => $dto->toAgentId,
            ]);

            $chat->transfers()->create([
                'from_agent_id' => $dto->fromAgentId,
                'to_agent_id' => $dto->toAgentId,
                'reason' => $dto->reason,
            ]);

            $fromAgent = \App\Models\User::find($dto->fromAgentId);
            $toAgent = \App\Models\User::find($dto->toAgentId);
            $this->systemMessage($dto->chatId, "Chat has been transferred from {$fromAgent->name} to {$toAgent->name}.");

            $this->activity->log($dto->fromAgentId, 'chat.transferred', 'Chat', $dto->chatId, [
                'to_agent_id' => $dto->toAgentId,
            ]);

            // Adjust load manually inside transfer instead of releasing and assigning freshly to avoid chat closing
            $fromLoad = \App\Models\AgentChatLoad::where('agent_id', $dto->fromAgentId)->first();
            if ($fromLoad && $fromLoad->active_chats > 0) {
                $fromLoad->decrement('active_chats');
            }

            $toLoad = \App\Models\AgentChatLoad::firstOrCreate(['agent_id' => $dto->toAgentId]);
            $toLoad->increment('active_chats');
            $toLoad->update(['last_assigned_at' => now()]);

            $chat = $chat->fresh(['visitor', 'agent']);

            // Alert queues and specific agents
            event(new \App\Events\AgentLoadUpdated($dto->fromAgentId));
            event(new \App\Events\AgentLoadUpdated($dto->toAgentId));

            event(new ChatTransferred($chat));
            event(new ChatAssigned($chat, $chat->agent));

            return $chat;
        });
    }

    /* ================================================================== */
    /*  7. ACCEPT CHAT (Agent picks a pending chat) */
    /* ================================================================== */

    /**
     * Agent manually accepts a pending chat.
     * Shortcut for assignAgent().
     */
    public function acceptChat(int $chatId, int $agentId): Chat
    {
        return $this->assignAgent($chatId, $agentId);
    }

    /* ================================================================== */
    /*  8. CAPTURE VISITOR EMAIL (offline / no-agent-reply fallback) */
    /* ================================================================== */

    /**
     * Store the email a visitor leaves when no agent has replied yet, and
     * post a visible system message so the agent picking up the chat sees it.
     */
    public function captureVisitorEmail(int $chatId, string $email): Chat
    {
        return DB::transaction(function () use ($chatId, $email) {
            $chat = $this->chats->findById($chatId);
            $chat->visitor->update(['email' => $email]);

            // Neutral wording — this system message is visible to the
            // visitor too (broadcast on the shared chat channel), so it
            // must read fine from either side, not just "Visitor left...".
            $this->systemMessage($chatId, "Email received: {$email}");

            return $chat->fresh(['visitor', 'agent']);
        });
    }

    /* ================================================================== */
    /*  9. MARK SEEN (visitor has read the chat) */
    /* ================================================================== */

    /**
     * Record that the visitor has read the chat up to now, and notify the
     * agent's dashboard in real time. One-directional: there is no
     * equivalent "agent has read this" signal shown to the visitor.
     */
    public function markSeenByVisitor(int $chatId): Chat
    {
        $seenAt = now();
        $chat = $this->chats->update($chatId, ['visitor_last_read_at' => $seenAt]);

        event(new \App\Events\MessageSeen($chatId, $seenAt->toIso8601String()));

        return $chat;
    }

    /* ================================================================== */
    /*  PRIVATE HELPERS */
    /* ================================================================== */

    /**
     * Insert a system-generated message and broadcast it immediately via Pusher.
     * ShouldBroadcastNow fires synchronously, so by the time Pusher delivers the
     * event to subscribers the surrounding transaction is always already committed.
     */
    private function systemMessage(int $chatId, string $text): void
    {
        $message = $this->messages->create([
            'chat_id' => $chatId,
            'sender_type' => MessageSenderType::SYSTEM->value,
            'sender_id' => null,
            'message' => $text,
        ]);

        $message->load('chat');
        event(new \App\Events\NewMessage($message));
    }

    /**
     * Insert an AI assistant (bot) message and broadcast it exactly like a
     * real chat message, so both the visitor's widget and the agent dashboard
     * render it in the timeline. Called by the GenerateBotReply job.
     */
    public function postBotMessage(int $chatId, string $text): void
    {
        $message = $this->messages->create([
            'chat_id' => $chatId,
            'sender_type' => MessageSenderType::BOT->value,
            'sender_id' => null,
            'message' => $text,
        ]);

        $message->load(['chat', 'sender']);
        event(new MessageSent($message));
        event(new \App\Events\NewMessage($message));
    }

    /**
     * Internal status transition — updates DB and logs, but does NOT fire events.
     * Used by sendMessage for auto-transition.
     */
    private function transitionStatus(Chat $chat, ChatStatus $newStatus, ?int $agentId): void
    {
        $oldStatus = $chat->status;
        $oldStatus->transitionTo($newStatus); // validate

        $this->chats->update($chat->id, ['status' => $newStatus->value]);

        $this->activity->log($agentId, 'chat.status_auto', 'Chat', $chat->id, [
            'from' => $oldStatus->value,
            'to' => $newStatus->value,
        ]);
    }
}
