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
            $table->decimal('average_speed', 5, 2)->nullable()->after('end_location');
            $table->decimal('max_speed', 5, 2)->nullable()->after('average_speed');
            $table->string('device_name')->nullable()->after('max_speed');
            $table->string('average_watts')->nullable()->after('device_name');
            $table->string('weighted_average_watts')->nullable()->after('average_watts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athlete_activities', function (Blueprint $table) {
             $table->dropColumn('average_speed');
             $table->dropColumn('max_speed');
             $table->dropColumn('device_name');
             $table->dropColumn('average_watts');
             $table->dropColumn('weighted_average_watts');
        });
    }
};
