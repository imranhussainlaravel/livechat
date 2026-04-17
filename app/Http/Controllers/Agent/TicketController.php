<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\DTOs\CreateTicketDTO;
use App\Services\TicketService;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(
        private TicketService $service,
        private \App\Repositories\Contracts\TicketRepositoryInterface $tickets
    ) {}

    /**
     * GET /agent/tickets — List all tickets for the agent.
     */
    public function index(Request $request)
    {
        $tickets = $this->tickets->paginate(15, ['agent_id' => $request->user()->id]);
        return view('agent.tickets.index', compact('tickets'));
    }

    /**
     * POST /agent/tickets — Create a ticket from a chat.
     */
    public function store(Request $request)
    {
        $request->validate([
            'chat_id'        => 'required|exists:chats,id',
            'status'         => 'sometimes|in:interested,not_interested',
            'quotation_sent' => 'sometimes|boolean',
            'amount'         => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $dto = CreateTicketDTO::fromRequest($request->all(), $request->user()->id);
        $this->service->create($dto);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Ticket created.'], 201);
        }

        return back()->with('success', 'Ticket created.');
    }

    /**
     * PATCH /agent/tickets/{id} — Update ticket.
     */
    public function update(Request $request, int $id)
    {
        $data = $request->validate([
            'status'         => 'sometimes|in:pending,interested,not_interested',
            'quotation_sent' => 'sometimes|boolean',
            'amount'         => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $this->service->update($id, $data, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Ticket updated.']);
        }

        return back()->with('success', 'Ticket updated.');
    }

    /**
     * PATCH /agent/tickets/{id}/interested
     */
    public function markInterested(Request $request, int $id)
    {
        $this->service->markInterested($id, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Ticket marked as interested.']);
        }

        return back()->with('success', 'Ticket marked as interested.');
    }

    /**
     * PATCH /agent/tickets/{id}/not-interested
     */
    public function markNotInterested(Request $request, int $id)
    {
        $this->service->markNotInterested($id, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Ticket marked as not interested.']);
        }

        return back()->with('success', 'Ticket marked as not interested.');
    }

    /**
     * POST /agent/tickets/{id}/quotation
     */
    public function sendQuotation(Request $request, int $id)
    {
        $request->validate(['amount' => 'required|numeric|min:0.01']);

        $this->service->sendQuotation($id, $request->user()->id, $request->amount);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Quotation sent.']);
        }

        return back()->with('success', 'Quotation sent to visitor.');
    }
}
