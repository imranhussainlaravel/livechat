<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CheckOfflineAgents
{
    /**
     * Finds agents marked ONLINE whose last heartbeat (updated_at)
     * was more than 2 minutes ago, and transitions them to AWAY.
     *
     * @return int Number of agents updated
     */
    public function execute(): int
    {
        $threshold = Carbon::now()->subMinutes(2);

        $offlineAgents = User::where('role', 'agent')
            ->where('status', 'online')
            ->where('updated_at', '<', $threshold)
            ->get();

        $count = 0;
        foreach ($offlineAgents as $agent) {
            $agent->status = 'away';
            $agent->save();
            $count++;

            Log::channel('single')->info("RESILIENCE: Agent {$agent->id} marked AWAY due to lost heartbeat.");
        }

        return $count; // return number of affected agents
    }
}
