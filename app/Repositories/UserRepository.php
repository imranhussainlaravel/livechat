<?php

namespace App\Repositories;

use App\Models\User;
use App\Enums\UserRole;
use App\Repositories\Contracts\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    public function findById(int $id)
    {
        return User::findOrFail($id);
    }

    public function findByEmail(string $email)
    {
        return User::where('email', $email)->first();
    }

    public function getAgents(array $filters = [])
    {
        $query = User::where('role', UserRole::AGENT);

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate($filters['per_page'] ?? 15);
    }

    public function getAvailableAgents()
    {
        return User::where('role', UserRole::AGENT)
            ->where('status', 'online')
            ->withCount(['assignedChats' => fn($q) => $q->whereIn('status', ['assigned', 'active', 'transferred'])])
            ->having('assigned_chats_count', '<', \DB::raw('max_chats'))
            ->orderBy('assigned_chats_count')
            ->get();
    }

    public function updateStatus(int $id, string $status)
    {
        $user = $this->findById($id);
        $user->update(['status' => $status, 'last_seen_at' => now()]);

        return $user;
    }

    public function create(array $data)
    {
        return User::create($data);
    }

    public function update(int $id, array $data)
    {
        $user = $this->findById($id);
        $user->update($data);

        return $user->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->findById($id)->delete();
    }
}
