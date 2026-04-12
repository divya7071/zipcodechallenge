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
        Schema::create('athlete_bikes', function (Blueprint $table) {
            $table->id();
            $table->string('strava_gear_id')->unique(); 
            $table->unsignedBigInteger('athlete_id'); 
            $table->boolean('primary')->default(false);
            $table->string('name')->nullable();
            $table->unsignedTinyInteger('resource_state')->nullable();
            $table->unsignedBigInteger('distance')->default(0); // meters
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_bikes');
    }
};
