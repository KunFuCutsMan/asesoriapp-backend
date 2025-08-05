<?php

use App\Models\Especialidad;
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
        Schema::table('estudiante', function (Blueprint $table) {
            $table->foreignIdFor(Especialidad::class, 'especialidadID')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('estudiante', function (Blueprint $table) {
            $table->dropForeignIdFor(Especialidad::class, 'especialidadID');
        });
    }
};
