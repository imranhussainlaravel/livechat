@extends('layouts.app')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Support Tickets</h1>
        <p class="text-gray-500 mt-1">Manage and track follow-ups and inquiries from your conversations.</p>
    </div>
</div>

<div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visitor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quotation</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tickets as $ticket)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-700">
                                {{ strtoupper(substr($ticket->chat->visitor->name ?? 'V', 0, 1)) }}
                            </div>
                            <div class="ml-3">
                                <div class="text-sm font-medium text-gray-900">{{ $ticket->chat->visitor->name ?? 'Unknown' }}</div>
                                <div class="text-xs text-gray-500">Chat #{{ $ticket->chat_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php
                            $statusColors = [
                                'pending' => 'bg-gray-100 text-gray-800',
                                'interested' => 'bg-emerald-100 text-emerald-800',
                                'not_interested' => 'bg-rose-100 text-rose-800',
                            ];
                            $status = $ticket->status->value ?? $ticket->status;
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusColors[$status] ?? 'bg-gray-100' }}">
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($ticket->quotation_sent)
                        <div class="text-sm text-gray-900 font-medium">${{ number_format($ticket->amount, 2) }}</div>
                        <div class="text-[10px] text-emerald-600 font-semibold uppercase">Sent</div>
                        @else
                        <span class="text-xs text-gray-400 italic">No quotation</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $ticket->created_at->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('agent.chats.show', $ticket->chat_id) }}" class="text-blue-600 hover:text-blue-900">View Chat</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500 whitespace-nowrap">
                        No tickets found.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($tickets->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        {{ $tickets->links() }}
    </div>
    @endif
</div>
@endsection
