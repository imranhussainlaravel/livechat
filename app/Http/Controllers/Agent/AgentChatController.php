<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentChatController extends Controller
{
    /**
     * GET /agent/other-agents — Directory of all other agents/admins.
     */
    public function index()
    {
        $currentUserId = Auth::id();
        $users = User::where('id', '!=', $currentUserId)
            ->withCount(['sentInternalMessages as unread_count' => function ($query) use ($currentUserId) {
                $query->where('receiver_id', $currentUserId)
                      ->where('is_read', false);
            }])
            ->orderBy('status', 'desc') // Online first
            ->orderBy('name', 'asc')
            ->get();

        return view('agent.other-agents.index', compact('users'));
    }

    /**
     * GET /agent/other-agents/{id} — Private chat with an agent.
     */
    public function show($id)
    {
        $otherUser = User::findOrFail($id);
        $currentUserId = Auth::id();

        $messages = InternalMessage::where(function ($q) use ($currentUserId, $id) {
            $q->where('sender_id', $currentUserId)->where('receiver_id', $id);
        })->orWhere(function ($q) use ($currentUserId, $id) {
            $q->where('sender_id', $id)->where('receiver_id', $currentUserId);
        })
        ->orderBy('created_at', 'asc')
        ->get();

        // Mark as read
        InternalMessage::where('sender_id', $id)
            ->where('receiver_id', $currentUserId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return view('agent.other-agents.chat', compact('otherUser', 'messages'));
    }

    /**
     * POST /agent/other-agents/{id}/message — Send a message.
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $message = InternalMessage::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $id,
            'message'     => $request->message,
        ]);

        // Broadcast event could go here

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Message sent.',
                'data'    => $message
            ]);
        }

        return back();
    }
}
