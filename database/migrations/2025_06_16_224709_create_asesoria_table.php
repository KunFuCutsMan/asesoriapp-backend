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
        Schema::create('asesoria', function (Blueprint $table) {
            $table->id();
            $table->date('diaAsesoria');
            $table->time('horaInicial', 0);
            $table->time('horaFinal', 0);
            $table->tinyInteger('estadoAsesoria')->comment('Estado actual de la asesoria. 0: No hecha, 1: En progreso, 2: Terminada, 3: Cancelada');

            $table->foreignId('estudianteID')->references('id')->on('estudiante');
            $table->foreignId('carreraID')->references('carreraID')->on('carrera-asignatura');
            $table->foreignId('asignaturaID')->references('asignaturaID')->on('carrera-asignatura');
            $table->foreignId('asesorID')->nullable()->references('id')->on('asesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesoria', function (Blueprint $table) {
            $table->dropForeign(['estudianteID']);
            $table->dropForeign(['carreraID']);
            $table->dropForeign(['asignaturaID']);
            $table->dropForeign(['asesorID']);
        });

        Schema::dropIfExists('asesoria');
    }
};
