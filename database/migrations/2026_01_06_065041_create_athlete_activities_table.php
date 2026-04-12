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
        Schema::create('athlete_activities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('activity_id')->unique();
            $table->bigInteger('athlete_id');
            $table->bigInteger('athlete_strava_id');
            $table->string('name');
            $table->float('distance');
            $table->integer('moving_time');
            $table->integer('elapsed_time');
            $table->string('type');
            $table->string('sport_type');
            $table->string('workout_type')->nullable();
            $table->string('elevation')->nullable();
            $table->string('relative_effort')->nullable();
            $table->text('map')->nullable();
            $table->json('passed_zips')->nullable();
            $table->json('photos')->nullable();
            $table->dateTime('date');
            $table->text('timezone')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_activities');
    }
};
