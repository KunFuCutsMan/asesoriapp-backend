<?php

use App\Models\Asesor;
use App\Models\Asignatura;
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
        Schema::create('asesor', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Estudiante::class, 'estudianteID');
        });

        Schema::create('asesor_asignatura', function (Blueprint $table) {
            $table->foreignIdFor(Asesor::class, 'asesorID');
            $table->foreignIdFor(Asignatura::class, 'asignaturaID');
        });

        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Asesor::class, 'asesorID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('admin', function (Blueprint $table) {
            $table->dropForeign(['asesorID']);
            $table->dropForeignIdFor(Asesor::class, 'asesorID');
        });
        Schema::table('asesor_asignatura', function (Blueprint $table) {
            $table->dropForeignIdFor(Asesor::class, 'asesorID');
            $table->dropForeignIdFor(Asignatura::class, 'asignaturaID');
        });
        Schema::table('asesor', function (Blueprint $table) {
            $table->dropForeignIdFor(Estudiante::class, 'estudianteID');
        });

        Schema::dropIfExists('admin');
        Schema::dropIfExists('asesor_asignatura');
        Schema::dropIfExists('asesor');
    }
};
