<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StartChatRequest;
use App\Http\Requests\SendMessageRequest;
use App\Http\Resources\ChatResource;
use App\Http\Resources\MessageResource;
use App\DTOs\SendMessageDTO;
use App\DTOs\StartChatDTO;
use App\Enums\MessageSenderType;
use App\Events\TypingIndicator;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Services\ChatService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function __construct(
        private ChatService                $chatService,
        private MessageRepositoryInterface $messages,
    ) {}

    /**
     * POST /api/chat/start — Visitor starts a new chat.
     */
    public function start(StartChatRequest $request): JsonResponse
    {
        $dto  = StartChatDTO::fromRequest($request->validated());
        $chat = $this->chatService->startChat($dto);

        return response()->json([
            'message' => 'Chat started.',
            'data'    => new ChatResource($chat),
        ], 201);
    }

    /**
     * POST /api/chat/{id}/send — Visitor sends a message.
     */
    public function send(SendMessageRequest $request, int $id): JsonResponse
    {
        $dto = new SendMessageDTO(
            chatId: $id,
            senderType: MessageSenderType::VISITOR->value,
            senderId: null,
            message: $request->validated('message'),
            metadata: $request->validated('metadata'),
        );

        $message = $this->chatService->sendMessage($dto);

        return response()->json([
            'message' => 'Message sent.',
            'data'    => new MessageResource($message),
        ], 201);
    }

    /**
     * GET /api/chat/{id}/messages — Get chat messages (visitor).
     */
    public function messages(int $id): JsonResponse
    {
        $messages = $this->messages->getByChatId($id);

        return response()->json([
            'data' => MessageResource::collection($messages),
            'meta' => [
                'current_page' => $messages->currentPage(),
                'last_page'    => $messages->lastPage(),
                'total'        => $messages->total(),
            ],
        ]);
    }

    /**
     * POST /api/chat/{id}/typing — Visitor broadcasts typing indicator.
     */
    public function typing(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'is_typing'    => 'required|boolean',
            'visitor_name' => 'nullable|string|max:255',
        ]);

        event(new TypingIndicator(
            chatId: $id,
            userId: 0,
            userName: $request->input('visitor_name', 'Visitor'),
            senderType: 'visitor',
            isTyping: $request->boolean('is_typing'),
        ));

        return response()->json(['message' => 'OK']);
    }
}
