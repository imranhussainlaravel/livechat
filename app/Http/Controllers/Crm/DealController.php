<?php

namespace App\Http\Controllers\Crm;

use App\Enums\DealStage;
use App\Enums\LostReason;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class DealController extends Controller
{
    /** Abort unless the current user may act on this deal. */
    private function authorizeDeal(Deal $deal): void
    {
        $user = auth()->user();

        if (! $user->isAdmin() && $deal->sales_rep_id !== $user->id) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Board is the default view; ?view=table switches to the list.
        $view = $request->query('view') === 'table' ? 'table' : 'board';

        if ($view === 'board') {
            $query = Deal::with(['lead.contact.company', 'salesRep', 'order']);
            if (! $user->isAdmin()) {
                $query->where('sales_rep_id', $user->id);
            }

            $dealsByStage = $query->orderBy('position')->latest()->get()
                ->groupBy(fn (Deal $deal) => $deal->stage->value);

            return view('crm.deals.board', [
                'stages' => DealStage::cases(),
                'dealsByStage' => $dealsByStage,
            ]);
        }

        $query = Deal::with(['lead.contact.company', 'salesRep', 'order'])->latest();

        if (! $user->isAdmin()) {
            $query->where('sales_rep_id', $user->id);
        }

        if ($request->filled('stage') && DealStage::tryFrom($request->stage)) {
            $query->where('stage', $request->stage);
        }

        $deals = $query->paginate(15)->withQueryString();

        // Stats strip: scoped to what the user can see.
        $statsBase = Deal::query();
        if (! $user->isAdmin()) {
            $statsBase->where('sales_rep_id', $user->id);
        }

        $openValue = (clone $statsBase)
            ->whereIn('stage', [DealStage::Quoted->value, DealStage::Negotiation->value])
            ->sum('value');

        $stageCounts = [];
        foreach (DealStage::cases() as $stage) {
            $stageCounts[$stage->value] = (clone $statsBase)->where('stage', $stage->value)->count();
        }

        $stages = DealStage::cases();

        return view('crm.deals.index', compact('deals', 'openValue', 'stageCounts', 'stages'));
    }

    /**
     * Drag-and-drop on the board: move a deal to a new stage column.
     * Dropping into Won/Lost fires the same side-effects as the Won/Lost buttons.
     * Returns JSON for the board's fetch() call.
     */
    public function updateStage(Request $request, Deal $deal)
    {
        $this->authorizeDeal($deal);

        $data = $request->validate([
            'stage' => 'required|in:' . implode(',', array_column(DealStage::cases(), 'value')),
        ]);

        $new = DealStage::from($data['stage']);

        if ($new === DealStage::Won) {
            $deal->update(['stage' => DealStage::Won->value]);
            if (! $deal->order) {
                Order::create(['deal_id' => $deal->id, 'status' => 'pending']);
            }
            $deal->lead?->update(['status' => 'won']);

            return response()->json(['message' => 'Deal won — order created.']);
        }

        if ($new === DealStage::Lost) {
            $deal->update(['stage' => DealStage::Lost->value]);
            $deal->lead?->update(['status' => 'lost']);

            return response()->json(['message' => 'Deal moved to Lost.']);
        }

        $deal->update(['stage' => $new->value]);

        return response()->json(['message' => "Moved to {$new->getLabel()}"]);
    }

    public function create()
    {
        $user = auth()->user();

        $leads = Lead::whereDoesntHave('deal')
            ->when(! $user->isAdmin(), fn ($q) => $q->where('assigned_agent_id', $user->id))
            ->with('contact.company')
            ->get();

        $salesReps = $user->isAdmin()
            ? User::where('role', UserRole::AGENT->value)->orderBy('name')->get()
            : collect();

        return view('crm.deals.create', [
            'leads' => $leads,
            'salesReps' => $salesReps,
            'stages' => DealStage::cases(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'stage' => 'required|in:' . implode(',', array_column(DealStage::cases(), 'value')),
            'value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'probability' => 'nullable|integer|min:0|max:100',
            'sales_rep_id' => 'nullable|exists:users,id',
        ]);

        // One deal per lead.
        if (Deal::where('lead_id', $data['lead_id'])->exists()) {
            return redirect()->back()->withInput()
                ->with('error', 'A deal already exists for this lead.');
        }

        $data['sales_rep_id'] = $user->isAdmin()
            ? ($data['sales_rep_id'] ?? $user->id)
            : $user->id;

        $deal = Deal::create($data);

        return redirect()->route('crm.deals.show', $deal)->with('success', 'Deal created.');
    }

    public function show(Deal $deal)
    {
        $this->authorizeDeal($deal);

        $deal->load(['lead.contact.company', 'salesRep', 'quotations', 'order']);

        return view('crm.deals.show', [
            'deal' => $deal,
            'lostReasons' => LostReason::cases(),
        ]);
    }

    public function edit(Deal $deal)
    {
        $this->authorizeDeal($deal);

        $user = auth()->user();

        $salesReps = $user->isAdmin()
            ? User::where('role', UserRole::AGENT->value)->orderBy('name')->get()
            : collect();

        $deal->load('lead.contact.company');

        return view('crm.deals.edit', [
            'deal' => $deal,
            'salesReps' => $salesReps,
            'stages' => DealStage::cases(),
        ]);
    }

    public function update(Request $request, Deal $deal)
    {
        $this->authorizeDeal($deal);

        $user = auth()->user();

        $data = $request->validate([
            'stage' => 'required|in:' . implode(',', array_column(DealStage::cases(), 'value')),
            'value' => 'nullable|numeric|min:0',
            'expected_close_date' => 'nullable|date',
            'probability' => 'nullable|integer|min:0|max:100',
            'sales_rep_id' => 'nullable|exists:users,id',
        ]);

        if (! $user->isAdmin()) {
            unset($data['sales_rep_id']);
        } elseif (empty($data['sales_rep_id'])) {
            unset($data['sales_rep_id']);
        }

        $deal->update($data);

        return redirect()->route('crm.deals.show', $deal)->with('success', 'Deal updated.');
    }

    public function destroy(Deal $deal)
    {
        $this->authorizeDeal($deal);

        $deal->delete();

        return redirect()->route('crm.deals.index')->with('success', 'Deal deleted.');
    }

    public function markWon(Deal $deal)
    {
        $this->authorizeDeal($deal);

        $deal->update(['stage' => DealStage::Won->value]);

        if (! $deal->order) {
            Order::create(['deal_id' => $deal->id, 'status' => 'pending']);
        }

        $deal->lead?->update(['status' => 'won']);

        return redirect()->back()->with('success', 'Deal won — order created.');
    }

    public function markLost(Request $request, Deal $deal)
    {
        $this->authorizeDeal($deal);

        $data = $request->validate([
            'lost_reason' => 'required|in:' . implode(',', array_column(LostReason::cases(), 'value')),
        ]);

        $deal->update([
            'stage' => DealStage::Lost->value,
            'lost_reason' => $data['lost_reason'],
        ]);

        $deal->lead?->update([
            'status' => 'lost',
            'lost_reason' => $data['lost_reason'],
        ]);

        return redirect()->back()->with('success', 'Deal marked as lost.');
    }
}
