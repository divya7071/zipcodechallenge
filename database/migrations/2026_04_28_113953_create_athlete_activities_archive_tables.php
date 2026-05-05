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
        Schema::create('athlete_activities_archive_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('activity_id');
            $table->unsignedBigInteger('athlete_id');
            $table->unsignedBigInteger('athlete_strava_id');
            $table->string('name');
            $table->double('distance');
            $table->integer('moving_time');
            $table->integer('elapsed_time');
            $table->string('type');
            $table->string('sport_type');
            $table->string('workout_type')->nullable();
            $table->string('elevation')->nullable();
            $table->string('relative_effort')->nullable();
            $table->text('map')->nullable();
            $table->json('passed_zips')->nullable();
            $table->text('start_location')->nullable();
            $table->text('end_location')->nullable();
            $table->decimal('average_speed', 5, 2)->nullable();
            $table->decimal('max_speed', 5, 2)->nullable();
            $table->string('device_name')->nullable();
            $table->string('average_watts')->nullable();
            $table->string('weighted_average_watts')->nullable();
            $table->dateTime('date');
            $table->text('timezone')->nullable();
            $table->timestamp('archived_at')->useCurrent();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_activities_archive_tables');
    }
};
