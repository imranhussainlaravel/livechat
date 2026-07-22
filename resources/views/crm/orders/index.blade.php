@extends('layouts.app')
@section('header_title', 'Orders')

@section('content')
<x-crm.page-header title="Orders" subtitle="Production orders created from won deals" />

@if(session('success'))
<div class="mb-4 px-4 py-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm font-medium">{{ session('success') }}</div>
@endif

<div class="mb-4 flex flex-wrap items-center gap-2">
    <a href="{{ route('crm.orders.index') }}"
       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all {{ empty($activeStatus) ? 'text-white bg-[#6366F1] border-[#6366F1]' : 'text-slate-300 border-slate-700 hover:bg-slate-800' }}">
        All
    </a>
    @foreach($statuses as $status)
    <a href="{{ route('crm.orders.index', ['status' => $status->value]) }}"
       class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-xs font-semibold border transition-all {{ $activeStatus === $status->value ? 'text-white bg-[#6366F1] border-[#6366F1]' : 'text-slate-300 border-slate-700 hover:bg-slate-800' }}">
        {{ $status->getLabel() }}
    </a>
    @endforeach
</div>

<x-crm.panel>
    @if($orders->count())
    <div class="overflow-x-auto">
        <table class="min-w-full text-left">
            <thead>
                <tr class="bg-slate-800/30 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <th class="px-6 py-4">Order</th>
                    <th class="px-6 py-4">Company / Contact</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Deadline</th>
                    <th class="px-6 py-4">Dispatched</th>
                    <th class="px-6 py-4">Delivered</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-700/50">
                @foreach($orders as $order)
                @php
                    $company = $order->deal?->lead?->contact?->company;
                    $contact = $order->deal?->lead?->contact;
                @endphp
                <tr class="hover:bg-slate-700/20 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('crm.orders.show', $order) }}" class="text-sm font-semibold text-slate-200 hover:text-[#6366F1] transition-colors">#{{ $order->id }}</a>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-slate-200">{{ $company?->name ?? '—' }}</div>
                        <div class="text-xs text-slate-500">{{ $contact?->name ?? '—' }}</div>
                    </td>
                    <td class="px-6 py-4">
                        <x-crm.badge :color="$order->status->getColor()" :label="$order->status->getLabel()" />
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">{{ $order->deadline ? $order->deadline->format('M d, Y') : '—' }}</td>
                    <td class="px-6 py-4">
                        @if($order->dispatch)
                        <x-crm.badge color="success" label="Yes" />
                        @else
                        <x-crm.badge color="gray" label="No" />
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-slate-400">{{ $order->delivered_at ? $order->delivered_at->format('M d, Y') : '—' }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('crm.orders.show', $order) }}" class="text-xs font-semibold text-slate-400 hover:text-[#6366F1] transition-colors">View</a>
                            <a href="{{ route('crm.orders.edit', $order) }}" class="text-xs font-semibold text-slate-400 hover:text-[#6366F1] transition-colors">Edit</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <x-crm.empty message="No orders yet. Orders appear here automatically when a deal is won." />
    @endif
</x-crm.panel>

@if($orders->hasPages())
<div class="mt-4">{{ $orders->links() }}</div>
@endif
@endsection
