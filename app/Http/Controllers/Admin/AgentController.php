<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function __construct(
        private UserRepositoryInterface $users,
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'max_chats' => 'sometimes|integer|min:1|max:50',
            'role' => 'sometimes|in:agent,production',
            'work_scope' => 'sometimes|nullable|in:lead_gen_only,sales_only,full_cycle',
            'can_live_chat' => 'sometimes|boolean',
        ]);

        $this->users->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            // Agent or production; admins are not created from this form.
            'role' => $request->input('role', UserRole::AGENT->value),
            'max_chats' => $request->max_chats ?? 5,
            // CRM fields — a chat agent doubles as a CRM user.
            'work_scope' => $request->input('work_scope', 'full_cycle'),
            'account_status' => 'active',
            'created_by_admin_id' => $request->user()->id,
            // Live Chat access (checkbox unchecked => absent => false).
            'can_live_chat' => $request->boolean('can_live_chat', true),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Agent created.'], 201);
        }

        // Flash the credentials once so the admin can copy + send them to the
        // new agent. Plaintext password is safe here — the admin just typed it,
        // it's shown a single time, then gone on the next request.
        return redirect()->route('admin.agents.index')
            ->with('success', 'Agent created successfully.')
            ->with('new_agent', [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => $request->input('role', UserRole::AGENT->value),
                'login_url' => route('login'),
            ]);
    }

    /**
     * PATCH /admin/agents/{id}/live-chat — Toggle a user's Live Chat access.
     */
    public function toggleLiveChat(Request $request, int $id)
    {
        $user = \App\Models\User::findOrFail($id);
        $user->update(['can_live_chat' => ! $user->can_live_chat]);

        return back()->with('success', $user->can_live_chat
            ? "Live Chat enabled for {$user->name}."
            : "Live Chat disabled for {$user->name}.");
    }

    /**
     * DELETE /admin/agents/{id} — Remove an agent.
     */
    public function destroy(Request $request, int $id)
    {
        $this->users->delete($id);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Agent removed.']);
        }

        return redirect()->route('admin.agents.index')->with('success', 'Agent removed.');
    }
}
