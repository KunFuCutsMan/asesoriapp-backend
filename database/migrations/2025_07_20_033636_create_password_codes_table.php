<?php

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
        Schema::create('password_code', function (Blueprint $table) {
            $table->id();
            $table->string('code', 6);
            $table->timestamps();
            $table->boolean('used')->default(false);

            $table->foreignIdFor(Estudiante::class, 'estudianteID');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_code', function (Blueprint $table) {
            $table->dropForeignIdFor(Estudiante::class, 'estudianteID');
        });
        Schema::dropIfExists('password_code');
    }
};
