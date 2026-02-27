<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\ActivityService;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $users,
        private ActivityService         $activity,
    ) {}

    /**
     * GET /admin/agents — Agent list page.
     */
    public function index(Request $request)
    {
        $agents = $this->users->getAgents($request->only(['status', 'per_page']));

        return view('admin.agents.index', compact('agents'));
    }

    /**
     * POST /admin/agents — Create a new agent.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'max_chats' => 'sometimes|integer|min:1|max:50',
        ]);

        $this->users->create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $request->password,
            'role'      => UserRole::AGENT->value,
            'max_chats' => $request->max_chats ?? 5,
        ]);

        $this->activity->log($request->user()->id, 'agent.created', 'User', null);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Agent created.'], 201);
        }

        return redirect()->route('admin.agents.index')->with('success', 'Agent created successfully.');
    }

    /**
     * DELETE /admin/agents/{id} — Remove an agent.
     */
    public function destroy(Request $request, int $id)
    {
        $this->users->delete($id);
        $this->activity->log($request->user()->id, 'agent.deleted', 'User', $id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Agent removed.']);
        }

        return redirect()->route('admin.agents.index')->with('success', 'Agent removed.');
    }
}
