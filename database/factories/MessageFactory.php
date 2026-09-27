<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Server;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['info', 'warning', 'error']),
            'text' => fake()->sentence(),
            'server_id' => Server::factory(),
        ];
    }
}
