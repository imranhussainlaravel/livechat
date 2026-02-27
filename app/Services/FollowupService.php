<?php

namespace App\Services;

use App\DTOs\CreateFollowupDTO;
use App\Enums\FollowupStatus;
use App\Repositories\Contracts\FollowupRepositoryInterface;

class FollowupService
{
    public function __construct(
        private FollowupRepositoryInterface $followups,
        private ActivityService             $activity,
    ) {}

    public function create(CreateFollowupDTO $dto)
    {
        $followup = $this->followups->create([
            'chat_id'       => $dto->chatId,
            'agent_id'      => $dto->agentId,
            'followup_time' => $dto->followupTime,
            'status'        => FollowupStatus::PENDING->value,
            'notes'         => $dto->notes,
        ]);

        $this->activity->log($dto->agentId, 'followup.created', 'Followup', $followup->id);

        $followup->loadMissing('agent');
        $agentName = $followup->agent->name ?? 'Agent';

        // Broadcast to Chat Room
        event(new \App\Events\FollowupScheduled(
            chatId: $dto->chatId,
            scheduledAt: $dto->followupTime,
            agentName: $agentName
        ));

        return $followup;
    }

    public function complete(int $followupId, int $agentId)
    {
        $followup = $this->followups->update($followupId, [
            'status' => FollowupStatus::COMPLETED->value,
        ]);

        $this->activity->log($agentId, 'followup.completed', 'Followup', $followupId);

        return $followup;
    }

    public function cancel(int $followupId, int $agentId)
    {
        $followup = $this->followups->update($followupId, [
            'status' => FollowupStatus::CANCELLED->value,
        ]);

        $this->activity->log($agentId, 'followup.cancelled', 'Followup', $followupId);

        return $followup;
    }
}
