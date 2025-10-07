<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\EventDetails;
use Carbon\Carbon;

class UpdateEventsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:update-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updating events status based on date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        EventDetails::where('date', '>=', $now)->update(['status' => 'aktuálny']);

        EventDetails::where('date', '<', $now)->update(['status' => 'ukončený']);

        $this->info('Events status has been successfully updated');
    }
}
