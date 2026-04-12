<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('athlete_clubs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('strava_club_id')->unique();
            $table->string('name');
            $table->string('sport_type')->nullable();
            $table->string('url')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('profile')->nullable();
            $table->string('profile_medium')->nullable();
            $table->boolean('is_private')->default(false);
            $table->boolean('featured')->default(false);
            $table->boolean('verified')->default(false);
            $table->integer('member_count')->default(0);
            $table->foreignId('athlete_id')
                  ->constrained('athletes')
                  ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('athlete_clubs');
    }
};