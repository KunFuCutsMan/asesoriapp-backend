<?php

namespace Tests\Feature;

use DateInterval;
use DateTimeImmutable;
use App\Http\Controllers\LoginController;
use App\Models\Estudiante;
use DateTimeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AsesoriaTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    public function test_store_asesoria_como_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create();
        $estudiante->refresh();
        $token = LoginController::creaToken($estudiante);

        $zone = new DateTimeZone('America/Mexico_City');
        $inicio = new DateTimeImmutable('now', $zone);
        $final = $inicio->add(new DateInterval('PT1H'));

        $response = $this
            ->withHeader('Authorization', 'Bearer ' . $token)
            ->post('/api/v1/asesoria/', [
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
