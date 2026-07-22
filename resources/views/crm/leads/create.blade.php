@extends('layouts.app')
@section('header_title', 'New Lead')

@php
    $inputClass = 'w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all';
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <x-crm.page-header title="New Lead" subtitle="Create a new sales lead" />

    <x-crm.panel title="Lead details">
        <form method="POST" action="{{ route('crm.leads.store') }}" class="p-6 space-y-5">
            @csrf

            <x-crm.field label="Contact" name="contact_id" :required="true">
                <div class="flex gap-2">
                    <select name="contact_id" id="contact_id" class="{{ $inputClass }}">
                        <option value="">Select a contact…</option>
                        @foreach($contacts as $contact)
                        <option value="{{ $contact->id }}" @selected(old('contact_id') == $contact->id)>
                            {{ $contact->name }}@if($contact->company) — {{ $contact->company->name }}@endif
                        </option>
                        @endforeach
                    </select>
                    <button type="button" onclick="crmModal.open('contact', '#contact_id')" title="New contact"
                            class="shrink-0 inline-flex items-center justify-center w-11 rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
            </x-crm.field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-crm.field label="Source" name="source" :required="true">
                    <select name="source" id="source" class="{{ $inputClass }}">
                        <option value="">Select…</option>
                        @foreach($sources as $source)
                        <option value="{{ $source->value }}" @selected(old('source') === $source->value)>{{ $source->getLabel() }}</option>
                        @endforeach
                    </select>
                </x-crm.field>

                <x-crm.field label="Product interest" name="product_interest" :required="true">
                    <select name="product_interest" id="product_interest" class="{{ $inputClass }}">
                        <option value="">Select…</option>
                        @foreach($productInterests as $interest)
                        <option value="{{ $interest->value }}" @selected(old('product_interest') === $interest->value)>{{ $interest->getLabel() }}</option>
                        @endforeach
                    </select>
                </x-crm.field>
            </div>

            @if($isAdmin)
            <x-crm.field label="Assigned agent" name="assigned_agent_id" :required="true">
                <select name="assigned_agent_id" id="assigned_agent_id" class="{{ $inputClass }}">
                    <option value="">Select an agent…</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}" @selected(old('assigned_agent_id') == $agent->id)>{{ $agent->name }}</option>
                    @endforeach
                </select>
            </x-crm.field>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-crm.field label="Follow-up date" name="follow_up_date">
                    <input type="date" name="follow_up_date" id="follow_up_date" value="{{ old('follow_up_date') }}" class="{{ $inputClass }}">
                </x-crm.field>
            </div>

            <x-crm.field label="Follow-up note" name="follow_up_note">
                <textarea name="follow_up_note" id="follow_up_note" rows="3" class="{{ $inputClass }}" placeholder="Optional note…">{{ old('follow_up_note') }}</textarea>
            </x-crm.field>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
                    Create Lead
                </button>
                <a href="{{ route('crm.leads.index') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </x-crm.panel>
</div>

@include('crm.partials.create-modals')
@endsection
