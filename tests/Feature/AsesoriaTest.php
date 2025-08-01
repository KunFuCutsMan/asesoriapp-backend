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

    public function test_asesor_actualiza_asesoria_como_en_proceso(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
            'horaInicial' => now()->subMinutes(2)->format('H:i'),
            'horaFinal' => now()->addMinutes(58)->format('H:i'),
            'asesorID' => $asesor->id,
        ])->recycle($asesor)->create();

        Sanctum::actingAs($asesor->estudiante, ['role:asesor']);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'asesorID' => $asesor->id,
        ]);

        $this->assertEquals(AsesoriaEstado::EN_PROGRESO, $response['estadoAsesoriaID']);
    }

    public function test_asesor_actualiza_asesoria_como_realizada(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'horaInicial' => now()->subMinutes(2)->format('H:i'),
            'horaFinal' => now()->addMinutes(58)->format('H:i'),
            'asesorID' => $asesor->id,
        ])->recycle($asesor)->create();

        Sanctum::actingAs($asesor->estudiante, ['role:asesor']);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'estadoAsesoriaID' => AsesoriaEstado::REALIZADA,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'asesorID' => $asesor->id,
        ]);

        $this->assertEquals(AsesoriaEstado::EN_PROGRESO, $response['estadoAsesoriaID']);
    }

    public function test_asesor_actualiza_asesoria_como_cancelada(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
            'horaInicial' => now()->subMinutes(2)->format('H:i'),
            'horaFinal' => now()->addMinutes(58)->format('H:i'),
            'asesorID' => $asesor->id,
        ])->recycle($asesor)->create();

        Sanctum::actingAs($asesor->estudiante, ['role:asesor']);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'estadoAsesoriaID' => AsesoriaEstado::CANCELADA,
        ]);

        $response->assertSuccessful();
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::CANCELADA,
            'asesorID' => $asesor->id,
        ]);

        $this->assertEquals(AsesoriaEstado::CANCELADA, $response['estadoAsesoriaID']);
    }

    public function test_asesor_no_puede_actualizar_asesoria_pendiente_antes_de_tiempo(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
            'horaInicial' => now()->addHour()->format('H:i'),
            'horaFinal' => now()->addHours(2)->format('H:i'),
            'asesorID' => $asesor->id,
        ])->recycle($asesor)->create();

        Sanctum::actingAs($asesor->estudiante, ['role:asesor']);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'asesorID' => $asesor->id,
        ]);
    }

    public function test_asesor_no_puede_poner_en_progreso_asesoria_terminada(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::REALIZADA,
            'horaInicial' => now()->subMinutes(2)->format('H:i'),
            'horaFinal' => now()->addMinutes(58)->format('H:i'),
            'asesorID' => $asesor->id,
        ])->recycle($asesor)->create();

        Sanctum::actingAs($asesor->estudiante, ['role:asesor']);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'asesorID' => $asesor->id,
        ]);
    }

    public function test_asesor_no_puede_poner_en_progreso_asesoria_cancelada(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::CANCELADA,
            'horaInicial' => now()->subMinutes(2)->format('H:i'),
            'horaFinal' => now()->addMinutes(58)->format('H:i'),
            'asesorID' => $asesor->id,
        ])->recycle($asesor)->create();

        Sanctum::actingAs($asesor->estudiante, ['role:asesor']);

        $response = $this->put('/api/v1/asesoria/' . $asesoria->id, [
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ]);

        $response->assertStatus(400);
        $this->assertDatabaseMissing('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'asesorID' => $asesor->id,
        ]);
    }
}
