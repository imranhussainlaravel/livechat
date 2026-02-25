<?php

namespace App\Repositories;

use App\Models\Followup;
use App\Enums\FollowupStatus;
use App\Repositories\Contracts\FollowupRepositoryInterface;

class FollowupRepository implements FollowupRepositoryInterface
{
    public function findById(int $id)
    {
        return Followup::findOrFail($id);
    }

    public function getByAgent(int $agentId, array $filters = [])
    {
        $query = Followup::where('agent_id', $agentId)->with('chat.visitor');

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('followup_time')->paginate($filters['per_page'] ?? 15);
    }

    public function getPending()
    {
        return Followup::where('status', FollowupStatus::PENDING)
            ->where('followup_time', '<=', now())
            ->with(['chat.visitor', 'agent'])
            ->get();
    }

    public function create(array $data)
    {
        return Followup::create($data);
    }

    public function update(int $id, array $data)
    {
        $followup = $this->findById($id);
        $followup->update($data);

        return $followup->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findById($id)->delete();
    }
}
