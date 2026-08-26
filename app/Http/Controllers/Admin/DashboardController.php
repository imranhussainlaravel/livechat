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
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /admin/dashboard — Admin overview with system-wide stats.
     */
    public function index(Request $request)
    {
        $daysParam = $request->get('days', '7');
        if ($daysParam === 'today') {
            $startDate = today();
            $days = 1;
        } else {
            $days = (int) $daysParam;
            $startDate = today()->subDays($days - 1);
        }
        $endDate = now();
        
        $prevStartDate = (clone $startDate)->subDays($days);
        $prevEndDate = (clone $startDate)->subSeconds(1);

        // --- Live Stats ---
        $activeChats = Chat::active()->count();
        $pendingQueue = Chat::queued()->count();
        $agentsOnline = User::where('role', UserRole::AGENT)->where('status', 'online')->count();

        // Stats over selected period
        $periodChats = Chat::whereBetween('created_at', [$startDate, $endDate])->count();
        $prevPeriodChats = Chat::whereBetween('created_at', [$prevStartDate, $prevEndDate])->count();
        $chatTrend = $this->calculateTrend($periodChats, $prevPeriodChats);
        
        $periodResolved = Chat::where('status', ChatStatus::CLOSED)->whereBetween('ended_at', [$startDate, $endDate])->count();
        $prevPeriodResolved = Chat::where('status', ChatStatus::CLOSED)->whereBetween('ended_at', [$prevStartDate, $prevEndDate])->count();
        $resolvedTrend = $this->calculateTrend($periodResolved, $prevPeriodResolved);
        $resolvedPercent = $periodChats > 0 ? round(($periodResolved / $periodChats) * 100) : 0;
        $prevResolvedPercent = $prevPeriodChats > 0 ? round(($prevPeriodResolved / $prevPeriodChats) * 100) : 0;
        $resolvedPercentTrend = $this->calculateTrend($resolvedPercent, $prevResolvedPercent);

        $stats = [
            'active_chats' => $activeChats,
            'pending_queue' => $pendingQueue,
            'agents_online' => $agentsOnline,
            'period_chats' => $periodChats,
            'chat_trend' => $chatTrend,
            'period_resolved' => $periodResolved,
            'resolved_percent' => $resolvedPercent,
            'resolved_percent_trend' => $resolvedPercentTrend,
            'trend_label' => $daysParam === 'today' ? 'vs yesterday' : "vs last {$days}d",
        ];

        // --- Sparklines (daily array for the period) ---
        $chatSparkline = [];
        $resolvedSparkline = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = today()->subDays($i);
            $chatSparkline[] = Chat::whereDate('created_at', $d)->count();
            $resolvedSparkline[] = Chat::where('status', ChatStatus::CLOSED)->whereDate('ended_at', $d)->count();
        }
        $stats['chat_sparkline'] = $this->makeSparklinePath($chatSparkline);
        $stats['resolved_sparkline'] = $this->makeSparklinePath($resolvedSparkline);

        // --- CRM overview ---
        // Open/total numbers are all-time as per standard pipelines. 
        $crm = [
            'leads_total'    => Lead::count(),
            'leads_open'     => Lead::whereNotIn('status', ['won', 'lost'])->count(),
            'deals_open'     => Deal::whereNotIn('stage', ['won', 'lost'])->count(),
            'pipeline_value' => (float) Deal::whereIn('stage', ['quoted', 'negotiation'])->sum('value'),
            // Scoped to period
            'deals_won'      => Deal::where('stage', 'won')->whereBetween('updated_at', [$startDate, $endDate])->count(),
            'won_value'      => (float) Deal::where('stage', 'won')->whereBetween('updated_at', [$startDate, $endDate])->sum('value'),
            'orders_active'  => Order::whereNotIn('status', ['delivered'])->count(),
        ];

        // --- Top Agents ---
        $agents = User::where('role', UserRole::AGENT)
            ->withCount(['assignedChats' => fn ($q) => $q->whereBetween('created_at', [$startDate, $endDate])])
            ->orderByDesc('assigned_chats_count')
            ->take(5)
            ->get();

        // --- Recent Chats ---
        $recentChats = Chat::with('visitor', 'agent')->latest()->take(5)->get();

        // --- Graph data: Leads vs Deals ---
        $graphData = [
            'labels' => [],
            'leads' => [],
            'deals' => [],
        ];
        // Donut Chart: Chat Volume
        $chatVolume = [
            'high' => Chat::whereBetween('created_at', [$startDate, $endDate])->where('priority', 'high')->count(),
            'normal' => Chat::whereBetween('created_at', [$startDate, $endDate])->where('priority', 'normal')->count(),
            'low' => Chat::whereBetween('created_at', [$startDate, $endDate])->where('priority', 'low')->count(),
        ];
        
        // Funnel Chart
        $funnelData = [
            'leads' => Lead::whereBetween('created_at', [$startDate, $endDate])->count(),
            'deals_created' => Deal::whereBetween('created_at', [$startDate, $endDate])->count(),
            'deals_won' => Deal::where('stage', 'won')->whereBetween('updated_at', [$startDate, $endDate])->count(),
        ];

        // Generate line chart data
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = today()->subDays($i);
            $graphData['labels'][] = $d->format('M d');
            $graphData['leads'][] = Lead::whereDate('created_at', $d)->count();
            $graphData['deals'][] = Deal::whereDate('created_at', $d)->count();
        }

        return view('admin.dashboard', compact('stats', 'crm', 'agents', 'recentChats', 'graphData', 'chatVolume', 'funnelData'));
    }

    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? ['type' => 'up', 'value' => '+100%'] : ['type' => 'flat', 'value' => '0%'];
        }
        $diff = $current - $previous;
        $pct = round(($diff / $previous) * 100);
        
        if ($pct > 0) return ['type' => 'up', 'value' => "+{$pct}%"];
        if ($pct < 0) return ['type' => 'down', 'value' => "{$pct}%"];
        return ['type' => 'flat', 'value' => '0%'];
    }

    private function makeSparklinePath(array $data)
    {
        if (empty($data)) return 'M0,15 L50,15';
        $max = max($data);
        if ($max == 0) return 'M0,15 L50,15';
        
        $width = 50;
        $height = 20;
        $step = count($data) > 1 ? $width / (count($data) - 1) : $width;
        
        $path = [];
        foreach ($data as $i => $val) {
            $x = $i * $step;
            // invert y (0 is top in SVG)
            $y = $height - (($val / $max) * $height) + 2;
            // constrain y
            $y = max(2, min($height - 2, $y));
            $prefix = $i === 0 ? 'M' : 'L';
            $path[] = sprintf("%s%.1f,%.1f", $prefix, $x, $y);
        }
        return implode(' ', $path);
    }
}
