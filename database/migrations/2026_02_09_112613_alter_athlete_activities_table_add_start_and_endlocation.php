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
        Schema::table('athlete_activities', function (Blueprint $table) {
             $table->text('start_location')->nullable()->after('photos');
             $table->text('end_location')->nullable()->after('start_location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athlete_activities', function (Blueprint $table) {
            $table->bigInteger('last_strava_activity_id')->nullable()->after('profile');
        });
    }
};
