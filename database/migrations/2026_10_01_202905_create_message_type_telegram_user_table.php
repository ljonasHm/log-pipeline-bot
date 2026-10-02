<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('message_type_telegram_user', function (Blueprint $table) {
            $table->foreignId('message_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('telegram_user_id')->constrained()->cascadeOnDelete();
            $table->primary(['message_type_id', 'telegram_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_type_telegram_user');
    }
};
