<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\DTOs\CreateFollowupDTO;
use App\Repositories\Contracts\FollowupRepositoryInterface;
use App\Services\FollowupService;
use Illuminate\Http\Request;

class FollowupController extends Controller
{
    public function __construct(
        private FollowupService             $service,
        private FollowupRepositoryInterface $followups,
    ) {}

    /**
     * GET /agent/followups — Followup list page.
     */
    public function index(Request $request)
    {
        $followups = $this->followups->getByAgent(
            $request->user()->id,
            $request->only(['status', 'per_page']),
        );

        return view('agent.followups.index', compact('followups'));
    }

    /**
     * POST /agent/followups — Create a new followup.
     */
    public function store(Request $request)
    {
        $request->validate([
            'chat_id'       => 'required|exists:chats,id',
            'followup_time' => 'required|date|after:now',
            'notes'         => 'nullable|string|max:1000',
        ]);

        $dto = CreateFollowupDTO::fromRequest($request->all(), $request->user()->id);
        $this->service->create($dto);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Follow-up created.'], 201);
        }

        return back()->with('success', 'Follow-up created.');
    }

    /**
     * PATCH /agent/followups/{id}/complete
     */
    public function complete(Request $request, int $id)
    {
        $this->service->complete($id, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Follow-up completed.']);
        }

        return back()->with('success', 'Follow-up completed.');
    }

    /**
     * PATCH /agent/followups/{id}/cancel
     */
    public function cancel(Request $request, int $id)
    {
        $this->service->cancel($id, $request->user()->id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Follow-up cancelled.']);
        }

        return back()->with('success', 'Follow-up cancelled.');
    }
}
