<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use App\Models\Asesor;
use App\Models\Asignatura;
use App\Models\Carrera;
use DateInterval;
use DateTimeImmutable;
use App\Models\Estudiante;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsesoriaTest extends TestCase
{
    public function test_estudiante_obtiene_sus_asesorias(): void
    {
        $estudiante = Estudiante::factory()->create();
        Asesoria::factory()
            ->count(5)
            ->recycle($estudiante)
            ->create([
                'estudianteID' => $estudiante->id,
            ]);

        Sanctum::actingAs($estudiante);
        $response = $this->get('/api/v1/asesoria/');

        $response->assertSuccessful();
        $response->assertJsonCount(5, 'data');
        $response->assertJsonStructure([
            'data' => [
                '*' => [
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
                    'estudianteID',
                    'asesor',
                ]
            ]
        ]);

        $body = $response->json('data');
        foreach ($body as $asesoria) {
            $this->assertEquals($estudiante->id, $asesoria['estudianteID']);
            $asignatura = Asignatura::find($asesoria['asignatura']['id']);
            $carrera = Carrera::find($asesoria['carrera']['id']);

            $this->assertNotNull($asignatura);
            $this->assertNotNull($carrera);

            $asignaturasPosibles = $carrera->asignaturas->pluck('id')->toArray();
            $this->assertContains($asesoria['asignatura']['id'], $asignaturasPosibles);

            $this->assertEquals($estudiante->id, $asesoria['estudianteID']);

            if ($asesoria['estadoAsesoria']['id'] == AsesoriaEstado::PENDIENTE) {
                $this->assertNull($asesoria['asesor']);
            } else {
                $this->assertNotNull($asesoria['asesor']);
            }
        }
    }

    public function test_store_asesoria_como_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create();
        $estudiante->refresh();

        Sanctum::actingAs($estudiante);

        $inicio = new DateTimeImmutable('now');
        $final = $inicio->add(new DateInterval('PT1H'));

        $asignatura = $estudiante->carrera->asignaturas()->first();

        $response = $this->post('/api/v1/asesoria/', [
            'carreraID' => $estudiante->carrera->id,
            'asignaturaID' => $asignatura->id,
            'diaAsesoria' => $inicio->format("d-m-y"),
            'horaInicial' => $inicio->format("H:i"),
            'horaFinal' => $final->format("H:i"),
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseCount('asesoria', 1);

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
                'estudianteID',
                'asesor',
            ]
        ]);

        $data = $response->json('data');
        $this->assertEquals($estudiante->id, $data['estudianteID']);
        $this->assertEquals(AsesoriaEstado::PENDIENTE, $data['estadoAsesoria']['id']);
        $this->assertNull($data['asesor']);
    }

    public function test_admin_asigna_asesoria_a_asesor(): void
    {
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
        ])->create();

        $asesor = Asesor::factory()->create();

        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin->asesor->estudiante);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'asesorID' => $asesor->id,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
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
                'estudianteID',
                'asesor' => [
                    'id',
                    'estudianteID',
                ],
            ]
        ]);

        $response->assertJsonPath('data.asesor.id', $asesor->id);
        $response->assertJsonPath('data.estadoAsesoria.id', AsesoriaEstado::EN_PROGRESO);
    }

    public function test_admin_to_puede_cambiar_asesoria_en_proceso(): void
    {
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ])->create();

        $asesor = Asesor::factory()->create();

        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin->asesor->estudiante);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'asesorID' => $asesor->id,
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('asesoria', [
            'id' => $asesoria->id,
            'asesorID' => $asesor->id,
        ]);
    }
}
