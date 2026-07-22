@extends('layouts.app')
@section('header_title', 'New Deal')

@section('content')
@php
    $inputClass = 'w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all';
@endphp
<div class="max-w-2xl mx-auto">
    <x-crm.page-header title="New Deal" subtitle="Create a deal from an existing lead" />

    @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm font-medium">{{ session('error') }}</div>
    @endif

    <x-crm.panel>
        <form method="POST" action="{{ route('crm.deals.store') }}" class="p-6 space-y-5">
            @csrf

            <x-crm.field label="Lead" name="lead_id" :required="true">
                <select name="lead_id" id="lead_id" class="{{ $inputClass }}" required>
                    <option value="">Select a lead…</option>
                    @foreach($leads as $lead)
                    <option value="{{ $lead->id }}" @selected(old('lead_id') == $lead->id)>
                        {{ $lead->contact?->name ?? 'Unknown' }} — {{ $lead->contact?->company?->name ?? 'No company' }}
                    </option>
                    @endforeach
                </select>
                @if($leads->isEmpty())
                <p class="mt-1 text-[11px] text-amber-400">No leads without a deal are available.</p>
                @endif
            </x-crm.field>

            <x-crm.field label="Stage" name="stage" :required="true">
                <select name="stage" id="stage" class="{{ $inputClass }}" required>
                    @foreach($stages as $stage)
                    <option value="{{ $stage->value }}" @selected(old('stage', 'quoted') === $stage->value)>{{ $stage->getLabel() }}</option>
                    @endforeach
                </select>
            </x-crm.field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-crm.field label="Value (PKR)" name="value">
                    <input type="number" step="0.01" min="0" name="value" id="value" value="{{ old('value') }}" placeholder="0.00" class="{{ $inputClass }}">
                </x-crm.field>

                <x-crm.field label="Probability (%)" name="probability">
                    <input type="number" min="0" max="100" name="probability" id="probability" value="{{ old('probability') }}" placeholder="0-100" class="{{ $inputClass }}">
                </x-crm.field>
            </div>

            <x-crm.field label="Expected Close Date" name="expected_close_date">
                <input type="date" name="expected_close_date" id="expected_close_date" value="{{ old('expected_close_date') }}" class="{{ $inputClass }}">
            </x-crm.field>

            @if($salesReps->isNotEmpty())
            <x-crm.field label="Sales Rep" name="sales_rep_id">
                <select name="sales_rep_id" id="sales_rep_id" class="{{ $inputClass }}">
                    <option value="">Assign to me</option>
                    @foreach($salesReps as $rep)
                    <option value="{{ $rep->id }}" @selected(old('sales_rep_id') == $rep->id)>{{ $rep->name }}</option>
                    @endforeach
                </select>
            </x-crm.field>
            @endif

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Create Deal</button>
                <a href="{{ route('crm.deals.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</a>
            </div>
        </form>
    </x-crm.panel>
</div>
@endsection
