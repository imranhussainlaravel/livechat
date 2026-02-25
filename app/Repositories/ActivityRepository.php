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
}
