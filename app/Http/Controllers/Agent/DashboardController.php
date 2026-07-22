<?php

namespace App\Http\Controllers\Agent;

use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Deal;
use App\Models\Lead;
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
            ->where('status', ChatStatus::CLOSED->value)
            ->count();

        $totalAssigned = Chat::where('assigned_agent_id', $agentId)->count();

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
            ->whereIn('status', [ChatStatus::ASSIGNED->value, ChatStatus::ACTIVE->value])
            ->with('visitor')
            ->latest()
            ->take(10)
            ->get();

        // Graph data: Agent's messages sent in the last 7 days
        $graphData = [
            'labels' => [],
            'values' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $graphData['labels'][] = $date->format('M d');
            $graphData['values'][] = ChatMessage::where('sender_id', $agentId)
                ->where('sender_type', MessageSenderType::AGENT->value)
                ->whereDate('created_at', $date)
                ->count();
        }

        // ── CRM figures for this agent (own records only) ──────────────
        $myLeads = Lead::where('assigned_agent_id', $agentId)->count();
        $myOpenLeads = Lead::where('assigned_agent_id', $agentId)
            ->whereNotIn('status', ['won', 'lost'])
            ->count();
        $myOpenDeals = Deal::where('sales_rep_id', $agentId)
            ->whereNotIn('stage', ['won', 'lost'])
            ->count();
        $myPipelineValue = (float) Deal::where('sales_rep_id', $agentId)
            ->whereNotIn('stage', ['won', 'lost'])
            ->sum('value');
        $myWonDeals = Deal::where('sales_rep_id', $agentId)
            ->where('stage', 'won')
            ->count();

        return view('agent.dashboard', [
            'metrics' => [
                'active_chats' => $activeChats,
                'total_resolved' => $totalResolved,
                'total_assigned' => $totalAssigned,
                'avg_resolution_mins' => floor((float) $avgResolutionTime),
                'messages_sent_today' => $messagesSentToday,
                'pending_queue' => $pendingChats,
            ],
            'crm' => [
                'my_leads' => $myLeads,
                'my_open_leads' => $myOpenLeads,
                'my_open_deals' => $myOpenDeals,
                'my_pipeline_value' => $myPipelineValue,
                'my_won_deals' => $myWonDeals,
            ],
            'recentChats' => $recentChats,
            'graphData' => $graphData,
        ]);
    }
}
