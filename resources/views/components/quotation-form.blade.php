<div x-data="{ open: false }" class="mt-4 border-t border-gray-100 pt-4">
    <button @click="open = !open" class="text-sm font-medium text-blue-600 hover:text-blue-700 flex items-center">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Send Quotation
    </button>

    <div x-show="open" class="mt-3 bg-gray-50 p-3 rounded-md" x-collapse>
        <form method="POST" action="{{ route('agent.tickets.sendQuotation', $ticketId) }}">
            @csrf
            <div class="mb-3">
                <label for="amount" class="block text-xs font-medium text-gray-700">Amount ($)</label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" name="amount" id="amount" step="0.01" min="0" required
                        class="focus:ring-blue-500 focus:border-blue-500 block w-full pl-7 sm:text-sm border-gray-300 rounded-md py-1.5"
                        placeholder="0.00">
                </div>
            </div>
            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
                Generate & Send
            </button>
        </form>
    </div>
</div>