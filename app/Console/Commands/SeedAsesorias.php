<?php

namespace App\Console\Commands;

use App\Models\Asesor;
use Illuminate\Console\Command;
use App\Models\Asesoria;
use App\Models\Estudiante;

class SeedAsesorias extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-asesorias {count=10} {--estudianteID=} {--asesorID=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Llena la base de datos con datos de asesorías';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->argument('count');
        $estudianteID = $this->option('estudianteID');
        $asesorID = $this->option('asesorID');

        if ($estudianteID) {
            $this->info("Sembrando asesorías para el estudiante con ID: $estudianteID");
            // Aquí se puede agregar la lógica para sembrar asesorías específicas para un estudiante
            $estudiante = Estudiante::find($estudianteID);
            Asesoria::factory()
                ->count($count)
                ->recycle($estudiante)
                ->create();
        } elseif ($asesorID) {
            $this->info("Sembrando asesorías para el asesor con ID: $asesorID");
            // Aquí se puede agregar la lógica para sembrar asesorías específicas para un asesor
            $asesor = Asesor::find($asesorID);
            Asesoria::factory()
                ->count($count)
                ->state([
                    'asesorID' => $asesor->id,
                ])
                ->recycle($asesor)
                ->create();
        } else {
            $this->info('Sembrando asesorías para todos los estudiantes');
            // Aquí se puede agregar la lógica para sembrar asesorías para todos los estudiantes

            Asesoria::factory()->count($count)->create();
        }
    }
}
