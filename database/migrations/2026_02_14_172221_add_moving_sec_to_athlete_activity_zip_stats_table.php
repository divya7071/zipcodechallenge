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
        Schema::table('athlete_activity_zip_stats', function (Blueprint $table) {
             $table->integer('moving_sec')->nullable()->after('elapsed_sec');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athlete_activity_zip_stats', function (Blueprint $table) {
             $table->dropColumn('moving_sec');
        });
    }
};
