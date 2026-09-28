<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cached_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roblox_user_id')->index();
            $table->string('category');
            $table->json('items');
            $table->integer('total_count')->default(0);
            $table->timestamp('cached_at')->useCurrent();
            $table->timestamps();
            $table->unique(['roblox_user_id', 'category']);
        });
    }
    public function down(): void { Schema::dropIfExists('cached_inventories'); }
};