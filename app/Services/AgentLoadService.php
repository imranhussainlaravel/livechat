<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class AgentLoadService
{
    /** Redis key for agent load tracking. */
    private const LOAD_KEY = 'agent_load:';

    /** Default max concurrent chats per agent if not set on model. */
    private const DEFAULT_MAX_CHATS = 5;

    /**
     * Get the current active chat count for an agent.
     * Uses Redis for fast, concurrency-safe reads.
     */
    public function getActiveCount(int $agentId): int
    {
        $cached = Redis::get(self::LOAD_KEY . $agentId);

        if ($cached !== null) {
            return (int) $cached;
        }

        // Cache miss — compute from DB and seed Redis
        $count = $this->computeFromDb($agentId);
        Redis::setex(self::LOAD_KEY . $agentId, 300, $count); // TTL 5 min

        return $count;
    }

    /**
     * Increment agent's active load (called on chat assignment).
     * Returns the new count after increment.
     */
    public function increment(int $agentId): int
    {
        $key = self::LOAD_KEY . $agentId;

        // Ensure key exists before incrementing
        if (Redis::exists($key) === 0) {
            $count = $this->computeFromDb($agentId);
            Redis::setex($key, 300, $count);
        }

        return (int) Redis::incr($key);
    }

    /**
     * Decrement agent's active load (called on chat close/transfer-from).
     * Returns the new count after decrement.
     */
    public function decrement(int $agentId): int
    {
        $key = self::LOAD_KEY . $agentId;

        if (Redis::exists($key) === 0) {
            $count = $this->computeFromDb($agentId);
            Redis::setex($key, 300, max(0, $count));
        }

        $new = (int) Redis::decr($key);

        // Guard against negative values
        if ($new < 0) {
            Redis::set($key, 0);
            return 0;
        }

        return $new;
    }

    /**
     * Sync Redis load from database (call periodically or on startup).
     */
    public function sync(int $agentId): int
    {
        $count = $this->computeFromDb($agentId);
        Redis::setex(self::LOAD_KEY . $agentId, 300, $count);

        return $count;
    }

    /**
     * Sync all online agents' load from database.
     */
    public function syncAll(): void
    {
        $agents = User::where('role', UserRole::AGENT)
            ->where('status', 'online')
            ->get();

        foreach ($agents as $agent) {
            $this->sync($agent->id);
        }
    }

    /**
     * Get the max concurrent chats allowed for an agent.
     */
    public function getMaxChats(int $agentId): int
    {
        return Cache::remember("agent_max_chats:{$agentId}", 600, function () use ($agentId) {
            $user = User::find($agentId);
            return $user?->max_chats ?? self::DEFAULT_MAX_CHATS;
        });
    }

    /**
     * Check if an agent has capacity for another chat.
     */
    public function hasCapacity(int $agentId): bool
    {
        return $this->getActiveCount($agentId) < $this->getMaxChats($agentId);
    }

    /**
     * Get all online agents with their load and capacity.
     * Returns collection sorted by available capacity (most free first).
     */
    public function getAgentsWithCapacity(): \Illuminate\Support\Collection
    {
        $agents = User::where('role', UserRole::AGENT)
            ->where('status', 'online')
            ->get();

        return $agents->map(function (User $agent) {
            $active = $this->getActiveCount($agent->id);
            $max    = $agent->max_chats ?? self::DEFAULT_MAX_CHATS;

            return (object) [
                'id'        => $agent->id,
                'name'      => $agent->name,
                'active'    => $active,
                'max'       => $max,
                'available' => $max - $active,
            ];
        })
            ->filter(fn($a) => $a->available > 0)
            ->sortByDesc('available')
            ->values();
    }

    /**
     * Compute active chat count from database.
     */
    private function computeFromDb(int $agentId): int
    {
        return User::find($agentId)
            ?->assignedChats()
            ->whereIn('status', ['open', 'in_progress'])
            ->count() ?? 0;
    }
}
