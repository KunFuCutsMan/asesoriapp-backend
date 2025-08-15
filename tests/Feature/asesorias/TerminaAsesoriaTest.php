<?php

namespace Tests\Feature;

use App\Models\Asesor;
use App\Models\Asesoria;
use App\Models\AsesoriaEstado;
use App\Models\Estudiante;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Clase que prueba la funcionalidad para terminar una asesoría.
 * 
 * # Proceso para terminar una asesoría
 * 
 * 1. Cuando se crea una asesoria, este tendrá un código de seguridad para terminar la asesoría.
 * Para cualquier intento de terminar la asesoría, se deberá proporcionar este código, pasando su
 * estado de `EN_PROCESO` a `REALIZADA`.
 * 
 * 2. El codigo de seguridad se presenta al asesor cuando la asesoria se encuentra en estado `EN_PROCESO`.
 *      - A) El asesor solo puede acceder al codigo de seguridad de las asesorias que le pertenezcan.
 * 
 * 3. El codigo de seguridad deberá ser ingresado por el asesorado para marcar la asesoria como `REALIZADA`.
 * La única manera de obtener este código es a través del asesor.
 *     - A) El estudiante solo puede terminar asesorias que le pertenezcan.
 *     - B) El estudiante solo puede terminar una asesoria si su estado es `EN_PROCESO`
 *     - C) El estudiante solo puede terminar una asesoria si la hora actual es mayor o igual a la hora final
 *     - D) El estudiante solo puede terminar una asesoria si el codigo de seguridad es correcto.
 * 
 * ## Casos de uso
 * 
 * 1. En cualquier momento, el asesor o asesorado pueden cancelar la asesoria, pasando su estado a `CANCELADA`,
 * sin la necesidad del codigo de seguridad.
 * 
 * 2. No se puede terminar una asesoria si el estado es `PENDIENTE` o `CANCELADA`.
 */

