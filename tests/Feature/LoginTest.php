<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_Login_Estudiante(): void
    {
        $contrasena = '123456789aB&';
        $estudiante = Estudiante::factory()->state([
            'contrasena' => $contrasena,
        ])->create();


        $response = $this->post('/api/v1/sanctum/token', [
            'numeroControl' => $estudiante->numeroControl,
            'contrasena' => $contrasena,
        ]);

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'token'
        ]);
    }

    public function test_Login_Estudiante_con_contrasena_equivocada(): void
    {
        $estudiante = Estudiante::factory()->create();

        $response = $this->post('/api/v1/sanctum/token', [
            'numeroControl' => $estudiante->numeroControl,
            'contrasena' => 'oiadDv0aea<3jr48frniesiuheh0fje9pf8no43rn',
        ]);

        $response->assertClientError();
    }
}
