<?php

namespace App\Services;

use App\DTOs\SendMessageDTO;
use App\DTOs\StartChatDTO;
use App\DTOs\TransferChatDTO;
use App\Enums\ChatStatus;
use App\Enums\QueueStatus;
use App\Enums\MessageSenderType;
use App\Events\ChatAssigned;
use App\Events\ChatClosed;
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
        private ChatRepositoryInterface    $chats,
        private MessageRepositoryInterface $messages,
        private QueueService               $queue,
        private ActivityService            $activity,
    ) {}

    /* ================================================================== */
    /*  1. START CHAT                                                      */
    /* ================================================================== */

    /**
     * Visitor starts a new chat from the website widget.
     * Creates visitor (if new), creates chat, attempts auto-assignment.
     */
    public function startChat(StartChatDTO $dto): Chat
    {
        return DB::transaction(function () use ($dto) {
            // Find or create visitor by session token
            $visitor = Visitor::firstOrCreate(
                ['session_token' => $dto->sessionToken],
                [
                    'name'     => $dto->visitorName,
                    'email'    => $dto->visitorEmail,
                    'metadata' => $dto->metadata,
                ]
            );

            // Create chat in PENDING status, QUEUED queue_status
            $chat = $this->chats->create([
                'visitor_id'   => $visitor->id,
                'status'       => ChatStatus::PENDING->value,
                'queue_status' => QueueStatus::QUEUED->value,
                'priority'     => 'normal',
                'subject'      => $dto->subject,
                'metadata'     => $dto->metadata,
                'started_at'   => now(),
            ]);

            // System welcome message
            $this->systemMessage($chat->id, 'Chat started. Please wait while we connect you to an agent...');

            $chat = $chat->fresh(['visitor', 'agent']);

            event(new ChatStarted($chat));

            return $chat;
        });
    }

    /* ================================================================== */
    /*  2. ASSIGN AGENT                                                    */
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
                'queue_status'      => QueueStatus::PICKED->value,
                'status'            => ChatStatus::ACTIVE->value,
            ]);

            $this->systemMessage($chatId, 'Agent has joined the conversation.');
            $this->activity->log($agentId, 'chat.assigned', 'Chat', $chatId);

            $chat = $chat->fresh(['visitor', 'agent']);

            event(new ChatAssigned($chat, $chat->agent));

            return $chat;
        });
    }

    /* ================================================================== */
    /*  3. SEND MESSAGE                                                    */
    /* ================================================================== */

    /**
     * Send a message within an existing chat.
     * Automatically transitions open → in_progress on first agent reply.
     */
    public function sendMessage(SendMessageDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            $chat = $this->chats->findById($dto->chatId);

            // Auto-transition: assigned → active on first agent message
            if (
                $dto->senderType === MessageSenderType::AGENT->value
                && $chat->status === ChatStatus::ASSIGNED
            ) {
                $this->transitionStatus($chat, ChatStatus::ACTIVE, $dto->senderId);
            }

            $message = $this->messages->create([
                'chat_id'     => $dto->chatId,
                'sender_type' => $dto->senderType,
                'sender_id'   => $dto->senderId,
                'message'     => $dto->message,
                'metadata'    => $dto->metadata,
            ]);

            event(new MessageSent($message->load('chat')));

            return $message;
        });
    }

    /* ================================================================== */
    /*  4. UPDATE STATUS                                                   */
    /* ================================================================== */

    /**
     * Transition chat to a new status with full flow validation.
     *
     * Status flow:
     *   pending → open → in_progress → solved → closed
     *   open/in_progress ↔ followup
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
                'to'   => $newStatus->value,
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
    /*  5. CLOSE CHAT                                                      */
    /* ================================================================== */

    /**
     * Close a chat. Validates the transition and sets ended_at.
     */
    public function closeChat(int $chatId, int $agentId): Chat
    {
        return $this->updateStatus($chatId, ChatStatus::CLOSED, $agentId);
    }

    /* ================================================================== */
    /*  6. TRANSFER CHAT                                                   */
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
                'to_agent_id'   => $dto->toAgentId,
                'reason'        => $dto->reason,
            ]);

            $this->systemMessage($dto->chatId, 'Chat has been transferred to another agent.');
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
    /*  7. ACCEPT CHAT (Agent picks a pending chat)                        */
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
    /*  PRIVATE HELPERS                                                    */
    /* ================================================================== */

    /**
     * Insert a system-generated message into the chat timeline.
     */
    private function systemMessage(int $chatId, string $text): void
    {
        $this->messages->create([
            'chat_id'     => $chatId,
            'sender_type' => MessageSenderType::SYSTEM->value,
            'sender_id'   => null,
            'message'     => $text,
        ]);
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
            'to'   => $newStatus->value,
        ]);
    }
}
