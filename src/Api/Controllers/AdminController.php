<?php

namespace Src\Api\Controllers;

use Illuminate\Routing\Controller;
use Src\Database\Models\Conversation;
use Src\Database\Models\User;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * GET /admin/conversations
     */
    public function conversations()
    {
        $conversations = Conversation::with(['agent', 'visitorSession'])
            ->orderBy('id', 'desc')
            ->paginate(50);

        return response()->json($conversations);
    }

    /**
     * GET /admin/agents
     */
    public function agents()
    {
        $agents = User::where('role', 'agent')->get();
        return response()->json($agents);
    }

    /**
     * GET /admin/analytics
     */
    public function analytics()
    {
        $stats = Conversation::select('state', DB::raw('count(*) as total'))
            ->groupBy('state')
            ->pluck('total', 'state');

        $activeAgents = User::where('role', 'agent')->where('status', 'online')->count();
        $breachedSla = Conversation::where('sla_state', 'BREACHED')->count();

        return response()->json([
            'status_counts' => $stats,
            'active_agents' => $activeAgents,
            'total_sla_breaches' => $breachedSla,
        ]);
    }
}
