<?php

use App\Models\Asesor;
use App\Models\DiaSemana;
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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->time('horaInicio');
            $table->boolean('disponible')->default(false);
            $table->foreignIdFor(DiaSemana::class, 'diaSemanaID');
            $table->foreignIdFor(Asesor::class, 'asesorID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropForeignIdFor(DiaSemana::class, 'diaSemanaID');
            $table->dropForeignIdFor(Asesor::class, 'asesorID');
        });
        Schema::dropIfExists('horarios');
    }
};
