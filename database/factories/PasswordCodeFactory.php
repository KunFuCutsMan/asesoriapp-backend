<?php

namespace Database\Factories;

use App\Models\Estudiante;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PasswordCode>
 */
class PasswordCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->generateCode(),
            'used' => false,
            'estudianteID' => Estudiante::factory()
        ];
    }

    private function generateCode(): string
    {
        $digits = Arr::random([1, 2, 3, 4, 5, 6, 7, 8, 9, 0], 6, true);
        return Arr::join($digits, '');
    }
}
