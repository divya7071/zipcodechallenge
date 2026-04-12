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
        Schema::create('zip_code_geometries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zip_code_id');
            $table->geometry('geom');
            $table->geometry('centroid');
            $table->foreign('zip_code_id')
                  ->references('id')
                  ->on('zip_codes')
                  ->onDelete('cascade');
            $table->spatialIndex('geom');
            $table->spatialIndex('centroid');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zip_code_geometries');
    }
};
