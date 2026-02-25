<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TicketResource;
use App\DTOs\CreateTicketDTO;
use App\Services\TicketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function __construct(private TicketService $service) {}

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id'        => 'required|exists:chats,id',
            'status'         => 'sometimes|in:interested,not_interested',
            'quotation_sent' => 'sometimes|boolean',
            'amount'         => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $dto    = CreateTicketDTO::fromRequest($request->all(), $request->user()->id);
        $ticket = $this->service->create($dto);

        return response()->json([
            'message' => 'Ticket created.',
            'data'    => new TicketResource($ticket),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'status'         => 'sometimes|in:interested,not_interested',
            'quotation_sent' => 'sometimes|boolean',
            'amount'         => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:2000',
        ]);

        $ticket = $this->service->update($id, $data, $request->user()->id);

        return response()->json([
            'message' => 'Ticket updated.',
            'data'    => new TicketResource($ticket),
        ]);
    }

    public function markInterested(Request $request, int $id): JsonResponse
    {
        $ticket = $this->service->markInterested($id, $request->user()->id);

        return response()->json([
            'message' => 'Ticket marked as interested.',
            'data'    => new TicketResource($ticket),
        ]);
    }

    public function markNotInterested(Request $request, int $id): JsonResponse
    {
        $ticket = $this->service->markNotInterested($id, $request->user()->id);

        return response()->json([
            'message' => 'Ticket marked as not interested.',
            'data'    => new TicketResource($ticket),
        ]);
    }

    public function sendQuotation(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $ticket = $this->service->sendQuotation($id, $request->user()->id, $request->amount);

        return response()->json([
            'message' => 'Quotation generated and sent to visitor.',
            'data'    => new TicketResource($ticket),
        ]);
    }
}
