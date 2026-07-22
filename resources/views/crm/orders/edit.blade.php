@extends('layouts.app')
@section('header_title', 'Edit Order #' . $order->id)

@section('content')
@php
    $company = $order->deal?->lead?->contact?->company;
    $dispatch = $order->dispatch;
    $inputClass = 'w-full bg-slate-900/50 border border-slate-700 rounded-xl px-3 py-2 text-sm text-slate-200 placeholder-slate-500 focus:ring-2 focus:ring-[#6366F1]/20 focus:border-[#6366F1] outline-none transition-all';
@endphp

<x-crm.page-header title="Edit Order #{{ $order->id }}" subtitle="{{ $company?->name ?? 'Update order and dispatch details' }}">
    <x-slot:actions>
        <a href="{{ route('crm.orders.show', $order) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</a>
    </x-slot:actions>
</x-crm.page-header>

<form method="POST" action="{{ route('crm.orders.update', $order) }}" class="space-y-6">
    @csrf
    @method('PUT')

    <x-crm.panel title="Order">
        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-crm.field label="Status" name="status" :required="true">
                <select id="status" name="status" class="{{ $inputClass }}">
                    @foreach($statuses as $status)
                    <option value="{{ $status->value }}" @selected(old('status', $order->status->value) === $status->value)>{{ $status->getLabel() }}</option>
                    @endforeach
                </select>
            </x-crm.field>

            <x-crm.field label="Deadline" name="deadline">
                <input type="date" id="deadline" name="deadline" value="{{ old('deadline', $order->deadline?->format('Y-m-d')) }}" class="{{ $inputClass }}">
            </x-crm.field>

            <x-crm.field label="Delivered At" name="delivered_at">
                <input type="datetime-local" id="delivered_at" name="delivered_at" value="{{ old('delivered_at', $order->delivered_at?->format('Y-m-d\TH:i')) }}" class="{{ $inputClass }}">
            </x-crm.field>

            <x-crm.field label="Special Instructions" name="special_instructions" class="md:col-span-2">
                <textarea id="special_instructions" name="special_instructions" rows="3" class="{{ $inputClass }}">{{ old('special_instructions', $order->special_instructions) }}</textarea>
            </x-crm.field>
        </div>
    </x-crm.panel>

    <x-crm.panel title="Dispatch">
        <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-5">
            <p class="md:col-span-2 text-xs text-slate-500">Leave all dispatch fields blank if the order has not been dispatched yet.</p>

            <x-crm.field label="Vehicle Info" name="vehicle_info">
                <input type="text" id="vehicle_info" name="vehicle_info" value="{{ old('vehicle_info', $dispatch?->vehicle_info) }}" class="{{ $inputClass }}" placeholder="e.g. Toyota Hiace — ABC-123">
            </x-crm.field>

            <x-crm.field label="Dispatch Date" name="dispatch_date">
                <input type="date" id="dispatch_date" name="dispatch_date" value="{{ old('dispatch_date', $dispatch?->dispatch_date?->format('Y-m-d')) }}" class="{{ $inputClass }}">
            </x-crm.field>

            <x-crm.field label="Invoice No" name="invoice_no">
                <input type="text" id="invoice_no" name="invoice_no" value="{{ old('invoice_no', $dispatch?->invoice_no) }}" class="{{ $inputClass }}">
            </x-crm.field>

            <x-crm.field label="Delivery Address" name="delivery_address" class="md:col-span-2">
                <textarea id="delivery_address" name="delivery_address" rows="3" class="{{ $inputClass }}">{{ old('delivery_address', $dispatch?->delivery_address) }}</textarea>
            </x-crm.field>
        </div>
    </x-crm.panel>

    <div class="flex items-center justify-end gap-2">
        <a href="{{ route('crm.orders.show', $order) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 border border-slate-700 hover:bg-slate-800 transition-all">Cancel</a>
        <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#6366F1] hover:bg-[#4F46E5] transition-all shadow-lg shadow-[#6366F1]/20">Save Changes</button>
    </div>
</form>
@endsection
