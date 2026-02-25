<?php

namespace App\Jobs;

use App\Models\Followup;
use App\Enums\FollowupStatus;
use App\Mail\FollowupReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Carbon;

class SendFollowupReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $now = Carbon::now();
        // Look for pending follow-ups scheduled between now and 15 mins from now
        $upperBound = $now->copy()->addMinutes(15);

        $followups = Followup::with(['chat.visitor', 'agent'])
            ->where('status', FollowupStatus::PENDING->value)
            ->whereBetween('followup_time', [$now, $upperBound])
            // Ensure we don't send multiple reminders if the job runs multiple times
            ->whereNull('reminder_sent_at')
            ->get();

        foreach ($followups as $followup) {
            if ($followup->agent) {
                Mail::to($followup->agent->email)->queue(new FollowupReminderMail($followup));

                // Mark reminder as sent
                $followup->update(['reminder_sent_at' => \Carbon\Carbon::now()]);
            }
        }
    }
}
