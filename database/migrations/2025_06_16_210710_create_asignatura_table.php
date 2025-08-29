<?php

use App\Models\Carrera;
use App\Models\Asignatura;
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

        Schema::create('carrera_asignatura', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Carrera::class, 'carreraID');
            $table->foreignIdFor(Asignatura::class, 'asignaturaID');
            $table->tinyInteger('semestre', false, true);
            $table->unique(['carreraID', 'asignaturaID']);
            // $table->primary(['carreraID', 'asignaturaID']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('carrera_asignatura', function (Blueprint $table) {
            $table->dropForeign(['carreraID']);
            $table->dropForeign(['asignaturaID']);
            // $table->dropPrimary(['carreraID', 'asignaturaID']);
            $table->dropUnique(['carreraID', 'asignaturaID']);
        });

        Schema::dropIfExists('carrera_asignatura');
        Schema::dropIfExists('asignatura');
    }
};
