<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChatStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\User;

class DashboardController extends Controller
{
    /**
     * GET /admin/dashboard — Admin overview with system-wide stats.
     */
    public function index()
    {
        $stats = [
            'active_chats' => Chat::whereIn('status', ['assigned', 'active'])->count(),
            'pending_queue' => Chat::where('status', ChatStatus::PENDING)->count(),
            'agents_online' => User::where('role', UserRole::AGENT)->where('status', 'online')->count(),
            'total_today' => Chat::whereDate('created_at', today())->count(),
            'closed_today' => Chat::where('status', ChatStatus::CLOSED)->whereDate('ended_at', today())->count(),
            'total_ongoing' => Chat::whereIn('status', ['pending', 'assigned', 'active'])->count(),
        ];

        // ── CRM overview (organisation-wide) ──────────────────────────
        $crm = [
            'leads_total'    => Lead::count(),
            'leads_open'     => Lead::whereNotIn('status', ['won', 'lost'])->count(),
            'deals_open'     => Deal::whereNotIn('stage', ['won', 'lost'])->count(),
            'pipeline_value' => (float) Deal::whereIn('stage', ['quoted', 'negotiation'])->sum('value'),
            'deals_won'      => Deal::where('stage', 'won')->count(),
            'won_value'      => (float) Deal::where('stage', 'won')->sum('value'),
            'orders_active'  => Order::whereNotIn('status', ['delivered'])->count(),
        ];

        $agents = User::where('role', UserRole::AGENT)
            ->withCount(['assignedChats' => fn ($q) => $q->whereIn('status', ['assigned', 'active'])])
            ->get();

        // Graph data: Chats created in the last 7 days
        $graphData = [
            'labels' => [],
            'values' => [],
        ];

        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $graphData['labels'][] = $date->format('M d');
            $graphData['values'][] = Chat::whereDate('created_at', $date)->count();
        }

        return view('admin.dashboard', compact('stats', 'crm', 'agents', 'graphData'));
    }
}
