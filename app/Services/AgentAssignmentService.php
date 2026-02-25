<?php

namespace App\Services;

use App\Enums\ChatStatus;
use App\Models\Chat;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

class AgentAssignmentService
{
    public function __construct(
        private UserRepositoryInterface $users,
        private ChatRepositoryInterface $chats,
    ) {}

    /**
     * Attempt to assign a chat to the least-loaded available agent.
     * Returns true if assigned, false if queued (no agents available).
     */
    public function tryAssign(Chat $chat): bool
    {
        $available = $this->users->getAvailableAgents();

        if ($available->isEmpty()) {
            return false; // Chat stays PENDING in queue
        }

        // Least-loaded first (already ordered by assigned_chats_count)
        $agent = $available->first();

        $this->chats->update($chat->id, [
            'assigned_agent_id' => $agent->id,
            'status'            => ChatStatus::OPEN->value,
        ]);

        return true;
    }
}
