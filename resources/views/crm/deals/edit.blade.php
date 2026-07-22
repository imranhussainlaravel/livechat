@extends('layouts.app')
@section('header_title', 'Edit Deal')

@section('content')
@php
    $inputClass = 'w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all';
@endphp
<div class="max-w-2xl mx-auto">
    <x-crm.page-header title="Edit Deal #{{ $deal->id }}" subtitle="{{ $deal->lead?->contact?->company?->name ?? '' }}" />

    <x-crm.panel>
        <form method="POST" action="{{ route('crm.deals.update', $deal) }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <x-crm.field label="Stage" name="stage" :required="true">
                <select name="stage" id="stage" class="{{ $inputClass }}" required>
                    @foreach($stages as $stage)
                    <option value="{{ $stage->value }}" @selected(old('stage', $deal->stage?->value) === $stage->value)>{{ $stage->getLabel() }}</option>
                    @endforeach
                </select>
            </x-crm.field>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <x-crm.field label="Value (PKR)" name="value">
                    <input type="number" step="0.01" min="0" name="value" id="value" value="{{ old('value', $deal->value) }}" placeholder="0.00" class="{{ $inputClass }}">
                </x-crm.field>

                <x-crm.field label="Probability (%)" name="probability">
                    <input type="number" min="0" max="100" name="probability" id="probability" value="{{ old('probability', $deal->probability) }}" placeholder="0-100" class="{{ $inputClass }}">
                </x-crm.field>
            </div>

            <x-crm.field label="Expected Close Date" name="expected_close_date">
                <input type="date" name="expected_close_date" id="expected_close_date" value="{{ old('expected_close_date', $deal->expected_close_date?->format('Y-m-d')) }}" class="{{ $inputClass }}">
            </x-crm.field>

            @if($salesReps->isNotEmpty())
            <x-crm.field label="Sales Rep" name="sales_rep_id">
                <select name="sales_rep_id" id="sales_rep_id" class="{{ $inputClass }}">
                    @foreach($salesReps as $rep)
                    <option value="{{ $rep->id }}" @selected(old('sales_rep_id', $deal->sales_rep_id) == $rep->id)>{{ $rep->name }}</option>
                    @endforeach
                </select>
            </x-crm.field>
            @endif

            <div class="flex items-center gap-2 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Save Changes</button>
                <a href="{{ route('crm.deals.show', $deal) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</a>
            </div>
        </form>
    </x-crm.panel>
</div>
@endsection
