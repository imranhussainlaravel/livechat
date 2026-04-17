<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\TransferChatRequest;
use App\DTOs\SendMessageDTO;
use App\DTOs\TransferChatDTO;
use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Events\AgentJoinedChat;
use App\Events\AgentLeftChat;
use App\Events\AgentStatusChanged;
use App\Events\TypingIndicator;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\MessageRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ChatService;
use App\Services\ActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class ChatController extends Controller
{
    public function __construct(
        private ChatService             $chatService,
        private ChatRepositoryInterface $chats,
        private UserRepositoryInterface $users,
        private ActivityService         $activity,
    ) {}

    /**
     * GET /agent/chats — Chat list page.
     */
    public function index(Request $request)
    {
        // By default, showing active assigned/active chats for this agent
        $filters = $request->only(['status', 'per_page']);
        if (!isset($filters['status'])) {
            $filters['status'] = 'active';
        }

        $chats = $this->chats->getByAgent(
            $request->user()->id,
            $filters,
        );

        return view('agent.chats.index', compact('chats'));
    }

    /**
     * GET /agent/chats/{id} — Single chat conversation page.
     */
    public function show(int $id)
    {
        $chat = $this->chats->findWithRelations($id, ['visitor', 'agent']);
        $messages = app(MessageRepositoryInterface::class)->getByChatId($id);

        // Get available agents for transfer dropdown
        $agents = $this->users->getAgents(['status' => 'online', 'per_page' => 50]);

        // Get Interaction Timeline
        $timeline = app(\App\Repositories\Contracts\ActivityRepositoryInterface::class)->getByChat($id);

        return view('agent.chats.show', compact('chat', 'messages', 'agents', 'timeline'));
    }

    /**
     * POST /agent/chats/{id}/accept — Accept a pending chat.
     */
    public function accept(Request $request, int $id)
    {
        $chat = $this->chatService->acceptChat($id, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Chat accepted.', 'redirect' => route('agent.chats.show', $id)]);
        }

        return redirect()->route('agent.chats.show', $id)->with('success', 'Chat accepted.');
    }

    /**
     * POST /agent/chats/{id}/message — Agent sends a message.
     */
    public function message(SendMessageRequest $request, int $id)
    {
        $dto = new SendMessageDTO(
            chatId: $id,
            senderType: MessageSenderType::AGENT->value,
            senderId: $request->user()->id,
            message: $request->validated('message'),
            metadata: $request->validated('metadata'),
        );

        $msg = $this->chatService->sendMessage($dto);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Message sent.',
                'data'    => $msg->toArray(),
            ], 201);
        }

        return back()->with('success', 'Message sent.');
    }

    /**
     * POST /agent/chats/{id}/transfer — Transfer to another agent.
     */
    public function transfer(TransferChatRequest $request, int $id)
    {
        $dto = TransferChatDTO::fromRequest(
            $request->validated(),
            $id,
            $request->user()->id,
        );

        $this->chatService->transferChat($dto);

        if ($request->expectsJson()) {
            return response()->json([
                'message'  => 'Chat transferred.',
                'redirect' => route('agent.chats.index')
            ]);
        }

        return redirect()->route('agent.chats.index')->with('success', 'Chat transferred.');
    }

    /**
     * PATCH /agent/chats/{id}/status — Update chat status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:pending,assigned,active,closed,transferred',
        ]);

        try {
            $newStatus = ChatStatus::from($request->status);
            $this->chatService->updateStatus($id, $newStatus, $request->user()->id);

            if ($request->expectsJson()) {
                return response()->json(['message' => "Chat status updated to {$newStatus->label()}."]);
            }

            return back()->with('success', "Chat status updated to {$newStatus->label()}.");
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * POST /agent/chats/{id}/close — Close a chat.
     */
    public function close(Request $request, int $id)
    {
        try {
            $this->chatService->closeChat($id, $request->user()->id);

            if ($request->expectsJson()) {
                return response()->json([
                    'message'  => 'Chat closed.',
                    'redirect' => route('agent.chats.index')
                ]);
            }

            return redirect()->route('agent.chats.index')->with('success', 'Chat closed.');
        } catch (InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * POST /agent/chats/{id}/visitor-note — Add a note to the visitor.
     */
    public function addVisitorNote(Request $request, int $id)
    {
        $request->validate(['note' => 'required|string|max:1000']);

        $chat = $this->chats->findWithRelations($id, ['visitor']);
        $visitor = $chat->visitor;

        $metadata = $visitor->metadata ?? [];
        $metadata['notes'][] = [
            'agent_id'   => $request->user()->id,
            'note'       => $request->note,
            'created_at' => now()->toIso8601String(),
        ];

        $visitor->update(['metadata' => $metadata]);
        $this->activity->log($request->user()->id, 'visitor.note_added', 'Visitor', $visitor->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Note added.', 'data' => $metadata['notes']]);
        }

        return back()->with('success', 'Note added to visitor profile.');
    }

    /**
     * POST /agent/chats/{id}/typing — Broadcast typing indicator (AJAX only).
     */
    public function typing(Request $request, int $id): JsonResponse
    {
        $request->validate(['is_typing' => 'required|boolean']);

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
     * POST /agent/chats/{id}/join — Agent joins chat room (AJAX only).
     */
    public function joinChat(Request $request, int $id): JsonResponse
    {
        event(new AgentJoinedChat($id, $request->user()));
        return response()->json(['message' => 'Joined chat.']);
    }

    /**
     * POST /agent/chats/{id}/leave — Agent leaves chat room (AJAX only).
     */
    public function leaveChat(Request $request, int $id): JsonResponse
    {
        event(new AgentLeftChat($id, $request->user()));
        return response()->json(['message' => 'Left chat.']);
    }
}
