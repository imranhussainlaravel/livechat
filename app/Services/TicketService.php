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

    public function markInterested(int $ticketId, int $agentId)
    {
        $ticket = $this->tickets->update($ticketId, [
            'status' => \App\Enums\TicketStatus::INTERESTED->value,
        ]);

        $this->activity->log($agentId, 'ticket.status_changed', 'Ticket', $ticketId, ['status' => 'interested']);

        return $ticket;
    }

    public function markNotInterested(int $ticketId, int $agentId)
    {
        $ticket = $this->tickets->update($ticketId, [
            'status' => \App\Enums\TicketStatus::NOT_INTERESTED->value,
        ]);

        $this->activity->log($agentId, 'ticket.status_changed', 'Ticket', $ticketId, ['status' => 'not_interested']);

        return $ticket;
    }

    public function sendQuotation(int $ticketId, int $agentId, float $amount)
    {
        $ticket = $this->tickets->update($ticketId, [
            'quotation_sent' => true,
            'amount'         => $amount,
        ]);

        // Eager load relationships needed for the email
        $ticket->loadMissing(['chat.visitor', 'agent']);

        // Queue the quotation email
        \Illuminate\Support\Facades\Mail::to($ticket->chat->visitor->email)
            ->queue(new \App\Mail\QuotationMail($ticket));

        $this->activity->log($agentId, 'ticket.quotation_sent', 'Ticket', $ticketId, ['amount' => $amount]);

        return $ticket;
    }
}
