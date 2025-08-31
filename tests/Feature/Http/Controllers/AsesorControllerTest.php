<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Asesor;
use App\Models\Estudiante;
use App\Models\Asignatura;
use App\Models\Carrera;
use App\Models\Horario;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsesorControllerTest extends TestCase
{

    private static $estructuraAsesorData = [
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

    private $estructuraRespuestaAsesores;

    public function __construct(...$args)
    {
        parent::__construct(...$args);
        $this->estructuraRespuestaAsesores = [
            'data' => [
                'ideales' => [
                    '*' => self::$estructuraAsesorData
                ],
                'asignatura' => [
                    '*' => self::$estructuraAsesorData
                ],
                'carrera' => [
                    '*' => self::$estructuraAsesorData
                ],
                'otros' => [
                    '*' => self::$estructuraAsesorData
                ]
            ]
        ];
    }

    /**
     * Prueba para acceder a los asesores disponibles para una asignatura en cierto horario,
     * donde se provee solo la hora inicial
     */
    public function test_obten_asesores_para_cierta_asesoria(): void
    {
        $calculoDif = Asignatura::find(46); // Calculo Diferencial
        $otrasAsignturas = Asignatura::where('id', '!=', 46)->get();

        Asesor::factory(2)
            ->hasAttached($calculoDif, [], 'asignaturas')
            ->afterCreating(function (Asesor $asesor) {
                Horario::factory()
                    ->state([
                        'horaInicio' => '10:00',
                        'disponible' => true,
                        'diaSemanaID' => 1,
                    ])
                    ->recycle($asesor)
                    ->create();
            })
            ->create();

        Asesor::factory(8)
            ->hasAttached($calculoDif, [], 'asignaturas')
            ->afterCreating(function (Asesor $asesor) {
                Horario::factory()
                    ->state([
                        'horaInicio' => '09:00',
                        'disponible' => true,
                        'diaSemanaID' => 1,
                    ])
                    ->recycle($asesor)
                    ->create();
            })
            ->create();

        Asesor::factory(10)
            ->hasAttached($otrasAsignturas->random(3), [], 'asignaturas')
            ->create();

        $this->assertDatabaseCount('asesor', 20);

        $estudiante = Estudiante::factory()->create();
        Sanctum::actingAs($estudiante);

        $response = $this->get('api/v1/asesor/of-asignatura/46?diaSemanaID=1&horaInicio=10:00');

        $response->assertStatus(200);

        $response->assertJsonStructure($this->estructuraRespuestaAsesores);

        $this->assertCount(2, $response->json('data.ideales'));
        $this->assertCount(8, $response->json('data.asignatura'));
        $this->assertCount(10, $response->json('data.carrera'));
        $this->assertCount(0, $response->json('data.otros'));

        $ideales = $response->json('data.ideales');
        $porAsignatura = $response->json('data.asignatura');
        $porCarrera = $response->json('data.carrera');
        $otros = $response->json('data.otros');

        // Los ideales deben tener la calculoDif 46 (Calculo Diferencial)
        foreach ($ideales as $asesor) {
            $this->assertTrue(
                collect($asesor['asignaturas'])->contains('id', 46)
            );
        }

        // Los de calculoDif deben tener la calculoDif 46 (Calculo Diferencial)
        foreach ($porAsignatura as $asesor) {
            $this->assertTrue(
                collect($asesor['asignaturas'])->contains('id', 46)
            );
        }

        // Los de carrera deben pertener a una carrera que tenga la calculoDif 46 (Calculo Diferencial)
        foreach ($porCarrera as $asesor) {
            $carreraAsesor = Carrera::find($asesor['estudiante']['carreraID']);
            $this->assertTrue(
                $carreraAsesor->asignaturas->contains($calculoDif)
            );
        }

        // Los otros no deben tener la calculoDif 46 (Calculo Diferencial)
        foreach ($otros as $asesor) {
            $this->assertFalse(
                collect($asesor['asignaturas'])->contains('id', 46)
            );
        }

        // Revisa si ningun asesor se repite entre los otros grupos
        foreach ($ideales as $asesorIdeal) {
            $this->assertFalse(
                collect($otros)->union($porCarrera)->union($porAsignatura)
                    ->contains('asesor.id', $asesorIdeal['asesor']['id'])
            );
        }

        foreach ($porAsignatura as $asesorPorAsig) {
            $this->assertFalse(
                collect($otros)->union($porCarrera)->union($ideales)
                    ->contains('asesor.id', $asesorPorAsig['asesor']['id'])
            );
        }

        foreach ($porCarrera as $asesorPorCarrera) {
            $this->assertFalse(
                collect($otros)->union($porAsignatura)->union($ideales)
                    ->contains('asesor.id', $asesorPorCarrera['asesor']['id'])
            );
        }

        foreach ($otros as $asesorNoIdeal) {
            $this->assertFalse(
                collect($porCarrera)->union($porAsignatura)->union($ideales)
                    ->contains('asesor.id', $asesorNoIdeal['asesor']['id'])
            );
        }
    }

    public function test_no_se_obtienen_asesores_de_asignatura_no_existente(): void
    {
        $estudiante = Estudiante::factory()->create();
        Sanctum::actingAs($estudiante);

        $response = $this->get('api/v1/asesor/of-calculoDif/999999');
        $response->assertStatus(404);
    }

    public function test_se_obtienen_asesores_con_hora_inicio_y_hora_final(): void
    {
        $calculoDif = Asignatura::find(46);
        $otrasAsignturas = Asignatura::where('id', '!=', 46)->get();

        // Asesor con una hora inicial y final
        Asesor::factory()
            ->hasAttached($calculoDif, [], 'asignaturas')
            ->afterCreating(function (Asesor $asesor) {
                Horario::factory()
                    ->state([
                        'horaInicio' => '10:00',
                        'disponible' => true,
                        'diaSemanaID' => 1,
                    ])
                    ->recycle($asesor)
                    ->create();
                Horario::factory()
                    ->state([
                        'horaInicio' => '11:00',
                        'disponible' => false,
                        'diaSemanaID' => 1,
                    ])
                    ->recycle($asesor)
                    ->create();
            })
            ->create();

        // Asesor con solo hora inicial
        Asesor::factory()
            ->hasAttached($calculoDif, [], 'asignaturas')
            ->afterCreating(function (Asesor $asesor) {
                Horario::factory()
                    ->state([
                        'horaInicio' => '10:00',
                        'disponible' => true,
                        'diaSemanaID' => 1,
                    ])
                    ->recycle($asesor)
                    ->create();
            })
            ->create();

        // Otros asesores sin la hora, pero si tiene la calculoDif
        Asesor::factory(8)
            ->hasAttached($calculoDif, [], 'asignaturas')
            ->afterCreating(function (Asesor $asesor) {
                Horario::factory()
                    ->state([
                        'horaInicio' => '9:00',
                        'disponible' => true,
                        'diaSemanaID' => 1,
                    ])
                    ->recycle($asesor)
                    ->create();
                Horario::factory()
                    ->state([
                        'horaInicio' => '10:00',
                        'disponible' => false,
                        'diaSemanaID' => 1,
                    ])
                    ->recycle($asesor)
                    ->create();
                Horario::factory()
                    ->state([
                        'horaInicio' => '11:00',
                        'disponible' => true,
                        'diaSemanaID' => 1,
                    ])
                    ->recycle($asesor)
                    ->create();
            })
            ->create();

        // Otros asesores
        Asesor::factory(10)
            ->hasAttached($otrasAsignturas->random(3), [], 'asignaturas')
            ->create();

        $this->assertDatabaseCount('asesor', 20);

        Sanctum::actingAs(Estudiante::factory()->create());
        $response = $this->get('api/v1/asesor/of-asignatura/46?diaSemanaID=1&horaInicio=10:00&horaFinal=11:00');

        $response->assertOk();
        $response->assertJsonStructure($this->estructuraRespuestaAsesores);
        $this->assertCount(2, $response->json('data.ideales'));
        $this->assertCount(8, $response->json('data.asignatura'));
        $this->assertCount(10, $response->json('data.carrera'));
        $this->assertCount(0, $response->json('data.otros'));

        $ideales = $response->json('data.ideales');
        $porAsignatura = $response->json('data.asignatura');
        $porCarrera = $response->json('data.carrera');
        $otros = $response->json('data.otros');

        // Los ideales deben tener la calculoDif 46 (Calculo Diferencial)
        foreach ($ideales as $asesor) {
            $this->assertTrue(
                collect($asesor['asignaturas'])->contains('id', 46)
            );
        }

        // Los de calculoDif deben tener la calculoDif 46 (Calculo Diferencial)
        foreach ($porAsignatura as $asesor) {
            $this->assertTrue(
                collect($asesor['asignaturas'])->contains('id', 46)
            );
        }

        // Los de carrera deben pertener a una carrera que tenga la calculoDif 46 (Calculo Diferencial)
        foreach ($porCarrera as $asesor) {
            $carreraAsesor = Carrera::find($asesor['estudiante']['carreraID']);
            $this->assertTrue(
                $carreraAsesor->asignaturas->contains($calculoDif)
            );
        }

        // Los otros no deben tener la calculoDif 46 (Calculo Diferencial)
        foreach ($otros as $asesor) {
            $this->assertFalse(
                collect($asesor['asignaturas'])->contains('id', 46)
            );
        }

        // Revisa si ningun asesor se repite entre los otros grupos
        foreach ($ideales as $asesorIdeal) {
            $this->assertFalse(
                collect($otros)->union($porCarrera)->union($porAsignatura)
                    ->contains('asesor.id', $asesorIdeal['asesor']['id'])
            );
        }

        foreach ($porAsignatura as $asesorPorAsig) {
            $this->assertFalse(
                collect($otros)->union($porCarrera)->union($ideales)
                    ->contains('asesor.id', $asesorPorAsig['asesor']['id'])
            );
        }

        foreach ($porCarrera as $asesorPorCarrera) {
            $this->assertFalse(
                collect($otros)->union($porAsignatura)->union($ideales)
                    ->contains('asesor.id', $asesorPorCarrera['asesor']['id'])
            );
        }

        foreach ($otros as $asesorNoIdeal) {
            $this->assertFalse(
                collect($porCarrera)->union($porAsignatura)->union($ideales)
                    ->contains('asesor.id', $asesorNoIdeal['asesor']['id'])
            );
        }
    }
}
