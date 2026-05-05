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
        Schema::create('athlete_accounts_archive_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('athlete_id');
            $table->unsignedBigInteger('strava_athlete_id');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('token_expires_at');
            $table->timestamp('archived_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_accounts_archive_tables');
    }
};
