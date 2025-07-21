<?php

namespace Tests\Feature;

use App\Models\Estudiante;
use App\Notifications\SendPasswordReset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordCodeTest extends TestCase
{
    use RefreshDatabase;
    protected $seed = true;

    /**
     * Prueba si se creó un código para el usuario, y que se mandó
     */
    public function test_generate_password_reset_code(): void
    {
        /** @var Estudiante */
        $estudiante = Estudiante::factory()->state([
            'numeroTelefono' => '2294581498'
        ])->create();

        Notification::fake();

        $response = $this->post('/api/v1/password', [
            'numeroControl' => $estudiante->numeroControl,
            'numeroTelefono' => $estudiante->numeroTelefono,
        ]);

        $response->assertOk();
        Notification::assertSentTo($estudiante, SendPasswordReset::class);

        // ¿Se creó el código?
        $estudiante->refresh();
        $codigo = $estudiante->passwordCode;

        $this->assertNotEmpty($codigo);
        $this->assertEquals(6, strlen($codigo->code));
        $this->assertIsNumeric($codigo->code);
    }
}
