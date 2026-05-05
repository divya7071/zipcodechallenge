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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->string('platform')->nullable();
            $table->string('type');
            $table->string('reference_id')->nullable();
            $table->string('method');
            $table->string('url');
            $table->longText('payload');
            $table->longText('response');
            $table->string('code');
            $table->longText('message');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
