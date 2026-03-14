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
        Schema::table('shops', function (Blueprint $table) {
            $table->string('webhook_url')->nullable()->after('api_token');
            $table->foreignId('epages_shop_id')->nullable()->after('id');
            $table->enum('source', ['api', 'appstore'])->default('api')->after('active');

            $table->index('epages_shop_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropIndex(['epages_shop_id']);
            $table->dropColumn(['webhook_url', 'epages_shop_id', 'source']);
        });
    }
};
