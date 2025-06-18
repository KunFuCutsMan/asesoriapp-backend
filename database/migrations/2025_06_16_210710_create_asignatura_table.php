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
        Schema::create('asignatura', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 64);
        });

        Schema::create('carrera-asignatura', function (Blueprint $table) {
            $table->foreignId('carreraID')->references('id')->on('carrera');
            $table->foreignId('asignaturaID')->references('id')->on('asignatura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrera-asignatura', function (Blueprint $table) {
            $table->dropForeign(['carreraID']);
            $table->dropForeign(['asignaturaID']);
        });

        Schema::dropIfExists('carrera-asignatura');
        Schema::dropIfExists('asignatura');
    }
};
