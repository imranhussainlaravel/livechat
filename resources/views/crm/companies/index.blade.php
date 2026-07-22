@extends('layouts.app')
@section('header_title', 'Companies')

@section('content')
<x-crm.page-header title="Companies" subtitle="All companies in the CRM">
    <x-slot:actions>
        <button type="button" onclick="crmModal.open('company')" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            New Company
        </button>
    </x-slot:actions>
</x-crm.page-header>

@include('crm.partials.create-modals')

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">{{ session('success') }}</div>
@endif

<x-crm.panel>
    @if($companies->count())
    <div class="overflow-x-auto">
        <table class="min-w-full text-left">
            <thead>
                <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">City</th>
                    <th class="px-6 py-4">Contacts</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                @foreach($companies as $company)
                <tr class="hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('crm.companies.show', $company) }}" class="text-sm font-semibold text-slate-200 hover:text-[#6366F1] transition-colors">{{ $company->name }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">{{ $company->city ?: '—' }}</td>
                    <td class="px-6 py-4"><x-crm.badge color="info" :label="$company->contacts_count . ' contacts'" /></td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('crm.companies.show', $company) }}" class="text-xs font-semibold text-slate-400 hover:text-[#6366F1] transition-colors">View</a>
                            <a href="{{ route('crm.companies.edit', $company) }}" class="text-xs font-semibold text-slate-400 hover:text-[#6366F1] transition-colors">Edit</a>
                            <form method="POST" action="{{ route('crm.companies.destroy', $company) }}" onsubmit="return confirm('Delete this company?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-semibold text-rose-400 hover:text-rose-300 transition-colors">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <x-crm.empty message="No companies yet.">
        <x-slot:action>
            <a href="{{ route('crm.companies.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">New Company</a>
        </x-slot:action>
    </x-crm.empty>
    @endif
</x-crm.panel>

@if($companies->hasPages())
<div class="mt-4">{{ $companies->links() }}</div>
@endif
@endsection
