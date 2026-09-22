<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\UserRole;

class TelegramUser extends Model
{
    protected $fillable = [
        'name',
        'chat_id',
        'telegram_id'
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class
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