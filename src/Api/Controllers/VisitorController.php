<?php

namespace Src\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Src\Database\Models\VisitorSession;
use Src\Database\Models\Conversation;
use Src\Database\Models\Message;
use Src\Events\EventBus\MessageSent;
use Src\Events\EventBus\ConversationStateUpdated;

class VisitorController extends Controller
{
    /**
     * POST /chat/start
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'visitor_name' => 'nullable|string',
            'visitor_email' => 'nullable|email',
            'queue' => 'nullable|string',
        ]);

        // Create Session
        $session = VisitorSession::create([
            'session_id' => Str::uuid()->toString(),
            'visitor_name' => $validated['visitor_name'] ?? 'Guest',
            'visitor_email' => $validated['visitor_email'] ?? null,
            'metadata' => $request->header('User-Agent'),
            'last_activity_at' => now(),
        ]);

        // Create Conversation (Starts as NEW)
        $conversation = Conversation::create([
            'session_id' => $session->session_id,
            'queue' => $validated['queue'] ?? 'default',
            'state' => 'NEW',
        ]);

        // Let's transition it to PENDING automatically so it shows in Queue
        // In a real scenario, this might happen after a bot interaction
        $conversation->state = 'PENDING';
        $conversation->save();

        ConversationStateUpdated::dispatch($conversation);

        return response()->json([
            'success' => true,
            'conversation_id' => $conversation->id,
            'session_id' => $session->session_id,
            'state' => $conversation->state,
        ], 201);
    }

    /**
     * POST /chat/:id/message
     */
    public function message(Request $request, $id)
    {
        $validated = $request->validate([
            'session_id' => 'required|string',
            'content' => 'required|string',
        ]);

        $conversation = Conversation::where('id', $id)
            ->where('session_id', $validated['session_id'])
            ->firstOrFail();

        // Must be actively participating in conversation to send message
        if (in_array($conversation->state, ['CLOSED', 'NEW'])) {
            return response()->json(['error' => 'Cannot send message to this conversation state.'], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'USER',
            'sender_id' => null, // We map by session instead
            'content' => $validated['content'],
        ]);

        // Update Session Activity
        $conversation->visitorSession()->update(['last_activity_at' => now()]);

        // Touch the conversation's updated_at required for SLA engine to reset timers properly
        $conversation->touch();

        // Broadcast Event
        MessageSent::dispatch($message);

        return response()->json([
            'success' => true,
            'message' => $message
        ], 201);
    }
}
