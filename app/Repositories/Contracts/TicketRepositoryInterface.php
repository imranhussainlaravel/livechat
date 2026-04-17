<?php

namespace App\Repositories\Contracts;

interface TicketRepositoryInterface
{
    public function findById(int $id);
    public function getByChatId(int $chatId);
    public function create(array $data);
    public function update(int $id, array $data);
    public function paginate(int $perPage = 15, array $filters = []);
}
