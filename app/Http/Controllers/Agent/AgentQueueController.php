<?php

namespace App\Http\Controllers\Agent;

use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Enums\QueueStatus;
use App\Events\ChatAssigned;
use App\Events\ChatQueueUpdated;
use App\Events\NewMessage;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentQueueController extends Controller
{
    /**
     * Show the Queue List to the Agent.
     */
    public function getQueueChats()
    {
        $chats = Chat::queued()
            ->orderBy('created_at', 'asc')
            ->with(['visitor', 'previousChat', 'messages' => fn ($q) => $q->latest()->limit(1)])
            ->get();

        return view('agent.queue.index', compact('chats'));
    }

    /**
     * Agent joins a chat from the queue.
     */
    public function joinChat(Request $request, $conversation_id)
    {
        $agentId = $request->user()->id;
        $agentName = $request->user()->name;
        $user = $request->user();

        try {
            DB::transaction(function () use ($conversation_id, $agentId, $agentName, $user) {
                // IMPORTANT: Prevent multiple agents picking same chat
                $chat = Chat::queued()
                    ->where('id', $conversation_id)
                    ->lockForUpdate()
                    ->first();

                if (! $chat) {
                    throw new \Exception('This chat is no longer available in the queue.');
                }

                // System update conversation
                $chat->update([
                    'assigned_agent_id' => $agentId,
                    'queue_status' => QueueStatus::PICKED,
                    'status' => ChatStatus::ACTIVE,
                ]);

                // Insert System message and broadcast it immediately so anyone
                // with the chat open (or the widget) sees it without refreshing.
                $message = ChatMessage::create([
                    'chat_id' => $chat->id,
                    'sender_type' => MessageSenderType::SYSTEM,
                    'sender_id' => null,
                    'message' => 'Agent '.$agentName.' joined the chat',
                ]);
                $message->load('chat');
                event(new NewMessage($message));

                // Fire event
                event(new ChatAssigned($chat->fresh(['visitor', 'agent']), $user));

                // Let other agents/admins know the pending queue count just dropped
                $this->broadcastQueueCount();
            });

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'You have joined the chat.',
                    'redirect' => route('agent.chats.show', $conversation_id),
                ]);
            }

            return redirect()->route('agent.chats.show', $conversation_id)->with('success', 'You have joined the chat.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 400);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Delete a pending chat that has been waiting in the queue for 24+ hours
     * and was never picked up. Chats are soft-deleted so they can be restored
     * if needed.
     */
    public function destroy(Request $request, $conversation_id)
    {
        $chat = Chat::queued()->where('id', $conversation_id)->first();

        if (! $chat) {
            $message = 'This chat is no longer pending and cannot be deleted here.';

            return $request->expectsJson()
                ? response()->json(['error' => $message], 400)
                : back()->with('error', $message);
        }

        if ($chat->created_at->gt(now()->subHours(24))) {
            $message = 'This chat can only be deleted after it has been waiting for 24 hours.';

            return $request->expectsJson()
                ? response()->json(['error' => $message], 400)
                : back()->with('error', $message);
        }

        $chat->delete();

        $this->broadcastQueueCount();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Chat deleted.']);
        }

        return back()->with('success', 'Chat deleted.');
    }

    /**
     * Notify agents/admins of the current pending-queue count.
     */
    private function broadcastQueueCount(): void
    {
        event(new ChatQueueUpdated(Chat::queued()->count()));
    }
}
