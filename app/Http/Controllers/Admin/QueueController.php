<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ChatStatus;
use App\Enums\QueueStatus;
use App\Http\Controllers\Controller;
use App\Models\Chat;

class QueueController extends Controller
{
    /**
     * Display the global pending queue for administrators.
     */
    public function index()
    {
        $chats = Chat::where('queue_status', QueueStatus::QUEUED)
            ->whereNull('assigned_agent_id')
            ->where('status', ChatStatus::PENDING)
            ->with(['visitor'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.queue.index', compact('chats'));
    }
}
