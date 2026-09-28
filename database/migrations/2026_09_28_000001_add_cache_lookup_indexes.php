<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cached_inventories', function (Blueprint $table): void {
            $table->index(['roblox_user_id', 'cached_at'], 'cached_inventories_user_cached_at_idx');
        });

        Schema::table('cached_item_details', function (Blueprint $table): void {
            $table->index('cached_at', 'cached_item_details_cached_at_idx');
            $table->index('is_limited', 'cached_item_details_is_limited_idx');
        });

        Schema::table('search_histories', function (Blueprint $table): void {
            $table->index('created_at', 'search_histories_created_at_idx');
        });
    }

    public function down(): void
    {
        Schema::table('search_histories', function (Blueprint $table): void {
            $table->dropIndex('search_histories_created_at_idx');
        });

        Schema::table('cached_item_details', function (Blueprint $table): void {
            $table->dropIndex('cached_item_details_is_limited_idx');
            $table->dropIndex('cached_item_details_cached_at_idx');
        });

        Schema::table('cached_inventories', function (Blueprint $table): void {
            $table->dropIndex('cached_inventories_user_cached_at_idx');
        });
    }
};
