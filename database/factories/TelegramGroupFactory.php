<?php

namespace Database\Factories;

use App\Enums\GroupRole;
use App\Models\TelegramGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TelegramGroup>
 */
class TelegramGroupFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->words(2, true),
            'role' => GroupRole::NONE,
            'chat_id' => fake()->unique()->numberBetween(-9_999_999_999, -1_000_000),
            'is_active' => true,
        ];
    }

    public function receiver(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => GroupRole::RECEIVER,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
