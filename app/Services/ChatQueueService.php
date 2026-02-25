<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Setting;
use Illuminate\Support\Facades\Redis;

class ChatQueueService
{
    /** Redis key for the FIFO waiting queue. */
    private const QUEUE_KEY = 'chat:waiting_queue';

    /** Redis key for queue metadata (per chat). */
    private const META_KEY = 'chat:queue_meta:';

    /** Default queue timeout in seconds (10 minutes). */
    private const DEFAULT_TIMEOUT = 600;

    /**
     * Push a chat to the end of the waiting queue (FIFO).
     */
    public function enqueue(int $chatId): void
    {
        // Add to the tail of the queue
        Redis::rpush(self::QUEUE_KEY, $chatId);

        // Store metadata: queued_at timestamp for timeout tracking
        Redis::hset(self::META_KEY . $chatId, 'queued_at', now()->timestamp);
        Redis::hset(self::META_KEY . $chatId, 'chat_id', $chatId);

        // Set a generous TTL on metadata to auto-cleanup stale entries
        Redis::expire(self::META_KEY . $chatId, 3600);
    }

    /**
     * Pop the next chat from the head of the queue (FIFO order).
     * Returns chat ID or null if queue is empty.
     */
    public function dequeue(): ?int
    {
        $chatId = Redis::lpop(self::QUEUE_KEY);

        if ($chatId === null) {
            return null;
        }

        // Clean up metadata
        Redis::del(self::META_KEY . $chatId);

        return (int) $chatId;
    }

    /**
     * Peek at the next chat without removing it.
     */
    public function peek(): ?int
    {
        $chatId = Redis::lindex(self::QUEUE_KEY, 0);

        return $chatId !== null ? (int) $chatId : null;
    }

    /**
     * Remove a specific chat from the queue (e.g., if visitor disconnects).
     */
    public function remove(int $chatId): bool
    {
        $removed = Redis::lrem(self::QUEUE_KEY, 0, $chatId);
        Redis::del(self::META_KEY . $chatId);

        return $removed > 0;
    }

    /**
     * Get the current queue length.
     */
    public function length(): int
    {
        return (int) Redis::llen(self::QUEUE_KEY);
    }

    /**
     * Get all chat IDs currently in the queue (for monitoring).
     */
    public function all(): array
    {
        return array_map('intval', Redis::lrange(self::QUEUE_KEY, 0, -1));
    }

    /**
     * Get position of a chat in the queue (1-based).
     * Returns null if not in queue.
     */
    public function position(int $chatId): ?int
    {
        $queue = $this->all();
        $index = array_search($chatId, $queue, true);

        return $index !== false ? $index + 1 : null;
    }

    /**
     * Check if a chat has exceeded the queue timeout.
     */
    public function isTimedOut(int $chatId): bool
    {
        $queuedAt = Redis::hget(self::META_KEY . $chatId, 'queued_at');

        if ($queuedAt === null) {
            return false;
        }

        $timeout = $this->getTimeout();

        return (now()->timestamp - (int) $queuedAt) >= $timeout;
    }

    /**
     * Get all timed-out chat IDs from the queue.
     */
    public function getTimedOut(): array
    {
        $timedOut = [];

        foreach ($this->all() as $chatId) {
            if ($this->isTimedOut($chatId)) {
                $timedOut[] = $chatId;
            }
        }

        return $timedOut;
    }

    /**
     * Purge timed-out chats from the queue.
     * Returns the IDs that were removed.
     */
    public function purgeTimedOut(): array
    {
        $timedOut = $this->getTimedOut();

        foreach ($timedOut as $chatId) {
            $this->remove($chatId);
        }

        return $timedOut;
    }

    /**
     * Flush the entire queue (admin/emergency use).
     */
    public function flush(): void
    {
        $chatIds = $this->all();

        Redis::del(self::QUEUE_KEY);

        foreach ($chatIds as $chatId) {
            Redis::del(self::META_KEY . $chatId);
        }
    }

    /**
     * Get the configured queue timeout in seconds.
     * Reads from settings table, falls back to default.
     */
    public function getTimeout(): int
    {
        return (int) (Setting::getValue('queue_timeout') ?? self::DEFAULT_TIMEOUT);
    }
}
