<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ChatResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\ActivityResource;
use App\Enums\ChatStatus;
use App\Enums\UserRole;
use App\Models\Chat;
use App\Models\Setting;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\ChatRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(
        private ChatRepositoryInterface     $chats,
        private UserRepositoryInterface     $users,
        private ActivityRepositoryInterface $activities,
    ) {}

    /**
     * GET /api/admin/dashboard — Dashboard stats.
     */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'data' => [
                'active_chats'   => Chat::whereIn('status', ['open', 'in_progress'])->count(),
                'pending_queue'  => Chat::where('status', ChatStatus::PENDING)->count(),
                'agents_online'  => User::where('role', UserRole::AGENT)->where('status', 'online')->count(),
                'total_today'    => Chat::whereDate('created_at', today())->count(),
                'solved_today'   => Chat::where('status', ChatStatus::SOLVED)->whereDate('updated_at', today())->count(),
                'closed_today'   => Chat::where('status', ChatStatus::CLOSED)->whereDate('ended_at', today())->count(),
            ],
        ]);
    }

    /**
     * GET /api/admin/agents — List all agents.
     */
    public function agents(Request $request): JsonResponse
    {
        $agents = $this->users->getAgents($request->only(['status', 'per_page']));

        return response()->json([
            'data' => UserResource::collection($agents),
            'meta' => [
                'current_page' => $agents->currentPage(),
                'last_page'    => $agents->lastPage(),
                'total'        => $agents->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/agents — Create a new agent.
     */
    public function storeAgent(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8',
            'max_chats' => 'sometimes|integer|min:1|max:50',
        ]);

        $agent = $this->users->create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => $request->password,
            'role'      => UserRole::AGENT->value,
            'max_chats' => $request->max_chats ?? 5,
        ]);

        return response()->json([
            'message' => 'Agent created.',
            'data'    => new UserResource($agent),
        ], 201);
    }

    /**
     * DELETE /api/admin/agents/{id} — Remove an agent.
     */
    public function destroyAgent(int $id): JsonResponse
    {
        $this->users->delete($id);

        return response()->json(['message' => 'Agent removed.']);
    }

    /**
     * GET /api/admin/chats — All chats with filters.
     */
    public function chats(Request $request): JsonResponse
    {
        $chats = $this->chats->getByStatus(
            $request->get('status', 'open'),
            $request->get('per_page', 15),
        );

        return response()->json([
            'data' => ChatResource::collection($chats),
            'meta' => [
                'current_page' => $chats->currentPage(),
                'last_page'    => $chats->lastPage(),
                'total'        => $chats->total(),
            ],
        ]);
    }

    /**
     * GET /api/admin/activities — Recent activity logs.
     */
    public function activities(): JsonResponse
    {
        $activities = $this->activities->getRecent(50);

        return response()->json([
            'data' => ActivityResource::collection($activities),
        ]);
    }

    /**
     * GET /api/admin/settings — List all settings.
     */
    public function settings(): JsonResponse
    {
        return response()->json([
            'data' => Setting::all()->groupBy('group'),
        ]);
    }

    /**
     * PUT /api/admin/settings — Bulk update settings.
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $request->validate([
            'settings'         => 'required|array',
            'settings.*.key'   => 'required|string',
            'settings.*.value' => 'required|string',
        ]);

        foreach ($request->settings as $item) {
            Setting::updateOrCreate(
                ['key' => $item['key']],
                ['value' => $item['value'], 'group' => $item['group'] ?? 'general'],
            );
        }

        return response()->json(['message' => 'Settings updated.']);
    }
}
