<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cached_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roblox_user_id')->unique();
            $table->string('username')->index();
            $table->string('display_name')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('account_created_at')->nullable();
            $table->string('avatar_url')->nullable();
            $table->json('raw_profile')->nullable();
            $table->timestamp('cached_at')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cached_users'); }
};