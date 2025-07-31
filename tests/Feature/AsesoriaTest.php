<?php

namespace Tests\Feature;

use App\Models\Asesoria;
use DateInterval;
use DateTimeImmutable;
use App\Models\Estudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsesoriaTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    public function test_store_asesoria_como_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create();
        $estudiante->refresh();

        Sanctum::actingAs($estudiante);

        $inicio = new DateTimeImmutable('now');
        $final = $inicio->add(new DateInterval('PT1H'));

        $response = $this->post('/api/v1/asesoria/', [
                'carreraID' => 1,
                'asignaturaID' => 1,
                'diaAsesoria' => $inicio->format("d-m-y"),
                'horaInicial' => $inicio->format("H:i"),
                'horaFinal' => $final->format("H:i"),
            ]);

        $response->assertSuccessful();
        $this->assertDatabaseCount('asesoria', 1);

        $response->assertJsonStructure([
            'id',
            'diaAsesoria',
            'horaInicial',
            'horaFinal',
            'estudianteID',
            'estadoAsesoria'
        ]);

        $this->assertEquals($estudiante->id, $response['estudianteID']);
        $this->assertEquals(0, $response['estadoAsesoria']);
    }
}
