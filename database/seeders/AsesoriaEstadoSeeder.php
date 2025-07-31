<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsesoriaEstadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->getEstados() as $estado) {
            DB::table('asesoria-estados')->insert([
                'estado' => $estado,
            ]);
        }
    }

    private function getEstados(): array
    {
        return [
            'Pendiente',
            'En Proceso',
            'Completada',
            'Cancelada',
        ];
    }
}
