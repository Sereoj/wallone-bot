<?php

use DefStudio\Telegraph\Models\TelegraphChat;
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
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('firstName')->comment('firstName');
            $table->string('lastName')->comment('lastName');
            $table->string('telegram_id')->comment('Account ID от тг');
            $table->boolean('status')->default(true)->nullable();
            $table->foreignIdFor(TelegraphChat::class)
                ->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('language_code');
            $table->string('access_token');
            $table->string('target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
