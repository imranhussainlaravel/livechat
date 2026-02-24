<?php

namespace Src\Agent\Heartbeat;

use Src\Database\Models\Agent;

/**
 * Heartbeat — tracks agent liveness.
 *
 * Agents send periodic heartbeats. If no heartbeat within
 * the threshold, the agent is marked offline by the
 * scheduled heartbeat-check job.
 */
class Heartbeat
{
    private const TIMEOUT_SECONDS = 120;

    /**
     * Record a heartbeat for an agent.
     */
    public function ping(int $agentId): Agent
    {
        $agent = Agent::findOrFail($agentId);
        $agent->update(['last_activity_at' => now()]);
        return $agent;
    }

    /**
     * Mark agents as offline if their last heartbeat exceeds the threshold.
     *
     * @return int Number of agents marked offline
     */
    public function sweepStale(): int
    {
        $threshold = now()->subSeconds(self::TIMEOUT_SECONDS);

        return Agent::where('status', '!=', 'offline')
            ->where('last_activity_at', '<', $threshold)
            ->update(['status' => 'offline']);
    }
}
