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
        Schema::create('athlete_activity_zip_stats_archive_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('athlete_activity_id');
            $table->unsignedBigInteger('athlete_id');
            $table->string('zip_code', 10);
            $table->decimal('distance_mi', 6, 2);
            $table->integer('elevation_gain_ft')->nullable();
            $table->integer('elapsed_sec');
            $table->integer('moving_sec')->nullable();
            $table->decimal('speed_mph', 5, 2);
            $table->decimal('max_speed_mph', 5, 2)->nullable();
            $table->decimal('distance_mi_up', 6, 2);
            $table->decimal('distance_mi_down', 6, 2);
            $table->decimal('speed_mph_up', 5, 2);
            $table->decimal('speed_mph_down', 5, 2);
            $table->tinyInteger('sort_order')->nullable();
            $table->tinyInteger('sort_order_down')->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('rank')->nullable();
            $table->timestamp('archived_at')->useCurrent();
         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_activity_zip_stats_archive_tables');
    }
};
