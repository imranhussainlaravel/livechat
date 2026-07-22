@extends('layouts.app')
@section('header_title', 'Edit Company')

@section('content')
<x-crm.page-header title="Edit Company" subtitle="{{ $company->name }}" />

<div class="max-w-2xl">
    <x-crm.panel>
        <form method="POST" action="{{ route('crm.companies.update', $company) }}" class="p-6 space-y-5">
            @csrf
            @method('PUT')

            <x-crm.field label="Name" name="name" :required="true">
                <input type="text" name="name" id="name" value="{{ old('name', $company->name) }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
            </x-crm.field>

            <x-crm.field label="City" name="city">
                <input type="text" name="city" id="city" value="{{ old('city', $company->city) }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
            </x-crm.field>

            <x-crm.field label="Industry Notes" name="industry_notes">
                <textarea name="industry_notes" id="industry_notes" rows="4" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">{{ old('industry_notes', $company->industry_notes) }}</textarea>
            </x-crm.field>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Update Company</button>
                <a href="{{ route('crm.companies.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</a>
            </div>
        </form>
    </x-crm.panel>
</div>
@endsection
