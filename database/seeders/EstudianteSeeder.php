<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Estudiante;
use App\Models\Asesor;
use App\Models\Admin;

class EstudianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Estudiante::factory()
            ->count(50)
            ->create();

        Asesor::factory()
            ->count(10)
            ->create();

        Admin::factory()
            ->count(5)
            ->create();
    }
}
