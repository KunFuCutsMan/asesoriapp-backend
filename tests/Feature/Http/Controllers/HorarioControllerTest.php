<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Asesor;
use App\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

use DateTimeImmutable;

class HorarioControllerTest extends TestCase
{
    public function test_asesor_publica_horario_para_lunes(): void
    {
        /** @var Asesor */
        $asesor = Asesor::factory()->create();

        // Estará ocupado de 8 a 13
        $horas = Horario::factory()
            ->count(5)
            ->state(new Sequence(
                function (Sequence $sequence) {
                    $offset = $sequence->index + 8;
                    $hora = DateTimeImmutable::createFromFormat('H:i', sprintf('%02d:00', $offset));
                    return [
                        'diaSemanaID' => 1,
                        'horaInicio' => $hora,
                        'disponible' => false,
                    ];
                }
            ))->make();

        Sanctum::actingAs($asesor->estudiante);
        $response = $this->patch("/api/v1/asesor/" . $asesor->id . "/horario", [
            'horas' => $horas->map(
                fn(Horario $horario) => [
                    'hora' => $horario->horaInicio,
                    'disponible' => false,
                    'diaSemanaID' => $horario->diaSemanaID,
                ]
            )
        ]);

        $response->assertSuccessful();

        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                "*" => [
                    'id',
                    'horaInicio',
                    'disponible',
                    'diaSemana' => [
                        'id',
                        'nombre'
                    ],
                    'asesor' => [
                        'id',
                        'estudianteID'
                    ],
                ]
            ]
        ]);

        $data = $response["data"];
        foreach ($data as $horario) {
            $this->assertEquals(false, $horario['disponible']);
            $this->assertEquals($asesor->id, $horario['asesor']['id']);
            $this->assertEquals($asesor->estudiante->id, $horario['asesor']['estudianteID']);
            $this->assertEquals(1, $horario['diaSemana']['id']);
        }
    }

    public function test_asesor_publica_horario_de_semana(): void
    {
        /** @var Asesor */
        $asesor = Asesor::factory()->create();

        // Estará ocupado de 8 a 13
        $horas = Horario::factory()
            ->count(20)
            ->state([
                'disponible' => false,
            ])->make();

        Sanctum::actingAs($asesor->estudiante);
        $response = $this->patch("/api/v1/asesor/" . $asesor->id . "/horario", [
            'horas' => $horas->map(
                fn(Horario $horario) => [
                    'hora' => $horario->horaInicio,
                    'disponible' => false,
                    'diaSemanaID' => $horario->diaSemanaID,
                ]
            )
        ]);

        $response->assertSuccessful();

        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                "*" => [
                    'id',
                    'horaInicio',
                    'disponible',
                    'diaSemana' => [
                        'id',
                        'nombre'
                    ],
                    'asesor' => [
                        'id',
                        'estudianteID'
                    ],
                ]
            ]
        ]);

        $data = $response["data"];
        foreach ($data as $horario) {
            $this->assertEquals(false, $horario['disponible']);
            $this->assertEquals($asesor->id, $horario['asesor']['id']);
            $this->assertEquals($asesor->estudiante->id, $horario['asesor']['estudianteID']);
        }
    }

    public function test_asesor_obtiene_su_horario(): void
    {
        $asesor = Asesor::factory()->create();
        Horario::factory()
            ->count(20)
            ->state(new Sequence(fn(Sequence $sequence) => [
                'disponible' => $sequence->index % 2 == 0
            ]))
            ->recycle($asesor)
            ->create();

        Sanctum::actingAs($asesor->estudiante);
        $response = $this->get("api/v1/asesor/" . $asesor->id . "/horario");

        $response->assertSuccessful();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'horaInicio',
                    'disponible',
                    'diaSemana' => [
                        'id',
                        'nombre'
                    ],
                    'asesor' => [
                        'id',
                        'estudianteID'
                    ],
                ]
            ]
        ]);

        $response->assertJsonCount(20, 'data');
        $data = $response['data'];

        foreach ($data as $horario) {
            $this->assertEquals($asesor->id, $horario['asesor']['id']);
            $this->assertStringMatchesFormat("%d:%d:%d", $horario['horaInicio']);

            [$hora, $minuto, $segundo] = explode(':', $horario['horaInicio']);
            $this->assertStringMatchesFormat("%d", $hora);
            $this->assertEquals("00", $minuto);
            $this->assertEquals("00", $segundo);
        }

        $horasOcupados = collect($data)->reduce(function (int $count, array $horario) {
            $count += $horario['disponible'] == true ? 1 : 0;
            return $count;
        }, 0);
        $this->assertEquals(10, $horasOcupados);
    }

    public function test_asesor_obtiene_horario_vacio(): void
    {
        $asesor = Asesor::factory()->create();

        Sanctum::actingAs($asesor->estudiante);
        $response = $this->get("api/v1/asesor/" . $asesor->id . "/horario");

        $response->assertSuccessful();
        $response->assertJsonIsObject();
        $response->assertJsonCount(0, 'data');
    }
}
