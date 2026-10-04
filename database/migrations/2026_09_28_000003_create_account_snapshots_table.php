<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('roblox_user_id');
            $table->unsignedInteger('limited_count')->default(0);
            $table->unsignedBigInteger('total_rap')->default(0);
            $table->unsignedInteger('average_rap')->default(0);
            $table->unsignedInteger('wearing_count')->default(0);
            $table->unsignedInteger('bundles_count')->default(0);
            $table->date('snapshot_date');
            $table->timestamps();
            $table->unique(['roblox_user_id', 'snapshot_date']);
            $table->index(['roblox_user_id', 'snapshot_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_snapshots');
    }
};