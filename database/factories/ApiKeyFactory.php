<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<ApiKey>
 */
class ApiKeyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'server_id' => Server::factory(),
            'name' => fake()->unique()->word(),
            'identifier' => fake()->unique()->lexify('????????????????'),
            'key_hash' => Hash::make('secret'),
        ];
    }
}
