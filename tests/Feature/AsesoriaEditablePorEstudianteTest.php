<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Asesor;
use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use Laravel\Sanctum\Sanctum;
use App\Models\Estudiante;

class AsesoriaEditablePorEstudianteTest extends TestCase
{
    public function test_estudiante_cancela_asesoria(): void
    {
        $estudiante = Estudiante::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
            'estudianteID' => $estudiante->id,
        ])->create();

        Sanctum::actingAs($estudiante);

        $response = $this->delete("/api/v1/asesoria/" . $asesoria->id);

        $response->assertSuccessful();
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'estudianteID' => $estudiante->id,
            'estadoAsesoriaID' => AsesoriaEstado::CANCELADA,
        ]);

        $response->assertJsonStructure([
            'data' => [
                'id',
                'diaAsesoria',
                'horaInicial',
                'horaFinal',
                'carrera' => [
                    'id',
                    'nombre'
                ],
                'asignatura' => [
                    'id',
                    'nombre'
                ],
                'estadoAsesoria' => [
                    'id',
                    'estado',
                ],
                'estudiante',
                'asesor',
            ]
        ]);

        $data = $response->json('data');

        $this->assertEquals(AsesoriaEstado::CANCELADA, $data['estadoAsesoria']['id']);
    }
}
