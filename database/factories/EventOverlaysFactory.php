<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EventOverlays>
 */
class EventOverlaysFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    protected $model = \App\Models\EventOverlays::class;

    public function definition(): array
    {
        return [
            'landing_img' => 'SomeImg.png',
            'frame_img' => 'SomeImg.png'
        ];
    }
}
