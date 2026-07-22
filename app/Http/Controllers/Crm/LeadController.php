<?php

namespace App\Http\Controllers\Crm;

use App\Enums\LeadActivityType;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\LostReason;
use App\Enums\ProductInterest;
use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\User;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    /**
     * Agents may only touch their own leads; admins may touch any.
     */
    private function authorizeLead(Lead $lead): void
    {
        $user = auth()->user();

        if ($user->isAdmin()) {
            return;
        }

        if ($lead->assigned_agent_id !== $user->id) {
            abort(403);
        }
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        $query = Lead::query()
            ->with(['contact.company', 'assignedAgent', 'deal']);

        if (! $user->isAdmin()) {
            $query->where('assigned_agent_id', $user->id);
        }

        // Board is the default view; ?view=table switches to the list.
        $view = $request->query('view') === 'table' ? 'table' : 'board';

        if ($view === 'board') {
            $leadsByStatus = $query->orderBy('position')->latest()->get()
                ->groupBy(fn (Lead $lead) => $lead->status->value);

            return view('crm.leads.board', [
                'statuses' => LeadStatus::cases(),
                'leadsByStatus' => $leadsByStatus,
            ]);
        }

        $status = $request->query('status');
        if ($status && ($statusEnum = LeadStatus::tryFrom($status))) {
            $query->where('status', $statusEnum->value);
        }

        $leads = $query->latest()->paginate(15)->withQueryString();

        return view('crm.leads.index', [
            'leads' => $leads,
            'statuses' => LeadStatus::cases(),
            'activeStatus' => $status,
        ]);
    }

    /**
     * Drag-and-drop on the board: move a lead to a new status column.
     * Returns JSON for the board's fetch() call.
     */
    public function updateStatus(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);

        $validated = $request->validate([
            'status' => ['required', 'in:' . $this->enumValues(LeadStatus::cases())],
        ]);

        $old = $lead->status;
        $new = LeadStatus::from($validated['status']);

        if ($old->value !== $new->value) {
            $lead->update(['status' => $new->value]);

            LeadActivity::create([
                'lead_id' => $lead->id,
                'user_id' => auth()->id(),
                'type' => LeadActivityType::StatusChange->value,
                'note' => "Status: {$old->getLabel()} → {$new->getLabel()} (board)",
            ]);
        }

        return response()->json(['message' => "Moved to {$new->getLabel()}"]);
    }

    public function create()
    {
        $user = auth()->user();

        if (! $user->canCreateLeads()) {
            return redirect()->route('crm.leads.index')
                ->with('error', 'Your work scope does not allow creating leads.');
        }

        return view('crm.leads.create', [
            'contacts' => Contact::with('company')->orderBy('name')->get(),
            'sources' => LeadSource::cases(),
            'productInterests' => ProductInterest::cases(),
            'agents' => $user->isAdmin()
                ? User::where('role', 'agent')->orderBy('name')->get()
                : collect(),
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (! $user->canCreateLeads()) {
            return redirect()->route('crm.leads.index')
                ->with('error', 'Your work scope does not allow creating leads.');
        }

        $rules = [
            'contact_id' => ['required', 'exists:contacts,id'],
            'source' => ['required', 'in:' . $this->enumValues(LeadSource::cases())],
            'product_interest' => ['required', 'in:' . $this->enumValues(ProductInterest::cases())],
            'follow_up_date' => ['nullable', 'date'],
            'follow_up_note' => ['nullable', 'string'],
        ];

        if ($user->isAdmin()) {
            $rules['assigned_agent_id'] = ['required', 'exists:users,id'];
        }

        $validated = $request->validate($rules);

        $lead = Lead::create([
            'contact_id' => $validated['contact_id'],
            'source' => $validated['source'],
            'product_interest' => $validated['product_interest'],
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'follow_up_note' => $validated['follow_up_note'] ?? null,
            'assigned_agent_id' => $user->isAdmin()
                ? $validated['assigned_agent_id']
                : $user->id,
            'status' => LeadStatus::New->value,
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type' => LeadActivityType::Note->value,
            'note' => 'Lead created',
        ]);

        return redirect()->route('crm.leads.show', $lead)
            ->with('success', 'Lead created.');
    }

    public function show(Lead $lead)
    {
        $this->authorizeLead($lead);

        $lead->load(['contact.company', 'assignedAgent', 'deal', 'activities.user']);

        return view('crm.leads.show', [
            'lead' => $lead,
            'activityTypes' => LeadActivityType::cases(),
            'lostReasons' => LostReason::cases(),
        ]);
    }

    public function edit(Lead $lead)
    {
        $this->authorizeLead($lead);

        $user = auth()->user();

        return view('crm.leads.edit', [
            'lead' => $lead,
            'contacts' => Contact::with('company')->orderBy('name')->get(),
            'sources' => LeadSource::cases(),
            'productInterests' => ProductInterest::cases(),
            'statuses' => LeadStatus::cases(),
            'agents' => $user->isAdmin()
                ? User::where('role', 'agent')->orderBy('name')->get()
                : collect(),
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);

        $user = auth()->user();

        $rules = [
            'contact_id' => ['required', 'exists:contacts,id'],
            'source' => ['required', 'in:' . $this->enumValues(LeadSource::cases())],
            'product_interest' => ['required', 'in:' . $this->enumValues(ProductInterest::cases())],
            'status' => ['required', 'in:' . $this->enumValues(LeadStatus::cases())],
            'follow_up_date' => ['nullable', 'date'],
            'follow_up_note' => ['nullable', 'string'],
        ];

        if ($user->isAdmin()) {
            $rules['assigned_agent_id'] = ['required', 'exists:users,id'];
        }

        $validated = $request->validate($rules);

        $data = [
            'contact_id' => $validated['contact_id'],
            'source' => $validated['source'],
            'product_interest' => $validated['product_interest'],
            'status' => $validated['status'],
            'follow_up_date' => $validated['follow_up_date'] ?? null,
            'follow_up_note' => $validated['follow_up_note'] ?? null,
        ];

        if ($user->isAdmin()) {
            $data['assigned_agent_id'] = $validated['assigned_agent_id'];
        }

        $lead->update($data);

        return redirect()->route('crm.leads.show', $lead)
            ->with('success', 'Lead updated.');
    }

    public function destroy(Lead $lead)
    {
        $this->authorizeLead($lead);

        $lead->delete();

        return redirect()->route('crm.leads.index')
            ->with('success', 'Lead deleted.');
    }

    public function markLost(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);

        $validated = $request->validate([
            'lost_reason' => ['required', 'in:' . $this->enumValues(LostReason::cases())],
        ]);

        $reason = LostReason::from($validated['lost_reason']);

        $lead->update([
            'status' => LeadStatus::Lost->value,
            'lost_reason' => $reason->value,
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => LeadActivityType::StatusChange->value,
            'note' => 'Marked lost: ' . $reason->getLabel(),
        ]);

        return redirect()->back()->with('success', 'Lead marked as lost.');
    }

    public function convert(Lead $lead)
    {
        $this->authorizeLead($lead);

        if ($lead->deal) {
            return redirect()->route('crm.deals.show', $lead->deal)
                ->with('info', 'This lead has already been converted to a deal.');
        }

        $deal = Deal::create([
            'lead_id' => $lead->id,
            'sales_rep_id' => $lead->assigned_agent_id ?? auth()->id(),
            'stage' => 'quoted',
        ]);

        $lead->update(['status' => LeadStatus::Quoted->value]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => LeadActivityType::StatusChange->value,
            'note' => 'Converted to deal',
        ]);

        return redirect()->route('crm.deals.show', $deal)
            ->with('success', 'Lead converted to deal.');
    }

    public function addActivity(Request $request, Lead $lead)
    {
        $this->authorizeLead($lead);

        $validated = $request->validate([
            'note' => ['required', 'string'],
            'type' => ['required', 'in:' . $this->enumValues(LeadActivityType::cases())],
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'note' => $validated['note'],
        ]);

        return redirect()->back()->with('success', 'Note logged.');
    }

    /**
     * Comma-joined list of backed-enum values for `in:` validation rules.
     *
     * @param  array<int, \BackedEnum>  $cases
     */
    private function enumValues(array $cases): string
    {
        return implode(',', array_map(fn ($case) => $case->value, $cases));
    }
}
