<?php

namespace App\Models;

use Database\Factories\MessageTypeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MessageType extends Model
{
    /** @use HasFactory<MessageTypeFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function telegramUsers(): BelongsToMany
    {
        return $this->belongsToMany(TelegramUser::class)
            ->withPivot('source');
    }

    public function scopeWithName(Builder $query, string $name): void
    {
        $query->whereRaw('lower(name) = ?', [strtolower(trim($name))]);
    }
}
