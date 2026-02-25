<?php

namespace App\Repositories;

use App\Models\Chat;
use App\Enums\ChatStatus;
use App\Repositories\Contracts\ChatRepositoryInterface;

class ChatRepository implements ChatRepositoryInterface
{
    public function findById(int $id)
    {
        return Chat::findOrFail($id);
    }

    public function findWithRelations(int $id, array $relations = [])
    {
        return Chat::with($relations)->findOrFail($id);
    }

    public function getByStatus(string $status, int $perPage = 15)
    {
        return Chat::where('status', $status)
            ->with(['visitor', 'agent'])
            ->latest()
            ->paginate($perPage);
    }

    public function getByAgent(int $agentId, array $filters = [])
    {
        $query = Chat::where('assigned_agent_id', $agentId)
            ->with(['visitor', 'messages' => fn($q) => $q->latest()->limit(1)]);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function create(array $data)
    {
        return Chat::create($data);
    }

    public function update(int $id, array $data)
    {
        $chat = $this->findById($id);
        $chat->update($data);

        return $chat->fresh();
    }

    public function getPendingChats()
    {
        return Chat::where('status', ChatStatus::PENDING)
            ->with('visitor')
            ->orderBy('created_at')
            ->get();
    }

    public function getActiveCount(int $agentId): int
    {
        return Chat::where('assigned_agent_id', $agentId)
            ->whereIn('status', array_map(fn($s) => $s->value, ChatStatus::activeStates()))
            ->count();
    }
}
