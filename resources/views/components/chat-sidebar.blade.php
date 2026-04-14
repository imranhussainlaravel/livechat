<div class="space-y-3 overflow-y-auto pr-2 pb-6">

    {{-- 1. Visitor Details --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3.5">
        <h4 class="text-xs font-semibold text-gray-900 mb-2.5 border-b border-gray-100 pb-1.5 flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Visitor Details
        </h4>
        <dl class="space-y-3">
            <div>
                <dt class="text-xs font-medium text-gray-500">Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $chat->visitor->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $chat->visitor->email ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-gray-500">Chat Started</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $chat->started_at?->format('M j, Y, g:i A') ?? '—' }}</dd>
            </div>
        </dl>
    </div>

    {{-- 2. Status Actions --}}
    @if(! in_array($chat->status->value, ['closed']))
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3.5">
        <h4 class="text-xs font-semibold text-gray-900 mb-2.5 border-b border-gray-100 pb-1.5 flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Chat Status
        </h4>
        <form method="POST" action="{{ route('agent.chats.updateStatus', $chat->id) }}">
            @csrf
            @method('PATCH')
            <div class="flex flex-col gap-3">
                <div class="flex gap-2">
                    <select name="status" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                        @foreach(['pending', 'assigned', 'active', 'closed', 'transferred'] as $s)
                        <option value="{{ $s }}" {{ $chat->status->value === $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}
                        </option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Update
                    </button>
                </div>

                {{-- Only Show Priority inside Status area for brevity --}}
                <div class="flex gap-2">
                    <select name="priority" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                        <option value="low" {{ $chat->priority === 'low' ? 'selected' : '' }}>Low Priority</option>
                        <option value="normal" {{ $chat->priority === 'normal' ? 'selected' : '' }}>Normal Priority</option>
                        <option value="high" {{ $chat->priority === 'high' ? 'selected' : '' }}>High Priority</option>
                    </select>
                    <button type="submit" formaction="{{ route('agent.chats.updateStatus', $chat->id) }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                        Set
                    </button>
                </div>
            </div>
        </form>
    </div>
    @endif

    {{-- 3. Followup Planner --}}
    @if(! in_array($chat->status->value, ['closed']))
    <div class="bg-indigo-50 rounded-lg shadow-sm border border-indigo-100 p-3.5">
        <h4 class="text-xs font-semibold text-indigo-900 mb-2.5 border-b border-indigo-200 pb-1.5 flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Schedule Follow-up
        </h4>
        <form method="POST" action="{{ route('agent.followups.store') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="chat_id" value="{{ $chat->id }}">
            <div>
                <input type="datetime-local" name="followup_time" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 text-xs">
            </div>
            <div>
                <input type="text" name="notes" placeholder="Follow-up reason..." class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 text-xs">
            </div>
            <button type="submit" class="w-full justify-center inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700">
                Set Reminder
            </button>
        </form>
    </div>
    @endif

    {{-- 4. Transfer --}}
    @if(! in_array($chat->status->value, ['closed']))
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3.5">
        <h4 class="text-xs font-semibold text-gray-900 mb-2.5 border-b border-gray-100 pb-1.5 flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
            </svg>
            Transfer Chat
        </h4>
        <form method="POST" action="{{ route('agent.chats.transfer', $chat->id) }}" class="space-y-3">
            @csrf
            <div>
                <select name="to_agent_id" required class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
                    <option value="">Select Agent...</option>
                    @foreach($agents as $agent)
                    @if($agent->id !== auth()->id())
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endif
                    @endforeach
                </select>
            </div>
            <div>
                <input type="text" name="reason" placeholder="Reason (optional)" class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 sm:text-sm sm:leading-6">
            </div>
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-600 hover:bg-yellow-700">
                Transfer to Agent
            </button>
        </form>
    </div>
    @endif

    {{-- 5. Visitor Note --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-3.5">
        <h4 class="text-xs font-semibold text-gray-900 mb-2.5 border-b border-gray-100 pb-1.5 flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            Internal Notes
        </h4>
        <form method="POST" action="{{ route('agent.chats.addVisitorNote', $chat->id) }}">
            @csrf
            <textarea name="note" rows="2" placeholder="Add a private note..." class="block w-full rounded-md border-0 py-1 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-blue-600 text-xs mb-2"></textarea>
            <button type="submit" class="w-full rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Save Note
            </button>
        </form>
    </div>

    {{-- 6. Escalate / Quotation --}}
    <div class="bg-green-50 border border-green-100 rounded-lg p-3.5">
        <h4 class="text-xs font-semibold text-green-900 mb-2 flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Sales & Tickets
        </h4>
        @php
        $ticket = $chat->ticket; // Ensure relationship exists
        @endphp

        @if(!$ticket)
        <p class="text-xs text-green-700 mb-3">Convert this chat into a sales/support ticket to send quotes.</p>
        <form method="POST" action="{{ route('agent.tickets.store') }}">
            @csrf
            <input type="hidden" name="chat_id" value="{{ $chat->id }}">
            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                Create Ticket
            </button>
        </form>
        @else
        <div class="mb-3">
            <p class="text-xs font-medium text-green-800">Assigned Ticket #{{ $ticket->id }}</p>
            <form method="POST" action="{{ route('agent.tickets.update', $ticket->id) }}" class="mt-2 flex gap-2">
                @csrf
                @method('PATCH')
                <select name="status" onchange="this.form.submit()" class="block w-full rounded-md border-0 py-1 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-green-600 sm:text-xs">
                    <option value="pending" {{ $ticket->status->value === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="interested" {{ $ticket->status->value === 'interested' ? 'selected' : '' }}>Interested</option>
                    <option value="not_interested" {{ $ticket->status->value === 'not_interested' ? 'selected' : '' }}>Not Interested</option>
                </select>
            </form>
        </div>

        <x-quotation-form :ticketId="$ticket->id" />
        @endif
    </div>

    {{-- 7. Interaction Timeline --}}
    @if(isset($timeline) && $timeline->isNotEmpty())
    <div class="bg-gray-50 rounded-lg shadow-sm border border-gray-100 p-5">
        <h4 class="text-sm font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2 flex items-center gap-2">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Interaction History
        </h4>
        <div class="flow-root">
            <ul role="list" class="-mb-8">
                @foreach($timeline as $index => $activity)
                <li>
                    <div class="relative pb-8">
                        @if(!$loop->last)
                        <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                        @endif
                        <div class="relative flex space-x-3">
                            <div>
                                <span class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center ring-8 ring-white">
                                    <svg class="h-4 w-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </span>
                            </div>
                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                <div>
                                    <p class="text-sm text-gray-500">
                                        {{ $activity->user->name ?? 'System' }} {{ str_replace('_', ' ', $activity->action) }}
                                        @if(isset($activity->metadata['amount']))
                                        (${{ number_format($activity->metadata['amount'], 2) }})
                                        @elseif(isset($activity->metadata['status']))
                                        ({{ ucfirst($activity->metadata['status']) }})
                                        @endif
                                    </p>
                                </div>
                                <div class="whitespace-nowrap text-right text-xs text-gray-500">
                                    <time datetime="{{ $activity->created_at->toIso8601String() }}">{{ $activity->created_at->diffForHumans() }}</time>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

</div>