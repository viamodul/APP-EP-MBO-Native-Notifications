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
        Schema::create('shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('shop_url');
            $table->enum('epages_version', ['now', 'base']);
            $table->text('api_token');
            $table->integer('polling_interval_minutes')->default(5);
            $table->timestamp('last_order_check')->nullable();
            $table->timestamp('last_processed_order_date')->nullable();
            $table->boolean('active')->default(true);
            $table->string('group_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
