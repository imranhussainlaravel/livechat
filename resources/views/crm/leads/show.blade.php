@extends('layouts.app')
@section('header_title', 'Lead')

@php
    $inputClass = 'w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all';
@endphp

@section('content')
<div class="max-w-5xl mx-auto">
    <x-crm.page-header :title="$lead->contact?->name ?? 'Lead'" :subtitle="$lead->contact?->company?->name">
        <x-slot:actions>
            <a href="{{ route('crm.leads.edit', $lead) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">
                Edit
            </a>
            <form method="POST" action="{{ route('crm.leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead? This cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 border border-rose-500/30 hover:bg-rose-500/10 transition-all">
                    Delete
                </button>
            </form>
        </x-slot:actions>
    </x-crm.page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: detail + timeline --}}
        <div class="lg:col-span-2 space-y-6">
            <x-crm.panel title="Details">
                <dl class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5 text-sm">
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Contact</dt>
                        <dd class="text-slate-200">{{ $lead->contact?->name ?? '—' }}</dd>
                        @if($lead->contact?->phone)<dd class="text-xs text-slate-500 mt-0.5">{{ $lead->contact->phone }}</dd>@endif
                        @if($lead->contact?->email)<dd class="text-xs text-slate-500 mt-0.5">{{ $lead->contact->email }}</dd>@endif
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Company</dt>
                        <dd class="text-slate-200">{{ $lead->contact?->company?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Status</dt>
                        <dd>@if($lead->status)<x-crm.badge :color="$lead->status->getColor()" :label="$lead->status->getLabel()" />@else — @endif</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Source</dt>
                        <dd class="text-slate-200">{{ $lead->source?->getLabel() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Product interest</dt>
                        <dd class="text-slate-200">{{ $lead->product_interest?->getLabel() ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Assigned agent</dt>
                        <dd class="text-slate-200">{{ $lead->assignedAgent?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Follow-up date</dt>
                        <dd class="{{ $lead->isOverdue() ? 'text-rose-400 font-semibold' : 'text-slate-200' }}">
                            {{ $lead->follow_up_date ? $lead->follow_up_date->format('M d, Y') : '—' }}
                            @if($lead->isOverdue())<span class="text-[11px]">(overdue)</span>@endif
                        </dd>
                    </div>
                    @if($lead->lost_reason)
                    <div>
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Lost reason</dt>
                        <dd class="text-slate-200">{{ $lead->lost_reason->getLabel() }}</dd>
                    </div>
                    @endif
                    @if($lead->follow_up_note)
                    <div class="sm:col-span-2">
                        <dt class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Follow-up note</dt>
                        <dd class="text-slate-300">{{ $lead->follow_up_note }}</dd>
                    </div>
                    @endif
                </dl>
            </x-crm.panel>

            {{-- Timeline --}}
            <x-crm.panel title="Activity">
                <div class="p-6">
                    @if($lead->activities->count())
                    <ol class="space-y-5">
                        @foreach($lead->activities as $activity)
                        <li class="flex gap-3">
                            <div class="mt-1 w-2 h-2 rounded-full bg-[#6366F1] shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-sm font-semibold text-slate-200">{{ $activity->type?->getLabel() ?? 'Activity' }}</span>
                                    <span class="text-xs text-slate-500">{{ $activity->user?->name ?? 'System' }}</span>
                                    <span class="text-xs text-slate-600">{{ $activity->created_at?->format('M d, Y g:i A') }}</span>
                                </div>
                                @if($activity->note)
                                <p class="text-sm text-slate-400 mt-1 whitespace-pre-line">{{ $activity->note }}</p>
                                @endif
                            </div>
                        </li>
                        @endforeach
                    </ol>
                    @else
                    <p class="text-sm text-slate-500">No activity yet.</p>
                    @endif

                    {{-- Log note --}}
                    <form method="POST" action="{{ route('crm.leads.activity', $lead) }}" class="mt-6 pt-6 border-t border-slate-700/50 space-y-4">
                        @csrf
                        <x-crm.field label="Log a note" name="note" :required="true">
                            <textarea name="note" id="note" rows="2" class="{{ $inputClass }}" placeholder="What happened?">{{ old('note') }}</textarea>
                        </x-crm.field>
                        <div class="flex items-end gap-3">
                            <x-crm.field label="Type" name="type" :required="true" class="flex-1">
                                <select name="type" id="type" class="{{ $inputClass }}">
                                    @foreach($activityTypes as $type)
                                    <option value="{{ $type->value }}" @selected(old('type', 'note') === $type->value)>{{ $type->getLabel() }}</option>
                                    @endforeach
                                </select>
                            </x-crm.field>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
                                Log
                            </button>
                        </div>
                    </form>
                </div>
            </x-crm.panel>
        </div>

        {{-- Right: deal + mark lost --}}
        <div class="space-y-6">
            <x-crm.panel title="Deal">
                <div class="p-6">
                    @if($lead->deal)
                    <p class="text-sm text-slate-400 mb-3">This lead is linked to a deal.</p>
                    <a href="{{ route('crm.deals.show', $lead->deal) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">
                        View Deal
                    </a>
                    @else
                    <p class="text-sm text-slate-400 mb-3">No deal yet. Convert this lead when it's ready to move forward.</p>
                    <form method="POST" action="{{ route('crm.leads.convert', $lead) }}">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">
                            Convert to Deal
                        </button>
                    </form>
                    @endif
                </div>
            </x-crm.panel>

            @if($lead->status !== \App\Enums\LeadStatus::Lost)
            <x-crm.panel title="Mark Lost">
                <form method="POST" action="{{ route('crm.leads.markLost', $lead) }}" class="p-6 space-y-4"
                      onsubmit="return confirm('Mark this lead as lost?')">
                    @csrf
                    <x-crm.field label="Lost reason" name="lost_reason" :required="true">
                        <select name="lost_reason" id="lost_reason" class="{{ $inputClass }}">
                            @foreach($lostReasons as $reason)
                            <option value="{{ $reason->value }}" @selected(old('lost_reason') === $reason->value)>{{ $reason->getLabel() }}</option>
                            @endforeach
                        </select>
                    </x-crm.field>
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-rose-400 border border-rose-500/30 hover:bg-rose-500/10 transition-all">
                        Mark Lost
                    </button>
                </form>
            </x-crm.panel>
            @endif
        </div>
    </div>
</div>
@endsection
