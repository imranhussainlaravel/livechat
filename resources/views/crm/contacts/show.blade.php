@extends('layouts.app')
@section('header_title', 'Contact Details')

@section('content')
<x-crm.page-header title="{{ $contact->name }}" subtitle="{{ $contact->designation ?: 'Contact details' }}">
    <x-slot:actions>
        <a href="{{ route('crm.contacts.edit', $contact) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Edit</a>
        <a href="{{ route('crm.contacts.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Back</a>
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
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Company</div>
                    <div class="text-sm text-slate-200">
                        @if($contact->company)
                        <a href="{{ route('crm.companies.show', $contact->company) }}" class="hover:text-[#6366F1] transition-colors">{{ $contact->company->name }}</a>
                        @else
                        —
                        @endif
                    </div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Designation</div>
                    <div class="text-sm text-slate-200">{{ $contact->designation ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Email</div>
                    <div class="text-sm text-slate-200">{{ $contact->email ?: '—' }}</div>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Phone</div>
                    <div class="text-sm text-slate-200">{{ $contact->phone ?: '—' }}</div>
                </div>
            </div>
        </x-crm.panel>
    </div>

    <div class="lg:col-span-2">
        <x-crm.panel title="Leads">
            @if($contact->leads->count())
            <div class="overflow-x-auto">
                <table class="min-w-full text-left">
                    <thead>
                        <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                            <th class="px-6 py-4">Lead</th>
                            <th class="px-6 py-4">Source</th>
                            <th class="px-6 py-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-700/50">
                        @foreach($contact->leads as $lead)
                        <tr class="hover:bg-slate-700/20 transition-colors">
                            <td class="px-6 py-4">
                                @if(Route::has('crm.leads.show'))
                                <a href="{{ route('crm.leads.show', $lead) }}" class="text-sm font-semibold text-slate-200 hover:text-[#6366F1] transition-colors">Lead #{{ $lead->id }}</a>
                                @else
                                <span class="text-sm font-semibold text-slate-200">Lead #{{ $lead->id }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400">{{ $lead->source?->getLabel() ?? '—' }}</td>
                            <td class="px-6 py-4">
                                @if($lead->status)
                                <x-crm.badge :color="$lead->status->getColor()" :label="$lead->status->getLabel()" />
                                @else
                                —
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-crm.empty message="No leads for this contact yet." />
            @endif
        </x-crm.panel>
    </div>
</div>
@endsection
