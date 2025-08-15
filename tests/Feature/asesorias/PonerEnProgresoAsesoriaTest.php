<?php

namespace Tests\Feature\asesorias;

use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use App\Models\Asesor;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PonerEnProgresoAsesoriaTest extends TestCase
{
    public function test_asesor_actualiza_asesoria_como_en_proceso(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
            'horaInicial' => now()->format('H:i'),
            'horaFinal' => now()->addHour()->format('H:i'),
            'asesorID' => $asesor->id,
        ])->recycle($asesor)->create();

        $this->travel(2)->minutes();

        Sanctum::actingAs($asesor->estudiante);

        $response = $this->put('/api/v1/asesorias/' . $asesoria->id, [
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'asesorID' => $asesor->id,
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
        $this->assertEquals($asesor->id, $data['asesor']['id']);
        $this->assertEquals(AsesoriaEstado::EN_PROGRESO, $data['estadoAsesoria']['id']);
    }
}
