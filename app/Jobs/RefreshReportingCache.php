<?php

namespace App\Jobs;

use App\Services\ReportingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RefreshReportingCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $days = 30)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(ReportingService $reporting): void
    {
        // Recompute and cache the metrics
        $reporting->refreshMetrics($this->days);
    }
}
