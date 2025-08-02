<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Asesor;
use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use Laravel\Sanctum\Sanctum;

class AsesoriaEditablePorAsesorTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

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

        Sanctum::actingAs($asesor->estudiante);

        $response = $this->delete("/api/v1/asesoria/$asesoria->id");

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
