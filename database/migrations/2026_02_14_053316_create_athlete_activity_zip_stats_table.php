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
        Schema::create('athlete_activity_zip_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('athlete_activity_id');
            $table->unsignedBigInteger('athlete_id'); 
            $table->string('zip_code', 10);
            $table->decimal('distance_mi', 6, 2);
            $table->integer('elapsed_sec');
            $table->decimal('speed_mph', 5, 2);
            $table->timestamps();
            $table->index(['zip_code', 'athlete_id']);
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_activity_zip_stats');
    }
};
