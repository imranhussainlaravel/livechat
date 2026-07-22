@extends('layouts.app')
@section('header_title', 'Company Details')

@section('content')
<x-crm.page-header title="{{ $company->name }}" subtitle="{{ $company->city ?: 'Company details' }}">
    <x-slot:actions>
        <a href="{{ route('crm.companies.edit', $company) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Edit</a>
        <a href="{{ route('crm.companies.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Back</a>
    </x-slot:actions>
</x-crm.page-header>

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <x-crm.panel title="Details">
            <div class="p-6 space-y-4">
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Name</div>
                    <div class="text-sm text-slate-200">{{ $company->name }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">City</div>
                    <div class="text-sm text-slate-200">{{ $company->city ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Industry Notes</div>
                    <div class="text-sm text-slate-300 leading-relaxed whitespace-pre-line">{{ $company->industry_notes ?: '—' }}</div>
                </div>
            </div>
        </x-crm.panel>
    </div>

    <div class="lg:col-span-2">
        <x-crm.panel title="Contacts">
            <x-slot:headerActions>
                <a href="{{ route('crm.contacts.create', ['company_id' => $company->id]) }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    New Contact
                </a>
            </x-slot:headerActions>

            @if($company->contacts->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Name</th>
                            <th class="px-6 py-4">Designation</th>
                            <th class="px-6 py-4">Email</th>
                            <th class="px-6 py-4">Phone</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($company->contacts as $contact)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4">
                                <a href="{{ route('crm.contacts.show', $contact) }}" class="text-sm font-semibold text-slate-200 hover:text-[#6366F1] transition-colors">{{ $contact->name }}</a>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $contact->designation ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $contact->email ?: '—' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $contact->phone ?: '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-crm.empty message="No contacts for this company yet.">
                <x-slot:action>
                    <a href="{{ route('crm.contacts.create', ['company_id' => $company->id]) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">New Contact</a>
                </x-slot:action>
            </x-crm.empty>
            @endif
        </x-crm.panel>
    </div>
</div>
@endsection
