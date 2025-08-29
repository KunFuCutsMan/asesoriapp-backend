<?php

use App\Models\Asesor;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Estudiante;
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
            $table->tinyInteger('estadoAsesoria')->comment('Estado actual de la asesoria. 0: No hecha, 1: En progreso, 2: Terminada, 3: Cancelada')->default(0);

            $table->foreignIdFor(Estudiante::class, 'estudianteID');
            $table->foreignIdFor(Asesor::class, 'asesorID')->nullable();
            $table->unsignedBigInteger('carreraID');
            $table->unsignedBigInteger('asignaturaID');

            $table->foreign(['carreraID', 'asignaturaID'])->references(['carreraID', 'asignaturaID'])->on('carrera_asignatura');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesoria', function (Blueprint $table) {
            $table->dropForeignIdFor(Estudiante::class, 'estudianteID');
            $table->dropForeignIdFor(Asesor::class, 'asesorID');
            $table->dropForeign(['carreraID', 'asignaturaID']);
        });

        Schema::dropIfExists('asesoria');
    }
};
