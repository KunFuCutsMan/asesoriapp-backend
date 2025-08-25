<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Asesor;
use App\Models\Estudiante;
use App\Models\Asignatura;
use App\Models\Horario;
use DateTimeImmutable;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsesorControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_obten_asesores_para_cierta_asesoria(): void
    {
        $asignatura = Asignatura::find(46); // Calculo Diferencial
        $otrasAsignturas = Asignatura::where('id', '!=', 46)->get();

        Asesor::factory(2)
            ->hasAttached($asignatura, [], 'asignaturas')
            ->afterCreating(function (Asesor $asesor) {
                $hora = new Horario();
                $hora->horaInicio = DateTimeImmutable::createFromFormat('H:i', '10:00');
                $hora->disponible = true;
                $hora->diaSemanaID = 1;

                $asesor->horarios()->save($hora);
            })
            ->create();

        Asesor::factory(8)
            ->hasAttached($asignatura, [], 'asignaturas')
            ->afterCreating(function (Asesor $asesor) {
                $hora = new Horario();
                $hora->horaInicio = DateTimeImmutable::createFromFormat('H:i', '09:00');
                $hora->disponible = true;
                $hora->diaSemanaID = 1;

                $asesor->horarios()->save($hora);
            })
            ->create();

        Asesor::factory(10)
            ->hasAttached($otrasAsignturas->random(3), [], 'asignaturas')
            ->create();

        $estudiante = Estudiante::factory()->create();
        Sanctum::actingAs($estudiante);

        $response = $this->get('api/v1/asesor/of-asignatura/46?diaSemanaID=1&horaInicio=10:00&horaFinal=11:00');

        $response->assertStatus(200);

        $estructuraAsesorData = [
            'asesor' => ['id', 'estudianteID'],
            'estudiante' => [
                'asesor' => ['id', 'estudianteID']
            ],
            'asignaturas' => [
                '*' => ['id', 'nombre']
            ],
            'horarios' => [
                '*' => ['id', 'horaInicio', 'disponible', 'diaSemana', 'asesor']
            ]
        ];

        $response->assertJsonStructure([
            'data' => [
                'ideales' => [
                    '*' => $estructuraAsesorData
                ],
                'asignatura' => [
                    '*' => $estructuraAsesorData
                ],
                'carrera' => [
                    '*' => $estructuraAsesorData
                ],
                'otros' => [
                    '*' => $estructuraAsesorData
                ]
            ]
        ]);

        $response->dump('data.asignatura');
        $this->assertCount(2, $response->json('data.ideales'));
        $this->assertCount(8, $response->json('data.asignatura'));
        $this->assertCount(10, $response->json('data.carrera'));
        $this->assertCount(0, $response->json('data.otros'));

        $ideales = $response->json('data.ideales');
        $porAsignatura = $response->json('data.asignatura');
        $porCarrera = $response->json('data.carrera');
        $otros = $response->json('data.otros');

        // Los ideales deben tener la asignatura 46 (Calculo Diferencial)
        foreach ($ideales as $asesor) {
            $this->assertTrue(
                collect($asesor['asignaturas'])->contains('id', 46)
            );
        }

        // Los otros no deben tener la asignatura 46 (Calculo Diferencial)
        foreach ($otros as $asesor) {
            $this->assertFalse(
                collect($asesor['asignaturas'])->contains('id', 46)
            );
        }

        // Revisa si ningun asesor se repite en ambos grupos
        foreach ($ideales as $asesorIdeal) {
            $this->assertFalse(
                collect($otros)->contains('asesor.id', $asesorIdeal['asesor']['id'])
            );
        }

        foreach ($otros as $asesorNoIdeal) {
            $this->assertFalse(
                collect($ideales)->contains('asesor.id', $asesorNoIdeal['asesor']['id'])
            );
        }
    }

    public function test_no_se_obtienen_asesores_de_asignatura_no_existente(): void
    {
        $estudiante = Estudiante::factory()->create();
        Sanctum::actingAs($estudiante);

        $response = $this->get('api/v1/asesor/of-asignatura/999999');
        $response->assertStatus(404);
    }
}
