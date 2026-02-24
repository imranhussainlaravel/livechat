<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Conversation;
use App\Services\CheckConversationSla;

class MonitorSla extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'livechat:monitor-sla';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitors active and pending conversations for SLA breaches.';

    protected CheckConversationSla $slaChecker;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CheckConversationSla $slaChecker)
    {
        parent::__construct();
        $this->slaChecker = $slaChecker;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Starting SLA monitoring...');

        // Get conversations that are PENDING or ACTIVE and not yet BREACHED SLA
        $conversations = Conversation::whereIn('state', ['PENDING', 'ACTIVE'])
            ->where('sla_state', '!=', 'BREACHED')
            ->get();

        $count = 0;
        foreach ($conversations as $conversation) {
            $this->slaChecker->execute($conversation);
            $count++;
        }

        $this->info("Processed {$count} conversations for SLA.");

        return Command::SUCCESS;
    }
}
