<?php

namespace App\Repositories;

use App\Models\Activity;
use App\Repositories\Contracts\ActivityRepositoryInterface;

class ActivityRepository implements ActivityRepositoryInterface
{
    public function log(array $data)
    {
        return Activity::create($data);
    }

    public function getByUser(int $userId, int $perPage = 20)
    {
        return Activity::where('user_id', $userId)
            ->latest()
            ->paginate($perPage);
    }

    public function getRecent(int $limit = 50)
    {
        return Activity::with('user')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getByChat(int $chatId)
    {
        return Activity::with('user')
            ->where(function ($q) use ($chatId) {
                // Actions directly on the Chat
                $q->where('reference_type', 'Chat')
                    ->where('reference_id', $chatId);
            })
            ->orWhere(function ($q) use ($chatId) {
                // Actions on Tickets belonging to this chat
                $q->where('reference_type', 'Ticket')
                    ->whereIn('reference_id', function ($sub) use ($chatId) {
                        $sub->select('id')->from('tickets')->where('chat_id', $chatId);
                    });
            })
            ->orWhere(function ($q) use ($chatId) {
                // Actions on Followups belonging to this chat
                $q->where('reference_type', 'Followup')
                    ->whereIn('reference_id', function ($sub) use ($chatId) {
                        $sub->select('id')->from('followups')->where('chat_id', $chatId);
                    });
            })
            ->latest()
            ->get();
    }
}
