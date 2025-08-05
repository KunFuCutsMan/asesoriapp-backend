<?php

namespace Tests\Feature;

use App\Models\Especialidad;
use App\Models\Estudiante;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EspecialidadTest extends TestCase
{
    public function test_obten_especialidades(): void
    {
        $response = $this->get('/api/v1/especialidades');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nombre',
                    'carreraID',
                ],
            ]
        ]);
    }

    public function test_obten_especialidad_de_carrera(): void
    {
        $response = $this->get('api/v1/carreras/6/especialidades');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'nombre',
                    'carreraID',
                ],
            ]
        ]);
        $response->assertJsonCount(3, 'data');
    }

    public function test_obten_especialidad_por_id(): void
    {
        $response = $this->get('api/v1/especialidades/1');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'nombre',
                'carreraID',
            ],
        ]);
    }

    public function test_obten_especialidad_no_encontrada(): void
    {
        $response = $this->get('api/v1/especialidades/999');
        $response->assertStatus(404);
    }

    public function test_estudiante_puede_insertar_especialidad_de_carrera(): void
    {
        /** @var Especialidad Sistemas Robóticos, Mecatrónica */
        $especialidad = Especialidad::find(12);
        $estudiante = Estudiante::factory()->state([
            'carreraID' => $especialidad->carreraID,
        ])->create();

        $especialidad->refresh();
        $estudiante->refresh();

        Sanctum::actingAs($estudiante);

        $response = $this->post('/api/v1/estudiante/especialidad', [
            'especialidadID' => $especialidad->id,
        ]);

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'nombre',
                'numeroControl',
                'apellidoPaterno',
                'apellidoMaterno',
                'semestre',
                'carrera' => [
                    'id',
                    'nombre',
                    'codigo',
                ],
                'especialidad' => [
                    'id',
                    'nombre',
                    'carreraID',
                ],
                'asesor',
            ]
        ]);

        $especialidad->refresh();
        $estudiante->refresh();

        $response->assertJsonPath('data.especialidad.id', $especialidad->id);
        $response->assertJsonPath('data.especialidad.nombre', $especialidad->nombre);
        $response->assertJsonPath('data.especialidad.carreraID', $especialidad->carreraID);

        $this->assertEquals($response['data']['carrera']['id'], $response['data']['especialidad']['carreraID']);
    }

    function test_estudiante_no_puede_insertar_especialidad_de_otra_carrera(): void
    {
        /** @var Especialidad Sistemas Robóticos, Mecatrónica */
        $especialidad = Especialidad::find(12);
        $estudiante = Estudiante::factory()->state([
            'carreraID' => 7, // Pero esta en mecanica
        ])->create();

        Sanctum::actingAs($estudiante);

        $response = $this->post('/api/v1/estudiante/especialidad', [
            'especialidadID' => $especialidad->id,
        ]);

        $response->assertClientError();
    }
}
