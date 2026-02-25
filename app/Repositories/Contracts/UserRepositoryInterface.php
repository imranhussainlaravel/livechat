<?php

namespace App\Repositories\Contracts;

interface UserRepositoryInterface
{
    public function findById(int $id);
    public function findByEmail(string $email);
    public function getAgents(array $filters = []);
    public function getAvailableAgents();
    public function updateStatus(int $id, string $status);
    public function create(array $data);
    public function update(int $id, array $data);
    public function delete(int $id): bool;
}
