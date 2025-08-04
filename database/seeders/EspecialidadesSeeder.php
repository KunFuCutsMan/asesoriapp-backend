<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecialidadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (EspecialidadesSeeder::$especialidades as $especialidad) {
            DB::table('especialidades')->insert([
                'nombre' => $especialidad[0],
                'carreraID' => $especialidad[1],
            ]);
        }
    }

    static $especialidades = [
        ['Gestión y Negocios', 1],
        ['Mercadotecnia y Negocios Internacionales', 1],
        ['Ingeniería de Procesos en Alimentos', 2],
        ['Ingeniería de Procesos en Ambiente-Energía', 2],
        ['Ingeniería en Procesos Farmacéuticos', 2],
        ['Aplicaciones Industriales', 3],
        ['Gestión de Sistemas Energéticos', 3],
        ['Gestión de Sistemas Energéticos', 4],
        ['Sistemas Digitales', 4],
        ['Gestión Industrial y de la Productividad', 5],
        ['Optativas', 5],
        ['Sistemas Robóticos y Mecatrónicos', 6],
        ['Sistemas Digitales', 6],
        ['Gestión de Sistemas Energéticos', 6],
        ['Manufactura', 7],
        ['Mantenimiento', 7],
        ['Concurrencia Computacional Avanzada', 8],
        ['Ciencia de Datos Aplicada', 8],
        ['Ingeniería de Procesos', 9],
        ['Gestión Ambiental y de la Seguridad', 9],
        ['Gestión de la Calidad de la Energía', 10],
        ['Ingeniería Ambiental y Seguridad Laboral', 10],
        ['Innovación para el desarrollo empresarial', 10],
        ['Calidad y Productividad', 11],
        ['Negocios Globales', 11],
    ];
}
