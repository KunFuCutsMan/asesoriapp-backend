<?php

namespace Database\Factories;

use App\Models\Especialidad;
use Database\Seeders\EspecialidadesSeeder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Especialidad>
 */
class EspecialidadFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $especialidad = Arr::random(EspecialidadesSeeder::$especialidades);
        return [
            'nombre' => $especialidad[0],
            'carreraID' => $especialidad[1],
        ];
    }

    public function deCarrera(int $carreraID): Factory
    {
        return $this->state(function () use ($carreraID) {
            $especialidad = Arr::first(EspecialidadesSeeder::$especialidades, function (array $val) use ($carreraID) {
                return $val[1] == $carreraID;
            });
            return [
                'nombre' => $especialidad[0],
                'carreraID' => $especialidad[1],
            ];
        });
    }
}
