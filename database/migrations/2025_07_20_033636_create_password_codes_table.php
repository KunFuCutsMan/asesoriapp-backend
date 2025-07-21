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
        Schema::create('password_code', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6);
            $table->timestamp('created_at');
            $table->timestamp('used_at')->nullable();

            $table->foreignId('estudianteID')->references('id')->on('estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_code', function (Blueprint $table) {
            $table->dropForeign('estudianteID');
        });
        Schema::dropIfExists('password_code');
    }
};
