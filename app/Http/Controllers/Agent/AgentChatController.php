<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\ChannelRead;
use App\Models\InternalMessage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

        // Initial page load only needs the most recent slice of history — capped
        // so a long-running DM/channel thread can't turn this into an
        // ever-growing unbounded query as it accumulates messages.
        $historyLimit = 200;

        if ($request->filled('dm')) {
            $other = User::find($request->query('dm'));
            if ($other) {
                $activeType = 'dm';
                $activeId = (string) $other->id;
                $activeTitle = $other->name;
                $messages = $this->dmQuery($other->id)->with('sender')->orderByDesc('created_at')->orderByDesc('id')
                    ->limit($historyLimit)->get()->reverse()->values();
                $this->markDmRead($other->id);
            }
        } elseif ($request->filled('channel') && isset(self::CHANNELS[$request->query('channel')])) {
            $activeType = 'group';
            $activeId = $request->query('channel');
            $activeTitle = self::CHANNELS[$activeId];
            $messages = InternalMessage::where('channel', $activeId)->with('sender')->orderByDesc('created_at')->orderByDesc('id')
                ->limit($historyLimit)->get()->reverse()->values();
        } else {
            // Default to the General channel.
            $activeType = 'group';
            $activeId = 'general';
            $activeTitle = self::CHANNELS['general'];
            $messages = InternalMessage::where('channel', 'general')->with('sender')->orderByDesc('created_at')->orderByDesc('id')
                ->limit($historyLimit)->get()->reverse()->values();
        }

        // Opening a channel marks it read up to its newest message.
        if ($activeType === 'group') {
            $this->markChannelRead($activeId);
        }

        return view('agent.other-agents.index', [
            'users' => $users,
            'channels' => self::CHANNELS,
            'activeType' => $activeType,
            'activeId' => $activeId,
            'activeTitle' => $activeTitle,
            'messages' => $messages,
            'channelUnread' => $this->channelUnreadCounts(),
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
            $this->markChannelRead($request->query('channel'));
        } else {
            return response()->json(['data' => []]);
        }

        $messages = $query->where('id', '>', $after)->with('sender')->orderBy('created_at')->get();

        return response()->json(['data' => $messages->map(fn ($m) => $this->serialize($m))->values()]);
    }

    /**
     * Mark a group channel read up to its most recent message for the current user.
     * Wrapped so a missing migration (channel_reads / internal_messages.channel)
     * degrades to a no-op instead of 500-ing the Team Chat page.
     */
    private function markChannelRead(string $channel): void
    {
        try {
            $maxId = (int) InternalMessage::where('channel', $channel)->max('id');

            ChannelRead::updateOrCreate(
                ['user_id' => Auth::id(), 'channel' => $channel],
                ['last_read_message_id' => $maxId],
            );
        } catch (\Throwable $e) {
            // channel_reads table not migrated yet — ignore.
        }
    }

    /** Unread message count per group channel for the current user (excludes own messages). */
    private function channelUnreadCounts(): array
    {
        $counts = array_fill_keys(array_keys(self::CHANNELS), 0);

        try {
            $meId = Auth::id();
            $channels = array_keys(self::CHANNELS);
            $reads = ChannelRead::where('user_id', $meId)->pluck('last_read_message_id', 'channel');

            // Single grouped query instead of one COUNT per channel.
            $rows = InternalMessage::whereIn('channel', $channels)
                ->where('sender_id', '!=', $meId)
                ->where(function ($q) use ($channels, $reads) {
                    foreach ($channels as $ch) {
                        $q->orWhere(function ($qq) use ($ch, $reads) {
                            $qq->where('channel', $ch)->where('id', '>', ($reads[$ch] ?? 0));
                        });
                    }
                })
                ->selectRaw('channel, COUNT(*) as cnt')
                ->groupBy('channel')
                ->pluck('cnt', 'channel');

            foreach ($rows as $key => $cnt) {
                $counts[$key] = (int) $cnt;
            }
        } catch (\Throwable $e) {
            // channel_reads not migrated yet — report zero unread.
        }

        return $counts;
    }

    /**
     * GET /agent/team/unread-summary — lightweight JSON poll for global
     * notifications: unread DM count + the newest unread DM. Used by the
     * layout to toast/notify when a teammate messages you from any page.
     */
    public function unreadSummary(Request $request)
    {
        $meId = Auth::id();

        // --- Unread direct messages ---
        $dmBase = InternalMessage::where('receiver_id', $meId)->where('is_read', false);
        $dmCount = (clone $dmBase)->count();
        $latestDm = (clone $dmBase)->with('sender')->orderByDesc('id')->first();

        // --- Unread group-channel messages (per-user read marker) ---
        // Guarded so a pending migration can't 500 this poll endpoint. This
        // endpoint is polled every ~12s by every online agent, so it's kept
        // to 2 queries total instead of 2-per-channel (count + first).
        $channelCount = 0;
        $latestChannelMsg = null;
        $latestChannelKey = null;

        try {
            $channels = array_keys(self::CHANNELS);
            $reads = ChannelRead::where('user_id', $meId)->pluck('last_read_message_id', 'channel');

            $unreadChannelBase = InternalMessage::whereIn('channel', $channels)
                ->where('sender_id', '!=', $meId)
                ->where(function ($q) use ($channels, $reads) {
                    foreach ($channels as $ch) {
                        $q->orWhere(function ($qq) use ($ch, $reads) {
                            $qq->where('channel', $ch)->where('id', '>', ($reads[$ch] ?? 0));
                        });
                    }
                });

            $channelCount = (clone $unreadChannelBase)->count();
            $latestChannelMsg = (clone $unreadChannelBase)->with('sender')->orderByDesc('id')->first();
            $latestChannelKey = $latestChannelMsg?->channel;
        } catch (\Throwable $e) {
            // channel_reads / channel column not migrated yet — DM-only summary.
        }

        // --- Pick the single newest unread message for the notification ---
        $latest = null;
        $useChannel = $latestChannelMsg && (! $latestDm || $latestChannelMsg->id > $latestDm->id);

        if ($useChannel) {
            $latest = [
                'id' => $latestChannelMsg->id,
                'sender_name' => ($latestChannelMsg->sender?->name ?? 'Teammate').' in #'.self::CHANNELS[$latestChannelKey],
                'preview' => Str::limit($latestChannelMsg->message, 80),
                'url' => route('agent.agents.index', ['channel' => $latestChannelKey]),
            ];
        } elseif ($latestDm) {
            $latest = [
                'id' => $latestDm->id,
                'sender_name' => $latestDm->sender?->name ?? 'Teammate',
                'preview' => Str::limit($latestDm->message, 80),
                'url' => route('agent.agents.index', ['dm' => $latestDm->sender_id]),
            ];
        }

        return response()->json([
            'count' => $dmCount + $channelCount,
            'latest' => $latest,
        ]);
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
