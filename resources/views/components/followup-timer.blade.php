<div class="bg-slate-800/40 border border-slate-700/50 rounded-2xl p-5 mb-5 shadow-xl" x-data="followupTimer('{{ $scheduledAt }}')">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-xs font-bold text-slate-300 uppercase tracking-widest flex items-center gap-2">
            <svg class="w-4 h-4 text-[#6366F1]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Scheduled Follow-up
        </h4>
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border uppercase tracking-widest"
            :class="isOverdue ? 'bg-rose-500/10 text-rose-400 border-rose-500/20' : 'bg-[#6366F1]/10 text-[#6366F1] border-[#6366F1]/20'"
            x-text="isOverdue ? 'Overdue' : 'Pending'">
        </span>
    </div>

    <p class="text-sm font-medium text-slate-100 mb-4" x-text="timeRemaining"></p>

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
            <button type="submit" class="w-full justify-center inline-flex items-center px-3 py-1.5 border border-gray-600 text-xs font-medium rounded shadow-sm text-gray-300 bg-gray-900 hover:bg-gray-800">
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