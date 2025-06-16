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
        Schema::create('asesor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudianteID')->references('id')->on('estudiante');
        });

        Schema::create('asesor-asignatura', function (Blueprint $table) {
            $table->foreignId('asesorID')->references('id')->on('asesor');
            $table->foreignId('asignaturaID')->references('id')->on('asignatura');
        });

        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asesorID')->references('id')->on('asesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropForeign(['asesorID']);
        });
        Schema::table('asesor-asignatura', function (Blueprint $table) {
            $table->dropForeign(['asesorID']);
            $table->dropForeign(['asignaturaID']);
        });
        Schema::table('asesor', function (Blueprint $table) {
            $table->dropForeign(['estudianteID']);
        });

        Schema::dropIfExists('admin');
        Schema::dropIfExists('asesor-asignatura');
        Schema::dropIfExists('asesor');
    }
};
