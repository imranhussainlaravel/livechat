<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Src\Analytics\HistoryTracker\HistoryTracker;

class DashboardController extends ApiController
{
    public function __construct(private readonly HistoryTracker $analytics) {}

    /**
     * GET /api/v1/admin/dashboard
     */
    public function index()
    {
        return $this->success($this->analytics->summary());
    }
}
