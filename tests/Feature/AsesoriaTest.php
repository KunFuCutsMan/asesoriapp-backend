<?php

namespace Tests\Feature;

use App\Http\Controllers\LoginController;
use App\Models\Estudiante;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsesoriaTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_store_asesoria_como_estudiante(): void
    {
        $estudiante = Estudiante::factory()->create();
        $token = LoginController::creaToken($estudiante);

        $inicio = new DateTimeImmutable('now');
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

        $response->assertOk();
    }
}
