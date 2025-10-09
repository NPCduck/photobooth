<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Event;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Action>
 */
class ActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\Action::class;

    public function definition(): array
    {
        return [
            'user_id' => 1,
            'event_id' => Event::factory(),
            'action_type' => $this->faker->word(),
            'description' => $this->faker->sentence(),
        ];
    }
}
