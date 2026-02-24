<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Services\AcceptConversation;
use App\Services\TransitionConversationState;
use App\Events\MessageSent;
use App\Events\ConversationStateUpdated;

class AgentController extends Controller
{
    /**
     * GET /agent/conversations?state=PENDING
     */
    public function index(Request $request)
    {
        $state = $request->query('state', 'PENDING');

        $conversations = Conversation::where('state', $state)
            ->with(['visitorSession'])
            ->orderBy('updated_at', 'asc')
            ->paginate(15);

        return response()->json($conversations);
    }

    /**
     * POST /agent/conversation/:id/accept
     */
    public function accept(Request $request, $id, AcceptConversation $acceptCase)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        try {
            // This UseCase enforces deterministic DB locked assignment rules
            $conversation = $acceptCase->execute($id, $validated['agent_id']);

            ConversationStateUpdated::dispatch($conversation);

            return response()->json([
                'success' => true,
                'conversation' => $conversation
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 409); // 409 Conflict logic
        }
    }

    /**
     * POST /agent/conversation/:id/message
     */
    public function message(Request $request, $id)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
            'content'  => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($id);

        if ($conversation->state !== 'ACTIVE' && $conversation->state !== 'ESCALATED') {
            return response()->json(['error' => 'Conversation is not active.'], 403);
        }

        if ($conversation->assigned_agent_id != $validated['agent_id']) {
            return response()->json(['error' => 'You do not own this conversation.'], 403);
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_type' => 'AGENT',
            'sender_id' => $validated['agent_id'],
            'content' => $validated['content'],
        ]);

        $conversation->touch();

        MessageSent::dispatch($message);

        return response()->json([
            'success' => true,
            'message' => $message
        ], 201);
    }

    /**
     * POST /agent/conversation/:id/close
     */
    public function close(Request $request, $id, TransitionConversationState $transitioner)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        $conversation = Conversation::findOrFail($id);

        if ($conversation->assigned_agent_id != $validated['agent_id']) {
            return response()->json(['error' => 'You do not own this conversation.'], 403);
        }

        // Deterministic Transition
        $transitioner->execute($conversation, 'CLOSED');

        ConversationStateUpdated::dispatch($conversation);

        return response()->json([
            'success' => true,
            'state' => $conversation->state
        ]);
    }

    /**
     * POST /agent/heartbeat
     */
    public function heartbeat(Request $request)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        // Instead of pure memory, this writes standard heartbeat rules directly to DB to adhere to architecture rules.
        $user = User::findOrFail($validated['agent_id']);
        if ($user->role !== 'agent') {
            return response()->json(['error' => 'Not an agent'], 403);
        }

        $user->status = 'online';
        $user->updated_at = now(); // the actual heartbeat tick
        $user->save();

        return response()->json(['success' => true, 'status' => $user->status]);
    }

    /**
     * GET /agent/conversation/:id
     */
    public function show(Request $request, $id)
    {
        $conversation = Conversation::with(['messages', 'visitorSession', 'agent'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }

    /**
     * POST /agent/conversation/:id/transfer
     */
    public function transfer(Request $request, $id, TransitionConversationState $transitioner)
    {
        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
            'target_queue' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($id);

        if ($conversation->assigned_agent_id != $validated['agent_id']) {
            return response()->json(['error' => 'Not your conversation.'], 403);
        }

        $conversation->queue = $validated['target_queue'];
        $conversation->assigned_agent_id = null; // Decouple agent
        $conversation->save();

        // Return state to PENDING so another agent can claim it
        $transitioner->execute($conversation, 'PENDING');

        ConversationStateUpdated::dispatch($conversation);

        return response()->json(['success' => true, 'state' => 'PENDING', 'queue' => $conversation->queue]);
    }
}
