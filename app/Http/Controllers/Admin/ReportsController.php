<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReportingService;

class ReportsController extends Controller
{
    public function __construct(private ReportingService $reports) {}

    /**
     * GET /admin/reports — Reports page with cached insights.
     */
    public function index()
    {
        $data = $this->reports->getAll();

        return view('admin.reports.index', compact('data'));
    }
}
