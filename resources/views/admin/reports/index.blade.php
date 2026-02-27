@extends('layouts.app')
@section('header_title', 'Reports & Insights')

@section('content')

<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">System Insights</h1>
        <p class="text-gray-500 mt-1">Aggregated data on chat performance and agent activity.</p>
    </div>
</div>

@if(is_array($data) && count($data) > 0)
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    @foreach($data as $key => $value)
    @if(is_array($value))
    <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden col-span-1 md:col-span-2 lg:col-span-1">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
            <h4 class="text-sm font-semibold text-gray-900 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                {{ ucfirst(str_replace('_', ' ', $key)) }}
            </h4>
        </div>
        <div class="p-6">
            <dl class="space-y-4">
                @foreach($value as $subKey => $subVal)
                <div class="flex justify-between items-center pb-4 border-b border-gray-50 last:border-0 last:pb-0">
                    <dt class="text-sm font-medium text-gray-500">{{ ucfirst(str_replace('_', ' ', $subKey)) }}</dt>
                    <dd class="text-sm font-bold text-gray-900">{{ is_numeric($subVal) ? number_format($subVal, 2) : $subVal }}</dd>
                </div>
                @endforeach
            </dl>
        </div>
    </div>
    @else
    <x-stat-card
        :title="ucfirst(str_replace('_', ' ', $key))"
        :value="is_numeric($value) ? number_format($value, 2) : $value"
        icon="chart-pie"
        color="blue" />
    @endif
    @endforeach
</div>
@else
<div class="bg-white border border-gray-100 rounded-lg shadow-sm p-12 text-center">
    <svg class="mx-auto h-12 w-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
    </svg>
    <h3 class="mt-2 text-sm font-semibold text-gray-900">No Data Available</h3>
    <p class="mt-1 text-sm text-gray-500">Report data is either empty or currently being aggregated. Check back later.</p>
</div>
@endif

@endsection