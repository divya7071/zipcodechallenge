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
        Schema::create('athlete_activity_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('athlete_activity_id');
            $table->text('media')->nullable();
            $table->timestamps();
            $table->index(['athlete_activity_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('athlete_activity_media');
    }
};
