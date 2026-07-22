@extends('layouts.app')
@section('header_title', 'New Contact')

@section('content')
<x-crm.page-header title="New Contact" subtitle="Add a contact to the CRM" />

<div class="max-w-2xl">
    <x-crm.panel>
        <form method="POST" action="{{ route('crm.contacts.store') }}" class="p-6 space-y-5">
            @csrf

            <x-crm.field label="Company" name="company_id" :required="true">
                <div class="flex gap-2">
                    <select name="company_id" id="company_id" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all">
                        <option value="">Select a company…</option>
                        @foreach($companies as $company)
                        <option value="{{ $company->id }}" @selected(old('company_id', $selectedCompanyId) == $company->id)>{{ $company->name }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="crmModal.open('company', '#company_id')" title="New company"
                            class="shrink-0 inline-flex items-center justify-center w-11 rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    </button>
                </div>
            </x-crm.field>

            <x-crm.field label="Name" name="name" :required="true">
                <input type="text" name="name" id="name" value="{{ old('name') }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all" placeholder="Jane Doe">
            </x-crm.field>

            <x-crm.field label="Email" name="email">
                <input type="email" name="email" id="email" value="{{ old('email') }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all" placeholder="jane@acme.com">
            </x-crm.field>

            <x-crm.field label="Phone" name="phone">
                <input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all" placeholder="+92 300 1234567">
            </x-crm.field>

            <x-crm.field label="Designation" name="designation">
                <input type="text" name="designation" id="designation" value="{{ old('designation') }}" class="w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all" placeholder="Procurement Manager">
            </x-crm.field>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Save Contact</button>
                <a href="{{ route('crm.contacts.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</a>
            </div>
        </form>
    </x-crm.panel>
</div>

@include('crm.partials.create-modals')
@endsection
