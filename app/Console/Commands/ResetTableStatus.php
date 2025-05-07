<?php

namespace App\Console\Commands;

use App\Models\Table;
use Illuminate\Console\Command;

class ResetTableStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tables:reset-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all tables status to available';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = Table::count();
        $this->info("Found {$count} tables in the database.");
        
        $this->info("Resetting all tables to 'available' status...");
        
        try {
            $updated = Table::query()->update(['status' => 'available']);
            $this->info("Successfully updated {$updated} tables to 'available' status.");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Error updating tables: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
