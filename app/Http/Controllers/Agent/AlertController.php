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
        $after = (int) $request->query('after', 0);

        // --- Pending queue (visible to everyone) ---
        // Same scope the queue page renders with, so the frontend can reconcile
        // its rows against this exact set.
        $pendingBase = Chat::queued();

        $pendingIds = (clone $pendingBase)->pluck('id')->all();
        $pendingCount = count($pendingIds);
        $latestPending = (clone $pendingBase)->with('visitor')->orderByDesc('id')->first();

        // --- Visitor messages in one of my active chats ---
        $myMsgBase = ChatMessage::where('sender_type', MessageSenderType::VISITOR->value)
            ->whereHas('chat', function ($q) use ($meId) {
                $q->where('assigned_agent_id', $meId)->whereIn('status', [
                    ChatStatus::ASSIGNED->value,
                    ChatStatus::ACTIVE->value,
                    ChatStatus::TRANSFERRED->value,
                ]);
            });

        // Newest id — used by the frontend to set its baseline on first load.
        $latestMsgId = (int) (clone $myMsgBase)->max('id');

        // EVERY new message since ?after (WhatsApp-style, one alert per message).
        // Only fetched once the frontend has a baseline (after > 0); capped so a
        // long absence can't produce a flood.
        $newMessages = collect();
        if ($after > 0) {
            $newMessages = (clone $myMsgBase)
                ->where('id', '>', $after)
                ->with('chat.visitor')
                ->orderBy('id')
                ->limit(20)
                ->get();
        }

        return response()->json([
            'pending_count' => $pendingCount,
            'pending_ids' => $pendingIds,
            'latest_pending' => $latestPending ? [
                'id' => $latestPending->id,
                'visitor_name' => $latestPending->visitor->name ?? 'Visitor',
            ] : null,
            'latest_msg_id' => $latestMsgId,
            'new_messages' => $newMessages->map(fn ($m) => [
                'id' => $m->id,
                'chat_id' => $m->chat_id,
                'visitor_name' => $m->chat?->visitor?->name ?? 'Visitor',
                'preview' => Str::limit($m->message, 80),
            ])->values(),
        ]);
    }
}