class TerminaAsesoriaTest extends TestCase
{
    /**
     * El codigo de seguridad se presenta al asesor cuando la asesoria se encuentra en estado `EN_PROCESO`.
     * 
     * El asesor asociado a la asesoria obtiene el codigo de seguridad
     * en cualquier momento.
     */
    public function test_asesor_obtiene_codigo_de_seguridad(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ])->recycle($asesor)->create();

        Sanctum::actingAs($asesor->estudiante);
        $response = $this->get(
            '/api/v1/asesorias/' . $asesoria->id . '/codigo-seguridad'
        );

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'codigo',
                'asesorID',
                'estudianteID',
                'estado' => [
                    'id',
                    'estado',
                ]
            ]
        ]);

        $response->assertJsonPath('data.id', $asesoria->id);
        $response->assertJsonPath('data.codigo', $asesoria->codigoSeguridad);
        $response->assertJsonPath('data.asesorID', $asesor->id);
        $response->assertJsonPath('data.estudianteID', $asesoria->estudianteID);
        $response->assertJsonPath('data.estado.id', AsesoriaEstado::EN_PROGRESO);
    }

    /**
     * El codigo de seguridad deberá ser ingresado por el asesorado para marcar la asesoria como `REALIZADA`.
     * 
     * El estudiante termina la asesoria después de que esta haya terminado.
     * El estudiante debe proporcionar el codigo de seguridad.
     */
    public function test_estudiante_termina_asesoria_despues_que_termine(): void
    {
        /** @var Estudiante */ $estudiante = Estudiante::factory()->create();
        /** @var Asesoria */ $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'horaInicial' => now()->subMinutes(60)->format('H:i'),
            'horaFinal' => now()->format('H:i'),
        ])->recycle($estudiante)->create();

        $this->assertEquals(AsesoriaEstado::EN_PROGRESO, $asesoria->estadoAsesoriaID);
        $this->assertEquals($asesoria->estudiante->id, $estudiante->id);

        /** El estudiante preguntó por el codigo */
        $this->travel(1)->minutes();
        $codigo = $asesoria->codigoSeguridad;

        Sanctum::actingAs($estudiante);
        $response = $this->post(
            '/api/v1/asesorias/' . $asesoria->id . '/terminar',
            [
                'codigo' => $codigo,
            ]
        );

        $response->assertOk();
        $response->assertJsonIsObject();
        $response->assertJsonStructure([
            'data' => [
                'id',
                'diaAsesoria',
                'horaInicial',
                'horaFinal',
                'carrera' => [
                    'id',
                    'nombre'
                ],
                'asignatura' => [
                    'id',
                    'nombre'
                ],
                'estadoAsesoria' => [
                    'id',
                    'estado',
                ],
                'estudiante',
                'asesor' => [
                    'id',
                    'estudianteID',
                ],
            ]
        ]);

        $asesoria->refresh();

        $data = $response->json('data');
        $this->assertEquals($estudiante->id, $data['estudiante']['id']);
        $this->assertEquals(AsesoriaEstado::REALIZADA, $data['estadoAsesoria']['id']);
        $this->assertNotNull($data['asesor']);
    }

    /**
     * =================================================================
     * # Casos de uso
     * =================================================================
     */

    /**
     * **2A:** El asesor solo puede acceder al codigo de seguridad de las asesorias que le pertenezcan.
     */

    public function test_caso_uso_asesor_intenta_acceder_codigo_de_asesoria_que_no_le_pertenece(): void
    {
        $asesor = Asesor::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
        ])->create();

        Sanctum::actingAs($asesor->estudiante);
        $response = $this->get(
            '/api/v1/asesorias/' . $asesoria->id . '/codigo-seguridad'
        );

        $response->assertStatus(403);
    }

    /**
     * **3A:** El estudiante solo puede terminar asesorias que le pertenezcan.
     */
    public function test_caso_uso_estudiante_intenta_terminar_asesoria_que_no_le_pertenece(): void
    {
        /** @var Estudiante */ $estudiante = Estudiante::factory()->create();
        /** @var Asesoria */ $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'horaInicial' => now()->subMinutes(60)->format('H:i'),
            'horaFinal' => now()->format('H:i'),
        ])->create();

        $this->assertEquals(AsesoriaEstado::EN_PROGRESO, $asesoria->estadoAsesoriaID);
        $this->assertNotEquals($asesoria->estudiante->id, $estudiante->id);

        /** El estudiante preguntó por el codigo */
        $this->travel(1)->minutes();
        $codigo = $asesoria->codigoSeguridad;

        Sanctum::actingAs($estudiante);
        $response = $this->post(
            '/api/v1/asesorias/' . $asesoria->id . '/terminar',
            [
                'codigo' => $codigo,
            ]
        );

        $response->assertStatus(403);
    }

    /**
     * **3B:** El estudiante solo puede terminar una asesoria si su estado es `EN_PROCESO`
     */
    public function test_caso_uso_estudiante_intenta_terminar_asesoria_pendiente_o_cancelada(): void
    {
        $estudiante = Estudiante::factory()->create();
        $asesoriaPendiente = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::PENDIENTE,
            'horaInicial' => now()->addMinutes(60)->format('H:i'),
            'horaFinal' => now()->addMinutes(120)->format('H:i'),
        ])->recycle($estudiante)->create();

        $this->assertEquals(AsesoriaEstado::PENDIENTE, $asesoriaPendiente->estadoAsesoriaID);
        $this->assertEquals($asesoriaPendiente->estudiante->id, $estudiante->id);

        /** El estudiante obtuvo el codigo, de alguna manera */
        $codigo = $asesoriaPendiente->codigoSeguridad;

        Sanctum::actingAs($estudiante);
        $this->post(
            '/api/v1/asesorias/' . $asesoriaPendiente->id . '/terminar',
            [
                'codigo' => $codigo,
            ]
        )->assertStatus(403);

        // Ahora intenta una asesoria cancelada
        $asesoriaCancelada = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::CANCELADA,
            'horaInicial' => now()->subMinutes(60)->format('H:i'),
            'horaFinal' => now()->format('H:i'),
        ])->recycle($estudiante)->create();

        $this->post(
            '/api/v1/asesorias/' . $asesoriaCancelada->id . '/terminar',
            [
                'codigo' => $codigo,
            ]
        )->assertStatus(403);
    }

    /**
     * **3C:** El estudiante solo puede terminar una asesoria si la hora actual es mayor o igual a la hora final
     */
    public function test_caso_uso_estudiante_intenta_terminar_asesoria_antes_de_hora_final(): void
    {
        $estudiante = Estudiante::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'horaInicial' => now()->subMinutes(60)->format('H:i'),
            'horaFinal' => now()->addMinutes(30)->format('H:i'), // Hora final en el futuro
        ])->recycle($estudiante)->create();

        $codigo = $asesoria->codigoSeguridad;

        Sanctum::actingAs($estudiante);
        $this->post(
            '/api/v1/asesorias/' . $asesoria->id . '/terminar',
            [
                'codigo' => $codigo,
            ]
        )->assertStatus(403);
    }

    /**
     * **3D:** El estudiante solo puede terminar una asesoria si el codigo de seguridad es correcto.
     */
    public function test_caso_uso_estudiante_intenta_terminar_asesoria_con_codigo_incorrecto(): void
    {
        $estudiante = Estudiante::factory()->create();
        $asesoria = Asesoria::factory()->state([
            'estadoAsesoriaID' => AsesoriaEstado::EN_PROGRESO,
            'codigoSeguridad' => '123456', // Codigo correcto
            'horaInicial' => now()->subMinutes(60)->format('H:i'),
            'horaFinal' => now()->format('H:i'),
        ])->recycle($estudiante)->create();

        $codigoIncorrecto = '101001'; // Codigo incorrecto
        Sanctum::actingAs($estudiante);
        $response = $this->post(
            '/api/v1/asesorias/' . $asesoria->id . '/terminar',
            [
                'codigo' => $codigoIncorrecto,
            ]
        );

        $response->assertStatus(400);
    }
}
