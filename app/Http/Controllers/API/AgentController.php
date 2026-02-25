<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\TransferChatRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\DTOs\SendMessageDTO;
use App\DTOs\TransferChatDTO;
use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Events\AgentStatusChanged;
use App\Events\TypingIndicator;
use App\Events\AgentJoinedChat;
use App\Events\AgentLeftChat;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ChatService;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

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
     * PATCH /api/agent/chat/{id}/status — Update chat status with flow validation.
     *
     * Flow: pending → open → in_progress → solved → closed
     */
    public function updateChatStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,solved,closed,followup',
        ]);

        try {
            $newStatus = ChatStatus::from($request->status);
            $chat = $this->chatService->updateStatus($id, $newStatus, $request->user()->id);

            return response()->json([
                'message' => "Chat status updated to {$newStatus->label()}.",
                'data'    => new ChatResource($chat),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/agent/chat/{id}/close — Close a chat.
     */
    public function close(Request $request, int $id): JsonResponse
    {
        try {
            $chat = $this->chatService->closeChat($id, $request->user()->id);

            return response()->json([
                'message' => 'Chat closed.',
                'data'    => new ChatResource($chat),
            ]);
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/agent/chat/{id}/typing — Broadcast typing indicator.
     */
    public function typing(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'is_typing' => 'required|boolean',
        ]);

        event(new TypingIndicator(
            chatId: $id,
            userId: $request->user()->id,
            userName: $request->user()->name,
            senderType: 'agent',
            isTyping: $request->boolean('is_typing'),
        ));

        return response()->json(['message' => 'OK']);
    }

    /**
     * POST /api/agent/chat/{id}/join — Agent joins a chat room (presence).
     */
    public function joinChat(Request $request, int $id): JsonResponse
    {
        event(new AgentJoinedChat($id, $request->user()));

        return response()->json(['message' => 'Joined chat.']);
    }

    /**
     * POST /api/agent/chat/{id}/leave — Agent leaves a chat room (presence).
     */
    public function leaveChat(Request $request, int $id): JsonResponse
    {
        event(new AgentLeftChat($id, $request->user()));

        return response()->json(['message' => 'Left chat.']);
    }

    /**
     * GET /api/agent/chat/{id} — Get details of a single chat including messages.
     */
    public function show(int $id): JsonResponse
    {
        $chat = $this->chats->findWithRelations($id, ['visitor', 'agent']);
        $messages = app(\App\Repositories\Contracts\MessageRepositoryInterface::class)->getByChatId($id);

        return response()->json([
            'data' => [
                'chat'     => new ChatResource($chat),
                'messages' => MessageResource::collection($messages),
                'meta'     => [
                    'current_page' => $messages->currentPage(),
                    'last_page'    => $messages->lastPage(),
                    'total'        => $messages->total(),
                ]
            ]
        ]);
    }

    /**
     * POST /api/agent/chat/{id}/visitor-note — Add a note to the visitor.
     */
    public function addVisitorNote(Request $request, int $id): JsonResponse
    {
        $request->validate(['note' => 'required|string|max:1000']);

        $chat = $this->chats->findWithRelations($id, ['visitor']);
        $visitor = $chat->visitor;

        // Append note to metadata array
        $metadata = $visitor->metadata ?? [];
        $metadata['notes'][] = [
            'agent_id'   => $request->user()->id,
            'note'       => $request->note,
            'created_at' => now()->toIso8601String(),
        ];

        $visitor->update(['metadata' => $metadata]);

        $this->activity->log($request->user()->id, 'visitor.note_added', 'Visitor', $visitor->id);

        return response()->json([
            'message' => 'Note added to visitor profile.',
            'data'    => $metadata['notes'],
        ]);
    }

    /**
     * GET /api/agent/metrics — Get performance metrics for the current agent.
     */
    public function metrics(Request $request): JsonResponse
    {
        $agentId = $request->user()->id;

        $activeChats = $this->chats->getActiveCount($agentId);

        $totalResolved = \App\Models\Chat::where('assigned_agent_id', $agentId)
            ->where('status', ChatStatus::SOLVED->value)
            ->count();

        // Calculate average resolution time (ended_at - started_at) in minutes
        $avgResolutionTime = \App\Models\Chat::where('assigned_agent_id', $agentId)
            ->whereNotNull('ended_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_time')
            ->value('avg_time');

        // Total messages sent by this agent today
        $messagesSentToday = \App\Models\ChatMessage::where('sender_id', $agentId)
            ->where('sender_type', MessageSenderType::AGENT->value)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        return response()->json([
            'data' => [
                'active_chats'         => $activeChats,
                'total_resolved'       => $totalResolved,
                'avg_resolution_mins'  => floor((float) $avgResolutionTime),
                'messages_sent_today'  => $messagesSentToday,
            ]
        ]);
    }
}
