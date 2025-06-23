<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
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
            'carreraID' => fake()->numberBetween(1, 11),
        ];
    }
}
