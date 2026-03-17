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
            $table->unsignedTinyInteger('api_failure_count')->default(0)->after('active');
            $table->timestamp('api_last_failure_at')->nullable()->after('api_failure_count');
            $table->string('api_failure_reason')->nullable()->after('api_last_failure_at');
            $table->timestamp('deactivated_at')->nullable()->after('api_failure_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shops', function (Blueprint $table) {
            $table->dropColumn([
                'api_failure_count',
                'api_last_failure_at',
                'api_failure_reason',
                'deactivated_at',
            ]);
        });
    }
};
