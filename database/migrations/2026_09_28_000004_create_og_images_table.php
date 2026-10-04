<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('og_images', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('roblox_user_id')->unique();
            $table->string('path');
            $table->timestamp('generated_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('og_images');
    }
};