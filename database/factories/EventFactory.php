<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;
use App\Models\EventPackages;
use App\Models\EventDetails;
use App\Models\EventOverlays;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\Event::class;
    
    public function definition(): array
    {
        return [
            'user_id' => 1,
            'name' => $this->faker->sentence(2),
        ];
    }

    public function configure()
    {
        return $this->afterCreating(function (Event $event) {
            // Vytvor detail
            EventDetails::factory()->create([
                'event_id' => $event->id,
            ]);

            // Vytvor package
            EventPackages::factory()->count(2)->create([
                'event_id' => $event->id,
            ]);

            // Vytvor overlay
            EventOverlays::factory()->count(3)->create([
                'event_id' => $event->id,
            ]);
        });
    }
}
