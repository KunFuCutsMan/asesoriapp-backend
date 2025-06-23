<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstudiantesTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     * A basic feature test example.
     */
    public function test_Estudiantes_Post_Route_crea_estudiante_correctamente(): void
    {
        $estudiante = Estudiante::factory()->state([
            'contrasena' => '123456789aB&',
        ])->make();

        $response = $this->post('/api/v1/estudiante', [
            'numeroControl' => $estudiante->numeroControl,
            'contrasena' => $estudiante->contrasena,
            'contrasena_confirmation' => $estudiante->contrasena,
            'nombre' => $estudiante->nombre,
            'apellidoPaterno' => $estudiante->apellidoPaterno,
            'apellidoMaterno' => $estudiante->apellidoMaterno,
            'numeroTelefono' => $estudiante->numeroTelefono,
            'semestre' => $estudiante->semestre,
            'carreraID' => $estudiante->carreraID,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseCount('estudiante', 1);
    }
}
