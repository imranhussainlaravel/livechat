<?php

namespace App\Repositories\Contracts;

interface ChatRepositoryInterface
{
    public function findById(int $id);
    public function findWithRelations(int $id, array $relations = []);
    public function getByStatus(string $status, int $perPage = 15);
    public function getByAgent(int $agentId, array $filters = []);
    public function create(array $data);
    public function update(int $id, array $data);
    public function getPendingChats();
    public function getActiveCount(int $agentId): int;
}
