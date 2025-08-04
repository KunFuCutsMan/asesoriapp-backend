<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use App\Models\Asesor;
use DateInterval;
use DateTimeImmutable;
use App\Models\Estudiante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AsesoriaTest extends TestCase
{
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
            'estadoAsesoriaID'
        ]);

        $this->assertEquals($estudiante->id, $response['estudianteID']);
        $this->assertEquals(AsesoriaEstado::PENDIENTE, $response['estadoAsesoriaID']);
    }

    public function test_admin_asigna_asesoria_a_asesor(): void
    {
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
        ])->create();

        $asesor = Asesor::factory()->create();

        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin->asesor->estudiante, ['role:asesor', 'role:admin']);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'asesorID' => $asesor->id,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'asesorID' => $asesor->id,
        ]);
    }

    public function test_admin_to_puede_cambiar_asesoria_en_proceso(): void
    {
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ])->create();

        $asesor = Asesor::factory()->create();

        $admin = Admin::factory()->create();
        Sanctum::actingAs($admin->asesor->estudiante, ['role:asesor', 'role:admin']);

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
