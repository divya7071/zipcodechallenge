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
             $table->bigInteger('last_strava_activity_id')->nullable()->after('profile');
             $table->timestamp('strava_synced_at')->nullable()->after('last_strava_activity_id');
             $table->boolean('is_syncing')->nullable()->after('strava_synced_at')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athletes', function (Blueprint $table) {
             $table->dropColumn('last_strava_activity_id');
             $table->dropColumn('strava_synced_at');
             $table->dropColumn('is_syncing');
        });
    }
};
