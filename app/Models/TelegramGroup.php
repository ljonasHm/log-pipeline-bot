<?php

namespace App\Models;

use App\Enums\GroupRole;
use Database\Factories\TelegramGroupFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TelegramGroup extends Model
{
    /** @use HasFactory<TelegramGroupFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'role',
        'chat_id',
        'is_active',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'role' => GroupRole::NONE->value,
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'role' => GroupRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function ignoredMessageTypes(): BelongsToMany
    {
        return $this->belongsToMany(MessageType::class);
    }
}
