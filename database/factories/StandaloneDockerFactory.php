<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StandaloneDockerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),
            'name' => fake()->unique()->word(),
            'network' => 'Helix Claude',
            'server_id' => 1,
        ];
    }
}
