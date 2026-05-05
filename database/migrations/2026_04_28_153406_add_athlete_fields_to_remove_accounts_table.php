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
        Schema::table('remove_accounts', function (Blueprint $table) {
            $table->bigInteger('athlete_strava_id')->after('athlete_id');
            $table->string('first_name')->nullable()->after('athlete_strava_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('email')->nullable()->after('last_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('remove_accounts', function (Blueprint $table) {
            //
        });
    }
};
