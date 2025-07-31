<?php

namespace Database\Factories;

use App\Models\Asesor;
use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use App\Models\Carrera;
use App\Models\Estudiante;
use DateTime;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Asesoria>
 */
class AsesoriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'diaAsesoria' => fake()->dateTimeBetween('-1 year', '+1 month'),
            'horaInicial' => fake()->time('H:i'),
            'horaFinal' => function (array $attributes) {
                $hora = DateTime::createFromFormat('H:i', $attributes['horaInicial']);
                $offset = fake()->randomElement(['+1 hour', '+2 hours']);
                $hora->modify($offset);
                return $hora->format('H:i');
            },
            'estadoAsesoriaID' => AsesoriaEstado::factory(),
            'estudianteID' => Estudiante::factory(),
            'carreraID' => function (array $attributes) {
                return Estudiante::find($attributes['estudianteID'])->carreraID;
            },
            'asignaturaID' => function (array $attributes) {
                return Carrera::find($attributes['carreraID'])->asignaturas->random()->id;
            },
            'asesorID' => Asesor::factory(),
        ];
    }
}
