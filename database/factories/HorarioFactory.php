<?php

namespace Database\Factories;

use App\Models\Asesor;
use DateTimeImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Horario>
 */
class HorarioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hora = random_int(7, 20);
        return [
            'horaInicio' => sprintf('%02d:00', $hora),
            'disponible' => $this->faker->boolean(),
            'diaSemanaID' => random_int(1, 5),
            'asesorID' => Asesor::factory(),
        ];
    }
}
