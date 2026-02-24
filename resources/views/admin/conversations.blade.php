@extends('layouts.app')

@section('title', 'Admin - All Conversations')

@section('content')
<div class="bg-white shadow rounded-lg p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">All Conversations</h2>
        <a href="/admin/dashboard" class="text-gray-500 hover:text-gray-700 font-medium">&larr; Back to Dashboard</a>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Queue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">State</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Agent ID</th>
                </tr>
            </thead>
            <tbody id="conversations-table" class="bg-white divide-y divide-gray-200">
                <tr>
                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Loading conversations...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        axios.get('/api/admin/conversations')
            .then(response => {
                const data = response.data.data; // paginate
                const tbody = document.getElementById('conversations-table');

                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No conversations found.</td></tr>';
                    return;
                }

                tbody.innerHTML = data.map(conv => `
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">#${conv.id}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${conv.visitor_session ? conv.visitor_session.visitor_name : 'Guest'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${conv.queue}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                ${conv.state}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${conv.assigned_agent_id || 'Unassigned'}</td>
                    </tr>
                `).join('');
            })
            .catch(error => {
                console.error("Failed to load conversations", error);
                document.getElementById('conversations-table').innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-red-500">Error loading data.</td></tr>';
            });
    });
</script>
@endpush