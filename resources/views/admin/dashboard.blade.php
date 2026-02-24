@extends('layouts.app')

@section('title', 'Admin Dashboard - LiveChat')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Admin Dashboard</h2>
        <a href="/admin/conversations" class="text-indigo-600 hover:text-indigo-800 font-medium">View All Conversations &rarr;</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8" id="analytics-grid">
        <!-- Injected via API -->
        <div class="p-4 border rounded-lg bg-gray-50 text-center text-gray-500">Loading metrics...</div>
        <div class="p-4 border rounded-lg bg-gray-50 text-center text-gray-500">Loading metrics...</div>
        <div class="p-4 border rounded-lg bg-gray-50 text-center text-gray-500">Loading metrics...</div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        axios.get('/api/admin/analytics')
            .then(response => {
                const data = response.data;
                const grid = document.getElementById('analytics-grid');

                grid.innerHTML = `
                    <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                        <h3 class="text-sm font-medium text-blue-500 uppercase tracking-wider">Active Agents</h3>
                        <p class="mt-2 text-3xl font-bold text-blue-700">${data.active_agents}</p>
                    </div>
                    <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                        <h3 class="text-sm font-medium text-red-500 uppercase tracking-wider">SLA Breaches</h3>
                        <p class="mt-2 text-3xl font-bold text-red-700">${data.total_sla_breaches}</p>
                    </div>
                    <div class="p-4 border border-green-200 rounded-lg bg-green-50">
                        <h3 class="text-sm font-medium text-green-500 uppercase tracking-wider">Status Overview</h3>
                        <p class="mt-2 text-sm text-gray-700">
                            ${Object.entries(data.status_counts).map(([k,v]) => `<span class="block">${k}: <b class="text-gray-900">${v}</b></span>`).join('')}
                        </p>
                    </div>
                `;
            })
            .catch(error => {
                console.error("Failed to load analytics", error);
                document.getElementById('analytics-grid').innerHTML = `<div class="col-span-3 text-red-500">Error loading metrics.</div>`;
            });
    });
</script>
@endpush