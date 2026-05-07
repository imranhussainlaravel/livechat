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

        // Load relations for the resource
        $chat->load(['visitor', 'agent']);

        return response()->json([
            'success' => true,
            'message' => 'Chat started.',
            'data'    => new ChatResource($chat),
        ], 201);
    }

    /**
     * GET /api/chat/recover — Recover session by token.
     */
    public function recover(Request $request): JsonResponse
    {
        $request->validate(['session_token' => 'required|string']);
        
        $chat = $this->chatService->recoverSession($request->query('session_token'));
        
        if (!$chat) {
            return response()->json([
                'success' => false,
                'message' => 'No session found.',
                'data'    => null
            ]);
        }

        return response()->json([
            'success' => true,
            'data'    => new ChatResource($chat),
        ]);
    }

    /**
     * GET /api/chat/details — Get chat details.
     */
    public function details(Request $request): JsonResponse
    {
        $id = $request->input('chat_id');
        $token = $request->header('X-Session-Token') ?? $request->input('session_token');
        
        $chat = $this->chatService->getChat($id);
        
        if (!$chat || !$chat->visitor || $chat->visitor->session_token !== $token) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $chat->load(['visitor', 'agent']);

        return response()->json([
            'success' => true,
            'data'    => new ChatResource($chat),
        ]);
    }

    /**
     * POST /api/chat/send — Visitor sends a message.
     */
    public function send(SendMessageRequest $request): JsonResponse
    {
        $id = $request->validated('chat_id');
        $token = $request->header('X-Session-Token') ?? $request->input('session_token');
        
        $chat = $this->chatService->getChat($id);
        if (!$chat || !$chat->visitor || $chat->visitor->session_token !== $token) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
     * GET /api/chat/messages — Get chat messages (visitor).
     */
    public function messages(Request $request): JsonResponse
    {
        $id = $request->query('chat_id');
        $token = $request->header('X-Session-Token') ?? $request->input('session_token');

        $chat = $this->chatService->getChat($id);
        if (!$chat || !$chat->visitor || $chat->visitor->session_token !== $token) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
     * POST /api/chat/typing — Visitor broadcasts typing indicator.
     */
    public function typing(Request $request): JsonResponse
    {
        $request->validate([
            'chat_id'      => 'required|integer',
            'is_typing'    => 'required|boolean',
            'visitor_name' => 'nullable|string|max:255',
        ]);

        $id    = $request->validated('chat_id');
        $token = $request->header('X-Session-Token') ?? $request->input('session_token');

        // Validate the visitor owns this chat
        $chat = $this->chatService->getChat($id);
        if (!$chat || !$chat->visitor || $chat->visitor->session_token !== $token) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        event(new TypingIndicator(
            chatId:     $id,
            userId:     0,
            userName:   $request->input('visitor_name', 'Visitor'),
            senderType: 'visitor',
            isTyping:   $request->boolean('is_typing'),
        ));

        return response()->json(['message' => 'OK']);
    }
}
