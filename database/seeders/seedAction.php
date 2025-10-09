<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Event;
use App\Models\Action;

class seedAction extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::all();

        foreach ($events as $event) {
            Action::factory(4)->create([
                'event_id' => $event->id,
            ]);
        }
        $this->command->info('Actions úspešne vygenerované!');
    }
}
