<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Src\Agent\Heartbeat\UseCases\CheckOfflineAgents;

class CheckAgentHeartbeat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'livechat:check-heartbeat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for agents who lost heartbeat connection and marks them AWAY.';

    protected CheckOfflineAgents $checker;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CheckOfflineAgents $checker)
    {
        parent::__construct();
        $this->checker = $checker;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Checking agent heartbeats...');

        $updatedCount = $this->checker->execute();

        $this->info("Heartbeat check complete. {$updatedCount} agents marked as AWAY.");

        return Command::SUCCESS;
    }
}
