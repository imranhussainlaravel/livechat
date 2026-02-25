<?php

namespace App\Repositories\Contracts;

interface FollowupRepositoryInterface
{
    public function findById(int $id);
    public function getByAgent(int $agentId, array $filters = []);
    public function getPending();
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;
}
