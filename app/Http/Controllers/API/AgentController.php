<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\TransferChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\DTOs\SendMessageDTO;
use App\DTOs\TransferChatDTO;
use App\Enums\MessageSenderType;
use App\Events\AgentStatusChanged;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ChatService;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(
        private ChatService             $chatService,
        private ChatRepositoryInterface $chats,
        private UserRepositoryInterface $users,
        private ActivityService         $activity,
    ) {}

    /**
     * GET /api/agent/chats — List chats for current agent.
     */
    public function chats(Request $request): JsonResponse
    {
        $chats = $this->chats->getByAgent(
            $request->user()->id,
            $request->only(['status', 'per_page']),
        );

        return response()->json([
            'data' => ChatResource::collection($chats),
            'meta' => [
                'current_page' => $chats->currentPage(),
                'last_page'    => $chats->lastPage(),
                'total'        => $chats->total(),
            ],
        ]);
    }

    /**
     * POST /api/agent/chat/{id}/accept — Accept a pending chat.
     */
    public function accept(Request $request, int $id): JsonResponse
    {
        $chat = $this->chatService->acceptChat($id, $request->user()->id);

        return response()->json([
            'message' => 'Chat accepted.',
            'data'    => new ChatResource($chat),
        ]);
    }

    /**
     * POST /api/agent/chat/{id}/message — Agent sends a message.
     */
    public function message(SendMessageRequest $request, int $id): JsonResponse
    {
        $dto = new SendMessageDTO(
            chatId: $id,
            senderType: MessageSenderType::AGENT->value,
            senderId: $request->user()->id,
            message: $request->validated('message'),
            metadata: $request->validated('metadata'),
        );

        $msg = $this->chatService->sendMessage($dto);

        return response()->json([
            'message' => 'Message sent.',
            'data'    => new MessageResource($msg),
        ], 201);
    }

    /**
     * POST /api/agent/chat/{id}/transfer — Transfer to another agent.
     */
    public function transfer(TransferChatRequest $request, int $id): JsonResponse
    {
        $dto = TransferChatDTO::fromRequest(
            $request->validated(),
            $id,
            $request->user()->id,
        );

        $chat = $this->chatService->transferChat($dto);

        return response()->json([
            'message' => 'Chat transferred.',
            'data'    => new ChatResource($chat),
        ]);
    }

    /**
     * POST /api/agent/chat/{id}/close — Close a chat.
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $chat = $this->chatService->closeChat($id, $request->user()->id);

        return response()->json([
            'message' => 'Chat closed.',
            'data'    => new ChatResource($chat),
        ]);
    }

    /**
     * PATCH /api/agent/status — Update agent's own status.
     */
    public function updateStatus(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:online,away,busy,offline',
        ]);

        $agent = $this->users->updateStatus($request->user()->id, $request->status);

        $this->activity->log($agent->id, 'agent.status_changed', 'User', $agent->id, ['status' => $request->status]);

        event(new AgentStatusChanged($agent));

        return response()->json([
            'message' => 'Status updated.',
            'data'    => ['status' => $agent->status],
        ]);
    }
}
