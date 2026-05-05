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
            $table->decimal('distance_mi_up', 6, 2)->after('max_speed_mph');
            $table->decimal('distance_mi_down', 6, 2)->after('distance_mi_up');
            $table->decimal('speed_mph_up', 5, 2)->after('distance_mi_down');
            $table->decimal('speed_mph_down', 5, 2)->after('speed_mph_up');
            $table->tinyInteger('sort_order')->nullable()->after('speed_mph_down');
            $table->tinyInteger('sort_order_down')->nullable()->after('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athlete_activity_zip_stats', function (Blueprint $table) {
           $table->dropColumn('distance_mi_up');
           $table->dropColumn('distance_mi_down');
           $table->dropColumn('speed_mph_up');
           $table->dropColumn('speed_mph_down');
           $table->dropColumn('sort_order');
           $table->dropColumn('sort_order_down');
        });
    }
};


