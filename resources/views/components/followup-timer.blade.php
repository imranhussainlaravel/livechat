<div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-4" x-data="followupTimer('{{ $scheduledAt }}')">
    <div class="flex items-center justify-between mb-2">
        <h4 class="text-sm font-semibold text-gray-900 flex items-center">
            <svg class="w-4 h-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Scheduled Follow-up
        </h4>
        <span class="text-xs font-medium px-2 py-1 rounded-full"
            :class="isOverdue ? 'bg-red-100 text-red-800' : 'bg-indigo-100 text-indigo-800'"
            x-text="isOverdue ? 'Overdue' : 'Pending'">
        </span>
    </div>

    <p class="text-sm text-gray-600 mb-3" x-text="timeRemaining"></p>

    <div class="flex gap-2">
        <form method="POST" action="{{ route('agent.followups.complete', $followupId) }}" class="flex-1">
            @csrf
            @method('PATCH')
            <button type="submit" class="w-full justify-center inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-green-600 hover:bg-green-700">
                Mark Done
            </button>
        </form>
        <form method="POST" action="{{ route('agent.followups.cancel', $followupId) }}" class="flex-1">
            @csrf
            @method('PATCH')
            <button type="submit" class="w-full justify-center inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded shadow-sm text-gray-700 bg-white hover:bg-gray-50">
                Cancel
            </button>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('followupTimer', (targetDateStr) => ({
            targetDate: new Date(targetDateStr).getTime(),
            timeRemaining: 'Calculating...',
            isOverdue: false,
            interval: null,

            init() {
                this.updateTimer();
                this.interval = setInterval(() => this.updateTimer(), 60000); // UI updates every minute
            },

            updateTimer() {
                const now = new Date().getTime();
                const distance = this.targetDate - now;

                if (distance < 0) {
                    this.isOverdue = true;
                    this.timeRemaining = 'This follow-up is overdue!';
                    clearInterval(this.interval);
                    return;
                }

                this.isOverdue = false;
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));

                if (days > 0) {
                    this.timeRemaining = `${days}d ${hours}h remaining`;
                } else if (hours > 0) {
                    this.timeRemaining = `${hours}h ${minutes}m remaining`;
                } else {
                    this.timeRemaining = `${minutes}m remaining`;
                }
            }
        }));
    });
</script>
@endpush