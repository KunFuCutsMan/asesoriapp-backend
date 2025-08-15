<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Asesor;
use App\Models\Asesoria;
use App\Models\Estudiante;
use App\Models\AsesoriaEstado;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CancelarAsesoriaTest extends TestCase
{
    public function test_estudiante_cancela_asesoria(): void
    {
        $estudiante = Estudiante::factory()->create();
        $asesorias = Asesoria::factory()
            ->count(2)
            ->state(new Sequence(
                ['estadoAsesoriaID' => AsesoriaEstado::PENDIENTE],
                ['estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO],
            ))
            ->recycle($estudiante)->create();

        Sanctum::actingAs($estudiante);
        foreach ($asesorias as $asesoria) {
            $response = $this->delete('api/v1/asesorias/' . $asesoria->id);
            $response->assertOk();
            $this->assertDatabaseHas('asesoria', [
                'id' => $asesoria->id,
                'estadoAsesoriaID' => AsesoriaEstado::CANCELADA,
            ]);
        }
    }

    public function test_asesor_cancela_asesoria(): void
    {
        $asesor = Asesor::factory()->create();
        $asesorias = Asesoria::factory()
            ->count(2)
            ->state(new Sequence(
                ['estadoAsesoriaID' => AsesoriaEstado::PENDIENTE],
                ['estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO],
            ))
            ->recycle($asesor)->create();

        foreach ($asesorias as $asesoria) {
            $asesoria->asesor()->associate($asesor);
            $asesoria->push();
        }

        Sanctum::actingAs($asesor->estudiante);
        foreach ($asesorias as $asesoria) {
            $response = $this->delete('api/v1/asesorias/' . $asesoria->id);
            $response->assertOk();
            $this->assertDatabaseHas('asesoria', [
                'id' => $asesoria->id,
                'estadoAsesoriaID' => AsesoriaEstado::CANCELADA,
            ]);
        }
    }

    public function test_admin_cancela_asesoria(): void
    {
        $admin = Admin::factory()->create();
        $asesorias = Asesoria::factory()
            ->count(2)
            ->state(new Sequence(
                ['estadoAsesoriaID' => AsesoriaEstado::PENDIENTE],
                ['estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO],
            ))
            ->create();

        Sanctum::actingAs($admin->asesor->estudiante);
        foreach ($asesorias as $asesoria) {
            $response = $this->delete('api/v1/asesorias/' . $asesoria->id);
            $response->assertOk();
            $this->assertDatabaseHas('asesoria', [
                'id' => $asesoria->id,
                'estadoAsesoriaID' => AsesoriaEstado::CANCELADA,
            ]);
        }
    }

    public function test_estudiante_no_puede_cancelar_asesoria_ajena(): void
    {
        $estudiante = Estudiante::factory()->create();
        $asesorias = Asesoria::factory()
            ->count(2)
            ->state(new Sequence(
                ['estadoAsesoriaID' => AsesoriaEstado::PENDIENTE],
                ['estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO],
            ))
            ->create();

        Sanctum::actingAs($estudiante);
        foreach ($asesorias as $asesoria) {
            $response = $this->delete('api/v1/asesorias/' . $asesoria->id);
            $response->assertStatus(403);
            $this->assertDatabaseHas('asesoria', [
                'id' => $asesoria->id,
                'estadoAsesoriaID' => $asesoria->estadoAsesoriaID,
            ]);
        }
    }

    public function test_asesor_no_puede_cancelar_asesoria_ajena(): void
    {
        $asesor = Asesor::factory()->create();
        $asesorias = Asesoria::factory()
            ->count(2)
            ->state(new Sequence(
                ['estadoAsesoriaID' => AsesoriaEstado::PENDIENTE],
                ['estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO],
            ))
            ->create();

        Sanctum::actingAs($asesor->estudiante);
        foreach ($asesorias as $asesoria) {
            $response = $this->delete('api/v1/asesorias/' . $asesoria->id);
            $response->assertStatus(403);
            $this->assertDatabaseHas('asesoria', [
                'id' => $asesoria->id,
                'estadoAsesoriaID' => $asesoria->estadoAsesoriaID,
            ]);
        }
    }

    public function test_estudiante_no_puede_cancelar_asesoria_realizada(): void
    {
        $estudiante = Estudiante::factory()->create();
        $asesoria = Asesoria::factory()
            ->state(['estadoAsesoriaID' => AsesoriaEstado::REALIZADA])
            ->recycle($estudiante)
            ->create();

        Sanctum::actingAs($estudiante);
        $response = $this->delete('api/v1/asesorias/' . $asesoria->id);
        $response->assertStatus(403);
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::REALIZADA,
        ]);
    }

    public function test_asesor_no_puede_cancelar_asesoria_realizada(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()
            ->state(['estadoAsesoriaID' => AsesoriaEstado::REALIZADA])
            ->recycle($asesor)
            ->create();

        Sanctum::actingAs($asesor->estudiante);
        $response = $this->delete('api/v1/asesorias/' . $asesoria->id);
        $response->assertStatus(403);
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::REALIZADA,
        ]);
    }

    public function test_admin_no_puede_cancelar_asesoria_realizada(): void
    {
        $admin = Admin::factory()->create();
        $asesoria = Asesoria::factory()
            ->state(['estadoAsesoriaID' => AsesoriaEstado::REALIZADA])
            ->create();

        Sanctum::actingAs($admin->asesor->estudiante);
        $response = $this->delete('api/v1/asesorias/' . $asesoria->id);
        $response->assertStatus(403);
        $this->assertDatabaseHas('asesoria', [
            'id' => $asesoria->id,
            'estadoAsesoriaID' => AsesoriaEstado::REALIZADA,
        ]);
    }
}
