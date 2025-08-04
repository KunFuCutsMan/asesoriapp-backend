<?php

namespace Database\Factories;

use App\Models\Especialidad;
use App\Models\Estudiante;
use App\Models\EstudianteEspecialidad;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Estudiante>
 */
class EstudianteFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    protected static ?int $carreraID;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'numeroControl' => strval(fake()->randomNumber(8, true)),
            'contrasena' => static::$password ??= Hash::make(fake()->password(10)),
            'nombre' => fake()->firstName(),
            'apellidoPaterno' => fake()->lastName(),
            'apellidoMaterno' => fake()->lastName(),
            'numeroTelefono' => '229' . strval(fake()->randomNumber(7, true)),
            'semestre' => fake()->randomDigitNot(0),
            'carreraID' => static::$carreraID ??= fake()->numberBetween(1, 11),
        ];
    }

    public function conEspecialidad(): Factory
    {
        return $this->has(Especialidad::factory()->deCarrera(static::$carreraID))
            ->afterCreating(function (Estudiante $estudiante) {

                $especialildades = Especialidad::where('carreraID', static::$carreraID)->get();
                /** @var Especialidad */
                $especialildad = $especialildades->random();

                EstudianteEspecialidad::insert([
                    'estudianteID' => $estudiante->id,
                    'especialidadID' => $especialildad->id,
                ]);
            });
    }
}
