<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\ChatStatus;
use App\Enums\UserRole;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /admin/dashboard — Admin overview with system-wide stats.
     */
    public function index()
    {
        $stats = [
            'active_chats'  => Chat::whereIn('status', ['assigned', 'active'])->count(),
            'pending_queue' => Chat::where('status', ChatStatus::PENDING)->count(),
            'agents_online' => User::where('role', UserRole::AGENT)->where('status', 'online')->count(),
            'total_today'   => Chat::whereDate('created_at', today())->count(),
            'closed_today'  => Chat::where('status', ChatStatus::CLOSED)->whereDate('ended_at', today())->count(),
        ];

        $agents = User::where('role', UserRole::AGENT)
            ->withCount(['assignedChats' => fn($q) => $q->whereIn('status', ['assigned', 'active'])])
            ->get();

        return view('admin.dashboard', compact('stats', 'agents'));
    }
}
