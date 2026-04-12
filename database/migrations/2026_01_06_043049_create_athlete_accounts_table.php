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
        Schema::create('athlete_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('athlete_id')
                ->constrained('athletes')
                ->cascadeOnDelete();
            $table->unsignedBigInteger('strava_athlete_id')->unique();
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('token_expires_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_accounts');
    }
};
