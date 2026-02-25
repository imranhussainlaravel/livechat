<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\FollowupResource;
use App\DTOs\CreateFollowupDTO;
use App\Repositories\Contracts\FollowupRepositoryInterface;
use App\Services\FollowupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FollowupController extends Controller
{
    public function __construct(
        private FollowupService                $service,
        private FollowupRepositoryInterface    $followups,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $followups = $this->followups->getByAgent(
            $request->user()->id,
            $request->only(['status', 'per_page']),
        );

        return response()->json([
            'data' => FollowupResource::collection($followups),
            'meta' => [
                'current_page' => $followups->currentPage(),
                'last_page'    => $followups->lastPage(),
                'total'        => $followups->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id'       => 'required|exists:chats,id',
            'followup_time' => 'required|date|after:now',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $dto      = CreateFollowupDTO::fromRequest($request->all(), $request->user()->id);
        $followup = $this->service->create($dto);

        return response()->json([
            'message' => 'Follow-up created.',
            'data'    => new FollowupResource($followup),
        ], 201);
    }

    public function complete(Request $request, int $id): JsonResponse
    {
        $followup = $this->service->complete($id, $request->user()->id);

        return response()->json([
            'message' => 'Follow-up completed.',
            'data'    => new FollowupResource($followup),
        ]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $followup = $this->service->cancel($id, $request->user()->id);

        return response()->json([
            'message' => 'Follow-up cancelled.',
            'data'    => new FollowupResource($followup),
        ]);
    }
}
