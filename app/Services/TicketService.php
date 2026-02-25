<?php

namespace App\Services;

use App\DTOs\CreateTicketDTO;
use App\Repositories\Contracts\TicketRepositoryInterface;

class TicketService
{
    public function __construct(
        private TicketRepositoryInterface $tickets,
        private ActivityService           $activity,
    ) {}

    public function create(CreateTicketDTO $dto)
    {
        $ticket = $this->tickets->create([
            'chat_id'        => $dto->chatId,
            'agent_id'       => $dto->agentId,
            'status'         => $dto->status,
            'quotation_sent' => $dto->quotationSent,
            'amount'         => $dto->amount,
            'notes'          => $dto->notes,
        ]);

        $this->activity->log($dto->agentId, 'ticket.created', 'Ticket', $ticket->id);

        return $ticket;
    }

    public function update(int $ticketId, array $data, int $agentId)
    {
        $ticket = $this->tickets->update($ticketId, $data);

        $this->activity->log($agentId, 'ticket.updated', 'Ticket', $ticketId);

        return $ticket;
    }
}
