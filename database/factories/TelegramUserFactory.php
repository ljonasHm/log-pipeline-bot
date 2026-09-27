<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\TelegramUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TelegramUser>
 */
class TelegramUserFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'role' => UserRole::NONE,
            'chat_id' => fake()->unique()->numberBetween(1_000_000, 9_999_999_999),
            'telegram_id' => fake()->unique()->numberBetween(1_000_000, 9_999_999_999),
        ];
    }

    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::ADMIN,
        ]);
    }

    public function receiver(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::RECEIVER,
        ]);
    }
}
