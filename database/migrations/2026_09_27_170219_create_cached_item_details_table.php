<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('cached_item_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id')->unique();
            $table->string('name')->nullable();
            $table->string('creator_name')->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_limited')->default(false);
            $table->integer('rap')->nullable();
            $table->json('raw_details')->nullable();
            $table->timestamp('cached_at')->useCurrent();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('cached_item_details'); }
};