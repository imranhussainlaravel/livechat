<?php

namespace Src\Agent\AgentManagement;

use Src\Agent\AgentManagement\Contracts\AgentManagementInterface;
use Src\Database\Models\Agent;
use Illuminate\Support\Facades\Hash;

/**
 * Agent Management — CRUD and status management for agents.
 *
 * All state persisted to DB. Status transitions are immediate writes.
 */
class AgentManagement implements AgentManagementInterface
{
    public function create(array $data): Agent
    {
        return Agent::create([
            'name'            => $data['name'],
            'email'           => $data['email'],
            'password'        => Hash::make($data['password']),
            'max_concurrency' => $data['max_concurrency'] ?? 5,
            'status'          => 'offline',
        ]);
    }

    public function update(int $agentId, array $data): Agent
    {
        $agent = Agent::findOrFail($agentId);
        $agent->update(array_filter([
            'name'            => $data['name'] ?? null,
            'email'           => $data['email'] ?? null,
            'max_concurrency' => $data['max_concurrency'] ?? null,
        ]));

        if (! empty($data['password'])) {
            $agent->update(['password' => Hash::make($data['password'])]);
        }

        return $agent->fresh();
    }

    public function setStatus(int $agentId, string $status): Agent
    {
        $agent = Agent::findOrFail($agentId);
        $agent->update([
            'status'           => $status,
            'last_activity_at' => now(),
        ]);
        return $agent->fresh();
    }

    public function delete(int $agentId): void
    {
        Agent::findOrFail($agentId)->delete();
    }

    public function findAvailable(): \Illuminate\Database\Eloquent\Collection
    {
        return Agent::where('status', 'online')
            ->whereRaw('current_load < max_concurrency')
            ->orderBy('current_load')
            ->get();
    }
}
