<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\TelegramUserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelegramUser extends Model
{
    /** @use HasFactory<TelegramUserFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'role',
        'chat_id',
        'telegram_id',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
        ];
    }

    public static function findByTelegramId(int $telegramId): ?self
    {
        return static::where('telegram_id', $telegramId)->first();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }
}
