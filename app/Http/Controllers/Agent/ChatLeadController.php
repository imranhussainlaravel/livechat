<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Http\Request;

/**
 * Turns a live-chat conversation into a CRM lead (Phase 5).
 *
 * Kept out of the live-chat ChatController on purpose so nothing in the
 * chat flow is affected. Endpoint: POST /agent/chats/{id}/create-lead.
 */
class ChatLeadController extends Controller
{
    public function store(Request $request, int $id)
    {
        $user = $request->user();

        if (! $user->canCreateLeads()) {
            return back()->with('error', 'Your work scope does not allow creating leads.');
        }

        $chat = Chat::with('visitor')->findOrFail($id);

        $validated = $request->validate([
            'company_name'     => 'required|string|max:255',
            'contact_name'     => 'required|string|max:255',
            'email'            => 'nullable|email|max:255',
            'phone'            => 'nullable|string|max:50',
            'source'           => 'required|string|in:cold_call,referral,website,exhibition,other',
            'product_interest' => 'required|string|in:boxes,pouches,labels,cartons,custom',
            'follow_up_note'   => 'nullable|string|max:1000',
        ]);

        // Find-or-create the company by name, then a contact, then the lead —
        // all owned by the acting agent.
        $company = Company::firstOrCreate(['name' => $validated['company_name']]);

        $contactData = [
            'company_id'  => $company->id,
            'name'        => $validated['contact_name'],
            'email'       => $validated['email'] ?? null,
            'phone'       => $validated['phone'] ?? null,
        ];
        if (\Illuminate\Support\Facades\Schema::hasColumn('contacts', 'created_by')) {
            $contactData['created_by'] = $user->id;
        }
        $contact = Contact::create($contactData);

        $lead = Lead::create([
            'contact_id'        => $contact->id,
            'source'            => $validated['source'],
            'status'            => 'new',
            'product_interest'  => $validated['product_interest'],
            'assigned_agent_id' => $user->id,
            'follow_up_note'    => $validated['follow_up_note'] ?? null,
        ]);

        LeadActivity::create([
            'lead_id' => $lead->id,
            'user_id' => $user->id,
            'type'    => 'note',
            'note'    => "Created from live chat #{$chat->id}".($chat->visitor?->name ? " ({$chat->visitor->name})" : ''),
        ]);

        return redirect()->route('crm.leads.show', $lead)->with('success', 'Lead created from chat.');
    }
}
