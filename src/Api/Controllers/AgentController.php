<?php

namespace Src\Api\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Src\Database\Models\Agent;
use Src\Agent\Heartbeat\Heartbeat;
use Src\Core\ConversationEngine\ConversationEngine;
use Src\Core\MessageTimeline\MessageTimeline;

class AgentController extends ApiController
{
    /**
     * POST /api/v1/agent/login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $agent = Agent::where('email', $request->email)->first();

        if (! $agent || ! Hash::check($request->password, $agent->password)) {
            return $this->error('Invalid credentials', 401);
        }

        $agent->update(['status' => 'online', 'last_activity_at' => now()]);
        $token = $agent->createToken('agent-token')->plainTextToken;

        return $this->success([
            'agent' => $agent->only(['id', 'name', 'email', 'status']),
            'token' => $token,
        ]);
    }

    /**
     * POST /api/v1/agent/heartbeat
     */
    public function heartbeat(Request $request, Heartbeat $heartbeat)
    {
        $heartbeat->ping($request->user()->id);
        return $this->success(null, 'pong');
    }

    /**
     * POST /api/v1/agent/status
     */
    public function status(Request $request)
    {
        $request->validate(['status' => 'required|in:online,away,offline']);
        $agent = $request->user();
        $agent->update(['status' => $request->status, 'last_activity_at' => now()]);
        return $this->success($agent->only(['id', 'status']));
    }

    /**
     * POST /api/v1/agent/conversations/{id}/messages
     */
    public function sendMessage(Request $request, int $conversationId, MessageTimeline $timeline)
    {
        $request->validate(['body' => 'required|string']);

        $message = $timeline->append([
            'conversation_id' => $conversationId,
            'sender_type'     => 'agent',
            'sender_id'       => $request->user()->id,
            'body'            => $request->body,
        ]);

        return $this->created($message);
    }

    /**
     * POST /api/v1/agent/conversations/{id}/close
     */
    public function closeConversation(int $conversationId, ConversationEngine $engine)
    {
        $conversation = $engine->close($conversationId);
        return $this->success($conversation);
    }
}
