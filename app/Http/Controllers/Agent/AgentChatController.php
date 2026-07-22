<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentChatController extends Controller
{
    /** Group channels available to everyone. */
    public const CHANNELS = [
        'general' => 'General',
        'leads' => 'Leads',
        'live-chat' => 'Live Chat',
    ];

    /**
     * GET /agent/other-agents — Team Chat (two-pane: DMs + group channels).
     * Active conversation chosen via ?dm={id} or ?channel={key}; defaults to General.
     */
    public function index(Request $request)
    {
        $meId = Auth::id();

        $users = User::where('id', '!=', $meId)
            ->withCount(['sentInternalMessages as unread_count' => function ($q) use ($meId) {
                $q->where('receiver_id', $meId)->where('is_read', false);
            }])
            ->orderByRaw("CASE WHEN status = 'online' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->get();

        // Resolve the active conversation.
        $activeType = null;
        $activeId = null;
        $activeTitle = null;
        $messages = collect();

        if ($request->filled('dm')) {
            $other = User::find($request->query('dm'));
            if ($other) {
                $activeType = 'dm';
                $activeId = (string) $other->id;
                $activeTitle = $other->name;
                $messages = $this->dmQuery($other->id)->with('sender')->orderBy('created_at')->get();
                $this->markDmRead($other->id);
            }
        } elseif ($request->filled('channel') && isset(self::CHANNELS[$request->query('channel')])) {
            $activeType = 'group';
            $activeId = $request->query('channel');
            $activeTitle = self::CHANNELS[$activeId];
            $messages = InternalMessage::where('channel', $activeId)->with('sender')->orderBy('created_at')->get();
        } else {
            // Default to the General channel.
            $activeType = 'group';
            $activeId = 'general';
            $activeTitle = self::CHANNELS['general'];
            $messages = InternalMessage::where('channel', 'general')->with('sender')->orderBy('created_at')->get();
        }

        return view('agent.other-agents.index', [
            'users' => $users,
            'channels' => self::CHANNELS,
            'activeType' => $activeType,
            'activeId' => $activeId,
            'activeTitle' => $activeTitle,
            'messages' => $messages,
        ]);
    }

    /**
     * GET /agent/other-agents/{id} — kept for backward-compatible links
     * (e.g. the header notifications); opens the DM in the two-pane view.
     */
    public function show($id)
    {
        return redirect()->route('agent.agents.index', ['dm' => $id]);
    }

    /**
     * POST /agent/other-agents/{id}/message — send a direct message.
     */
    public function store(Request $request, $id)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $message = InternalMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $id,
            'message' => $request->message,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => $this->serialize($message->load('sender'))]);
        }

        return redirect()->route('agent.agents.index', ['dm' => $id]);
    }

    /**
     * POST /agent/other-agents/channel/{key}/message — send a group-channel message.
     */
    public function storeChannel(Request $request, string $key)
    {
        abort_unless(isset(self::CHANNELS[$key]), 404);

        $request->validate(['message' => 'required|string|max:2000']);

        $message = InternalMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => null,
            'channel' => $key,
            'message' => $request->message,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['data' => $this->serialize($message->load('sender'))]);
        }

        return redirect()->route('agent.agents.index', ['channel' => $key]);
    }

    /**
     * GET /agent/other-agents/messages — JSON poll for new messages in the
     * active conversation since ?after={id}. Used for near-real-time updates.
     */
    public function fetch(Request $request)
    {
        $after = (int) $request->query('after', 0);

        if ($request->filled('dm')) {
            $query = $this->dmQuery($request->query('dm'));
            $this->markDmRead($request->query('dm'));
        } elseif ($request->filled('channel') && isset(self::CHANNELS[$request->query('channel')])) {
            $query = InternalMessage::where('channel', $request->query('channel'));
        } else {
            return response()->json(['data' => []]);
        }

        $messages = $query->where('id', '>', $after)->with('sender')->orderBy('created_at')->get();

        return response()->json(['data' => $messages->map(fn ($m) => $this->serialize($m))->values()]);
    }

    /** Messages exchanged one-to-one between the current user and $otherId. */
    private function dmQuery($otherId)
    {
        $meId = Auth::id();

        return InternalMessage::whereNull('channel')->where(function ($q) use ($meId, $otherId) {
            $q->where(fn ($w) => $w->where('sender_id', $meId)->where('receiver_id', $otherId))
              ->orWhere(fn ($w) => $w->where('sender_id', $otherId)->where('receiver_id', $meId));
        });
    }

    private function markDmRead($otherId): void
    {
        InternalMessage::where('sender_id', $otherId)
            ->where('receiver_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    private function serialize(InternalMessage $m): array
    {
        return [
            'id' => $m->id,
            'sender_id' => $m->sender_id,
            'sender_name' => $m->sender?->name ?? 'User',
            'content' => $m->message,
            'mine' => $m->sender_id === Auth::id(),
            'time' => $m->created_at?->format('M d, g:i A'),
        ];
    }
}
