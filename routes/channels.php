<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Chat;

/*
|--------------------------------------------------------------------------
| Private Channels — Per-Chat Messaging
|--------------------------------------------------------------------------
|
| Each chat gets its own private channel. Only the assigned agent and
| the visitor (matched by session_token) can subscribe.
|
*/

Broadcast::channel('chat.{chatId}', function ($user, int $chatId) {
    $chat = Chat::with('visitor')->find($chatId);

    if (! $chat) {
        return false;
    }

    // 1. Authenticated Agents/Admins
    if ($user) {
        return $user->isAdmin() || $chat->assigned_agent_id === $user->id;
    }

    // 2. Public Visitors (Verified by Session Token)
    $sessionToken = request()->header('X-Session-Token');
    
    if ($sessionToken && $chat->visitor && $chat->visitor->session_token === $sessionToken) {
        return true;
    }

    return false;
});

/*
|--------------------------------------------------------------------------
| Presence Channel — Agents Online Dashboard
|--------------------------------------------------------------------------
|
| Shows which agents are currently online. Only authenticated agents
| and admins can join. Returns public-facing identity data.
|
*/
Broadcast::channel('agents', function ($user) {
    if ($user->isAdmin() || $user->isAgent()) {
        return [
            'id'     => $user->id,
            'name'   => $user->name,
            'role'   => $user->role->value,
            'status' => $user->status,
        ];
    }

    return false;
});

/*
|--------------------------------------------------------------------------
| Presence Channel — Individual Chat Room
|--------------------------------------------------------------------------
|
| Presence channel per chat so participants can see who is currently
| viewing the chat (typing indicators, read receipts, etc.).
|
*/
Broadcast::channel('chat-room.{chatId}', function ($user, int $chatId) {
    $chat = Chat::find($chatId);

    if (! $chat) {
        return false;
    }

    if ($user->isAdmin() || $chat->assigned_agent_id === $user->id) {
        return [
            'id'   => $user->id,
            'name' => $user->name,
            'role' => $user->role->value,
        ];
    }

    return false;
});

/*
|--------------------------------------------------------------------------
| Private Channel — Agent-specific Notifications
|--------------------------------------------------------------------------
|
| Each agent gets a private channel for assignment notifications,
| transfer alerts, etc.
|
*/
Broadcast::channel('agent.{agentId}', function ($user, int $agentId) {
    return $user->id === $agentId || $user->isAdmin();
});

/*
|--------------------------------------------------------------------------
| Private Channel — Admin Dashboard
|--------------------------------------------------------------------------
*/
Broadcast::channel('admin', function ($user) {
    return $user->isAdmin();
});

/*
|--------------------------------------------------------------------------
| Phase 2 Queue System Channels
|--------------------------------------------------------------------------
*/
Broadcast::channel('admin.queue', function ($user) {
    return $user->isAdmin();
});

Broadcast::channel('agent.queue', function ($user) {
    return $user->isAgent() || $user->isAdmin();
});

Broadcast::channel('admin.agents.load', function ($user) {
    return $user->isAdmin();
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
