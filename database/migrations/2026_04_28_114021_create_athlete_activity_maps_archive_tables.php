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
        Schema::create('athlete_activity_maps_archive_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('athlete_activity_id');
            $table->unsignedBigInteger('activity_id');
            $table->bigInteger('athlete_id');
            $table->longText('map'); 
            $table->timestamp('archived_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_activity_maps_archive_tables');
    }
};
