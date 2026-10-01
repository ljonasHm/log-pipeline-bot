<?php

namespace Database\Factories;

use App\Models\MessageType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageType>
 */
class MessageTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->lexify('????????'),
            'title' => fake()->words(2, true),
        ];
    }
}
