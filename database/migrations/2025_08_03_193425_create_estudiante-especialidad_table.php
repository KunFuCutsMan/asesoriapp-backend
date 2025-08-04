<?php

use App\Models\Especialidad;
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
        Schema::create('estudiante-especialidad', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Estudiante::class, 'estudianteID');
            $table->foreignIdFor(Especialidad::class, 'especialidadID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiante-especialidad', function (Blueprint $table) {
            $table->dropForeign(['estudianteID']);
            $table->dropForeign(['especialidadID']);
        });
        Schema::dropIfExists('estudiante-especialidad');
    }
};
