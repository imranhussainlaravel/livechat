<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Repositories\Contracts\ActivityRepositoryInterface;

class ActivityController extends Controller
{
    public function __construct(private ActivityRepositoryInterface $activities) {}

    /**
     * GET /admin/activities — Recent activity log.
     */
    public function index()
    {
        $activities = Activity::latest()->paginate(15);

        return view('admin.activities.index', compact('activities'));
    }
}
