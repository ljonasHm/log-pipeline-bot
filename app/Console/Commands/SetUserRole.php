<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use App\Models\TelegramUser;
use Illuminate\Console\Command;

class SetUserRole extends Command
{
    protected $signature = 'user:role
        {role : New role}
        {--id= : Telegram user database ID}
        {--telegram-id= : Telegram user ID}
        {--chat-id= : Telegram chat ID}';

    protected $description = 'Change Telegram user role';

    public function handle()
    {
        $role = UserRole::tryFrom($this->argument('role'));

        if ($role === null) {
            $this->error('Invalid role.');

            $this->line(
                'Available roles: '.
                implode(', ', array_column(UserRole::cases(), 'value'))
            );

            return self::FAILURE;
        }

        $identifiers = [
            'id' => $this->option('id'),
            'telegram_id' => $this->option('telegram-id'),
            'chat_id' => $this->option('chat-id'),
        ];

        $identifiers = array_filter(
            $identifiers,
            fn ($value) => $value !== null
        );

        if (count($identifiers) !== 1) {
            $this->error(
                'Specify exactly one of: --id, --telegram-id, --chat-id.'
            );

            return self::FAILURE;
        }

        $column = array_key_first($identifiers);
        $value = $identifiers[$column];

        $user = TelegramUser::where($column, $value)->first();

        if ($user === null) {
            $this->error('User not found.');

            return self::FAILURE;
        }

        if ($user->isPermanentAdmin()) {
            $this->error('This user\'s role cannot be changed.');

            return self::FAILURE;
        }

        $user->role = $role;
        $user->save();

        $this->info(
            "User #{$user->id} role changed to {$role->value}."
        );

        return self::SUCCESS;
    }
}
