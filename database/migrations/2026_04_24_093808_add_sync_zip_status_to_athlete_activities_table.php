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
               $table->tinyInteger('sync_zip_status')->after('timezone')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athlete_activities', function (Blueprint $table) {
            $table->dropColumn('sync_zip_status');
        });
    }
};
