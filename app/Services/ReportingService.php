<?php

namespace App\Services;

use App\Enums\ChatStatus;
use App\Enums\MessageSenderType;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ReportingService
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Retrieve all reporting metrics (from cache).
     */
    public function getMetrics(int $days = 30): array
    {
        // For dynamic range we cache by days
        return Cache::remember("reporting:metrics:{$days}", self::CACHE_TTL, function () use ($days) {
            return [
                'daily_conversations'  => $this->getDailyConversations($days),
                'agent_performance'    => $this->getAgentPerformance($days),
                'conversion_rate'      => $this->getConversionRate($days),
                'avg_resolution_mins'  => $this->getChatResolutionTime($days),
                'avg_response_mins'    => $this->getAverageResponseTime($days),
                'last_updated'         => now()->toIso8601String(),
            ];
        });
    }

    /**
     * Force refresh the reporting cache.
     */
    public function refreshMetrics(int $days = 30): void
    {
        Cache::forget("reporting:metrics:{$days}");
        $this->getMetrics($days); // Re-computes and caches
    }

    /**
     * Determine the number of chats created per day over a period.
     */
    private function getDailyConversations(int $days): array
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $data = Chat::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill in missing dates with zero
        $filledData = [];
        $current = $startDate->copy();
        $end = Carbon::now()->startOfDay();

        while ($current->lte($end)) {
            $dateStr = $current->format('Y-m-d');
            $filledData[$dateStr] = $data[$dateStr] ?? 0;
            $current->addDay();
        }

        return $filledData;
    }

    /**
     * Aggregation of agent performance metrics.
     */
    private function getAgentPerformance(int $days): array
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // Get all agents
        $agents = User::where('role', 'agent')->get(['id', 'name']);

        $performance = [];

        foreach ($agents as $agent) {
            $totalResolved = Chat::where('assigned_agent_id', $agent->id)
                ->where('status', ChatStatus::CLOSED->value)
                ->where('created_at', '>=', $startDate)
                ->count();

            $avgResolutionTime = Chat::where('assigned_agent_id', $agent->id)
                ->whereNotNull('ended_at')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_time')
                ->value('avg_time');

            $messagesSent = ChatMessage::where('sender_id', $agent->id)
                ->where('sender_type', MessageSenderType::AGENT->value)
                ->where('created_at', '>=', $startDate)
                ->count();

            $activeLoad = app(AgentLoadService::class)->getActiveCount($agent->id);

            $performance[] = [
                'agent_id'             => $agent->id,
                'name'                 => $agent->name,
                'total_resolved'       => $totalResolved,
                'avg_resolution_mins'  => floor((float) $avgResolutionTime),
                'messages_sent'        => $messagesSent,
                'current_active_load'  => $activeLoad,
            ];
        }

        return $performance;
    }

    /**
     * Percentage of chats that transition into "interested" tickets or have quotation sent.
     */
    private function getConversionRate(int $days): float
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $totalChats = Chat::where('created_at', '>=', $startDate)->count();

        if ($totalChats === 0) {
            return 0.0;
        }

        $convertedChats = Ticket::where('created_at', '>=', $startDate)
            ->where(function ($query) {
                $query->where('status', 'interested')
                    ->orWhere('quotation_sent', true);
            })
            ->distinct('chat_id')
            ->count('chat_id');

        return round(($convertedChats / $totalChats) * 100, 2);
    }

    /**
     * Average turnaround (resolution) time for closed chats.
     */
    private function getChatResolutionTime(int $days): float
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        $avgMins = Chat::whereNotNull('ended_at')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, started_at, ended_at)) as avg_time')
            ->value('avg_time');

        return floor((float) $avgMins);
    }

    /**
     * Average time taken for an agent to send the FIRST message after the chat started.
     */
    private function getAverageResponseTime(int $days): float
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();

        // Optimized raw SQL query to find the gap between chat.started_at and the first agent message
        $avgResponse = DB::table('chats')
            ->join('chat_messages', 'chats.id', '=', 'chat_messages.chat_id')
            ->where('chats.created_at', '>=', $startDate)
            ->where('chat_messages.sender_type', MessageSenderType::AGENT->value)
            ->whereIn('chat_messages.id', function ($query) {
                // Determine the first agent message for each chat
                $query->select(DB::raw('MIN(id)'))
                    ->from('chat_messages')
                    ->where('sender_type', MessageSenderType::AGENT->value)
                    ->groupBy('chat_id');
            })
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, chats.started_at, chat_messages.created_at)) as avg_time')
            ->value('avg_time');

        return max(0, round((float) $avgResponse, 2));
    }
}
