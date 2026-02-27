<?php

namespace App\Repositories\Contracts;

interface ActivityRepositoryInterface
{
    public function log(array $data);
    public function getByUser(int $userId, int $perPage = 20);
    public function getRecent(int $limit = 50);
    public function getByChat(int $chatId);
}
