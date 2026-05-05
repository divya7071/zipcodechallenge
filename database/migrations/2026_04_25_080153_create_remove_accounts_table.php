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
        Schema::create('remove_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('athlete_id')->nullable();
            $table->string('reason')->nullable();
            $table->string('other_reason')->nullable();
            $table->text('comments');
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remove_accounts');
    }
};
