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
        Schema::table('athlete_activity_media', function (Blueprint $table) {
            $table->unsignedBigInteger('athlete_id')->after('athlete_activity_id'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athlete_activity_media', function (Blueprint $table) {
            $table->dropColumn('athlete_id');
        });
    }
};
