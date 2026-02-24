<?php

namespace Src\Analytics\HistoryTracker;

use Src\Database\Models\Conversation;
use Illuminate\Support\Facades\DB;

/**
 * History Tracker — reads conversation data to produce analytics.
 *
 * All data comes from the database. No in-memory aggregation state.
 */
class HistoryTracker
{
    /**
     * Dashboard summary stats.
     */
    public function summary(): array
    {
        return [
            'total_conversations' => Conversation::count(),
            'active'              => Conversation::where('state', 'active')->count(),
            'pending'             => Conversation::where('state', 'pending')->count(),
            'escalated'           => Conversation::where('state', 'escalated')->count(),
            'closed_today'        => Conversation::where('state', 'closed')
                ->whereDate('resolved_at', today())
                ->count(),
        ];
    }

    /**
     * Average first response time (in seconds) for a date range.
     */
    public function avgFirstResponse(string $from, string $to): ?float
    {
        return Conversation::whereNotNull('first_response_at')
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, first_response_at)) as avg_frt'))
            ->value('avg_frt');
    }

    /**
     * Average resolution time (in seconds) for a date range.
     */
    public function avgResolutionTime(string $from, string $to): ?float
    {
        return Conversation::whereNotNull('resolved_at')
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw('AVG(TIMESTAMPDIFF(SECOND, created_at, resolved_at)) as avg_rt'))
            ->value('avg_rt');
    }

    /**
     * Conversation count grouped by day for a date range.
     */
    public function volumeByDay(string $from, string $to): array
    {
        return Conversation::whereBetween('created_at', [$from, $to])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /**
     * SLA compliance rate (% within SLA) for a date range.
     */
    public function slaComplianceRate(string $from, string $to): float
    {
        $total = Conversation::whereBetween('created_at', [$from, $to])->count();

        if ($total === 0) {
            return 100.0;
        }

        $breached = Conversation::whereBetween('created_at', [$from, $to])
            ->where('sla_status', 'breached')
            ->count();

        return round((($total - $breached) / $total) * 100, 2);
    }
}
