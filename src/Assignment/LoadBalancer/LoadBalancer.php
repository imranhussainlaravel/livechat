<?php

namespace Src\Assignment\LoadBalancer;

use Src\Database\Models\Agent;
use Src\Database\Models\Conversation;
use Illuminate\Support\Facades\DB;

/**
 * Load Balancer — assigns conversations to agents using least-load strategy.
 *
 * Algorithm:
 *  1. Find agents in the conversation's queue (or all if no queue)
 *  2. Filter to online agents below max concurrency
 *  3. Pick the agent with the lowest current_load
 *  4. Atomically increment their load and assign the conversation
 */
class LoadBalancer
{
    /**
     * Assign a conversation to the best available agent.
     *
     * @return Agent|null The assigned agent, or null if none available
     */
    public function assign(Conversation $conversation): ?Agent
    {
        return DB::transaction(function () use ($conversation) {
            $query = Agent::where('status', 'online')
                ->whereRaw('current_load < max_concurrency')
                ->lockForUpdate();

            // Scope to queue's agents if queue has agent associations
            if ($conversation->queue_id) {
                $query->whereHas('queues', fn($q) => $q->where('queues.id', $conversation->queue_id));
            }

            $agent = $query->orderBy('current_load')->first();

            if (! $agent) {
                return null;
            }

            $agent->increment('current_load');
            $conversation->update([
                'current_agent_id' => $agent->id,
                'state'            => 'active',
            ]);

            return $agent;
        });
    }

    /**
     * Release an agent's load slot when a conversation ends.
     */
    public function release(Agent $agent): void
    {
        DB::transaction(function () use ($agent) {
            $agent->lockForUpdate()->decrement('current_load');
            // Ensure load never goes negative
            Agent::where('id', $agent->id)
                ->where('current_load', '<', 0)
                ->update(['current_load' => 0]);
        });
    }
}
