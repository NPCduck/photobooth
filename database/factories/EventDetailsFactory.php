<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventDetails>
 */
class EventDetailsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\EventDetails::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->words($nb = 1, $asText = true),
            'status' => 'aktuálny',
            'date' => $this->faker->dateTimeBetween($startDate = 'now', $endDate = '+1 years', $timezone = null),
            'time_start' => $this->faker->time('H:i:s'),
            'time_end' => $this->faker->time('H:i:s'),
            'loc_venue' => $this->faker->streetAddress(),
            'loc_address' => $this->faker->address()
        ];
    }
}