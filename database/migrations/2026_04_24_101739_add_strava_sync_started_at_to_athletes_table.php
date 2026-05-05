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
        Schema::table('athletes', function (Blueprint $table) {
              $table->timestamp('strava_sync_started_at')->nullable()->after('last_strava_activity_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athletes', function (Blueprint $table) {
              $table->dropColumn('strava_sync_started_at');
        });
    }
};
