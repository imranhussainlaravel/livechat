<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Repositories\Contracts\ChatRepositoryInterface;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private ChatRepositoryInterface $chats,
    ) {}

    /**
     * GET /agent/dashboard — Agent dashboard with stats overview.
     */
    public function index(Request $request)
    {
        $agentId = $request->user()->id;

        $activeChats = $this->chats->getActiveCount($agentId);

        $totalResolved = Chat::where('assigned_agent_id', $agentId)
            ->where('status', ChatStatus::SOLVED->value)
            ->count();

        $avgResolutionTime = Chat::where('assigned_agent_id', $agentId)
            ->whereNotNull('ended_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_time')
            ->value('avg_time');

        $messagesSentToday = ChatMessage::where('sender_id', $agentId)
            ->where('sender_type', MessageSenderType::AGENT->value)
            ->whereDate('created_at', now()->toDateString())
            ->count();

        $pendingChats = Chat::where('status', ChatStatus::PENDING->value)->count();

        $recentChats = Chat::where('assigned_agent_id', $agentId)
            ->whereIn('status', ['open', 'in_progress'])
            ->with('visitor')
            ->latest()
            ->take(10)
            ->get();

        return view('agent.dashboard', [
            'metrics' => [
                'active_chats'        => $activeChats,
                'total_resolved'      => $totalResolved,
                'avg_resolution_mins' => floor((float) $avgResolutionTime),
                'messages_sent_today' => $messagesSentToday,
                'pending_queue'       => $pendingChats,
            ],
            'recentChats' => $recentChats,
        ]);
    }
}
