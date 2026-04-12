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
        Schema::table('athletes', function (Blueprint $table) {
           $table->boolean('premium')->nullable()->after('sex');
           $table->integer('follower_count')->nullable()->after('premium');
           $table->integer('friend_count')->nullable()->after('follower_count');
           $table->tinyInteger('athlete_type')->nullable()->after('friend_count');
           $table->tinyInteger('badge_type_id')->nullable()->after('athlete_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('athletes', function (Blueprint $table) {
            $table->dropColumn('premium');
            $table->dropColumn('follower_count');
            $table->dropColumn('friend_count');
            $table->dropColumn('athlete_type');
            $table->dropColumn('badge_type_id');
        });
    }
};
