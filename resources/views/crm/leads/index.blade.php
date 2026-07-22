@extends('layouts.app')
@section('header_title', 'Leads')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-crm.page-header title="Leads" subtitle="Track and follow up on your sales leads">
        <x-slot:actions>
            {{-- Board / Table toggle --}}
            <div class="inline-flex rounded-xl border border-slate-700 overflow-hidden">
                <a href="{{ route('crm.leads.index') }}" class="px-3 py-2 text-xs font-semibold text-slate-400 hover:bg-slate-800">Board</a>
                <a href="{{ route('crm.leads.index', ['view' => 'table']) }}" class="px-3 py-2 text-xs font-semibold bg-[#6366F1]/10 text-[#6366F1] border-l border-slate-700">Table</a>
            </div>
            <a href="{{ route('crm.leads.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                New Lead
            </a>
        </x-slot:actions>
    </x-crm.page-header>

    {{-- Status filter pills --}}
    <div class="mb-5 flex flex-wrap items-center gap-2">
        <a href="{{ route('crm.leads.index', ['view' => 'table']) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-all {{ ! $activeStatus ? 'bg-[#6366F1]/10 text-[#6366F1] border-[#6366F1]/30' : 'text-slate-400 border-slate-700 hover:bg-slate-800' }}">
            All
        </a>
        @foreach($statuses as $status)
        <a href="{{ route('crm.leads.index', ['view' => 'table', 'status' => $status->value]) }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold border transition-all {{ $activeStatus === $status->value ? 'bg-[#6366F1]/10 text-[#6366F1] border-[#6366F1]/30' : 'text-slate-400 border-slate-700 hover:bg-slate-800' }}">
            {{ $status->getLabel() }}
        </a>
        @endforeach
    </div>

    <x-crm.panel>
        @if($leads->count())
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Contact</th>
                        <th class="px-6 py-4">Source</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Agent</th>
                        <th class="px-6 py-4">Follow-up</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/50">
                    @foreach($leads as $lead)
                    <tr class="hover:bg-slate-700/20 transition-colors">
                        <td class="px-6 py-4">
                            <a href="{{ route('crm.leads.show', $lead) }}" class="font-semibold text-slate-200 hover:text-white">
                                {{ $lead->contact?->name ?? '—' }}
                            </a>
                            @if($lead->contact?->company)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $lead->contact->company->name }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <x-crm.badge color="gray" :label="$lead->source?->getLabel() ?? '—'" />
                        </td>
                        <td class="px-6 py-4">
                            @if($lead->status)
                            <x-crm.badge :color="$lead->status->getColor()" :label="$lead->status->getLabel()" />
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-300">{{ $lead->assignedAgent?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm {{ $lead->isOverdue() ? 'text-rose-400 font-semibold' : 'text-slate-300' }}">
                            {{ $lead->follow_up_date ? $lead->follow_up_date->format('M d, Y') : '—' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('crm.leads.show', $lead) }}" class="text-slate-400 hover:text-[#6366F1]" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </a>
                                <a href="{{ route('crm.leads.edit', $lead) }}" class="text-slate-400 hover:text-[#6366F1]" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <x-crm.empty message="No leads found.">
            <x-slot:action>
                <a href="{{ route('crm.leads.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
                    New Lead
                </a>
            </x-slot:action>
        </x-crm.empty>
        @endif
    </x-crm.panel>

    @if($leads->hasPages())
    <div class="mt-4">{{ $leads->links() }}</div>
    @endif
</div>
@endsection
