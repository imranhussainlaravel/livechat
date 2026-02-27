<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Events\AgentStatusChanged;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function __construct(private UserRepositoryInterface $users) {}

    /**
     * PATCH /agent/status — Update agent online/away/offline status (AJAX).
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:online,away,offline',
        ]);

        $this->users->updateStatus($request->user()->id, $request->status);

        event(new AgentStatusChanged($request->user()->fresh()));

        return response()->json(['message' => 'Status updated.']);
    }
}
