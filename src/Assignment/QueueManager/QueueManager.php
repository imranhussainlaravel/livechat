<?php

namespace Src\Assignment\QueueManager;

use Src\Database\Models\Queue;

/**
 * Queue Manager — manages conversation routing queues.
 *
 * Queues partition conversations by topic/department.
 * Each queue can have assigned agents.
 */
class QueueManager
{
    public function create(array $data): Queue
    {
        return Queue::create([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
        ]);
    }

    public function update(int $queueId, array $data): Queue
    {
        $queue = Queue::findOrFail($queueId);
        $queue->update(array_filter($data));
        return $queue->fresh();
    }

    public function delete(int $queueId): void
    {
        Queue::findOrFail($queueId)->delete();
    }

    /**
     * Get the default queue (first active queue).
     */
    public function defaultQueue(): ?Queue
    {
        return Queue::where('is_active', true)->first();
    }
}
