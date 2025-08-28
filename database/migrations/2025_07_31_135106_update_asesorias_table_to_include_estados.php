<?php

use App\Models\AsesoriaEstado;
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
        Schema::table('asesoria', function (Blueprint $table) {
            $table->foreignIdFor(AsesoriaEstado::class, 'estadoAsesoriaID')->default(1);
            $table->dropColumn('estadoAsesoria');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asesoria', function (Blueprint $table) {
            $table->dropForeignIdFor(AsesoriaEstado::class, 'estadoAsesoriaID');
            $table->tinyInteger('estadoAsesoria')->comment('Estado actual de la asesoria. 0: No hecha, 1: En progreso, 2: Terminada, 3: Cancelada')->default(0);
            $table->dropColumn('estadoAsesoriaID');
        });
    }
};
