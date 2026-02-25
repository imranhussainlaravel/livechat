<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InsightsController extends Controller
{
    public function __construct(private ReportingService $reporting) {}

    /**
     * GET /api/admin/reports
     * Retrieves aggregated system metrics (cached).
     */
    public function index(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 30);
        $force = $request->boolean('force_refresh', false);

        if ($force) {
            $this->reporting->refreshMetrics($days);
        }

        $metrics = $this->reporting->getMetrics($days);

        return response()->json([
            'data' => $metrics,
        ]);
    }
}
