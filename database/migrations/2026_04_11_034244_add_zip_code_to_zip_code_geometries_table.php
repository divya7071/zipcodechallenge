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
        Schema::table('zip_code_geometries', function (Blueprint $table) {
             $table->string('zip_code', 10)->after('zip_code_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zip_code_geometries', function (Blueprint $table) {
             $table->dropColumn('zip_code');
        });
    }
};
