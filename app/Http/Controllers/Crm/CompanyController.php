<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::withCount('contacts')->orderBy('name')->paginate(15);

        return view('crm.companies.index', compact('companies'));
    }

    public function create()
    {
        return view('crm.companies.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'industry_notes' => 'nullable|string',
        ]);

        $company = Company::create($data);

        // Modal (AJAX) create returns the new record so the caller can slot it into a <select>.
        if ($request->expectsJson()) {
            return response()->json(['id' => $company->id, 'name' => $company->name], 201);
        }

        return redirect()->route('crm.companies.index')->with('success', 'Company created.');
    }

    public function show(Company $company)
    {
        $company->load('contacts');

        return view('crm.companies.show', compact('company'));
    }

    public function edit(Company $company)
    {
        return view('crm.companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:255',
            'industry_notes' => 'nullable|string',
        ]);

        $company->update($data);

        return redirect()->route('crm.companies.index')->with('success', 'Company updated.');
    }

    public function destroy(Company $company)
    {
        $company->delete();

        return redirect()->route('crm.companies.index')->with('success', 'Company deleted.');
    }
}
