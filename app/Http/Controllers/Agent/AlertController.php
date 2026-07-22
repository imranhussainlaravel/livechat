<?php

namespace App\Http\Controllers\Agent;

use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Lightweight polling endpoint for live-chat notifications. Used by the layout
 * so new pending chats and new visitor messages alert the agent on ANY page —
 * without depending on a configured broadcaster (Pusher/Reverb) or queue worker.
 */
class AlertController extends Controller
{
    public function poll(Request $request)
    {
        $meId = $request->user()->id;

        // --- Pending queue (visible to everyone) ---
        // Same scope the queue page renders with, so the frontend can reconcile
        // its rows against this exact set.
        $pendingBase = Chat::queued();

        $pendingIds = (clone $pendingBase)->pluck('id')->all();
        $pendingCount = count($pendingIds);
        $latestPending = (clone $pendingBase)->with('visitor')->orderByDesc('id')->first();

        // --- Newest visitor message in one of my active chats ---
        $latestMsg = ChatMessage::where('sender_type', MessageSenderType::VISITOR->value)
            ->whereHas('chat', function ($q) use ($meId) {
                $q->where('assigned_agent_id', $meId)->whereIn('status', [
                    ChatStatus::ASSIGNED->value,
                    ChatStatus::ACTIVE->value,
                    ChatStatus::TRANSFERRED->value,
                ]);
            })
            ->with('chat.visitor')
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'pending_count' => $pendingCount,
            'pending_ids' => $pendingIds,
            'latest_pending' => $latestPending ? [
                'id' => $latestPending->id,
                'visitor_name' => $latestPending->visitor->name ?? 'Visitor',
            ] : null,
            'latest_message' => $latestMsg ? [
                'id' => $latestMsg->id,
                'chat_id' => $latestMsg->chat_id,
                'visitor_name' => $latestMsg->chat?->visitor?->name ?? 'Visitor',
                'preview' => Str::limit($latestMsg->message, 80),
            ] : null,
        ]);
    }
}
