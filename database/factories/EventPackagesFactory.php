<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventPackages>
 */
class EventPackagesFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\EventPackages::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words($nb = 1, $asText = true),
            'price' => $this->faker->randomFloat(2, 3, 15),
            'photo_limit' => $this->faker->numberBetween($min = 0, $max = 50)
        ];
    }
}
