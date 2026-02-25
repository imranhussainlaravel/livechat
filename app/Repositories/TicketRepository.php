<?php

namespace App\Repositories;

use App\Models\Ticket;
use App\Repositories\Contracts\TicketRepositoryInterface;

class TicketRepository implements TicketRepositoryInterface
{
    public function findById(int $id)
    {
        return Ticket::findOrFail($id);
    }

    public function getByChatId(int $chatId)
    {
        return Ticket::where('chat_id', $chatId)->first();
    }

    public function create(array $data)
    {
        return Ticket::create($data);
    }

    public function update(int $id, array $data)
    {
        $ticket = $this->findById($id);
        $ticket->update($data);

        return $ticket->fresh();
    }
}
