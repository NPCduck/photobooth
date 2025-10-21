<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Event;
use App\Models\EventClient;

class SeedEventClient extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = Event::all();

        foreach ($events as $event) {
            EventClient::factory()->create([
                'event_id' => $event->id,
            ]);
        }
        $this->command->info('Klienti úspešne vygenerovaní!');
    }
}
