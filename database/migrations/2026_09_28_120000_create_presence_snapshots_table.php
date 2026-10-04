<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('presence_snapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roblox_user_id');
            $table->string('status_key', 20);
            $table->string('game_name')->nullable();
            $table->unsignedBigInteger('place_id')->nullable();
            $table->unsignedBigInteger('universe_id')->nullable();
            $table->timestamp('observed_at')->index();
            $table->index(['roblox_user_id', 'observed_at']);
            $table->index(['roblox_user_id', 'status_key', 'game_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presence_snapshots');
    }
};
