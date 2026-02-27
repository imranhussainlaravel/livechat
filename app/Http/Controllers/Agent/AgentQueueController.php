<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Enums\ChatStatus;
use App\Enums\QueueStatus;
use App\Enums\MessageSenderType;
use App\Events\ChatAssigned;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgentQueueController extends Controller
{
    /**
     * Show the Queue List to the Agent.
     */
    public function getQueueChats()
    {
        // 1. Queue page must only show where queue_status = queued and assigned_agent_id IS NULL and status = open
        $chats = Chat::where('queue_status', QueueStatus::QUEUED)
            ->whereNull('assigned_agent_id')
            ->where('status', ChatStatus::PENDING)
            ->orderBy('created_at', 'asc')
            ->with(['visitor', 'messages' => fn($q) => $q->latest()->limit(1)])
            ->get();

        return view('agent.queue.index', compact('chats'));
    }

    /**
     * Agent joins a chat from the queue.
     */
    public function joinChat(Request $request, $conversation_id)
    {
        $agentId   = $request->user()->id;
        $agentName = $request->user()->name;
        $user      = $request->user();

        try {
            DB::transaction(function () use ($conversation_id, $agentId, $agentName, $user) {
                // IMPORTANT: Prevent multiple agents picking same chat
                $chat = Chat::where('id', $conversation_id)
                    ->whereNull('assigned_agent_id')
                    ->where('queue_status', QueueStatus::QUEUED)
                    ->lockForUpdate()
                    ->first();

                if (! $chat) {
                    throw new \Exception('This chat is no longer available in the queue.');
                }

                // System update conversation
                $chat->update([
                    'assigned_agent_id' => $agentId,
                    'queue_status'      => QueueStatus::PICKED,
                    'status'            => ChatStatus::ACTIVE,
                ]);

                // Insert System message
                ChatMessage::create([
                    'chat_id'     => $chat->id,
                    'sender_type' => MessageSenderType::SYSTEM,
                    'sender_id'   => null,
                    'message'     => 'Agent ' . $agentName . ' joined the chat',
                ]);

                // Fire event
                event(new ChatAssigned($chat->fresh(['visitor', 'agent']), $user));
            });

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Chat picked successfully.']);
            }

            return redirect()->route('agent.chats.show', $conversation_id)->with('success', 'You have joined the chat.');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json(['error' => $e->getMessage()], 400);
            }
            return back()->with('error', $e->getMessage());
        }
    }
}
