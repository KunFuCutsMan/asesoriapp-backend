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
        Schema::create('estudiante', function (Blueprint $table) {
            $table->id();
            $table->string('numeroControl', 8);
            $table->char('contrasena', 72)->comment("Encriptada con bcrypt");
            $table->string('nombre', 32);
            $table->string('apellidoPaterno', 32);
            $table->string('apellidoMaterno', 32);
            $table->char('numeroTelefono', 10);
            $table->tinyInteger('semestre', false, true);

            $table->foreignId('carreraID')->references('id')->on('carrera');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiante', function (Blueprint $table) {
            $table->dropForeign(['carreraID']);
        });

        Schema::dropIfExists('estudiante');
    }
};
