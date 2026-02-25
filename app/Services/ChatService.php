<?php

namespace App\Services;

use App\DTOs\SendMessageDTO;
use App\DTOs\StartChatDTO;
use App\DTOs\TransferChatDTO;
use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Events\ChatClosed;
use App\Events\ChatStarted;
use App\Events\ChatTransferred;
use App\Events\MessageSent;
use App\Models\Chat;
use App\Models\Visitor;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatService
{
    public function __construct(
        private ChatRepositoryInterface    $chats,
        private MessageRepositoryInterface $messages,
        private AgentAssignmentService     $assignment,
        private ActivityService            $activity,
    ) {}

    /**
     * Start a new chat from a visitor widget.
     */
    public function startChat(StartChatDTO $dto): Chat
    {
        return DB::transaction(function () use ($dto) {
            // Find or create visitor
            $visitor = Visitor::firstOrCreate(
                ['session_token' => $dto->sessionToken],
                [
                    'name'     => $dto->visitorName,
                    'email'    => $dto->visitorEmail,
                    'metadata' => $dto->metadata,
                ]
            );

            // Create the chat
            $chat = $this->chats->create([
                'visitor_id'  => $visitor->id,
                'status'      => ChatStatus::PENDING->value,
                'priority'    => 'normal',
                'subject'     => $dto->subject,
                'metadata'    => $dto->metadata,
                'started_at'  => now(),
            ]);

            // Try auto-assign to available agent
            $this->assignment->tryAssign($chat);

            event(new ChatStarted($chat->fresh(['visitor', 'agent'])));

            return $chat->fresh(['visitor', 'agent']);
        });
    }

    /**
     * Send a message within an existing chat.
     */
    public function sendMessage(SendMessageDTO $dto)
    {
        $message = $this->messages->create([
            'chat_id'     => $dto->chatId,
            'sender_type' => $dto->senderType,
            'sender_id'   => $dto->senderId,
            'message'     => $dto->message,
            'metadata'    => $dto->metadata,
        ]);

        event(new MessageSent($message->load('chat')));

        return $message;
    }

    /**
     * Agent accepts a pending chat.
     */
    public function acceptChat(int $chatId, int $agentId): Chat
    {
        return DB::transaction(function () use ($chatId, $agentId) {
            $chat = $this->chats->update($chatId, [
                'assigned_agent_id' => $agentId,
                'status'            => ChatStatus::OPEN->value,
            ]);

            $this->messages->create([
                'chat_id'     => $chatId,
                'sender_type' => MessageSenderType::SYSTEM->value,
                'sender_id'   => null,
                'message'     => 'Agent has joined the conversation.',
            ]);

            $this->activity->log($agentId, 'chat.accepted', 'Chat', $chatId);

            return $chat->fresh(['visitor', 'agent']);
        });
    }

    /**
     * Transfer chat to another agent.
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

            $this->messages->create([
                'chat_id'     => $dto->chatId,
                'sender_type' => MessageSenderType::SYSTEM->value,
                'sender_id'   => null,
                'message'     => 'Chat has been transferred to another agent.',
            ]);

            $this->activity->log($dto->fromAgentId, 'chat.transferred', 'Chat', $dto->chatId);

            event(new ChatTransferred($chat->fresh(['visitor', 'agent'])));

            return $chat->fresh(['visitor', 'agent']);
        });
    }

    /**
     * Close a chat.
     */
    public function closeChat(int $chatId, int $agentId): Chat
    {
        return DB::transaction(function () use ($chatId, $agentId) {
            $chat = $this->chats->update($chatId, [
                'status'   => ChatStatus::CLOSED->value,
                'ended_at' => now(),
            ]);

            $this->messages->create([
                'chat_id'     => $chatId,
                'sender_type' => MessageSenderType::SYSTEM->value,
                'sender_id'   => null,
                'message'     => 'Chat has been closed.',
            ]);

            $this->activity->log($agentId, 'chat.closed', 'Chat', $chatId);

            event(new ChatClosed($chat->fresh(['visitor', 'agent'])));

            return $chat->fresh(['visitor', 'agent']);
        });
    }
}
