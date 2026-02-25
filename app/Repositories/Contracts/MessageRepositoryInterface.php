<?php

namespace App\Repositories\Contracts;

interface MessageRepositoryInterface
{
    public function getByChatId(int $chatId, int $perPage = 50);
    public function create(array $data);
}
