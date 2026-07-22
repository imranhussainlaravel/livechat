<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /** Agents may only touch their own contacts; admins may touch any. */
    private function authorizeContact(Contact $contact): void
    {
        $user = auth()->user();

        if (! $user->isAdmin() && $contact->created_by !== $user->id) {
            abort(403);
        }
    }

    public function index()
    {
        $contacts = Contact::with('company')
            ->visibleTo(auth()->user())
            ->orderBy('name')
            ->paginate(15);

        return view('crm.contacts.index', compact('contacts'));
    }

    public function create(Request $request)
    {
        $companies = Company::orderBy('name')->get();

        return view('crm.contacts.create', [
            'companies' => $companies,
            'selectedCompanyId' => $request->input('company_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
        ]);

        $data['created_by'] = auth()->id();

        $contact = Contact::create($data);

        // Modal (AJAX) create returns the new record (with a company-qualified label).
        if ($request->expectsJson()) {
            $contact->load('company');

            return response()->json([
                'id' => $contact->id,
                'label' => $contact->name.($contact->company ? ' — '.$contact->company->name : ''),
            ], 201);
        }

        return redirect()->route('crm.contacts.index')->with('success', 'Contact created.');
    }

    public function show(Contact $contact)
    {
        $this->authorizeContact($contact);

        $contact->load(['company', 'leads']);

        return view('crm.contacts.show', compact('contact'));
    }

    public function edit(Contact $contact)
    {
        $this->authorizeContact($contact);

        $companies = Company::orderBy('name')->get();

        return view('crm.contacts.edit', compact('contact', 'companies'));
    }

    public function update(Request $request, Contact $contact)
    {
        $this->authorizeContact($contact);

        $data = $request->validate([
            'company_id' => 'required|exists:companies,id',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'designation' => 'nullable|string|max:255',
        ]);

        $contact->update($data);

        return redirect()->route('crm.contacts.index')->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact)
    {
        $this->authorizeContact($contact);

        $contact->delete();

        return redirect()->route('crm.contacts.index')->with('success', 'Contact deleted.');
    }
}
